<?php
/**
 * Template Name: Basic Page
 */

get_header();
?>

<main id="main-content" class="basic-page">
    <div class="basic-page__container">

        <?php
        while (have_posts()) :
            the_post();
        ?>

            <article <?php post_class('basic-page__article'); ?>>

                <header class="basic-page__header">
                    <h1 class="basic-page__title">
                        <?php the_title(); ?>
                    </h1>
                </header>

                <div class="basic-page__content">
                    <?php the_content(); ?>
                </div>

            </article>

        <?php endwhile; ?>

    </div>
</main>

<?php
get_footer();