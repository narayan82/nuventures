<?php
/**
 * NuJourney closing copy.
 *
 * @package NuVentures
 */

$journey_id   = isset($args['journey_id']) ? (int) $args['journey_id'] : get_the_ID();
$closing_text = get_field('closing_text', $journey_id);
$website      = get_field('website', $journey_id);

if (is_array($website)) {
    $website = isset($website['url']) ? $website['url'] : '';
}

$website = is_string($website) ? trim($website) : '';

if ($website && !preg_match('#^https?://#i', $website)) {
    $website = 'https://' . ltrim($website, '/');
}

if (!$closing_text && !$website) {
    return;
}
?>

<section class="nu-journey-closing">
    <?php if ($closing_text) : ?>
        <div><?php echo wp_kses_post(wpautop($closing_text)); ?></div>
    <?php endif; ?>

    <?php if ($website) : ?>
        <a
            class="nu-journey-closing__cta"
            href="<?php echo esc_url($website); ?>"
            target="_blank"
            rel="noopener noreferrer"
        >
            <?php esc_html_e('Find out More', 'nuventures'); ?>
        </a>
    <?php endif; ?>
</section>
