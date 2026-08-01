<?php
/**
 * NuPOD listing card.
 *
 * @package NuVentures
 */

$podcast = isset($args['podcast']) && is_array($args['podcast']) ? $args['podcast'] : array();
$post    = isset($podcast['post']) && $podcast['post'] instanceof WP_Post ? $podcast['post'] : null;

if (!$post) {
    return;
}

$post_id        = $post->ID;
$title          = isset($podcast['title']) && $podcast['title'] ? $podcast['title'] : get_the_title($post);
$description    = isset($podcast['description']) ? $podcast['description'] : '';
$spotify_url    = isset($podcast['spotify_url']) && is_string($podcast['spotify_url']) ? trim($podcast['spotify_url']) : '';
$youtube_url    = isset($podcast['youtube_url']) && is_string($podcast['youtube_url']) ? trim($podcast['youtube_url']) : '';
$podcast_image  = isset($podcast['image']) ? $podcast['image'] : null;
$speaker_ids    = isset($podcast['speaker_ids']) && is_array($podcast['speaker_ids']) ? $podcast['speaker_ids'] : array();
$date_timestamp = isset($podcast['date_timestamp']) ? (int) $podcast['date_timestamp'] : get_post_time('U', true, $post);
$image_id       = 0;
$image_url      = '';
$image_alt      = '';

if (is_numeric($podcast_image)) {
    $image_id = (int) $podcast_image;
} elseif (is_array($podcast_image)) {
    $image_id  = isset($podcast_image['ID']) ? (int) $podcast_image['ID'] : (isset($podcast_image['id']) ? (int) $podcast_image['id'] : 0);
    $image_url = isset($podcast_image['url']) ? $podcast_image['url'] : '';
    $image_alt = isset($podcast_image['alt']) ? $podcast_image['alt'] : '';
} elseif (is_string($podcast_image)) {
    $image_url = $podcast_image;
}

$has_image = (bool) ($image_id || $image_url);
$mic_url   = get_template_directory_uri() . '/assets/images/podcast/microphone-icon.svg';
?>

<article
    class="nupod-card<?php echo $has_image ? '' : ' nupod-card--no-image'; ?>"
    data-nupod-card
    data-title="<?php echo esc_attr(wp_strip_all_tags($title)); ?>"
    data-date="<?php echo esc_attr(gmdate('Y-m-d', $date_timestamp)); ?>"
    data-speakers="<?php echo esc_attr(implode(' ', array_map('absint', $speaker_ids))); ?>"
>
    <?php if ($has_image) : ?>
        <div class="nupod-card__media">
            <?php if ($image_id) : ?>
                <?php
                echo wp_get_attachment_image(
                    $image_id,
                    'large',
                    false,
                    array(
                        'class'    => 'nupod-card__image',
                        'alt'      => $image_alt ? $image_alt : $title,
                        'loading'  => 'lazy',
                        'decoding' => 'async',
                        'sizes'    => '(max-width: 550px) calc(100vw - 40px), (max-width: 900px) 210px, 210px',
                    )
                );
                ?>
            <?php else : ?>
                <img
                    class="nupod-card__image"
                    src="<?php echo esc_url($image_url); ?>"
                    alt="<?php echo esc_attr($image_alt ? $image_alt : $title); ?>"
                    loading="lazy"
                    decoding="async"
                >
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="nupod-card__content">
        <time class="nupod-card__date" datetime="<?php echo esc_attr(gmdate('Y-m-d', $date_timestamp)); ?>">
            <?php echo esc_html(wp_date('j-M-Y', $date_timestamp)); ?>
        </time>

        <h2 class="nupod-card__title"><?php echo esc_html($title); ?></h2>

        <?php if ('' !== trim(wp_strip_all_tags($description))) : ?>
            <div class="nupod-card__description"><?php echo wp_kses_post($description); ?></div>
        <?php endif; ?>

        <?php if ($spotify_url || $youtube_url) : ?>
            <div class="nupod-card__actions">
                <?php if ($spotify_url) : ?>
                    <a class="nupod-card__cta" href="<?php echo esc_url($spotify_url); ?>" target="_blank" rel="noopener noreferrer">
                        <img src="<?php echo esc_url($mic_url); ?>" alt="" width="16" height="16">
                        <span><?php esc_html_e('Listen on Spotify', 'nuventures'); ?></span>
                    </a>
                <?php endif; ?>

                <?php if ($youtube_url) : ?>
                    <a class="nupod-card__cta" href="<?php echo esc_url($youtube_url); ?>" target="_blank" rel="noopener noreferrer">
                        <img src="<?php echo esc_url($mic_url); ?>" alt="" width="16" height="16">
                        <span><?php esc_html_e('Watch on Youtube', 'nuventures'); ?></span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</article>
