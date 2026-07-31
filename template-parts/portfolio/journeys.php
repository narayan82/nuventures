<?php
/**
 * Portfolio page Nu Journeys carousel.
 *
 * @package NuVentures
 */

$resolve_image = isset($args['resolve_image']) && is_callable($args['resolve_image']) ? $args['resolve_image'] : null;

if (!$resolve_image) {
    return;
}

$journeys_query = new WP_Query(
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

if (!$journeys_query->have_posts()) {
    return;
}

$asset_path = get_template_directory_uri() . '/assets/images/portfolio/';
?>

<section class="portfolio-journeys" data-portfolio-journeys aria-labelledby="portfolio-journeys-title">
    <h1 class="portfolio-page__heading" id="portfolio-journeys-title"><?php esc_html_e('Nu-journeys with the founders', 'nuventures'); ?></h1>

    <div class="portfolio-journeys__track" data-portfolio-journeys-track tabindex="0" aria-label="<?php esc_attr_e('Nu journeys', 'nuventures'); ?>">
        <?php
        while ($journeys_query->have_posts()) :
            $journeys_query->the_post();
            $journey_id  = get_the_ID();
            $title       = get_field('long_title', $journey_id);
            $description = get_field('description', $journey_id);
            $image       = $resolve_image(get_field('main_photo', $journey_id), get_the_title());
            ?>
            <a class="portfolio-journeys__card" href="<?php echo esc_url(get_permalink($journey_id)); ?>">
                <span class="portfolio-journeys__media">
                    <?php if ($image['id']) : ?>
                        <?php
                        echo wp_get_attachment_image(
                            $image['id'],
                            'large',
                            false,
                            array(
                                'class'    => 'portfolio-journeys__image',
                                'alt'      => $image['alt'],
                                'loading'  => 'lazy',
                                'decoding' => 'async',
                                'sizes'    => '(max-width: 550px) calc(100vw - 40px), 250px',
                            )
                        );
                        ?>
                    <?php elseif ($image['url']) : ?>
                        <img class="portfolio-journeys__image" src="<?php echo esc_url($image['url']); ?>" alt="<?php echo esc_attr($image['alt']); ?>" loading="lazy" decoding="async">
                    <?php endif; ?>
                </span>

                <span class="portfolio-journeys__content">
                    <span class="portfolio-journeys__title"><?php echo esc_html($title ? $title : get_the_title()); ?></span>
                    <?php if ($description) : ?>
                        <span class="portfolio-journeys__description"><?php echo esc_html(wp_strip_all_tags($description)); ?></span>
                    <?php endif; ?>
                    <span class="portfolio-journeys__cta">
                        <img src="<?php echo esc_url($asset_path . 'arrow-right-white.svg'); ?>" alt="" width="24" height="24">
                        <?php esc_html_e('Explore Their Journey', 'nuventures'); ?>
                    </span>
                </span>
            </a>
        <?php endwhile; ?>
    </div>

    <div class="portfolio-journeys__controls" data-portfolio-journeys-controls>
        <button class="portfolio-journeys__control portfolio-journeys__control--previous" type="button" aria-label="<?php esc_attr_e('Previous journey', 'nuventures'); ?>" data-portfolio-journeys-previous hidden>
            <img src="<?php echo esc_url($asset_path . 'arrow-right-control.svg'); ?>" alt="" width="16" height="16">
        </button>
        <button class="portfolio-journeys__control" type="button" aria-label="<?php esc_attr_e('Next journey', 'nuventures'); ?>" data-portfolio-journeys-next hidden>
            <img src="<?php echo esc_url($asset_path . 'arrow-right-control.svg'); ?>" alt="" width="16" height="16">
        </button>
    </div>
</section>

<?php wp_reset_postdata(); ?>
