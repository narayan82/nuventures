<?php
/**
 * Server-side pitch-deck extraction through the OpenAI Responses API.
 *
 * OPENAI_API_KEY, NUVENTURES_OPENAI_PROMPT_ID, and
 * NUVENTURES_OPENAI_PROMPT_VERSION must be defined outside the theme.
 *
 * @package NuVentures
 */

defined('ABSPATH') || exit;

const NUVENTURES_PITCH_MAX_FILE_SIZE = 10485760;
const NUVENTURES_PITCH_SESSION_TTL   = 3600;

/**
 * Create a short-lived, stateless token for the currently rendered pitch flow.
 *
 * @return string
 */
function nuventures_create_pitch_session_token() {
    $issued_at = time();
    $payload   = array(
        'iat'   => $issued_at,
        'exp'   => $issued_at + NUVENTURES_PITCH_SESSION_TTL,
        'nonce' => wp_generate_password(24, false, false),
        'ua'    => hash_hmac(
            'sha256',
            isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '',
            wp_salt('nonce')
        ),
    );
    $encoded   = nuventures_pitch_base64url_encode(wp_json_encode($payload));
    $signature = hash_hmac('sha256', $encoded, wp_salt('nonce'), true);

    return $encoded . '.' . nuventures_pitch_base64url_encode($signature);
}

/**
 * Register the public route. Authorization is enforced by the signed pitch token.
 */
function nuventures_register_pitch_api_route() {
    register_rest_route(
        'nuventures/v1',
        '/analyse-pitch',
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'nuventures_analyse_pitch',
            'permission_callback' => '__return_true',
        )
    );
}
add_action('rest_api_init', 'nuventures_register_pitch_api_route');

/**
 * Analyse one uploaded pitch-deck PDF.
 *
 * @param WP_REST_Request $request REST request.
 * @return WP_REST_Response|WP_Error
 */
function nuventures_analyse_pitch(WP_REST_Request $request) {
    $session_token = (string) $request->get_param('pitch_session');
    if (!nuventures_verify_pitch_session_token($session_token)) {
        return new WP_Error(
            'nuventures_invalid_pitch_session',
            __('Your pitch session has expired. Please close the form and start again.', 'nuventures'),
            array('status' => 403)
        );
    }

    if (
        !defined('OPENAI_API_KEY') ||
        !is_string(OPENAI_API_KEY) ||
        '' === trim(OPENAI_API_KEY)
    ) {
        return new WP_Error(
            'nuventures_pitch_api_key_missing',
            __('Pitch analysis is not configured on this server.', 'nuventures'),
            array('status' => 500)
        );
    }

    if (
        !defined('NUVENTURES_OPENAI_PROMPT_ID') ||
        !is_string(NUVENTURES_OPENAI_PROMPT_ID) ||
        '' === trim(NUVENTURES_OPENAI_PROMPT_ID) ||
        !defined('NUVENTURES_OPENAI_PROMPT_VERSION') ||
        (!is_string(NUVENTURES_OPENAI_PROMPT_VERSION) && !is_int(NUVENTURES_OPENAI_PROMPT_VERSION)) ||
        '' === trim((string) NUVENTURES_OPENAI_PROMPT_VERSION)
    ) {
        return new WP_Error(
            'nuventures_pitch_prompt_missing',
            __('Pitch analysis is not configured: the published prompt ID or version is missing.', 'nuventures'),
            array('status' => 500)
        );
    }

    $files = $request->get_file_params();
    $file  = isset($files['pitch_deck']) && is_array($files['pitch_deck']) ? $files['pitch_deck'] : null;
    $valid = nuventures_validate_pitch_pdf($file);

    if (is_wp_error($valid)) {
        return $valid;
    }

    $file_id = '';

    try {
        $uploaded = nuventures_openai_upload_pitch_file($file);
        if (is_wp_error($uploaded)) {
            return $uploaded;
        }

        $file_id  = $uploaded;
        $response_id = '';
        $response = nuventures_openai_extract_pitch($file_id, $response_id);
        if (is_wp_error($response)) {
            return $response;
        }

        $validated = nuventures_validate_pitch_extraction($response);
        if (is_wp_error($validated)) {
            return $validated;
        }

        if ('' !== $response_id) {
            set_transient(
                nuventures_pitch_analysis_transient_key($session_token),
                array('openai_response_id' => $response_id),
                NUVENTURES_PITCH_SESSION_TTL
            );
        }

        return rest_ensure_response($validated);
    } finally {
        if ('' !== $file_id) {
            nuventures_openai_delete_pitch_file($file_id);
        }
    }
}

/**
 * Validate the incoming PDF.
 *
 * @param array|null $file Uploaded file.
 * @return true|WP_Error
 */
function nuventures_validate_pitch_pdf($file) {
    if (!$file) {
        return new WP_Error(
            'nuventures_pitch_file_missing',
            __('Please select a PDF pitch deck.', 'nuventures'),
            array('status' => 400)
        );
    }

    $upload_error = isset($file['error']) ? (int) $file['error'] : UPLOAD_ERR_NO_FILE;
    if (UPLOAD_ERR_OK !== $upload_error) {
        $message = UPLOAD_ERR_INI_SIZE === $upload_error || UPLOAD_ERR_FORM_SIZE === $upload_error
            ? __('The PDF must be 10 MB or smaller.', 'nuventures')
            : __('The PDF could not be uploaded. Please try again.', 'nuventures');

        return new WP_Error('nuventures_pitch_upload_failed', $message, array('status' => 400));
    }

    $size     = isset($file['size']) ? (int) $file['size'] : 0;
    $tmp_name = isset($file['tmp_name']) ? (string) $file['tmp_name'] : '';
    $name     = isset($file['name']) ? (string) $file['name'] : '';

    if ($size <= 0 || '' === $tmp_name || !is_uploaded_file($tmp_name)) {
        return new WP_Error(
            'nuventures_pitch_file_empty',
            __('The selected PDF is empty or invalid.', 'nuventures'),
            array('status' => 400)
        );
    }

    if ($size > NUVENTURES_PITCH_MAX_FILE_SIZE) {
        return new WP_Error(
            'nuventures_pitch_file_too_large',
            __('The PDF must be 10 MB or smaller.', 'nuventures'),
            array('status' => 400)
        );
    }

    if ('pdf' !== strtolower(pathinfo($name, PATHINFO_EXTENSION))) {
        return new WP_Error(
            'nuventures_pitch_file_type',
            __('Only PDF pitch decks are supported.', 'nuventures'),
            array('status' => 400)
        );
    }

    $mime = '';
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = (string) $finfo->file($tmp_name);
    }

    $handle = fopen($tmp_name, 'rb');
    $magic  = $handle ? fread($handle, 5) : '';
    if ($handle) {
        fclose($handle);
    }

    if ('application/pdf' !== strtolower($mime) || '%PDF-' !== $magic) {
        return new WP_Error(
            'nuventures_pitch_file_type',
            __('The selected file is not a valid PDF.', 'nuventures'),
            array('status' => 400)
        );
    }

    return true;
}

/**
 * Upload the deck using a server-created multipart body.
 *
 * @param array $file Validated uploaded file.
 * @return string|WP_Error OpenAI file ID.
 */
function nuventures_openai_upload_pitch_file($file) {
    $contents = file_get_contents($file['tmp_name']);
    if (false === $contents || '' === $contents) {
        return new WP_Error(
            'nuventures_pitch_file_read',
            __('The PDF could not be read. Please try again.', 'nuventures'),
            array('status' => 400)
        );
    }

    $boundary = '--------------------------' . wp_generate_password(24, false, false);
    $filename = sanitize_file_name($file['name']);
    $body     = '--' . $boundary . "\r\n";
    $body    .= "Content-Disposition: form-data; name=\"purpose\"\r\n\r\n";
    $body    .= "user_data\r\n";
    $body    .= '--' . $boundary . "\r\n";
    $body    .= 'Content-Disposition: form-data; name="file"; filename="' . $filename . "\"\r\n";
    $body    .= "Content-Type: application/pdf\r\n\r\n";
    $body    .= $contents . "\r\n";
    $body    .= '--' . $boundary . "--\r\n";

    $response = wp_remote_post(
        'https://api.openai.com/v1/files',
        array(
            'headers' => array(
                'Authorization' => 'Bearer ' . trim(OPENAI_API_KEY),
                'Content-Type'  => 'multipart/form-data; boundary=' . $boundary,
            ),
            'body'    => $body,
            'timeout' => 60,
        )
    );

    if (is_wp_error($response)) {
        return nuventures_pitch_upstream_error('upload', $response);
    }

    $status = wp_remote_retrieve_response_code($response);
    $data   = json_decode(wp_remote_retrieve_body($response), true);
    if ($status < 200 || $status >= 300) {
        return nuventures_pitch_upstream_error('upload', null, $status);
    }

    if (!is_array($data) || empty($data['id'])) {
        return nuventures_pitch_local_error('upload_parse', 'Successful file upload response did not contain a file ID.');
    }

    return sanitize_text_field($data['id']);
}

/**
 * Send the uploaded file to the Responses API using the published prompt.
 *
 * @param string $file_id OpenAI file ID.
 * @return array|WP_Error Decoded extraction result.
 */
function nuventures_openai_extract_pitch($file_id, &$response_id = '') {
    $body = array(
        'prompt' => array(
            'id'      => trim(NUVENTURES_OPENAI_PROMPT_ID),
            'version' => trim((string) NUVENTURES_OPENAI_PROMPT_VERSION),
        ),
        'input'  => array(
            array(
                'role'    => 'user',
                'content' => array(
                    array(
                        'type'    => 'input_file',
                        'file_id' => $file_id,
                    ),
                    array(
                        'type' => 'input_text',
                        'text' => 'Analyse the attached pitch deck using the published prompt.',
                    ),
                ),
            ),
        ),
    );

    $response = wp_remote_post(
        'https://api.openai.com/v1/responses',
        array(
            'headers' => array(
                'Authorization' => 'Bearer ' . trim(OPENAI_API_KEY),
                'Content-Type'  => 'application/json',
            ),
            'body'    => wp_json_encode($body),
            'timeout' => 120,
        )
    );

    if (is_wp_error($response)) {
        return nuventures_pitch_upstream_error('analysis', $response);
    }

    $status        = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);

    if ($status < 200 || $status >= 300) {
        return nuventures_pitch_upstream_error('analysis', null, $status);
    }

    $data = json_decode($response_body, true);
    if (!is_array($data)) {
        return nuventures_pitch_local_error(
            'response_parse',
            'OpenAI returned HTTP 200 but its response body was not valid JSON: ' . json_last_error_msg()
        );
    }

    $response_id = isset($data['id']) && is_string($data['id'])
        ? sanitize_text_field($data['id'])
        : '';

    $diagnostics = array();
    $output_text = nuventures_pitch_response_output_text($data, $diagnostics);
    if ('' === $output_text) {
        return nuventures_pitch_local_error(
            'structured_output_missing',
            'No assistant output_text was found.',
            $diagnostics
        );
    }

    $extraction = json_decode($output_text, true);
    if (!is_array($extraction)) {
        return nuventures_pitch_local_error(
            'structured_output_decode',
            'Structured output JSON could not be decoded: ' . json_last_error_msg(),
            $diagnostics
        );
    }

    return $extraction;
}

/**
 * Build a non-reversible transient key for server-side pitch analysis state.
 *
 * @param string $session_token Signed pitch-session token.
 * @return string
 */
function nuventures_pitch_analysis_transient_key($session_token) {
    return 'nuv_pitch_' . hash_hmac('sha256', $session_token, wp_salt('auth'));
}

/**
 * Delete the temporary OpenAI file. This function never exposes deletion errors.
 *
 * @param string $file_id OpenAI file ID.
 */
function nuventures_openai_delete_pitch_file($file_id) {
    wp_remote_request(
        'https://api.openai.com/v1/files/' . rawurlencode($file_id),
        array(
            'method'  => 'DELETE',
            'headers' => array('Authorization' => 'Bearer ' . trim(OPENAI_API_KEY)),
            'timeout' => 30,
        )
    );
}

/**
 * Validate and normalize model output. Missing fields are recalculated locally.
 *
 * @param array $result Model result.
 * @return array|WP_Error
 */
function nuventures_validate_pitch_extraction($result) {
    $keys          = nuventures_pitch_answer_keys();
    $required_keys = array_merge($keys, array('complete', 'missing_fields'));

    foreach ($required_keys as $required_key) {
        if (!array_key_exists($required_key, $result)) {
            return nuventures_pitch_local_error(
                'validation',
                'Missing required top-level key: ' . $required_key
            );
        }
    }

    if (!is_bool($result['complete'])) {
        return nuventures_pitch_local_error('validation', 'The complete value was not a boolean.');
    }

    if (!is_array($result['missing_fields'])) {
        return nuventures_pitch_local_error('validation', 'The missing_fields value was not an array.');
    }

    $answers = array();
    foreach ($keys as $key) {
        $value = $result[$key];
        if ('founder_count' === $key) {
            if (null !== $value && !is_int($value)) {
                return nuventures_pitch_local_error(
                    'validation',
                    'The founder_count value was neither an integer nor null.'
                );
            }

            $answers[$key] = is_int($value) && $value > 0 ? $value : null;
            continue;
        }

        if (null !== $value && !is_string($value)) {
            return nuventures_pitch_local_error(
                'validation',
                'The ' . $key . ' value was neither a string nor null.'
            );
        }

        if (null === $value) {
            $answers[$key] = null;
            continue;
        }

        $value = preg_replace('/\s+/u', ' ', trim(wp_strip_all_tags($value)));
        $value = is_string($value) ? $value : '';

        if ('company_website' === $key) {
            $value = nuventures_pitch_clean_website_display($value);
        }

        if (in_array($key, array('what_are_you_building', 'problem_and_customer'), true)) {
            $value = nuventures_pitch_limit_answer($value, 140);
        }

        if (
            '' === $value ||
            ('company_website' === $key && !nuventures_pitch_is_valid_website($value))
        ) {
            $answers[$key] = null;
            continue;
        }

        $answers[$key] = $value;
    }

    $missing = array_values(
        array_filter(
            $keys,
            static function ($key) use ($answers) {
                return null === $answers[$key];
            }
        )
    );

    $validated                   = $answers;
    $validated['complete']       = empty($missing);
    $validated['missing_fields'] = $missing;

    return $validated;
}

/**
 * Remove common document labels and surrounding punctuation without changing a
 * clearly stated domain's protocol or readable display form.
 *
 * @param string $value Extracted website value.
 * @return string
 */
function nuventures_pitch_clean_website_display($value) {
    $value = preg_replace('/^(?:company\s+)?(?:website|web|url)\s*:\s*/i', '', $value);
    $value = is_string($value) ? $value : '';

    return trim($value, " \t\n\r\0\x0B,;.()[]<>");
}

/**
 * Validate a website while preserving the model's original display value.
 *
 * @param string $value Extracted website or domain.
 * @return bool
 */
function nuventures_pitch_is_valid_website($value) {
    $validation_url = preg_match('#^https?://#i', $value) ? $value : 'https://' . $value;
    $validated      = filter_var($validation_url, FILTER_VALIDATE_URL);

    if (false === $validated) {
        return false;
    }

    $host = wp_parse_url($validation_url, PHP_URL_HOST);

    return is_string($host) && false !== strpos($host, '.');
}

/**
 * Keep a supported answer within its schema limit instead of discarding it.
 * The model is instructed to rewrite first; this is only a defensive fallback.
 *
 * @param string $value  Supported answer.
 * @param int    $length Maximum characters.
 * @return string
 */
function nuventures_pitch_limit_answer($value, $length) {
    $value_length = function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    if ($value_length <= $length) {
        return $value;
    }

    $shortened  = function_exists('mb_substr') ? mb_substr($value, 0, $length + 1) : substr($value, 0, $length + 1);
    $last_space = function_exists('mb_strrpos') ? mb_strrpos($shortened, ' ') : strrpos($shortened, ' ');

    if (false !== $last_space && $last_space >= (int) floor($length * 0.65)) {
        $shortened = function_exists('mb_substr') ? mb_substr($shortened, 0, $last_space) : substr($shortened, 0, $last_space);
    } else {
        $shortened = function_exists('mb_substr') ? mb_substr($shortened, 0, $length) : substr($shortened, 0, $length);
    }

    return rtrim($shortened, " \t\n\r\0\x0B,;:-");
}

/**
 * Pull output_text from the raw Responses REST payload.
 *
 * @param array $response    Responses API payload.
 * @param array $diagnostics Safe structural diagnostics.
 * @return string
 */
function nuventures_pitch_response_output_text($response, &$diagnostics = array()) {
    $diagnostics = array(
        'response_id'       => isset($response['id']) && is_string($response['id']) ? $response['id'] : '',
        'response_status'   => isset($response['status']) && is_string($response['status']) ? $response['status'] : '',
        'output_item_types' => array(),
        'content_types'     => array(),
    );

    if (empty($response['output']) || !is_array($response['output'])) {
        return '';
    }

    foreach ($response['output'] as $item) {
        $item_type = isset($item['type']) && is_string($item['type']) ? $item['type'] : 'unknown';
        $diagnostics['output_item_types'][] = $item_type;

        if (!empty($item['content']) && is_array($item['content'])) {
            foreach ($item['content'] as $content) {
                $diagnostics['content_types'][] = isset($content['type']) && is_string($content['type'])
                    ? $content['type']
                    : 'unknown';
            }
        }

        if (
            'message' !== $item_type ||
            !isset($item['role']) ||
            'assistant' !== $item['role']
        ) {
            continue;
        }

        if (empty($item['content']) || !is_array($item['content'])) {
            continue;
        }

        foreach ($item['content'] as $content) {
            $content_type = isset($content['type']) && is_string($content['type']) ? $content['type'] : 'unknown';

            if (
                'output_text' === $content_type &&
                isset($content['text']) &&
                is_string($content['text']) &&
                '' !== trim($content['text'])
            ) {
                return $content['text'];
            }
        }
    }

    return '';
}

/**
 * Create a safe HTTP 500 response for local parsing or validation failures.
 *
 * @param string $stage       Internal stage label.
 * @param string $reason      Exact non-sensitive failure reason.
 * @param array  $diagnostics Structural response diagnostics.
 * @return WP_Error
 */
function nuventures_pitch_local_error($stage, $reason, $diagnostics = array()) {
    $context = array(
        'stage'  => sanitize_key($stage),
        'reason' => sanitize_text_field($reason),
    );

    if ($diagnostics) {
        $context['response_id']       = isset($diagnostics['response_id']) ? sanitize_text_field($diagnostics['response_id']) : '';
        $context['response_status']   = isset($diagnostics['response_status']) ? sanitize_text_field($diagnostics['response_status']) : '';
        $context['output_item_types'] = isset($diagnostics['output_item_types']) ? array_map('sanitize_key', $diagnostics['output_item_types']) : array();
        $context['content_types']     = isset($diagnostics['content_types']) ? array_map('sanitize_key', $diagnostics['content_types']) : array();
    }

    error_log('NuVentures pitch local failure: ' . wp_json_encode($context));

    return new WP_Error(
        'nuventures_pitch_processing_failed',
        __('The pitch deck was analysed, but its structured result could not be processed. Please try again or enter your details manually.', 'nuventures'),
        array('status' => 500)
    );
}

/**
 * Create a safe public error for an upstream failure.
 *
 * @param string        $stage  Internal stage label.
 * @param WP_Error|null $error  Optional WordPress transport error.
 * @param int           $status Optional HTTP status.
 * @return WP_Error
 */
function nuventures_pitch_upstream_error($stage, $error = null, $status = 0) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        $detail = $error instanceof WP_Error ? $error->get_error_code() : (string) $status;
        error_log('NuVentures pitch ' . sanitize_key($stage) . ' failed: ' . sanitize_text_field($detail));
    }

    return new WP_Error(
        'nuventures_pitch_analysis_failed',
        __('We could not analyse that deck right now. Please try again or enter your details manually.', 'nuventures'),
        array('status' => 502)
    );
}

/**
 * Verify signature, expiry, and browser binding of a pitch-session token.
 *
 * @param string $token Signed token.
 * @return bool
 */
function nuventures_verify_pitch_session_token($token) {
    $parts = explode('.', $token);
    if (2 !== count($parts)) {
        return false;
    }

    $expected = hash_hmac('sha256', $parts[0], wp_salt('nonce'), true);
    $provided = nuventures_pitch_base64url_decode($parts[1]);
    if (false === $provided || !hash_equals($expected, $provided)) {
        return false;
    }

    $decoded = nuventures_pitch_base64url_decode($parts[0]);
    $payload = false !== $decoded ? json_decode($decoded, true) : null;
    if (
        !is_array($payload) ||
        empty($payload['iat']) ||
        empty($payload['exp']) ||
        empty($payload['ua']) ||
        (int) $payload['iat'] > time() + 60 ||
        (int) $payload['exp'] < time()
    ) {
        return false;
    }

    $current_ua = hash_hmac(
        'sha256',
        isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '',
        wp_salt('nonce')
    );

    return hash_equals((string) $payload['ua'], $current_ua);
}

/**
 * @return string[]
 */
function nuventures_pitch_answer_keys() {
    return array(
        'company_name',
        'founder_count',
        'company_website',
        'what_are_you_building',
        'problem_and_customer',
        'raising_and_unlock',
        'hard_to_copy',
    );
}

/**
 * @param string $value Raw value.
 * @return string
 */
function nuventures_pitch_base64url_encode($value) {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

/**
 * @param string $value URL-safe base64 value.
 * @return string|false
 */
function nuventures_pitch_base64url_decode($value) {
    $padding = strlen($value) % 4;
    if ($padding) {
        $value .= str_repeat('=', 4 - $padding);
    }

    return base64_decode(strtr($value, '-_', '+/'), true);
}
