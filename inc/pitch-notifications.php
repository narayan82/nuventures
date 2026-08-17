<?php
/**
 * Administrator email notifications for completed Pitch Submissions.
 *
 * @package NuVentures
 */

defined('ABSPATH') || exit;

const NUVENTURES_PITCH_NOTIFICATION_EMAILS_OPTION = 'nuventures_pitch_notification_emails';

/** Parse, sanitize and deduplicate a recipient configuration value. */
function nuventures_parse_pitch_notification_emails($configured) {
    $values     = is_array($configured)
        ? $configured
        : preg_split('/[,;\r\n]+/', (string) $configured);
    $recipients = array();

    foreach ((array) $values as $value) {
        $email = sanitize_email(trim((string) $value));
        if ($email && is_email($email)) {
            $recipients[] = $email;
        }
    }

    return array_values(array_unique($recipients));
}

/** Resolve recipients from WP Admin, with wp-config.php as a fallback. */
function nuventures_pitch_notification_recipients() {
    $configured = get_option(NUVENTURES_PITCH_NOTIFICATION_EMAILS_OPTION, '');

    if ('' === trim((string) $configured) && defined('NUVENTURES_PITCH_NOTIFICATION_EMAILS')) {
        $configured = NUVENTURES_PITCH_NOTIFICATION_EMAILS;
    }

    return nuventures_parse_pitch_notification_emails($configured);
}

/** Store only valid recipient addresses in a consistent comma-separated form. */
function nuventures_sanitize_pitch_notification_emails($value) {
    return implode(',', nuventures_parse_pitch_notification_emails($value));
}

/** Register the administrator-only notification setting. */
function nuventures_register_pitch_notification_settings() {
    register_setting(
        'nuventures_pitch_notifications',
        NUVENTURES_PITCH_NOTIFICATION_EMAILS_OPTION,
        array(
            'type'              => 'string',
            'sanitize_callback' => 'nuventures_sanitize_pitch_notification_emails',
            'default'           => '',
            'show_in_rest'      => false,
        )
    );

    add_settings_section(
        'nuventures_pitch_notification_recipients',
        __('Email recipients', 'nuventures'),
        '__return_false',
        'nuventures-pitch-notifications'
    );

    add_settings_field(
        NUVENTURES_PITCH_NOTIFICATION_EMAILS_OPTION,
        __('Notification emails', 'nuventures'),
        'nuventures_pitch_notification_emails_field',
        'nuventures-pitch-notifications',
        'nuventures_pitch_notification_recipients'
    );
}
add_action('admin_init', 'nuventures_register_pitch_notification_settings');

/** Render the recipient input. */
function nuventures_pitch_notification_emails_field() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to manage Pitch Submission notifications.', 'nuventures'), '', array('response' => 403));
    }

    $value = (string) get_option(NUVENTURES_PITCH_NOTIFICATION_EMAILS_OPTION, '');
    ?>
    <textarea
        class="large-text"
        id="<?php echo esc_attr(NUVENTURES_PITCH_NOTIFICATION_EMAILS_OPTION); ?>"
        name="<?php echo esc_attr(NUVENTURES_PITCH_NOTIFICATION_EMAILS_OPTION); ?>"
        rows="4"
        placeholder="person1@example.com, person2@example.com"
    ><?php echo esc_textarea($value); ?></textarea>
    <p class="description">
        <?php esc_html_e('Enter one or more email addresses separated by commas, semicolons, or new lines. Invalid addresses will not be saved.', 'nuventures'); ?>
    </p>
    <?php
}

/** Add Notification Settings beneath the private Pitch Submissions menu. */
function nuventures_add_pitch_notification_settings_page() {
    add_submenu_page(
        'edit.php?post_type=' . NUVENTURES_PITCH_SUBMISSION_POST_TYPE,
        __('Pitch Notification Settings', 'nuventures'),
        __('Notification Settings', 'nuventures'),
        'manage_options',
        'nuventures-pitch-notifications',
        'nuventures_render_pitch_notification_settings_page'
    );
}
add_action('admin_menu', 'nuventures_add_pitch_notification_settings_page');

/** Render the administrator settings screen. */
function nuventures_render_pitch_notification_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to manage Pitch Submission notifications.', 'nuventures'), '', array('response' => 403));
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Pitch Notification Settings', 'nuventures'); ?></h1>
        <p><?php esc_html_e('Choose who receives an email after a completed Pitch Submission is successfully saved.', 'nuventures'); ?></p>
        <form action="options.php" method="post">
            <?php
            settings_fields('nuventures_pitch_notifications');
            do_settings_sections('nuventures-pitch-notifications');
            submit_button(__('Save Notification Settings', 'nuventures'));
            ?>
        </form>
    </div>
    <?php
}

/** Format a saved value for safe HTML email output. */
function nuventures_pitch_notification_value($value, $multiline = false) {
    $value = trim((string) $value);
    if ('' === $value) {
        return '&mdash;';
    }

    $escaped = esc_html($value);
    return $multiline ? nl2br($escaped) : $escaped;
}

/** Build an authenticated bridge URL for a protected deck download. */
function nuventures_pitch_notification_deck_url($attachment_id) {
    return admin_url(
        'admin-post.php?action=nuventures_pitch_notification_deck&attachment_id=' . absint($attachment_id)
    );
}

/** Create a fresh, administrator-bound download nonce after authentication. */
function nuventures_pitch_notification_deck_redirect() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to access this pitch deck.', 'nuventures'), '', array('response' => 403));
    }

    $attachment_id = isset($_GET['attachment_id']) ? absint($_GET['attachment_id']) : 0;
    if (!$attachment_id || !get_post_meta($attachment_id, '_nuventures_private_pitch_deck', true)) {
        wp_die(esc_html__('Pitch deck not found.', 'nuventures'), '', array('response' => 404));
    }

    wp_safe_redirect(nuventures_pitch_deck_download_url($attachment_id));
    exit;
}
add_action('admin_post_nuventures_pitch_notification_deck', 'nuventures_pitch_notification_deck_redirect');

/** Send unauthenticated email-link visitors through WordPress login first. */
function nuventures_pitch_notification_deck_login() {
    auth_redirect();
}
add_action('admin_post_nopriv_nuventures_pitch_notification_deck', 'nuventures_pitch_notification_deck_login');

/**
 * Send the final notification for a saved Pitch Submission.
 *
 * @return bool True when already sent or successfully sent; false otherwise.
 */
function nuventures_send_pitch_notification($submission_id) {
    $submission_id = absint($submission_id);
    if (!$submission_id || NUVENTURES_PITCH_SUBMISSION_POST_TYPE !== get_post_type($submission_id)) {
        return false;
    }

    if ('private' !== get_post_status($submission_id) || !get_post_meta($submission_id, 'submitted_at', true)) {
        return false;
    }

    if (get_post_meta($submission_id, '_pitch_notification_sent', true)) {
        return true;
    }

    $recipients = nuventures_pitch_notification_recipients();
    if (!$recipients) {
        return false;
    }

    $fields = array(
        'full_name'             => get_post_meta($submission_id, 'full_name', true),
        'mobile'                => get_post_meta($submission_id, 'mobile', true),
        'email'                 => get_post_meta($submission_id, 'email', true),
        'company_name'          => get_post_meta($submission_id, 'company_name', true),
        'founder_count'         => get_post_meta($submission_id, 'founder_count', true),
        'company_website'       => get_post_meta($submission_id, 'company_website', true),
        'what_are_you_building' => get_post_meta($submission_id, 'what_are_you_building', true),
        'problem_and_customer'  => get_post_meta($submission_id, 'problem_and_customer', true),
        'raising_and_unlock'    => get_post_meta($submission_id, 'raising_and_unlock', true),
        'hard_to_copy'          => get_post_meta($submission_id, 'hard_to_copy', true),
        'submitted_at'          => get_post_meta($submission_id, 'submitted_at', true),
    );

    $company_name = trim((string) $fields['company_name']);
    $subject      = $company_name
        ? sprintf('New NuVentures Pitch Submission — %s', $company_name)
        : 'New NuVentures Pitch Submission';
    $admin_url    = admin_url('post.php?post=' . $submission_id . '&action=edit');
    $attachment_id = absint(get_post_meta($submission_id, 'pitch_deck_attachment_id', true));
    $deck_url      = $attachment_id && get_post_meta($attachment_id, '_nuventures_private_pitch_deck', true)
        ? nuventures_pitch_notification_deck_url($attachment_id)
        : '';

    $submitted_at = trim((string) $fields['submitted_at']);
    if ($submitted_at) {
        $submitted_timestamp = mysql2date('U', $submitted_at, false);
        if ($submitted_timestamp) {
            $submitted_at = wp_date(
                get_option('date_format') . ' ' . get_option('time_format'),
                $submitted_timestamp,
                wp_timezone()
            );
        }
    }

    $row_style   = 'padding:10px 12px;border-bottom:1px solid #eeeeee;vertical-align:top;text-align:left;';
    $label_style = $row_style . 'width:32%;color:#555555;font-weight:600;';
    $value_style = $row_style . 'color:#141b34;';

    ob_start();
    ?>
    <!doctype html>
    <html lang="en">
    <body style="margin:0;padding:24px;background:#f6f6f6;color:#141b34;font-family:Arial,sans-serif;line-height:1.5;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <td align="center">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:720px;background:#ffffff;border:1px solid #eeeeee;border-radius:12px;overflow:hidden;">
                        <tr>
                            <td style="padding:24px 28px;background:#ffffff;border-bottom:3px solid #da0009;">
                                <div style="font-size:22px;font-weight:700;">NuVentures</div>
                                <div style="margin-top:4px;color:#555555;">New pitch submission</div>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:24px 28px;">
                                <h2 style="margin:0 0 12px;font-size:18px;">Submitted by</h2>
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:28px;border:1px solid #eeeeee;border-radius:8px;border-collapse:separate;overflow:hidden;">
                                    <tr><th style="<?php echo esc_attr($label_style); ?>">Full Name</th><td style="<?php echo esc_attr($value_style); ?>"><?php echo nuventures_pitch_notification_value($fields['full_name']); ?></td></tr>
                                    <tr><th style="<?php echo esc_attr($label_style); ?>">Mobile</th><td style="<?php echo esc_attr($value_style); ?>"><?php echo nuventures_pitch_notification_value($fields['mobile']); ?></td></tr>
                                    <?php if (is_email($fields['email'])) : ?>
                                        <tr><th style="<?php echo esc_attr($label_style); ?>">Email</th><td style="<?php echo esc_attr($value_style); ?>"><?php echo esc_html($fields['email']); ?></td></tr>
                                    <?php endif; ?>
                                    <tr><th style="<?php echo esc_attr($label_style); ?>">Submitted</th><td style="<?php echo esc_attr($value_style); ?>"><?php echo nuventures_pitch_notification_value($submitted_at); ?></td></tr>
                                </table>

                                <h2 style="margin:0 0 12px;font-size:18px;">Company</h2>
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:28px;border:1px solid #eeeeee;border-radius:8px;border-collapse:separate;overflow:hidden;">
                                    <tr><th style="<?php echo esc_attr($label_style); ?>">Company Name</th><td style="<?php echo esc_attr($value_style); ?>"><?php echo nuventures_pitch_notification_value($fields['company_name']); ?></td></tr>
                                    <tr><th style="<?php echo esc_attr($label_style); ?>">Number of Founders</th><td style="<?php echo esc_attr($value_style); ?>"><?php echo nuventures_pitch_notification_value($fields['founder_count']); ?></td></tr>
                                    <tr><th style="<?php echo esc_attr($label_style); ?>">Website</th><td style="<?php echo esc_attr($value_style); ?>"><?php echo nuventures_pitch_notification_value($fields['company_website']); ?></td></tr>
                                </table>

                                <h2 style="margin:0 0 12px;font-size:18px;">Pitch</h2>
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:28px;border:1px solid #eeeeee;border-radius:8px;border-collapse:separate;overflow:hidden;">
                                    <tr><th style="<?php echo esc_attr($label_style); ?>">What are you building?</th><td style="<?php echo esc_attr($value_style); ?>"><?php echo nuventures_pitch_notification_value($fields['what_are_you_building'], true); ?></td></tr>
                                    <tr><th style="<?php echo esc_attr($label_style); ?>">Problem and Customer</th><td style="<?php echo esc_attr($value_style); ?>"><?php echo nuventures_pitch_notification_value($fields['problem_and_customer'], true); ?></td></tr>
                                    <tr><th style="<?php echo esc_attr($label_style); ?>">Raise and Unlock</th><td style="<?php echo esc_attr($value_style); ?>"><?php echo nuventures_pitch_notification_value($fields['raising_and_unlock'], true); ?></td></tr>
                                    <tr><th style="<?php echo esc_attr($label_style); ?>">Hard to Copy</th><td style="<?php echo esc_attr($value_style); ?>"><?php echo nuventures_pitch_notification_value($fields['hard_to_copy'], true); ?></td></tr>
                                </table>

                                <?php if ($deck_url) : ?>
                                    <p style="margin:0 0 16px;"><a href="<?php echo esc_url($deck_url); ?>" style="display:inline-block;padding:10px 16px;border-radius:20px;background:#da0009;color:#ffffff;text-decoration:none;font-weight:600;">View/download pitch deck</a></p>
                                <?php endif; ?>
                                <p style="margin:0;"><a href="<?php echo esc_url($admin_url); ?>" style="color:#da0009;text-decoration:underline;font-weight:600;">View Submission in WordPress Admin</a></p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>
    <?php
    $message = (string) ob_get_clean();
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $email   = sanitize_email((string) $fields['email']);

    if ($email && is_email($email)) {
        $headers[] = 'Reply-To: ' . $email;
    }

    $sent = wp_mail($recipients, $subject, $message, $headers);
    if ($sent) {
        update_post_meta($submission_id, '_pitch_notification_sent', 1);
        return true;
    }

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('NuVentures pitch notification failed for submission ID ' . $submission_id);
    }

    return false;
}
