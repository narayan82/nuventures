<?php
/**
 * NuPOD card grid.
 *
 * @package NuVentures
 */

$podcasts = isset($args['podcasts']) && is_array($args['podcasts']) ? $args['podcasts'] : array();
?>

<div class="nupod-list__grid" data-nupod-grid>
    <?php foreach ($podcasts as $podcast) : ?>
        <?php get_template_part('template-parts/nupod/card', null, array('podcast' => $podcast)); ?>
    <?php endforeach; ?>
</div>

<p class="nupod-list__empty" data-nupod-empty hidden><?php esc_html_e('No podcasts found.', 'nuventures'); ?></p>
