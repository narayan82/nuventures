<?php
/**
 * Homepage companies section.
 *
 * @package NuVentures
 */

$companies_query = new WP_Query(
    array(
        'post_type'              => 'nu-journey',
        'post_status'            => 'publish',
        'posts_per_page'         => -1,
        'orderby'                => 'date',
        'order'                  => 'ASC',
        'no_found_rows'          => true,
        'ignore_sticky_posts'    => true,
        'update_post_term_cache' => false,
    )
);

$resolve_image = static function ($image, $fallback_alt) {
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
?>

<section class="companies-we-back" data-companies-carousel aria-labelledby="companies-we-back-title">
    <h2 class="companies-we-back__title" id="companies-we-back-title"><?php esc_html_e('Nu Journeys with our Bold Founders', 'nuventures'); ?></h2>
     <p class="podcast-quotes__supporting">
                <?php esc_html_e('Explore the  stages as Brands go from an Idea to reality', 'nuventures'); ?>
            </p>
    <div class="companies-we-back__track" data-companies-track>
        <?php foreach ($companies_query->posts as $company_post) : ?>
            <?php
            $company_id   = $company_post->ID;
            $company_name = get_the_title($company_post);
            $main_image   = $resolve_image(get_field('main_photo', $company_id), $company_name);
            $logo_image   = $resolve_image(get_field('logo', $company_id), $company_name . ' logo');
            $founders     = get_field('the_bold_founders', $company_id);

            if (is_array($founders)) {
                $founders = implode(', ', array_filter($founders));
            }
            ?>
            <a class="companies-we-back__card" href="<?php echo esc_url(get_permalink($company_id)); ?>">
                <span class="companies-we-back__media">
                    <?php if ($main_image['id']) : ?>
                        <?php
                        echo wp_get_attachment_image(
                            $main_image['id'],
                            'large',
                            false,
                            array(
                                'class'    => 'companies-we-back__main-image',
                                'alt'      => $main_image['alt'],
                                'loading'  => 'lazy',
                                'decoding' => 'async',
                                'sizes'    => '(max-width: 550px) 100vw, 390px',
                            )
                        );
                        ?>
                    <?php elseif ($main_image['url']) : ?>
                        <img
                            class="companies-we-back__main-image"
                            src="<?php echo esc_url($main_image['url']); ?>"
                            alt="<?php echo esc_attr($main_image['alt']); ?>"
                            loading="lazy"
                            decoding="async"
                        >
                    <?php endif; ?>
                </span>

                <span class="companies-we-back__content">
                    <span class="companies-we-back__logo">
                        <?php if ($logo_image['id']) : ?>
                            <?php
                            echo wp_get_attachment_image(
                                $logo_image['id'],
                                'medium',
                                false,
                                array(
                                    'class'    => 'companies-we-back__logo-image',
                                    'alt'      => $logo_image['alt'],
                                    'loading'  => 'lazy',
                                    'decoding' => 'async',
                                    'sizes'    => '70px',
                                )
                            );
                            ?>
                        <?php elseif ($logo_image['url']) : ?>
                            <img
                                class="companies-we-back__logo-image"
                                src="<?php echo esc_url($logo_image['url']); ?>"
                                alt="<?php echo esc_attr($logo_image['alt']); ?>"
                                loading="lazy"
                                decoding="async"
                            >
                        <?php endif; ?>
                    </span>

                    <span class="companies-we-back__founders">
                        <span class="companies-we-back__eyebrow"><?php esc_html_e('THE BOLD FOUNDERS', 'nuventures'); ?></span>
                        <?php if ($founders) : ?>
                            <span class="companies-we-back__founder-names"><?php echo nl2br(esc_html($founders)); ?></span>
                        <?php endif; ?>
                    </span>
                </span>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="companies-we-back__actions" data-companies-controls>
        <button class="companies-we-back__control companies-we-back__control--previous" type="button" aria-label="<?php esc_attr_e('Previous companies', 'nuventures'); ?>" data-companies-previous hidden>
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/investors/chevron-right.svg'); ?>" alt="" width="8" height="14">
        </button>

        <a class="companies-we-back__cta" href="<?php echo esc_url(home_url('/portfolio/')); ?>"><?php esc_html_e('View our portfolio', 'nuventures'); ?></a>

        <button class="companies-we-back__control" type="button" aria-label="<?php esc_attr_e('Next companies', 'nuventures'); ?>" data-companies-next hidden>
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/investors/chevron-right.svg'); ?>" alt="" width="8" height="14">
        </button>
    </div>
</section>

<?php wp_reset_postdata(); ?>
