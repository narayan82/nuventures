<?php
/**
 * Private administrator-only Pitch Submission storage.
 *
 * @package NuVentures
 */

defined('ABSPATH') || exit;

const NUVENTURES_PITCH_SUBMISSION_POST_TYPE = 'pitch_submission';

/** Register the private Pitch Submission post type. */
function nuventures_register_pitch_submission_post_type() {
    $admin_cap = 'manage_options';

    register_post_type(
        NUVENTURES_PITCH_SUBMISSION_POST_TYPE,
        array(
            'labels'              => array(
                'name'          => __('Pitch Submissions', 'nuventures'),
                'singular_name' => __('Pitch Submission', 'nuventures'),
                'menu_name'     => __('Pitch Submissions', 'nuventures'),
                'edit_item'     => __('View Pitch Submission', 'nuventures'),
            ),
            'public'              => false,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'show_ui'             => true,
            'show_in_menu'        => current_user_can($admin_cap),
            'show_in_rest'        => false,
            'has_archive'         => false,
            'rewrite'             => false,
            'query_var'           => false,
            'supports'            => array('title'),
            'map_meta_cap'        => false,
            'capabilities'        => array(
                'edit_post'              => $admin_cap,
                'read_post'              => $admin_cap,
                'delete_post'            => $admin_cap,
                'edit_posts'             => $admin_cap,
                'edit_others_posts'      => $admin_cap,
                'publish_posts'          => $admin_cap,
                'read_private_posts'     => $admin_cap,
                'delete_posts'           => $admin_cap,
                'delete_private_posts'   => $admin_cap,
                'delete_published_posts' => $admin_cap,
                'delete_others_posts'    => $admin_cap,
                'edit_private_posts'     => $admin_cap,
                'edit_published_posts'   => $admin_cap,
                'create_posts'           => $admin_cap,
            ),
        )
    );
}
add_action('init', 'nuventures_register_pitch_submission_post_type');

/** Register the verified public submission endpoint. */
function nuventures_register_pitch_submission_route() {
    register_rest_route(
        'nuventures/v1',
        '/submit-pitch',
        array(
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'nuventures_store_pitch_submission',
            'permission_callback' => '__return_true',
        )
    );
}
add_action('rest_api_init', 'nuventures_register_pitch_submission_route');

/**
 * Persist a completed pitch. The signed short-lived browser session is the
 * authorization boundary for this public intake endpoint.
 */
function nuventures_store_pitch_submission(WP_REST_Request $request) {
    $session_token = (string) $request->get_param('pitch_session');
    if (!nuventures_verify_pitch_session_token($session_token)) {
        return new WP_Error('nuventures_invalid_pitch_session', __('Your pitch session has expired.', 'nuventures'), array('status' => 403));
    }

    $existing = get_transient('nuv_submitted_' . hash_hmac('sha256', $session_token, wp_salt('secure_auth')));
    if ($existing) {
        return rest_ensure_response(array('submitted' => true));
    }

    $verification_token = sanitize_text_field((string) $request->get_param('verification_token'));
    $submitted_mobile   = sanitize_text_field((string) $request->get_param('mobile'));
    $normalized_mobile  = nuventures_pitch_normalize_mobile($submitted_mobile);
    if (!nuventures_pitch_get_verified_otp_state($verification_token, $session_token, $submitted_mobile)) {
        return new WP_Error(
            'nuventures_pitch_mobile_not_verified',
            __('Please verify your mobile number before submitting your pitch.', 'nuventures'),
            array('status' => 403)
        );
    }

    $fields = array(
        'full_name'             => sanitize_text_field((string) $request->get_param('full_name')),
        'mobile'                => '+91' . $normalized_mobile,
        'email'                 => sanitize_email((string) $request->get_param('email')),
        'company_name'          => sanitize_text_field((string) $request->get_param('company_name')),
        'founder_count'         => absint($request->get_param('founder_count')),
        'company_website'       => sanitize_text_field((string) $request->get_param('company_website')),
        'what_are_you_building' => sanitize_textarea_field((string) $request->get_param('what_are_you_building')),
        'problem_and_customer'  => sanitize_textarea_field((string) $request->get_param('problem_and_customer')),
        'raising_and_unlock'    => sanitize_textarea_field((string) $request->get_param('raising_and_unlock')),
        'hard_to_copy'          => sanitize_textarea_field((string) $request->get_param('hard_to_copy')),
    );

    if ('' === $fields['full_name'] || '' === $fields['company_name']) {
        return new WP_Error('nuventures_pitch_submission_invalid', __('Please complete the required pitch details.', 'nuventures'), array('status' => 400));
    }

    $post_id = wp_insert_post(
        array(
            'post_type'   => NUVENTURES_PITCH_SUBMISSION_POST_TYPE,
            'post_status' => 'private',
            'post_title'  => $fields['company_name'] . ' — ' . $fields['full_name'],
        ),
        true
    );
    if (is_wp_error($post_id)) {
        return new WP_Error('nuventures_pitch_submission_failed', __('Your pitch could not be saved. Please try again.', 'nuventures'), array('status' => 500));
    }

    foreach ($fields as $key => $value) {
        update_post_meta($post_id, $key, $value);
    }

    $analysis = get_transient(nuventures_pitch_analysis_transient_key($session_token));
    update_post_meta($post_id, 'submitted_at', current_time('mysql'));
    update_post_meta($post_id, 'openai_response_id', is_array($analysis) && !empty($analysis['openai_response_id']) ? sanitize_text_field($analysis['openai_response_id']) : '');
    update_post_meta($post_id, 'submission_status', 'new');
    update_post_meta($post_id, 'pitch_deck_attachment_id', 0);

    $files = $request->get_file_params();
    if (!empty($files['pitch_deck']) && is_array($files['pitch_deck'])) {
        $valid = nuventures_validate_pitch_pdf($files['pitch_deck']);
        if (is_wp_error($valid)) {
            wp_delete_post($post_id, true);
            return $valid;
        }

        $attachment_id = nuventures_store_private_pitch_deck($files['pitch_deck'], $post_id);
        if (is_wp_error($attachment_id)) {
            wp_delete_post($post_id, true);
            return $attachment_id;
        }
        update_post_meta($post_id, 'pitch_deck_attachment_id', $attachment_id);
    }

    set_transient('nuv_submitted_' . hash_hmac('sha256', $session_token, wp_salt('secure_auth')), $post_id, NUVENTURES_PITCH_SESSION_TTL);
    delete_transient(nuventures_pitch_analysis_transient_key($session_token));
    delete_transient(nuventures_pitch_otp_transient_key($verification_token));

    return rest_ensure_response(array('submitted' => true));
}

/** Store a PDF in a non-public directory and register a private attachment. */
function nuventures_store_private_pitch_deck($file, $submission_id) {
    $private_dir = trailingslashit(dirname(ABSPATH)) . 'nuventures-private-pitches';
    if (!wp_mkdir_p($private_dir) || !is_writable($private_dir)) {
        return new WP_Error('nuventures_private_storage_unavailable', __('Secure deck storage is unavailable.', 'nuventures'), array('status' => 500));
    }

    $filename = wp_unique_filename($private_dir, wp_generate_uuid4() . '.pdf');
    $path     = trailingslashit($private_dir) . $filename;
    if (!move_uploaded_file($file['tmp_name'], $path)) {
        return new WP_Error('nuventures_private_upload_failed', __('The deck could not be stored securely.', 'nuventures'), array('status' => 500));
    }
    @chmod($path, 0640);

    $attachment_id = wp_insert_attachment(
        array(
            'post_mime_type' => 'application/pdf',
            'post_title'     => 'Pitch deck — ' . get_the_title($submission_id),
            'post_status'    => 'private',
            'post_parent'    => $submission_id,
            'guid'           => '',
        ),
        $path,
        $submission_id,
        true
    );
    if (is_wp_error($attachment_id)) {
        wp_delete_file($path);
        return $attachment_id;
    }

    update_attached_file($attachment_id, $path);
    update_post_meta($attachment_id, '_nuventures_private_pitch_deck', 1);
    return $attachment_id;
}

/** Add the protected administrator download action. */
function nuventures_pitch_deck_download_url($attachment_id) {
    return wp_nonce_url(
        admin_url('admin-post.php?action=nuventures_download_pitch_deck&attachment_id=' . absint($attachment_id)),
        'nuventures_download_pitch_deck_' . absint($attachment_id)
    );
}

/** Serve a deck only after an explicit administrator capability check. */
function nuventures_download_pitch_deck() {
    if (!current_user_can('manage_options')) {
        status_header(403);
        exit('Forbidden');
    }

    $attachment_id = isset($_GET['attachment_id']) ? absint($_GET['attachment_id']) : 0;
    check_admin_referer('nuventures_download_pitch_deck_' . $attachment_id);
    if (!$attachment_id || !get_post_meta($attachment_id, '_nuventures_private_pitch_deck', true)) {
        status_header(404);
        exit('Not found');
    }

    $path = get_attached_file($attachment_id);
    if (!$path || !is_readable($path)) {
        status_header(404);
        exit('Not found');
    }

    nocache_headers();
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . sanitize_file_name(get_the_title($attachment_id)) . '.pdf"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}
add_action('admin_post_nuventures_download_pitch_deck', 'nuventures_download_pitch_deck');

/** Block direct attachment pages and public attachment URLs for private decks. */
function nuventures_protect_pitch_attachment_url($url, $attachment_id) {
    if (!get_post_meta($attachment_id, '_nuventures_private_pitch_deck', true)) {
        return $url;
    }
    return current_user_can('manage_options') ? nuventures_pitch_deck_download_url($attachment_id) : '';
}
add_filter('wp_get_attachment_url', 'nuventures_protect_pitch_attachment_url', 10, 2);

function nuventures_block_pitch_attachment_page() {
    if (is_attachment() && get_post_meta(get_queried_object_id(), '_nuventures_private_pitch_deck', true)) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
    }
}
add_action('template_redirect', 'nuventures_block_pitch_attachment_page', 0);

/** Explicitly reject unauthorized access to every Pitch Submission admin screen. */
function nuventures_guard_pitch_submission_admin() {
    if (!is_admin()) {
        return;
    }
    $post_type = isset($_REQUEST['post_type']) ? sanitize_key(wp_unslash($_REQUEST['post_type'])) : '';
    $post_id   = isset($_REQUEST['post']) ? absint($_REQUEST['post']) : 0;
    $is_private_deck = $post_id && (bool) get_post_meta($post_id, '_nuventures_private_pitch_deck', true);
    $is_pitch  = NUVENTURES_PITCH_SUBMISSION_POST_TYPE === $post_type || ($post_id && NUVENTURES_PITCH_SUBMISSION_POST_TYPE === get_post_type($post_id)) || $is_private_deck;
    if ($is_pitch && !current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to access Pitch Submissions.', 'nuventures'), '', array('response' => 403));
    }
}
add_action('admin_init', 'nuventures_guard_pitch_submission_admin', 1);

/** Deny core attachment capabilities for private decks to non-administrators. */
function nuventures_private_pitch_attachment_meta_caps($caps, $cap, $user_id, $args) {
    if (!in_array($cap, array('read_post', 'edit_post', 'delete_post'), true) || empty($args[0])) {
        return $caps;
    }

    $attachment_id = absint($args[0]);
    if (!get_post_meta($attachment_id, '_nuventures_private_pitch_deck', true)) {
        return $caps;
    }

    return user_can($user_id, 'manage_options') ? array('manage_options') : array('do_not_allow');
}
add_filter('map_meta_cap', 'nuventures_private_pitch_attachment_meta_caps', 10, 4);

/** Block guessed private attachment IDs through the core media REST endpoint. */
function nuventures_protect_pitch_attachment_rest($result, $server, $request) {
    $route = $request->get_route();
    if (!preg_match('#^/wp/v2/media/(\d+)(?:/|$)#', $route, $matches)) {
        return $result;
    }

    $attachment_id = absint($matches[1]);
    if (!get_post_meta($attachment_id, '_nuventures_private_pitch_deck', true)) {
        return $result;
    }

    if (!current_user_can('manage_options')) {
        return new WP_Error('nuventures_pitch_deck_forbidden', __('You are not allowed to access this pitch deck.', 'nuventures'), array('status' => 403));
    }

    return $result;
}
add_filter('rest_pre_dispatch', 'nuventures_protect_pitch_attachment_rest', 10, 3);

/** Hide private decks from non-administrator Media Library queries. */
function nuventures_hide_private_pitch_attachments($query) {
    if (!is_admin() || current_user_can('manage_options') || 'attachment' !== $query->get('post_type')) {
        return;
    }

    $meta_query   = (array) $query->get('meta_query');
    $meta_query[] = array(
        'key'     => '_nuventures_private_pitch_deck',
        'compare' => 'NOT EXISTS',
    );
    $query->set('meta_query', $meta_query);
}
add_action('pre_get_posts', 'nuventures_hide_private_pitch_attachments');

function nuventures_hide_private_pitch_attachments_rest($args) {
    if (current_user_can('manage_options')) {
        return $args;
    }

    $args['meta_query']   = isset($args['meta_query']) ? (array) $args['meta_query'] : array();
    $args['meta_query'][] = array(
        'key'     => '_nuventures_private_pitch_deck',
        'compare' => 'NOT EXISTS',
    );
    return $args;
}
add_filter('rest_attachment_query', 'nuventures_hide_private_pitch_attachments_rest');

/** Explicit sitemap exclusion in addition to the CPT's non-public flags. */
function nuventures_exclude_pitch_submissions_from_sitemap($post_types) {
    unset($post_types[NUVENTURES_PITCH_SUBMISSION_POST_TYPE]);
    return $post_types;
}
add_filter('wp_sitemaps_post_types', 'nuventures_exclude_pitch_submissions_from_sitemap', 100);

/** Admin columns. */
function nuventures_pitch_submission_columns($columns) {
    if (!current_user_can('manage_options')) {
        return array();
    }
    return array(
        'cb'        => $columns['cb'],
        'company'   => __('Company', 'nuventures'),
        'founder'   => __('Founder', 'nuventures'),
        'submitted' => __('Submitted', 'nuventures'),
        'raise'     => __('Raise', 'nuventures'),
        'deck'      => __('Deck', 'nuventures'),
        'status'    => __('Status', 'nuventures'),
    );
}
add_filter('manage_pitch_submission_posts_columns', 'nuventures_pitch_submission_columns');

function nuventures_pitch_submission_column_content($column, $post_id) {
    if (!current_user_can('manage_options')) {
        status_header(403);
        return;
    }
    if ('company' === $column) {
        echo '<strong><a href="' . esc_url(get_edit_post_link($post_id)) . '">' . esc_html(get_post_meta($post_id, 'company_name', true)) . '</a></strong>';
    } elseif ('founder' === $column) {
        echo esc_html(get_post_meta($post_id, 'full_name', true));
    } elseif ('submitted' === $column) {
        echo esc_html(get_post_meta($post_id, 'submitted_at', true));
    } elseif ('raise' === $column) {
        echo esc_html(wp_trim_words(get_post_meta($post_id, 'raising_and_unlock', true), 12));
    } elseif ('deck' === $column) {
        $attachment_id = absint(get_post_meta($post_id, 'pitch_deck_attachment_id', true));
        echo $attachment_id ? '<a href="' . esc_url(nuventures_pitch_deck_download_url($attachment_id)) . '">' . esc_html__('Download', 'nuventures') . '</a>' : '—';
    } elseif ('status' === $column) {
        echo esc_html(ucfirst((string) get_post_meta($post_id, 'submission_status', true)));
    }
}
add_action('manage_pitch_submission_posts_custom_column', 'nuventures_pitch_submission_column_content', 10, 2);

/** Details/status meta box. */
function nuventures_pitch_submission_meta_boxes() {
    if (current_user_can('manage_options')) {
        add_meta_box('nuventures-pitch-details', __('Pitch Details', 'nuventures'), 'nuventures_pitch_submission_meta_box', NUVENTURES_PITCH_SUBMISSION_POST_TYPE, 'normal', 'high');
    }
}
add_action('add_meta_boxes_pitch_submission', 'nuventures_pitch_submission_meta_boxes');

function nuventures_pitch_submission_meta_box($post) {
    if (!current_user_can('manage_options')) {
        wp_die('Forbidden', '', array('response' => 403));
    }
    wp_nonce_field('nuventures_save_pitch_submission', 'nuventures_pitch_submission_nonce');
    $labels = array(
        'full_name' => 'Full name', 'mobile' => 'Mobile', 'email' => 'Email', 'company_name' => 'Company name',
        'founder_count' => 'Founder count', 'company_website' => 'Company website', 'what_are_you_building' => 'What are you building?',
        'problem_and_customer' => 'Problem and customer', 'raising_and_unlock' => 'Raise and unlock', 'hard_to_copy' => 'Hard to copy',
        'submitted_at' => 'Submitted at', 'openai_response_id' => 'OpenAI response ID',
    );
    echo '<table class="form-table"><tbody>';
    foreach ($labels as $key => $label) {
        echo '<tr><th>' . esc_html($label) . '</th><td>' . nl2br(esc_html((string) get_post_meta($post->ID, $key, true))) . '</td></tr>';
    }

    $attachment_id = absint(get_post_meta($post->ID, 'pitch_deck_attachment_id', true));
    echo '<tr><th>' . esc_html__('Pitch deck', 'nuventures') . '</th><td>';
    if ($attachment_id && get_post_meta($attachment_id, '_nuventures_private_pitch_deck', true)) {
        echo '<a class="button button-primary" href="' . esc_url(nuventures_pitch_deck_download_url($attachment_id)) . '">';
        echo esc_html__('Download pitch deck (PDF)', 'nuventures');
        echo '</a>';
    } else {
        echo '<span>' . esc_html__('No pitch deck was uploaded with this submission.', 'nuventures') . '</span>';
    }
    echo '</td></tr>';

    $status = (string) get_post_meta($post->ID, 'submission_status', true);
    echo '<tr><th><label for="nuventures-submission-status">' . esc_html__('Status', 'nuventures') . '</label></th><td><select id="nuventures-submission-status" name="nuventures_submission_status">';
    foreach (array('new' => 'New', 'reviewing' => 'Reviewing', 'contacted' => 'Contacted', 'declined' => 'Declined', 'closed' => 'Closed') as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($status, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select></td></tr></tbody></table>';
}

function nuventures_save_pitch_submission_status($post_id) {
    if (NUVENTURES_PITCH_SUBMISSION_POST_TYPE !== get_post_type($post_id)) {
        return;
    }
    if (empty($_POST['nuventures_pitch_submission_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nuventures_pitch_submission_nonce'])), 'nuventures_save_pitch_submission')) {
        return;
    }
    if (!current_user_can('manage_options')) {
        wp_die('Forbidden', '', array('response' => 403));
    }
    $allowed = array('new', 'reviewing', 'contacted', 'declined', 'closed');
    $status  = isset($_POST['nuventures_submission_status']) ? sanitize_key(wp_unslash($_POST['nuventures_submission_status'])) : '';
    if (in_array($status, $allowed, true)) {
        update_post_meta($post_id, 'submission_status', $status);
    }
}
add_action('save_post_pitch_submission', 'nuventures_save_pitch_submission_status');

/** Delete the private file when its attachment is permanently deleted. */
function nuventures_delete_private_pitch_file($post_id) {
    if (NUVENTURES_PITCH_SUBMISSION_POST_TYPE === get_post_type($post_id)) {
        $attachment_id = absint(get_post_meta($post_id, 'pitch_deck_attachment_id', true));
        if ($attachment_id) {
            wp_delete_attachment($attachment_id, true);
        }
        return;
    }

    if ('attachment' === get_post_type($post_id) && get_post_meta($post_id, '_nuventures_private_pitch_deck', true)) {
        $path = get_attached_file($post_id);
        if ($path && is_file($path)) {
            wp_delete_file($path);
        }
    }
}
add_action('before_delete_post', 'nuventures_delete_private_pitch_file');
