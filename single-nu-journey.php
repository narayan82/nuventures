<?php
/**
 * Single Nu Journey template.
 *
 * @package NuVentures
 */

get_header();

while (have_posts()) :
    the_post();

    $journey_id = get_the_ID();

    $resolve_image = static function ($image, $fallback_alt = '') {
        $image_id  = 0;
        $image_url = '';
        $image_alt = '';

        if (is_numeric($image)) {
            $image_id = (int) $image;
        } elseif (is_array($image)) {
            $image_id  = isset($image['ID']) ? (int) $image['ID'] : (isset($image['id']) ? (int) $image['id'] : 0);
            $image_url = isset($image['url']) ? (string) $image['url'] : '';
            $image_alt = isset($image['alt']) ? (string) $image['alt'] : '';
        } elseif (is_string($image)) {
            $image_url = $image;
        }

        if ($image_id && !$image_alt) {
            $image_alt = (string) get_post_meta($image_id, '_wp_attachment_image_alt', true);
        }

        return array(
            'id'  => $image_id,
            'url' => $image_url,
            'alt' => $image_alt ?: $fallback_alt,
        );
    };

    $shared_args = array(
        'journey_id'    => $journey_id,
        'resolve_image' => $resolve_image,
    );
    ?>

    <main class="nu-journey">
        <article class="nu-journey__article">
            <?php get_template_part('template-parts/nu-journey/intro', null, $shared_args); ?>
            <?php get_template_part('template-parts/nu-journey/timeline', null, $shared_args); ?>
            <?php get_template_part('template-parts/nu-journey/numbers', null, $shared_args); ?>
            <?php get_template_part('template-parts/nu-journey/testimonial', null, $shared_args); ?>
            <?php get_template_part('template-parts/nu-journey/closing', null, $shared_args); ?>
        </article>
    </main>

<?php endwhile; ?>

<?php get_footer(); ?>
