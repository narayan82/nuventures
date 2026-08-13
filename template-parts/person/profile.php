<?php
/**
 * Person profile rail.
 *
 * @package NuVentures
 */

$person_id   = isset($args['person_id']) ? absint($args['person_id']) : get_the_ID();
$person_name = isset($args['person_name']) ? $args['person_name'] : get_the_title($person_id);
$first_name  = !empty($args['first_name']) ? $args['first_name'] : $person_name;
$designation = get_field('designation', $person_id);
$photo       = nuventures_get_image_details(get_field('photo', $person_id), $person_name);
$socials     = array(
    'youtube'  => array('label' => 'YouTube', 'url' => get_field('youtube', $person_id)),
    'x'        => array('label' => 'X', 'url' => get_field('x', $person_id)),
    'instagram'=> array('label' => 'Instagram', 'url' => get_field('instagram', $person_id)),
    'linkedin' => array('label' => 'LinkedIn', 'url' => get_field('linkedin', $person_id)),
);
?>
<header class="person-profile" aria-label="<?php echo esc_attr(sprintf(__('%s profile', 'nuventures'), $person_name)); ?>">
    <?php if ($photo['id'] || $photo['url']) : ?>
        <div class="person-profile__photo">
            <?php if ($photo['id']) : ?>
                <?php echo wp_get_attachment_image($photo['id'], 'large', false, array('alt' => $photo['alt'], 'sizes' => '(max-width: 550px) calc(100vw - 40px), 260px')); ?>
            <?php else : ?>
                <img src="<?php echo esc_url($photo['url']); ?>" alt="<?php echo esc_attr($photo['alt']); ?>">
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <h1><?php echo esc_html($person_name); ?></h1>
    <?php if ($designation) : ?><p class="person-profile__designation"><?php echo esc_html($designation); ?></p><?php endif; ?>

    <ul class="person-profile__socials">
        <?php foreach ($socials as $key => $social) : ?>
            <?php if (!empty($social['url'])) : ?>
                <li>
                    <a href="<?php echo esc_url($social['url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($social['label']); ?>">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/header/' . $key . '.svg'); ?>" alt="" width="20" height="20">
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>

    <a class="person-profile__pitch button-solid-red" href="<?php echo esc_url(home_url('/pitch/')); ?>">
        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/header/rocket.svg'); ?>" alt="" width="16" height="16">
        <span><?php echo esc_html(sprintf(__('Pitch Your Idea', 'nuventures'))); ?></span>
    </a>
</header>
