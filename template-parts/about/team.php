<?php
/**
 * About page team grid.
 *
 * @package NuVentures
 */

$people_query = new WP_Query(
    array(
        'post_type'           => 'person',
        'post_status'         => 'publish',
        'posts_per_page'      => -1,
        'orderby'             => array(
            'menu_order' => 'ASC',
            'title'      => 'ASC',
        ),
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    )
);

$person_taxonomies = get_object_taxonomies('person', 'names');
$partner_taxonomy  = '';

foreach ($person_taxonomies as $person_taxonomy) {
    if (term_exists('partner', $person_taxonomy)) {
        $partner_taxonomy = $person_taxonomy;
        break;
    }
}
?>

<section class="about-team" aria-labelledby="about-team-title" data-about-team>
    <h2 class="about-team__title" id="about-team-title"><?php esc_html_e('Our Team', 'nuventures'); ?></h2>

    <div class="about-team__controls">
        <label class="about-team__search">
            <span class="screen-reader-text"><?php esc_html_e('Search for a person', 'nuventures'); ?></span>
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/portfolio/search.svg'); ?>" alt="" width="24" height="24">
            <input
                type="search"
                placeholder="<?php esc_attr_e('Search for a person', 'nuventures'); ?>"
                data-about-team-search
            >
        </label>

        <label class="about-team__sort">
            <span class="screen-reader-text"><?php esc_html_e('Sort people by name', 'nuventures'); ?></span>
            <select data-about-team-sort>
                <option value=""><?php esc_html_e('Sort (A-Z)', 'nuventures'); ?></option>
                <option value="az"><?php esc_html_e('A-Z', 'nuventures'); ?></option>
                <option value="za"><?php esc_html_e('Z-A', 'nuventures'); ?></option>
            </select>
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/portfolio/chevron-down.svg'); ?>" alt="" width="24" height="24">
        </label>
    </div>

    <div class="about-team__grid" data-about-team-grid>
        <?php foreach ($people_query->posts as $person_post) : ?>
            <?php
            $person_id         = $person_post->ID;
            $person_name       = get_field('name', $person_id);
            $designation       = get_field('designation', $person_id);
            $photo             = get_field('photo', $person_id);
            $photo_id          = 0;
            $photo_url         = '';
            $photo_alt         = '';
            $category_slugs    = array();
            $is_partner        = $partner_taxonomy && has_term('partner', $partner_taxonomy, $person_id);
            $card_element      = $is_partner ? 'a' : 'article';
            $card_attributes   = $is_partner ? ' href="' . esc_url(get_permalink($person_id)) . '"' : '';

            if (!$person_name) {
                $person_name = get_the_title($person_post);
            }

            if (is_numeric($photo)) {
                $photo_id = (int) $photo;
            } elseif (is_array($photo)) {
                $photo_id  = isset($photo['ID']) ? (int) $photo['ID'] : (isset($photo['id']) ? (int) $photo['id'] : 0);
                $photo_url = isset($photo['url']) ? $photo['url'] : '';
                $photo_alt = isset($photo['alt']) ? $photo['alt'] : '';
            } elseif (is_string($photo)) {
                $photo_url = $photo;
            }

            foreach ($person_taxonomies as $person_taxonomy) {
                $person_terms = get_the_terms($person_id, $person_taxonomy);

                if (!is_wp_error($person_terms) && $person_terms) {
                    foreach ($person_terms as $person_term) {
                        $category_slugs[] = sanitize_title($person_term->slug);
                    }
                }
            }

            $category_slugs = array_values(array_unique(array_filter($category_slugs)));
            ?>
            <<?php echo esc_html($card_element); ?>
                class="about-team__card<?php echo $is_partner ? ' about-team__card--linked' : ''; ?>"
                data-person-name="<?php echo esc_attr(wp_strip_all_tags($person_name)); ?>"
                <?php if ($category_slugs) : ?>
                    data-person-category="<?php echo esc_attr(implode(' ', $category_slugs)); ?>"
                <?php endif; ?>
                <?php echo $card_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            >
                <?php if ($photo_id) : ?>
                    <?php
                    echo wp_get_attachment_image(
                        $photo_id,
                        'large',
                        false,
                        array(
                            'class'    => 'about-team__photo',
                            'alt'      => $photo_alt ? $photo_alt : $person_name,
                            'loading'  => 'lazy',
                            'decoding' => 'async',
                            'sizes'    => '(max-width: 550px) calc(50vw - 25px), (max-width: 900px) calc(33.333vw - 27px), 232px',
                        )
                    );
                    ?>
                <?php elseif ($photo_url) : ?>
                    <img
                        class="about-team__photo"
                        src="<?php echo esc_url($photo_url); ?>"
                        alt="<?php echo esc_attr($photo_alt ? $photo_alt : $person_name); ?>"
                        loading="lazy"
                        decoding="async"
                    >
                <?php endif; ?>

                <span class="about-team__identity">
                    <span class="about-team__name"><?php echo esc_html($person_name); ?></span>
                    <?php if ($designation) : ?>
                        <span class="about-team__designation"><?php echo esc_html($designation); ?></span>
                    <?php endif; ?>
                </span>

                <?php if ($is_partner) : ?>
                    <span class="about-team__arrow" aria-hidden="true">
                        <img
                            src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/investors/arrow-right.svg'); ?>"
                            alt=""
                            width="13"
                            height="12"
                        >
                    </span>
                <?php endif; ?>
            </<?php echo esc_html($card_element); ?>>
        <?php endforeach; ?>
    </div>
</section>

<?php wp_reset_postdata(); ?>
