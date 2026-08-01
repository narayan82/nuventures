<?php
/**
 * About page template.
 *
 * @package NuVentures
 */

get_header();
?>

<main class="about-page">
    <?php get_template_part('template-parts/about/intro'); ?>
    <?php get_template_part('template-parts/about/team'); ?>
</main>

<?php get_footer(); ?>
