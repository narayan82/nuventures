<?php
/**
 * Initiatives page card grid.
 *
 * @package NuVentures
 */

$initiatives = isset($args['initiatives']) && is_array($args['initiatives']) ? $args['initiatives'] : array();
?>

<section class="initiatives-list portfolio-companies" data-initiatives-list aria-label="<?php esc_attr_e('Initiatives', 'nuventures'); ?>">
    <div class="initiatives-list__grid" data-initiatives-grid>
        <?php foreach ($initiatives as $initiative) : ?>
            <?php get_template_part('template-parts/initiatives/card', null, array('initiative' => $initiative)); ?>
        <?php endforeach; ?>
    </div>

    <p class="initiatives-list__empty" data-initiatives-empty hidden><?php esc_html_e('No initiatives found.', 'nuventures'); ?></p>

    <button class="initiatives-list__load-more" type="button" data-initiatives-load-more hidden>
        <?php esc_html_e('Load More', 'nuventures'); ?>
    </button>
</section>
