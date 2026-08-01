<?php
/**
 * NuPOD speaker and sort controls.
 *
 * @package NuVentures
 */

$speaker_options = isset($args['speakers']) && is_array($args['speakers']) ? $args['speakers'] : array();
$chevron_url     = get_template_directory_uri() . '/assets/images/portfolio/chevron-down.svg';
?>

<div class="portfolio-companies__toolbar nupod-list__toolbar">
    <label class="portfolio-companies__filter">
        <span class="screen-reader-text"><?php esc_html_e('Filter by Speaker', 'nuventures'); ?></span>
        <select data-nupod-speaker>
            <option value=""><?php esc_html_e('Filter by Speaker', 'nuventures'); ?></option>
            <?php foreach ($speaker_options as $speaker_id => $speaker_name) : ?>
                <option value="<?php echo esc_attr($speaker_id); ?>"><?php echo esc_html($speaker_name); ?></option>
            <?php endforeach; ?>
        </select>
        <img src="<?php echo esc_url($chevron_url); ?>" alt="" width="24" height="24">
    </label>

    <label class="portfolio-companies__sort">
        <span class="screen-reader-text"><?php esc_html_e('Sort podcasts', 'nuventures'); ?></span>
        <select data-nupod-sort>
            <option value="recent"><?php esc_html_e('Sort (Recent First)', 'nuventures'); ?></option>
            <option value="oldest"><?php esc_html_e('Sort (Oldest First)', 'nuventures'); ?></option>
            <option value="az"><?php esc_html_e('Sort (A-Z)', 'nuventures'); ?></option>
            <option value="za"><?php esc_html_e('Sort (Z-A)', 'nuventures'); ?></option>
        </select>
        <img src="<?php echo esc_url($chevron_url); ?>" alt="" width="24" height="24">
    </label>
</div>
