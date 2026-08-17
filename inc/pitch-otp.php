<?php
/**
 * Server-side 2Factor.in OTP verification for the Pitch workflow.
 *
 * @package NuVentures
 */

defined('ABSPATH') || exit;

const NUVENTURES_PITCH_OTP_TTL             = 1800;
const NUVENTURES_PITCH_OTP_RESEND_COOLDOWN = 45;
const NUVENTURES_PITCH_OTP_RATE_WINDOW     = 900;
const NUVENTURES_PITCH_OTP_RATE_LIMIT      = 5;
const NUVENTURES_PITCH_OTP_TEMPLATE        = 'IXDEAS OTP';

/** Normalize an Indian mobile number to ten digits. */
function nuventures_pitch_normalize_mobile($mobile) {
    $digits = preg_replace('/\D+/', '', (string) $mobile);

    if (12 === strlen($digits) && 0 === strpos($digits, '91')) {
        $digits = substr($digits, 2);
    } elseif (11 === strlen($digits) && '0' === $digits[0]) {
        $digits = substr($digits, 1);
    }

    return preg_match('/^[6-9]\d{9}$/', $digits) ? $digits : '';
}

/** Hash opaque verification tokens before using them as transient keys. */
function nuventures_pitch_otp_transient_key($token) {
    return 'nuv_otp_' . hash_hmac('sha256', (string) $token, wp_salt('auth'));
}

/** Bind OTP state to the signed pitch session without storing the raw token. */
function nuventures_pitch_otp_session_hash($pitch_session) {
    return hash_hmac('sha256', (string) $pitch_session, wp_salt('secure_auth'));
}

/** Build a cooldown key bound to both pitch session and mobile number. */
function nuventures_pitch_otp_cooldown_key($pitch_session, $mobile) {
    return 'nuv_otp_cool_' . hash_hmac('sha256', $pitch_session . '|' . $mobile, wp_salt('nonce'));
}

/** Return the direct client IP used only for conservative rate limiting. */
function nuventures_pitch_otp_client_ip() {
    return isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : 'unknown';
}

/** Consume one request from a rolling transient-backed rate-limit bucket. */
function nuventures_pitch_otp_consume_rate_limit($scope, $identity) {
    $key      = 'nuv_otp_rate_' . hash_hmac('sha256', $scope . '|' . $identity, wp_salt('nonce'));
    $now      = time();
    $attempts = get_transient($key);
    $attempts = is_array($attempts) ? array_values(array_filter($attempts, static function ($timestamp) use ($now) {
        return (int) $timestamp > $now - NUVENTURES_PITCH_OTP_RATE_WINDOW;
    })) : array();

    if (count($attempts) >= NUVENTURES_PITCH_OTP_RATE_LIMIT) {
        return false;
    }

    $attempts[] = $now;
    set_transient($key, $attempts, NUVENTURES_PITCH_OTP_RATE_WINDOW);
    return true;
}

/** Return a generic error while optionally recording a safe local diagnostic. */
function nuventures_pitch_otp_error($message, $status = 400, $diagnostic = '') {
    if ($diagnostic && defined('WP_DEBUG') && WP_DEBUG) {
        error_log('NuVentures OTP: ' . sanitize_text_field($diagnostic));
    }
    wp_send_json_error(array('message' => $message), $status);
}

/** Call a 2Factor endpoint without exposing its response to the browser. */
function nuventures_pitch_2factor_request($path) {
    if (!defined('NUVENTURES_2FACTOR_API_KEY') || !is_string(NUVENTURES_2FACTOR_API_KEY) || '' === trim(NUVENTURES_2FACTOR_API_KEY)) {
        return new WP_Error('not_configured', 'OTP service is not configured.');
    }

    $url      = 'https://2factor.in/API/V1/' . rawurlencode(trim(NUVENTURES_2FACTOR_API_KEY)) . '/' . ltrim($path, '/');
    $response = wp_remote_get($url, array('timeout' => 20));
    if (is_wp_error($response)) {
        return $response;
    }

    $http_status = (int) wp_remote_retrieve_response_code($response);
    $body        = json_decode(wp_remote_retrieve_body($response), true);
    if ($http_status < 200 || $http_status >= 300 || !is_array($body)) {
        return new WP_Error('upstream_failure', '2Factor HTTP status ' . $http_status);
    }

    return $body;
}

/** Send or resend an OTP. */
function nuventures_pitch_send_otp() {
    check_ajax_referer('nuventures_pitch_otp', 'nonce');

    $pitch_session = isset($_POST['pitch_session']) ? sanitize_text_field(wp_unslash($_POST['pitch_session'])) : '';
    $mobile        = nuventures_pitch_normalize_mobile(isset($_POST['mobile']) ? wp_unslash($_POST['mobile']) : '');
    $token         = isset($_POST['verification_token']) ? sanitize_text_field(wp_unslash($_POST['verification_token'])) : '';

    if (!nuventures_verify_pitch_session_token($pitch_session)) {
        nuventures_pitch_otp_error(__('Your pitch session has expired. Please close the form and start again.', 'nuventures'), 403);
    }
    if ('' === $mobile) {
        nuventures_pitch_otp_error(__('Please enter a valid Indian mobile number.', 'nuventures'));
    }

    $cooldown_key  = nuventures_pitch_otp_cooldown_key($pitch_session, $mobile);
    $last_sent_at  = (int) get_transient($cooldown_key);
    $cooldown_left = NUVENTURES_PITCH_OTP_RESEND_COOLDOWN - (time() - $last_sent_at);
    if ($last_sent_at && $cooldown_left > 0) {
        nuventures_pitch_otp_error(sprintf(__('Please wait %d seconds before requesting another code.', 'nuventures'), $cooldown_left), 429);
    }

    $state = array();
    if ('' !== $token) {
        $state = get_transient(nuventures_pitch_otp_transient_key($token));
        if (
            !is_array($state) ||
            empty($state['pitch_session_hash']) ||
            empty($state['mobile']) ||
            !hash_equals((string) $state['pitch_session_hash'], nuventures_pitch_otp_session_hash($pitch_session)) ||
            $mobile !== $state['mobile']
        ) {
            nuventures_pitch_otp_error(__('Your verification session has expired. Please request a new code.', 'nuventures'), 403);
        }
        $remaining = NUVENTURES_PITCH_OTP_RESEND_COOLDOWN - (time() - (int) $state['last_sent_at']);
        if ($remaining > 0) {
            nuventures_pitch_otp_error(sprintf(__('Please wait %d seconds before requesting another code.', 'nuventures'), $remaining), 429);
        }
    } else {
        $token = wp_generate_password(48, false, false);
    }

    if (!nuventures_pitch_otp_consume_rate_limit('mobile', $mobile) || !nuventures_pitch_otp_consume_rate_limit('ip', nuventures_pitch_otp_client_ip())) {
        nuventures_pitch_otp_error(__('Too many OTP requests. Please try again shortly.', 'nuventures'), 429);
    }

    $response = nuventures_pitch_2factor_request(
        'SMS/' . rawurlencode('+91' . $mobile) . '/AUTOGEN/' . rawurlencode(NUVENTURES_PITCH_OTP_TEMPLATE)
    );
    if (is_wp_error($response) || empty($response['Status']) || 'Success' !== $response['Status'] || empty($response['Details'])) {
        $diagnostic = is_wp_error($response) ? $response->get_error_message() : '2Factor send returned a non-success result.';
        nuventures_pitch_otp_error(__("We couldn't send a verification code right now. Please try again.", 'nuventures'), 502, $diagnostic);
    }

    $now   = time();
    $state = array(
        'mobile'             => $mobile,
        'session_id'         => sanitize_text_field((string) $response['Details']),
        'verified'           => false,
        'created_at'         => !empty($state['created_at']) ? (int) $state['created_at'] : $now,
        'last_sent_at'       => $now,
        'verified_at'        => 0,
        'pitch_session_hash' => nuventures_pitch_otp_session_hash($pitch_session),
    );
    set_transient(nuventures_pitch_otp_transient_key($token), $state, NUVENTURES_PITCH_OTP_TTL);
    set_transient($cooldown_key, $now, NUVENTURES_PITCH_OTP_RESEND_COOLDOWN);

    wp_send_json_success(array(
        'message'            => __("We've sent a verification code to your mobile.", 'nuventures'),
        'verification_token' => $token,
        'cooldown'           => NUVENTURES_PITCH_OTP_RESEND_COOLDOWN,
    ));
}
add_action('wp_ajax_nopriv_nuventures_pitch_send_otp', 'nuventures_pitch_send_otp');
add_action('wp_ajax_nuventures_pitch_send_otp', 'nuventures_pitch_send_otp');

/** Verify an entered OTP against the server-held 2Factor session ID. */
function nuventures_pitch_verify_otp() {
    check_ajax_referer('nuventures_pitch_otp', 'nonce');

    $pitch_session = isset($_POST['pitch_session']) ? sanitize_text_field(wp_unslash($_POST['pitch_session'])) : '';
    $token         = isset($_POST['verification_token']) ? sanitize_text_field(wp_unslash($_POST['verification_token'])) : '';
    $otp           = isset($_POST['otp']) ? preg_replace('/\D+/', '', wp_unslash($_POST['otp'])) : '';

    if (!nuventures_verify_pitch_session_token($pitch_session)) {
        nuventures_pitch_otp_error(__('Your pitch session has expired. Please close the form and start again.', 'nuventures'), 403);
    }
    if (!preg_match('/^\d{6}$/', $otp) || '' === $token) {
        nuventures_pitch_otp_error(__("That code doesn't look right. Please try again.", 'nuventures'));
    }

    $key   = nuventures_pitch_otp_transient_key($token);
    $state = get_transient($key);
    if (!is_array($state) || empty($state['session_id']) || empty($state['pitch_session_hash']) || !hash_equals((string) $state['pitch_session_hash'], nuventures_pitch_otp_session_hash($pitch_session))) {
        nuventures_pitch_otp_error(__('This code has expired. Please request a new one.', 'nuventures'), 410);
    }

    if (!nuventures_pitch_otp_consume_rate_limit('verify', $token)) {
        nuventures_pitch_otp_error(__('Too many verification attempts. Please request a new code shortly.', 'nuventures'), 429);
    }

    $response = nuventures_pitch_2factor_request('SMS/VERIFY/' . rawurlencode($state['session_id']) . '/' . rawurlencode($otp));
    if (is_wp_error($response)) {
        nuventures_pitch_otp_error(__("We couldn't verify the code right now. Please try again.", 'nuventures'), 502, $response->get_error_message());
    }
    if (empty($response['Status']) || 'Success' !== $response['Status']) {
        $details = isset($response['Details']) ? strtolower((string) $response['Details']) : '';
        $expired = false !== strpos($details, 'expire');
        nuventures_pitch_otp_error($expired ? __('This code has expired. Please request a new one.', 'nuventures') : __("That code doesn't look right. Please try again.", 'nuventures'), $expired ? 410 : 400);
    }

    $state['verified']    = true;
    $state['verified_at'] = time();
    set_transient($key, $state, NUVENTURES_PITCH_OTP_TTL);
    wp_send_json_success(array('message' => __('Mobile number verified.', 'nuventures')));
}
add_action('wp_ajax_nopriv_nuventures_pitch_verify_otp', 'nuventures_pitch_verify_otp');
add_action('wp_ajax_nuventures_pitch_verify_otp', 'nuventures_pitch_verify_otp');

/** Validate final-submission OTP state. */
function nuventures_pitch_get_verified_otp_state($token, $pitch_session, $mobile) {
    $normalized_mobile = nuventures_pitch_normalize_mobile($mobile);
    $state             = $token ? get_transient(nuventures_pitch_otp_transient_key($token)) : false;

    if (!is_array($state) || empty($state['verified']) || empty($state['pitch_session_hash']) || empty($state['mobile']) || !$normalized_mobile) {
        return false;
    }

    return hash_equals((string) $state['pitch_session_hash'], nuventures_pitch_otp_session_hash($pitch_session))
        && hash_equals((string) $state['mobile'], $normalized_mobile)
        ? $state
        : false;
}
