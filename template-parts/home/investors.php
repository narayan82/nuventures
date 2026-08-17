<?php
/**
 * Homepage investors section.
 *
 * @package NuVentures
 */

$current_person_id = isset($args['current_person_id']) ? absint($args['current_person_id']) : 0;
$layout            = isset($args['layout']) ? sanitize_key($args['layout']) : '';
$section_class     = 'home-investors';

if ('person-grid' === $layout) {
    $section_class .= ' home-investors--person-grid';
}

$partner_taxonomy = '';

foreach (get_object_taxonomies('person', 'names') as $person_taxonomy) {
    if (term_exists('partner', $person_taxonomy)) {
        $partner_taxonomy = $person_taxonomy;
        break;
    }
}

$people_query = new WP_Query(
    array(
        'post_type'              => 'person',
        'post_status'            => 'publish',
        'posts_per_page'         => -1,
        'orderby'                => array(
            'menu_order' => 'ASC',
            'title'      => 'ASC',
        ),
        'no_found_rows'          => true,
        'ignore_sticky_posts'    => true,
        'update_post_term_cache' => false,
        'tax_query'              => $partner_taxonomy
            ? array(
                array(
                    'taxonomy' => $partner_taxonomy,
                    'field'    => 'slug',
                    'terms'    => array('partner'),
                ),
            )
            : array(
                array(
                    'taxonomy' => 'category',
                    'field'    => 'term_id',
                    'terms'    => array(0),
                ),
            ),
    )
);

$ordered_people = $people_query->posts;
?>

<section class="<?php echo esc_attr($section_class); ?>" data-investors-carousel aria-labelledby="home-investors-title">
    <h2 class="home-investors__title" id="home-investors-title"><?php esc_html_e('Meet the investors', 'nuventures'); ?></h2>

    <div class="home-investors__track" data-investors-track>
        <?php foreach ($ordered_people as $person_post) : ?>
            <?php
            $person_id   = $person_post->ID;

            if ($current_person_id === $person_id) {
                continue;
            }

            $person_name = get_field('name', $person_id);
            $photo       = get_field('photo', $person_id);
            $photo_id    = 0;
            $photo_url   = '';

            if (!$person_name) {
                $person_name = get_the_title($person_post);
            }

            if (is_numeric($photo)) {
                $photo_id = (int) $photo;
            } elseif (is_array($photo)) {
                $photo_id  = isset($photo['ID']) ? (int) $photo['ID'] : (isset($photo['id']) ? (int) $photo['id'] : 0);
                $photo_url = isset($photo['url']) ? $photo['url'] : '';
            } elseif (is_string($photo)) {
                $photo_url = $photo;
            }

            $card_class = 'home-investors__card';

            if (!$photo_id && !$photo_url) {
                $card_class .= ' home-investors__card--no-photo';
            }

            ?>
            <a class="<?php echo esc_attr($card_class); ?>" href="<?php echo esc_url(get_permalink($person_id)); ?>"<?php echo 'person-grid' === $layout ? '' : ' data-investor-tilt'; ?>>
                <?php if ($photo_id) : ?>
                    <?php
                    echo wp_get_attachment_image(
                        $photo_id,
                        'large',
                        false,
                        array(
                            'class'    => 'home-investors__photo',
                            'alt'      => $person_name,
                            'loading'  => 'lazy',
                            'decoding' => 'async',
                            'sizes'    => '(max-width: 550px) calc(100vw - 48px), 232px',
                        )
                    );
                    ?>
                <?php elseif ($photo_url) : ?>
                    <img
                        class="home-investors__photo"
                        src="<?php echo esc_url($photo_url); ?>"
                        alt="<?php echo esc_attr($person_name); ?>"
                        loading="lazy"
                        decoding="async"
                    >
                <?php endif; ?>

                <span class="home-investors__name"><?php echo esc_html($person_name); ?></span>
                <span class="home-investors__card-arrow" aria-hidden="true">
                    <span class="home-investors__arrow-icon">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/investors/arrow-right.svg'); ?>" alt="" width="13" height="12">
                    </span>
                </span>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="home-investors__actions" data-investors-controls>
        <button class="home-investors__control home-investors__control--previous" type="button" aria-label="<?php esc_attr_e('Previous investors', 'nuventures'); ?>" data-investors-previous hidden>
            <span class="home-investors__arrow-icon">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/investors/chevron-right.svg'); ?>" alt="" width="8" height="14">
            </span>
        </button>

        <?php // TODO: Replace the placeholder URL when the team page destination is confirmed. ?>
        <a class="home-investors__cta" href="<?php echo esc_url(home_url('/about/')); ?>"><?php esc_html_e('View the nu team', 'nuventures'); ?></a>

        <button class="home-investors__control" type="button" aria-label="<?php esc_attr_e('Next investors', 'nuventures'); ?>" data-investors-next hidden>
            <span class="home-investors__arrow-icon">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/investors/chevron-right.svg'); ?>" alt="" width="8" height="14">
            </span>
        </button>
    </div>
</section>

<?php wp_reset_postdata(); ?>
