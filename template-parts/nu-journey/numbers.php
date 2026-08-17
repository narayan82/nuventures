<?php
/**
 * NuJourney numbers.
 *
 * @package NuVentures
 */

$journey_id = isset($args['journey_id']) ? (int) $args['journey_id'] : get_the_ID();
$numbers    = get_field('numbers', $journey_id);

if (!is_array($numbers) || !$numbers) {
    return;
}
?>

<section class="nu-journey-numbers" aria-label="<?php esc_attr_e('Journey numbers', 'nuventures'); ?>">
    <ul class="nu-journey-numbers__grid">
        <?php foreach ($numbers as $number) : ?>
            <?php
            $count = isset($number['number_count']) ? trim((string) $number['number_count']) : '';
            $label = isset($number['number_label']) ? trim((string) $number['number_label']) : '';

            if (!$count && !$label) {
                continue;
            }
            ?>
            <li class="nu-journey-numbers__item">
                <?php if ($count) : ?>
                    <strong><?php echo esc_html($count); ?></strong>
                <?php endif; ?>
                <?php if ($label) : ?>
                    <span><?php echo esc_html($label); ?></span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
