<?php
/**
 * Safe fallback template for posts and archives.
 *
 * @package NuVentures
 */

get_header();
?>

<main id="main-content" class="single-post-page">
    <?php if (have_posts()) : ?>
        <header class="single-post-page__header">
            <h1 class="single-post-page__title">
                <?php
                if (is_home() && !is_front_page()) {
                    single_post_title();
                } elseif (is_archive()) {
                    the_archive_title();
                } else {
                    esc_html_e('Latest updates', 'nuventures');
                }
                ?>
            </h1>
            <?php the_archive_description('<div class="single-post-page__description">', '</div>'); ?>
        </header>

        <div class="single-post-page__content">
            <?php while (have_posts()) : ?>
                <?php the_post(); ?>
                <article <?php post_class(); ?>>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <?php the_excerpt(); ?>
                </article>
            <?php endwhile; ?>
        </div>

        <?php the_posts_pagination(); ?>
    <?php else : ?>
        <header class="single-post-page__header">
            <h1 class="single-post-page__title"><?php esc_html_e('Nothing found', 'nuventures'); ?></h1>
        </header>
        <div class="single-post-page__content">
            <p><?php esc_html_e('We could not find any content here.', 'nuventures'); ?></p>
            <p><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Return home', 'nuventures'); ?></a></p>
        </div>
    <?php endif; ?>
</main>

<?php get_footer(); ?>
