<?php
/**
 * NuPOD listing page template.
 *
 * @package NuVentures
 */

$nupod_normalize_date = static function ($date_value, $fallback_timestamp) {
    if ($date_value instanceof DateTimeInterface) {
        return $date_value->getTimestamp();
    }

    $date_string = is_scalar($date_value) ? trim((string) $date_value) : '';

    if ($date_string) {
        $formats = array('Ymd', 'Y-m-d', 'd/m/Y', 'm/d/Y', 'j-M-Y', 'd-M-Y');

        foreach ($formats as $format) {
            $date = DateTimeImmutable::createFromFormat('!' . $format, $date_string);

            if ($date instanceof DateTimeImmutable) {
                return $date->getTimestamp();
            }
        }

        $timestamp = strtotime($date_string);

        if (false !== $timestamp) {
            return $timestamp;
        }
    }

    return (int) $fallback_timestamp;
};

$nupod_collect_people = static function ($value) use (&$nupod_collect_people) {
    $person_ids = array();

    if ($value instanceof WP_Post) {
        $person_ids[] = $value->ID;
    } elseif (is_numeric($value)) {
        $person_ids[] = (int) $value;
    } elseif (is_array($value)) {
        if (isset($value['ID']) || isset($value['id'])) {
            $person_ids[] = isset($value['ID']) ? (int) $value['ID'] : (int) $value['id'];
        } else {
            foreach ($value as $person_value) {
                $person_ids = array_merge($person_ids, $nupod_collect_people($person_value));
            }
        }
    }

    return array_values(array_unique(array_filter($person_ids)));
};

$podcast_query = new WP_Query(
    array(
        'post_type'           => 'podcast',
        'post_status'         => 'publish',
        'posts_per_page'      => -1,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    )
);

$podcasts        = array();
$speaker_options = array();

foreach ($podcast_query->posts as $podcast_post) {
    $podcast_id    = $podcast_post->ID;
    $speaker_ids   = $nupod_collect_people(get_field('podcast_by', $podcast_id));
    $date_timestamp = $nupod_normalize_date(
        get_field('podcast_date', $podcast_id),
        get_post_time('U', true, $podcast_post)
    );

    foreach ($speaker_ids as $speaker_id) {
        $speaker_name = get_field('name', $speaker_id);

        if (!$speaker_name) {
            $speaker_name = get_the_title($speaker_id);
        }

        if ($speaker_name) {
            $speaker_options[$speaker_id] = $speaker_name;
        }
    }

    $podcasts[] = array(
        'post'           => $podcast_post,
        'title'          => get_field('podcast_title', $podcast_id),
        'description'    => get_field('podcast_description', $podcast_id),
        'youtube_url'    => get_field('youtube_url', $podcast_id),
        'spotify_url'    => get_field('spotify_url', $podcast_id),
        'image'          => get_field('podcast_image', $podcast_id),
        'speaker_ids'    => $speaker_ids,
        'date_timestamp' => $date_timestamp,
    );
}

wp_reset_postdata();
natcasesort($speaker_options);

get_header();
?>

<main class="nupod-page">
    <?php get_template_part('template-parts/nupod/intro'); ?>

    <section class="nupod-list portfolio-companies" data-nupod-list aria-label="<?php esc_attr_e('Podcasts', 'nuventures'); ?>">
        <?php get_template_part('template-parts/nupod/filters', null, array('speakers' => $speaker_options)); ?>
        <?php get_template_part('template-parts/nupod/grid', null, array('podcasts' => $podcasts)); ?>
    </section>
</main>

<?php get_footer(); ?>
