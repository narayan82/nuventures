<?php
/**
 * Homepage latest initiatives and updates.
 *
 * @package NuVentures
 */

$latest_updates = new WP_Query(
    array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 8,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    )
);

if (!$latest_updates->have_posts()) {
    return;
}
?>

<section class="latest-updates" data-latest-updates-carousel aria-labelledby="latest-updates-title">
    <h2 class="latest-updates__title" id="latest-updates-title">
        <?php esc_html_e('Latest Initiatives & Updates', 'nuventures'); ?>
    </h2>

    <ul
        class="latest-updates__track"
        data-latest-updates-track
        aria-label="<?php esc_attr_e('Latest initiatives and updates', 'nuventures'); ?>"
        tabindex="0"
    >
        <?php
        while ($latest_updates->have_posts()) :
            $latest_updates->the_post();
            $categories     = get_the_category();
            $first_category = $categories ? $categories[0] : null;
            ?>
            <li class="latest-updates__item">
                <a class="latest-updates__card" href="<?php echo esc_url(get_permalink()); ?>">
                    <time class="latest-updates__date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                        <?php echo esc_html(get_the_date('j-M-Y')); ?>
                    </time>

                    <span class="latest-updates__post-title"><?php echo esc_html(get_the_title()); ?></span>

                    <?php if ($first_category instanceof WP_Term) : ?>
                        <span class="latest-updates__category"><?php echo esc_html($first_category->name); ?></span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endwhile; ?>
    </ul>

    <?php wp_reset_postdata(); ?>

    <div class="latest-updates__controls" data-latest-updates-controls>
        <button
            class="latest-updates__control latest-updates__control--previous"
            type="button"
            aria-label="<?php esc_attr_e('Previous updates', 'nuventures'); ?>"
            data-latest-updates-previous
            hidden
        >
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/investors/chevron-right.svg'); ?>" alt="" width="8" height="14">
        </button>

        <?php // TODO: Replace with the initiatives archive URL once that destination is defined. ?>
        <a class="latest-updates__cta" href="#">
            <?php esc_html_e('See All Initiatives', 'nuventures'); ?>
        </a>

        <button
            class="latest-updates__control"
            type="button"
            aria-label="<?php esc_attr_e('Next updates', 'nuventures'); ?>"
            data-latest-updates-next
            hidden
        >
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/investors/chevron-right.svg'); ?>" alt="" width="8" height="14">
        </button>
    </div>
</section>
