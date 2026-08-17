<?php
/**
 * NuJourney related testimonial.
 *
 * @package NuVentures
 */

$journey_id    = isset($args['journey_id']) ? (int) $args['journey_id'] : get_the_ID();
$resolve_image = isset($args['resolve_image']) && is_callable($args['resolve_image']) ? $args['resolve_image'] : null;
$query         = new WP_Query(
    array(
        'post_type'              => 'testimonial',
        'post_status'            => 'publish',
        'posts_per_page'         => 1,
        'no_found_rows'          => true,
        'ignore_sticky_posts'    => true,
        'update_post_term_cache' => false,
        'meta_query'             => array(
            array(
                'key'     => 'nujourney',
                'value'   => '"' . $journey_id . '"',
                'compare' => 'LIKE',
            ),
        ),
    )
);

if (!$query->have_posts()) {
    wp_reset_postdata();
    return;
}

$query->the_post();
$testimonial_id = get_the_ID();
$quote          = get_field('testimonial', $testimonial_id);
$name           = get_field('name', $testimonial_id);
$designation    = get_field('designation', $testimonial_id);
$company_name   = get_field('company_name', $testimonial_id);
$company_url    = get_field('company_website', $testimonial_id);
$photo          = $resolve_image
    ? $resolve_image(get_field('photo', $testimonial_id), $name ? $name : __('Testimonial author', 'nuventures'))
    : array('id' => 0, 'url' => '', 'alt' => '');

if (is_array($company_url)) {
    $company_url = isset($company_url['url']) ? $company_url['url'] : '';
}

if (!$quote) {
    wp_reset_postdata();
    return;
}

$quote_icon = get_template_directory_uri() . '/assets/images/nu-journey/quote.svg';
?>

<section class="nu-journey-testimonial" aria-label="<?php esc_attr_e('Testimonial', 'nuventures'); ?>">
    <blockquote class="nu-journey-testimonial__quote">
        <img src="<?php echo esc_url($quote_icon); ?>" alt="" width="24" height="18" aria-hidden="true">
        <div><?php echo wp_kses_post(wpautop($quote)); ?></div>
    </blockquote>

    <?php if ($name || $designation || $company_name || !empty($photo['id']) || !empty($photo['url'])) : ?>
        <div class="nu-journey-testimonial__attribution">
            <?php if (!empty($photo['id'])) : ?>
                <?php
                echo wp_get_attachment_image(
                    $photo['id'],
                    'thumbnail',
                    false,
                    array(
                        'class'    => 'nu-journey-testimonial__photo',
                        'alt'      => $photo['alt'],
                        'loading'  => 'lazy',
                        'decoding' => 'async',
                        'sizes'    => '56px',
                    )
                );
                ?>
            <?php elseif (!empty($photo['url'])) : ?>
                <img
                    class="nu-journey-testimonial__photo"
                    src="<?php echo esc_url($photo['url']); ?>"
                    alt="<?php echo esc_attr($photo['alt']); ?>"
                    width="56"
                    height="56"
                    loading="lazy"
                    decoding="async"
                >
            <?php endif; ?>

            <div class="nu-journey-testimonial__attribution-copy">
                <?php if ($name) : ?>
                    <strong><?php echo esc_html($name); ?></strong>
                <?php endif; ?>

                <?php if ($designation || $company_name) : ?>
                    <span>
                        <?php echo esc_html($designation); ?>
                        <?php if ($designation && $company_name) : ?>, <?php endif; ?>
                        <?php if ($company_name && $company_url) : ?>
                            <a href="<?php echo esc_url($company_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($company_name); ?></a>
                        <?php elseif ($company_name) : ?>
                            <?php echo esc_html($company_name); ?>
                        <?php endif; ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>

<?php wp_reset_postdata(); ?>
