<?php
/**
 * Nu Journey closing copy.
 *
 * @package NuVentures
 */

$journey_id   = isset($args['journey_id']) ? (int) $args['journey_id'] : get_the_ID();
$closing_text = get_field('closing_text', $journey_id);

if (!$closing_text) {
    return;
}
?>

<section class="nu-journey-closing">
    <div><?php echo wp_kses_post(wpautop($closing_text)); ?></div>
</section>
