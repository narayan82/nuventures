<?php
/**
 * Portfolio page template.
 *
 * @package NuVentures
 */

$portfolio_resolve_image = static function ($image, $fallback_alt) {
    $image_id  = 0;
    $image_url = '';
    $image_alt = '';

    if (is_numeric($image)) {
        $image_id = (int) $image;
    } elseif (is_array($image)) {
        $image_id  = isset($image['ID']) ? (int) $image['ID'] : (isset($image['id']) ? (int) $image['id'] : 0);
        $image_url = isset($image['url']) ? $image['url'] : '';
        $image_alt = isset($image['alt']) ? $image['alt'] : '';
    } elseif (is_string($image)) {
        $image_url = $image;
    }

    if ($image_id && !$image_alt) {
        $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
    }

    return array(
        'id'  => $image_id,
        'url' => $image_url,
        'alt' => $image_alt ? $image_alt : $fallback_alt,
    );
};

get_header();
?>

<main class="portfolio-page" id="main-content">
    <?php get_template_part('template-parts/portfolio/journeys', null, array('resolve_image' => $portfolio_resolve_image)); ?>
    <?php get_template_part('template-parts/portfolio/company-grid', null, array('resolve_image' => $portfolio_resolve_image)); ?>
</main>

<?php get_footer(); ?>
