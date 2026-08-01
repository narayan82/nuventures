<?php
/**
 * Initiatives page search, category, and sort controls.
 *
 * @package NuVentures
 */

$category_options = isset($args['categories']) && is_array($args['categories']) ? $args['categories'] : array();
$asset_path       = get_template_directory_uri() . '/assets/images/portfolio/';
?>

<div class="portfolio-companies__toolbar initiatives-list__toolbar" data-initiatives-toolbar>
    <label class="portfolio-companies__search initiatives-list__search">
        <span class="screen-reader-text"><?php esc_html_e('Search initiatives', 'nuventures'); ?></span>
        <img src="<?php echo esc_url($asset_path . 'search.svg'); ?>" alt="" width="24" height="24">
        <input
            type="search"
            placeholder="<?php esc_attr_e('Search initiatives', 'nuventures'); ?>"
            data-initiatives-search
        >
    </label>

    <label class="portfolio-companies__filter">
        <span class="screen-reader-text"><?php esc_html_e('Filter by Category', 'nuventures'); ?></span>
        <select data-initiatives-category>
            <option value=""><?php esc_html_e('Filter by Category', 'nuventures'); ?></option>
            <?php foreach ($category_options as $category_slug => $category_name) : ?>
                <option value="<?php echo esc_attr($category_slug); ?>"><?php echo esc_html($category_name); ?></option>
            <?php endforeach; ?>
        </select>
        <img src="<?php echo esc_url($asset_path . 'chevron-down.svg'); ?>" alt="" width="24" height="24">
    </label>

    <label class="portfolio-companies__sort">
        <span class="screen-reader-text"><?php esc_html_e('Sort initiatives', 'nuventures'); ?></span>
        <select data-initiatives-sort>
            <option value="recent"><?php esc_html_e('Sort (Recent first)', 'nuventures'); ?></option>
            <option value="oldest"><?php esc_html_e('Sort (Oldest first)', 'nuventures'); ?></option>
        </select>
        <img src="<?php echo esc_url($asset_path . 'chevron-down.svg'); ?>" alt="" width="24" height="24">
    </label>
</div>
