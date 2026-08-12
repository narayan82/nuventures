<?php
/**
 * Homepage podcast quotes carousel.
 *
 * @package NuVentures
 */

$podcast_query = new WP_Query(
    array(
        'post_type'              => 'podcast',
        'post_status'            => 'publish',
        'posts_per_page'         => -1,
        'orderby'                => 'date',
        'order'                  => 'DESC',
        'fields'                 => 'ids',
        'no_found_rows'          => true,
        'ignore_sticky_posts'    => true,
        'update_post_term_cache' => false,
    )
);

$podcast_quotes = array();

foreach ($podcast_query->posts as $podcast_id) {
    $quote_rows = get_field('quotes', $podcast_id);
    $youtube_url = get_field('youtube_url', $podcast_id);

    if (is_array($youtube_url)) {
        $youtube_url = isset($youtube_url['url']) ? $youtube_url['url'] : '';
    }

    $youtube_url = is_string($youtube_url) ? trim($youtube_url) : '';

    if (!is_array($quote_rows)) {
        continue;
    }

    foreach ($quote_rows as $quote_row) {
        $quote       = isset($quote_row['quote']) ? $quote_row['quote'] : '';
        $quote_by    = isset($quote_row['quote_by']) ? $quote_row['quote_by'] : '';
        $description = isset($quote_row['description']) ? $quote_row['description'] : '';

        if ('' === trim(wp_strip_all_tags($quote)) || '' === trim(wp_strip_all_tags($quote_by))) {
            continue;
        }

        $podcast_quotes[] = array(
            'quote'       => $quote,
            'quote_by'    => $quote_by,
            'description' => $description,
            'youtube_url' => $youtube_url,
        );

        if (5 === count($podcast_quotes)) {
            break 2;
        }
    }
}

if (!$podcast_quotes) {
    return;
}

$podcast_asset_path = get_template_directory_uri() . '/assets/images/podcast/';
?>

<section class="podcast-quotes" data-podcast-carousel aria-labelledby="podcast-quotes-title">
    <div class="podcast-quotes__intro">
        <img
            class="podcast-quotes__microphone"
            src="<?php echo esc_url($podcast_asset_path . 'microphone.png'); ?>"
            alt=""
            width="64"
            height="64"
        >

        <div class="podcast-quotes__intro-copy">
            <h2 class="podcast-quotes__title" id="podcast-quotes-title">
                <span><?php esc_html_e('nupod.', 'nuventures'); ?></span>
                <?php esc_html_e('stories. ideas. impact.', 'nuventures'); ?>
            </h2>
            <p class="podcast-quotes__supporting">
                <?php esc_html_e('Your front row seat to venture capital, innovation, tech and the startups shaping tomorrow.', 'nuventures'); ?>
            </p>
        </div>
    </div>

    <div
        class="podcast-quotes__track"
        data-podcast-track
        role="region"
        aria-label="<?php esc_attr_e('Podcast quotes', 'nuventures'); ?>"
        tabindex="0"
    >
        <?php foreach ($podcast_quotes as $podcast_quote) : ?>
            <article class="podcast-quotes__card">
                <blockquote class="podcast-quotes__quote">
                    <?php echo wp_kses_post($podcast_quote['quote']); ?>
                </blockquote>

                <div class="podcast-quotes__attribution">
                    <cite class="podcast-quotes__person">- <?php echo esc_html($podcast_quote['quote_by']); ?></cite>
                    <?php if ('' !== trim(wp_strip_all_tags($podcast_quote['description']))) : ?>
                        <p class="podcast-quotes__description"><?php echo wp_kses_post($podcast_quote['description']); ?></p>
                    <?php endif; ?>
                </div>

                <?php if ($podcast_quote['youtube_url']) : ?>
                    <a class="podcast-quotes__cta" href="<?php echo esc_url($podcast_quote['youtube_url']); ?>" target="_blank" rel="noopener noreferrer">
                        <img
                            src="<?php echo esc_url($podcast_asset_path . 'microphone-icon.svg'); ?>"
                            alt=""
                            width="16"
                            height="16"
                        >
                        <span><?php esc_html_e('Listen to the Podcast', 'nuventures'); ?></span>
                    </a>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="podcast-quotes__controls" data-podcast-controls hidden>
        <button class="podcast-quotes__control podcast-quotes__control--previous" type="button" aria-label="<?php esc_attr_e('Previous podcast quote', 'nuventures'); ?>" data-podcast-previous>
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/investors/chevron-right.svg'); ?>" alt="" width="8" height="14">
        </button>
        <a class="podcast-quotes__explore" href="<?php echo esc_url(home_url('/nupod/')); ?>">
            <?php esc_html_e('Explore All Podcasts', 'nuventures'); ?>
        </a>
        <button class="podcast-quotes__control" type="button" aria-label="<?php esc_attr_e('Next podcast quote', 'nuventures'); ?>" data-podcast-next>
            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/investors/chevron-right.svg'); ?>" alt="" width="8" height="14">
        </button>
    </div>
</section>
