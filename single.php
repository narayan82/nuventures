<?php
/**
 * Default single-post template.
 *
 * Used by posts created under Posts in WordPress Admin.
 *
 * @package NuVentures
 */

get_header();
?>

<main class="single-post-page">
    <?php while (have_posts()) : ?>
        <?php the_post(); ?>

        <article <?php post_class('single-post-page__article'); ?>>
            <header class="single-post-page__header">
                <a class="single-post-page__back" href="<?php echo esc_url(home_url('/initiatives/')); ?>">
                    <img
                        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/investors/chevron-left-red.svg'); ?>"
                        alt=""
                        width="8"
                        height="14"
                        aria-hidden="true"
                    >
                    <?php esc_html_e('See all initiatives', 'nuventures'); ?>
                </a>
                <h1 class="single-post-page__title"><?php the_title(); ?></h1>
            </header>

            <div class="single-post-page__content">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>
</main>

<?php get_footer(); ?>
