<?php
/**
 * 404 page template.
 *
 * @package NuVentures
 */

get_header();
?>

<main id="main-content" class="single-post-page">
    <article class="single-post-page__article">
        <header class="single-post-page__header">
            <h1 class="single-post-page__title"><?php esc_html_e('Page not found', 'nuventures'); ?></h1>
        </header>

        <div class="single-post-page__content">
            <p><?php esc_html_e('The page you are looking for may have moved or no longer exists.', 'nuventures'); ?></p>
            <nav aria-label="<?php esc_attr_e('Helpful links', 'nuventures'); ?>">
                <ul>
                    <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'nuventures'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/portfolio/')); ?>"><?php esc_html_e('Portfolio', 'nuventures'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/about/')); ?>"><?php esc_html_e('About', 'nuventures'); ?></a></li>
                    <li><a href="<?php echo esc_url(home_url('/nupod/')); ?>"><?php esc_html_e('NuPOD', 'nuventures'); ?></a></li>
                </ul>
            </nav>
        </div>
    </article>
</main>

<?php get_footer(); ?>
