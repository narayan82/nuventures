<?php
/**
 * Related Person podcasts.
 *
 * @package NuVentures
 */

$podcasts = isset($args['podcasts']) && is_array($args['podcasts']) ? $args['podcasts'] : array();
if (!$podcasts) {
    return;
}
?>
<section class="person-related person-related--podcasts" aria-labelledby="person-podcasts-title">
    <header class="person-related__heading">
        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/podcast/microphone.png'); ?>" alt="" width="64" height="64">
        <h2 id="person-podcasts-title"><?php esc_html_e('Podcasts', 'nuventures'); ?></h2>
    </header>
    <div class="person-related__grid">
        <?php foreach ($podcasts as $podcast) : ?>
            <?php
            $id          = $podcast->ID;
            $title       = get_field('podcast_title', $id) ?: get_the_title($id);
            $spotify     = get_field('spotify_url', $id);
            $youtube     = get_field('youtube_url', $id);
            $url         = $spotify ?: ($youtube ?: get_permalink($id));
            $external    = (bool) ($spotify || $youtube);
            $date_value  = get_field('podcast_date', $id);
            $timestamp   = $date_value ? strtotime((string) $date_value) : get_post_time('U', true, $podcast);
            ?>
            <article class="person-related-card">
                <time datetime="<?php echo esc_attr(gmdate('Y-m-d', $timestamp)); ?>"><?php echo esc_html(wp_date('j-M-Y', $timestamp)); ?></time>
                <h3><?php echo esc_html($title); ?></h3>
                <a class="person-related-card__cta" href="<?php echo esc_url($url); ?>"<?php echo $external ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/podcast/microphone-icon.svg'); ?>" alt="" width="16" height="16">
                    <span><?php esc_html_e('Listen Now', 'nuventures'); ?></span>
                </a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
