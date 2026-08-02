<?php
/**
 * Person testimonial panel.
 *
 * @package NuVentures
 */

$testimonial = isset($args['testimonial']) && $args['testimonial'] instanceof WP_Post ? $args['testimonial'] : null;
if (!$testimonial) {
    return;
}

$testimonial_id = $testimonial->ID;
$quote          = get_field('testimonial', $testimonial_id);
if (!$quote) {
    return;
}

$name         = get_field('name', $testimonial_id);
$designation  = get_field('designation', $testimonial_id);
$company_name = get_field('company_name', $testimonial_id);
$company_url  = get_field('company_website', $testimonial_id);
$photo        = nuventures_get_image_details(get_field('photo', $testimonial_id), $name);
?>
<section class="person-testimonial" aria-label="<?php esc_attr_e('Testimonial', 'nuventures'); ?>">
    <blockquote>
        <img class="person-testimonial__quote-icon" src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/nu-journey/quote.svg'); ?>" alt="" width="24" height="18">
        <div><?php echo wp_kses_post(wpautop($quote)); ?></div>
    </blockquote>
    <?php if ($name || $designation || $company_name) : ?>
        <div class="person-testimonial__attribution">
            <?php if ($photo['id'] || $photo['url']) : ?>
                <?php if ($photo['id']) : ?>
                    <?php echo wp_get_attachment_image($photo['id'], 'thumbnail', false, array('alt' => $photo['alt'])); ?>
                <?php else : ?>
                    <img src="<?php echo esc_url($photo['url']); ?>" alt="<?php echo esc_attr($photo['alt']); ?>">
                <?php endif; ?>
            <?php endif; ?>
            <div>
                <?php if ($name) : ?><strong><?php echo esc_html($name); ?></strong><?php endif; ?>
                <span>
                    <?php echo esc_html($designation); ?>
                    <?php if ($designation && $company_name) : ?>, <?php endif; ?>
                    <?php if ($company_name && $company_url) : ?>
                        <a href="<?php echo esc_url(is_array($company_url) ? ($company_url['url'] ?? '') : $company_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($company_name); ?></a>
                    <?php elseif ($company_name) : ?>
                        <?php echo esc_html($company_name); ?>
                    <?php endif; ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</section>
