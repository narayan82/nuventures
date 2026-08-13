<?php
/**
 * Single Person template.
 *
 * @package NuVentures
 */

get_header();

while (have_posts()) :
    the_post();

    $person_id   = get_the_ID();
    $person_name = get_field('name', $person_id);
    $person_name = $person_name ? $person_name : get_the_title($person_id);
    $first_name  = trim((string) strtok($person_name, ' '));

    $taxonomy_terms = get_the_terms($person_id, 'partner-interest');
    $taxonomy_terms = is_array($taxonomy_terms) ? $taxonomy_terms : array();

    $testimonial_query = new WP_Query(
        array(
            'post_type'              => 'testimonial',
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'orderby'                => 'date',
            'order'                  => 'DESC',
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_term_cache' => false,
        )
    );
    $testimonials = array_values(
        array_filter(
            $testimonial_query->posts,
            static function ($testimonial) use ($person_id) {
                return in_array($person_id, nuventures_normalize_related_ids(get_field('select_partners', $testimonial->ID)), true);
            }
        )
    );

    $podcast_query = new WP_Query(
        array(
            'post_type'           => 'podcast',
            'post_status'         => 'publish',
            'posts_per_page'      => -1,
            'orderby'             => 'date',
            'order'               => 'DESC',
            'no_found_rows'       => true,
            'ignore_sticky_posts' => true,
        )
    );
    $podcasts = array_values(
        array_filter(
            $podcast_query->posts,
            static function ($podcast) use ($person_id) {
                return in_array($person_id, nuventures_normalize_related_ids(get_field('podcast_by', $podcast->ID)), true);
            }
        )
    );

    $article_query = new WP_Query(
        array(
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => -1,
            'orderby'             => 'date',
            'order'               => 'DESC',
            'no_found_rows'       => true,
            'ignore_sticky_posts' => true,
        )
    );
    $articles = array_values(
        array_filter(
            $article_query->posts,
            static function ($article) use ($person_id) {
                return in_array($person_id, nuventures_normalize_related_ids(get_field('people_involved', $article->ID)), true);
            }
        )
    );

    $shared_args = array(
        'person_id'      => $person_id,
        'person_name'    => $person_name,
        'first_name'     => $first_name,
        'taxonomy_terms' => $taxonomy_terms,
        'testimonials'   => array_slice($testimonials, 0, 2),
        'podcasts'       => $podcasts,
        'articles'       => $articles,
    );
    ?>
    <main class="person-page" id="main-content">
        <article class="person-page__layout">
            <?php get_template_part('template-parts/person/profile', null, $shared_args); ?>

            <div class="person-page__content">
                <?php get_template_part('template-parts/person/profile-points', null, $shared_args); ?>

                <?php if (!empty($testimonials[0])) : ?>
                    <?php get_template_part('template-parts/person/testimonial', null, array('testimonial' => $testimonials[0])); ?>
                <?php endif; ?>

                <?php if ($taxonomy_terms) : ?>
                    <section class="person-interests" aria-labelledby="person-interests-title">
                        <h2 id="person-interests-title"><?php esc_html_e('Areas of Interest', 'nuventures'); ?></h2>
                        <ul>
                            <?php foreach ($taxonomy_terms as $term) : ?>
                                <li><?php echo esc_html($term->name); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>

                <?php get_template_part('template-parts/person/podcasts', null, $shared_args); ?>

                <?php if (!empty($testimonials[1])) : ?>
                    <?php get_template_part('template-parts/person/testimonial', null, array('testimonial' => $testimonials[1])); ?>
                <?php endif; ?>

                <?php get_template_part('template-parts/person/articles', null, $shared_args); ?>
            </div>
        </article>
    </main>
    <?php
endwhile;

wp_reset_postdata();
get_footer();
