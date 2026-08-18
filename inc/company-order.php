<?php
/**
 * Administrator-only visual ordering for Portfolio companies.
 *
 * @package NuVentures
 */

defined('ABSPATH') || exit;

const NUVENTURES_COMPANY_ORDER_PAGE       = 'nuventures-company-order';
const NUVENTURES_COMPANY_ORDER_NONCE      = 'nuventures_company_order';
const NUVENTURES_COMPANY_ORDER_AJAX_ACTION = 'nuventures_save_company_order';

/** Add Portfolio Order beneath the Company post type menu. */
function nuventures_add_company_order_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $GLOBALS['nuventures_company_order_hook'] = add_submenu_page(
        'edit.php?post_type=company',
        __('Portfolio Order', 'nuventures'),
        __('Portfolio Order', 'nuventures'),
        'manage_options',
        NUVENTURES_COMPANY_ORDER_PAGE,
        'nuventures_render_company_order_page'
    );
}
add_action('admin_menu', 'nuventures_add_company_order_page', 20);

/** Resolve a Company logo across supported ACF image return formats. */
function nuventures_company_order_logo($company_id, $company_name) {
    if (!function_exists('get_field')) {
        return '';
    }

    $logo    = get_field('logo', $company_id);
    $logo_id = 0;
    $url     = '';
    $alt     = $company_name . ' logo';

    if (is_numeric($logo)) {
        $logo_id = absint($logo);
    } elseif (is_array($logo)) {
        $logo_id = isset($logo['ID']) ? absint($logo['ID']) : (isset($logo['id']) ? absint($logo['id']) : 0);
        $url     = isset($logo['url']) ? (string) $logo['url'] : '';
        $alt     = !empty($logo['alt']) ? (string) $logo['alt'] : $alt;
    } elseif (is_string($logo)) {
        $url = $logo;
    }

    if ($logo_id) {
        return wp_get_attachment_image(
            $logo_id,
            'thumbnail',
            false,
            array(
                'class'    => 'nuventures-company-order__logo',
                'alt'      => $alt,
                'loading'  => 'lazy',
                'decoding' => 'async',
            )
        );
    }

    if ($url) {
        return '<img class="nuventures-company-order__logo" src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '" loading="lazy" decoding="async">';
    }

    return '<span class="nuventures-company-order__logo-placeholder" aria-hidden="true"></span>';
}

/** Render the compact sortable Company list. */
function nuventures_render_company_order_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to order Portfolio companies.', 'nuventures'), '', array('response' => 403));
    }

    $companies = get_posts(
        array(
            'post_type'              => 'company',
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'orderby'                => array(
                'menu_order' => 'ASC',
                'title'      => 'ASC',
            ),
            'order'                  => 'ASC',
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
        )
    );
    ?>
    <div class="wrap nuventures-company-order">
        <h1><?php esc_html_e('Portfolio Order', 'nuventures'); ?></h1>
        <p><?php esc_html_e('Drag the companies into the order in which they should initially appear on the Portfolio page, then save.', 'nuventures'); ?></p>

        <?php if ($companies) : ?>
            <ul class="nuventures-company-order__list" data-company-order-list>
                <?php foreach ($companies as $company) : ?>
                    <?php
                    $company_name = function_exists('get_field') ? get_field('company_name', $company->ID) : '';
                    $company_name = is_string($company_name) && trim($company_name) ? trim($company_name) : get_the_title($company);
                    ?>
                    <li class="nuventures-company-order__item" data-company-id="<?php echo esc_attr($company->ID); ?>">
                        <button
                            class="nuventures-company-order__handle"
                            type="button"
                            aria-label="<?php echo esc_attr(sprintf(__('Drag to reorder %s', 'nuventures'), $company_name)); ?>"
                            title="<?php esc_attr_e('Drag to reorder', 'nuventures'); ?>"
                        >
                            <span class="dashicons dashicons-move" aria-hidden="true"></span>
                        </button>
                        <span class="nuventures-company-order__media">
                            <?php echo nuventures_company_order_logo($company->ID, $company_name); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Helper escapes generated markup. ?>
                        </span>
                        <strong class="nuventures-company-order__name"><?php echo esc_html($company_name); ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>

            <p class="submit">
                <button class="button button-primary" type="button" data-company-order-save><?php esc_html_e('Save Order', 'nuventures'); ?></button>
                <span class="nuventures-company-order__status" data-company-order-status role="status" aria-live="polite"></span>
            </p>
        <?php else : ?>
            <p><?php esc_html_e('No published companies were found.', 'nuventures'); ?></p>
        <?php endif; ?>
    </div>
    <?php
}

/** Enqueue drag-and-drop assets only on the Portfolio Order screen. */
function nuventures_enqueue_company_order_assets($hook_suffix) {
    $screen_hook = isset($GLOBALS['nuventures_company_order_hook']) ? $GLOBALS['nuventures_company_order_hook'] : '';
    if (!$screen_hook || $hook_suffix !== $screen_hook || !current_user_can('manage_options')) {
        return;
    }

    $script_path = get_template_directory() . '/assets/admin/company-order.js';
    $style_path  = get_template_directory() . '/assets/admin/company-order.css';

    wp_enqueue_style('dashicons');
    wp_enqueue_style(
        'nuventures-company-order',
        get_template_directory_uri() . '/assets/admin/company-order.css',
        array(),
        file_exists($style_path) ? (string) filemtime($style_path) : null
    );
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script(
        'nuventures-company-order',
        get_template_directory_uri() . '/assets/admin/company-order.js',
        array('jquery', 'jquery-ui-sortable'),
        file_exists($script_path) ? (string) filemtime($script_path) : null,
        true
    );
    wp_localize_script(
        'nuventures-company-order',
        'nuventuresCompanyOrder',
        array(
            'ajaxUrl'  => admin_url('admin-ajax.php'),
            'action'   => NUVENTURES_COMPANY_ORDER_AJAX_ACTION,
            'nonce'    => wp_create_nonce(NUVENTURES_COMPANY_ORDER_NONCE),
            'saving'   => __('Saving…', 'nuventures'),
            'saved'    => __('Portfolio order saved.', 'nuventures'),
            'error'    => __('The order could not be saved. Please try again.', 'nuventures'),
        )
    );
}
add_action('admin_enqueue_scripts', 'nuventures_enqueue_company_order_assets');

/** Save a complete, verified Company order through the administrator AJAX API. */
function nuventures_save_company_order() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You are not allowed to order Portfolio companies.', 'nuventures')), 403);
    }

    if (!check_ajax_referer(NUVENTURES_COMPANY_ORDER_NONCE, 'nonce', false)) {
        wp_send_json_error(array('message' => __('Security check failed. Refresh the page and try again.', 'nuventures')), 403);
    }

    $submitted = isset($_POST['company_ids']) ? wp_unslash($_POST['company_ids']) : array();
    if (!is_array($submitted)) {
        wp_send_json_error(array('message' => __('Invalid company order.', 'nuventures')), 400);
    }

    $company_ids = array_values(array_unique(array_filter(array_map('absint', $submitted))));
    $published_ids = get_posts(
        array(
            'post_type'              => 'company',
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'orderby'                => 'ID',
            'order'                  => 'ASC',
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        )
    );

    $submitted_set = $company_ids;
    $published_set = array_map('absint', $published_ids);
    sort($submitted_set, SORT_NUMERIC);
    sort($published_set, SORT_NUMERIC);

    if (!$company_ids || $submitted_set !== $published_set) {
        wp_send_json_error(array('message' => __('The submitted order must contain every published Company exactly once.', 'nuventures')), 400);
    }

    foreach ($company_ids as $menu_order => $company_id) {
        if ('company' !== get_post_type($company_id)) {
            wp_send_json_error(array('message' => __('Invalid Company in the submitted order.', 'nuventures')), 400);
        }

        $updated = wp_update_post(
            array(
                'ID'         => $company_id,
                'menu_order' => $menu_order,
            ),
            true
        );

        if (is_wp_error($updated)) {
            wp_send_json_error(array('message' => __('One or more companies could not be reordered.', 'nuventures')), 500);
        }
    }

    wp_send_json_success(array('message' => __('Portfolio order saved.', 'nuventures')));
}
add_action('wp_ajax_' . NUVENTURES_COMPANY_ORDER_AJAX_ACTION, 'nuventures_save_company_order');
