<?php
/**
 * Nu Journey introduction.
 *
 * @package NuVentures
 */

$journey_id    = isset($args['journey_id']) ? (int) $args['journey_id'] : get_the_ID();
$resolve_image = isset($args['resolve_image']) && is_callable($args['resolve_image']) ? $args['resolve_image'] : null;
$logo          = $resolve_image ? $resolve_image(get_field('logo', $journey_id), get_the_title($journey_id) . ' logo') : array();
$long_title    = get_field('long_title', $journey_id);
$description   = get_field('description', $journey_id);

if (!$logo && !$long_title && !$description) {
    return;
}
?>

<header class="nu-journey__intro">
    <?php if (!empty($logo['id'])) : ?>
        <?php
        echo wp_get_attachment_image(
            $logo['id'],
            'medium',
            false,
            array(
                'class'    => 'nu-journey__logo',
                'alt'      => $logo['alt'],
                'loading'  => 'eager',
                'decoding' => 'async',
            )
        );
        ?>
    <?php elseif (!empty($logo['url'])) : ?>
        <img class="nu-journey__logo" src="<?php echo esc_url($logo['url']); ?>" alt="<?php echo esc_attr($logo['alt']); ?>" loading="eager" decoding="async">
    <?php endif; ?>

    <h1 class="nu-journey__title"><?php echo esc_html($long_title ?: get_the_title($journey_id)); ?></h1>

    <?php if ($description) : ?>
        <div class="nu-journey__description"><?php echo wp_kses_post(wpautop($description)); ?></div>
    <?php endif; ?>
    <p class="nu-journey-timeline__instruction">
        <?php esc_html_e('(Click/Tap on a timeline entry to find out more)', 'nuventures'); ?>
    </p>
</header>
