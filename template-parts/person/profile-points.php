<?php
/**
 * Person profile points.
 *
 * @package NuVentures
 */

$person_id = isset($args['person_id']) ? absint($args['person_id']) : get_the_ID();
$pronouns  = strtolower(trim((string) get_field('pronouns', $person_id)));
$possessive = 'her' === $pronouns ? __('Her', 'nuventures') : __('His', 'nuventures');
$subject    = 'her' === $pronouns ? __('She', 'nuventures') : __('He', 'nuventures');
$points    = array(
    array('label' => sprintf(__('%s Background', 'nuventures'), $possessive), 'title' => get_field('background', $person_id), 'description' => get_field('background_descr', $person_id)),
    array('label' => sprintf(__('%s Brings to the table', 'nuventures'), $subject), 'title' => get_field('brings', $person_id), 'description' => get_field('brings_description', $person_id)),
    array('label' => sprintf(__('%s Backs', 'nuventures'), $subject), 'title' => get_field('backs', $person_id), 'description' => get_field('backs_description', $person_id)),
);
?>
<section class="person-points" aria-label="<?php esc_attr_e('Profile highlights', 'nuventures'); ?>">
    <?php foreach ($points as $index => $point) : ?>
        <?php if ($point['title'] || $point['description']) : ?>
            <div class="person-point">
                <div class="person-point__eyebrow">
                    <span><?php echo esc_html($index + 1); ?></span>
                    <?php echo esc_html($point['label']); ?>
                    <?php if ($index < 2) : ?>
                        <img
                            class="person-point__connector person-point__connector--<?php echo esc_attr($index + 1); ?>"
                            src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/person/profile-connector-0' . ($index + 1) . '.svg'); ?>"
                            alt=""
                            aria-hidden="true"
                        >
                    <?php endif; ?>
                </div>
                <?php if ($point['title']) : ?><h2><?php echo esc_html($point['title']); ?></h2><?php endif; ?>
                <?php if ($point['description']) : ?><div class="person-point__description"><?php echo wp_kses_post(wpautop($point['description'])); ?></div><?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</section>
