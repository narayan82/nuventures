<?php
/**
 * Initiatives listing page template.
 *
 * @package NuVentures
 */

$initiatives_query = new WP_Query(
    array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => -1,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    )
);

$initiatives       = array();
$category_options  = array();

foreach ($initiatives_query->posts as $initiative_post) {
    $initiative_id = $initiative_post->ID;
    $categories    = get_the_category($initiative_id);

    foreach ($categories as $category) {
        $category_options[$category->slug] = $category->name;
    }

    $initiatives[] = array(
        'post'           => $initiative_post,
        'featured_image' => get_post_thumbnail_id($initiative_id),
        'categories'     => $categories,
    );
}

wp_reset_postdata();
asort($category_options, SORT_NATURAL | SORT_FLAG_CASE);

get_header();
?>

<main class="initiatives-page" id="main-content">
    <?php get_template_part('template-parts/initiatives/intro'); ?>
    <?php get_template_part('template-parts/initiatives/filters', null, array('categories' => $category_options)); ?>
    <?php get_template_part('template-parts/initiatives/grid', null, array('initiatives' => $initiatives)); ?>
</main>

<?php get_footer(); ?>
