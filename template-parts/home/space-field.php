<?php
/**
 * Decorative homepage space field.
 *
 * @package NuVentures
 */

$space_image_url = get_template_directory_uri() . '/assets/images/space-section/space-field.png';
$space_asset_path = get_template_directory_uri() . '/assets/images/space-section/';

$vision_principles = array(
    array(
        'title'       => 'See before it is obvious',
        'description' => 'Recognise meaningful shifts before they become consensus.',
    ),
    array(
        'title'       => 'Back what can endure',
        'description' => 'Support founders building beyond cycles and for scale.',
    ),
    array(
        'title'       => 'Turn insight to momentum',
        'description' => 'Unite capital, operators and industry around emerging opportunity.',
    ),
    array(
        'title'       => 'Build beyond boundaries',
        'description' => 'Help India-born companies shape markets worldwide.',
    ),
);
?>

<section class="space-field" aria-labelledby="space-field-title">
    <div class="space-field__scene" aria-hidden="true">
        <img
            class="space-field__image space-field__image--primary"
            src="<?php echo esc_url($space_image_url); ?>"
            alt=""
            width="1672"
            height="941"
            loading="lazy"
            decoding="async"
        >
        <img
            class="space-field__image space-field__image--continuation"
            src="<?php echo esc_url($space_image_url); ?>"
            alt=""
            width="1672"
            height="941"
            loading="lazy"
            decoding="async"
        >
    </div>

    <div class="space-field__vision" data-vision-carousel>
        <div class="space-field__vision-intro">
            <p class="space-field__vision-eyebrow"><?php esc_html_e('The Vision', 'nuventures'); ?></p>
            <h2 class="space-field__vision-title" id="space-field-title">
                <?php esc_html_e('Our Aim is to shape what comes next, not simply invest in it', 'nuventures'); ?>
            </h2>
            <p class="space-field__vision-description">
                <?php esc_html_e('NuVentures is building toward a future where experience, capital and conviction come together to identify meaningful change early.', 'nuventures'); ?>
            </p>
        </div>

        <ol
            class="space-field__principles"
            data-vision-track
            aria-label="<?php esc_attr_e('NuVentures vision points', 'nuventures'); ?>"
            tabindex="0"
        >
            <?php foreach ($vision_principles as $index => $principle) : ?>
                <li class="space-field__principle">
                    <div class="space-field__principle-marker" aria-hidden="true">
                        <img
                            src="<?php echo esc_url($space_asset_path . (0 === $index ? 'line-start.svg' : 'line-middle.svg')); ?>"
                            alt=""
                            width="<?php echo 0 === $index ? '84' : '83'; ?>"
                            height="1"
                        >
                        <span><?php echo esc_html($index + 1); ?></span>
                        <img
                            src="<?php echo esc_url($space_asset_path . (0 === $index ? 'line-end.svg' : 'line-middle.svg')); ?>"
                            alt=""
                            width="<?php echo 0 === $index ? '84' : '83'; ?>"
                            height="1"
                        >
                    </div>
                    <div class="space-field__principle-copy">
                        <h3><?php echo esc_html($principle['title']); ?></h3>
                        <p><?php echo esc_html($principle['description']); ?></p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>

        <span class="space-field__shuttle" aria-hidden="true">
            <img
                src="<?php echo esc_url($space_asset_path . 'shuttle.png'); ?>"
                alt=""
                width="313"
                height="210"
                loading="lazy"
                decoding="async"
            >
        </span>

        <div class="space-field__vision-controls" data-vision-controls hidden>
            <button class="space-field__vision-control space-field__vision-control--previous" type="button" aria-label="<?php esc_attr_e('Previous vision point', 'nuventures'); ?>" data-vision-previous>
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/investors/chevron-right.svg'); ?>" alt="" width="8" height="14">
            </button>
            <button class="space-field__vision-control" type="button" aria-label="<?php esc_attr_e('Next vision point', 'nuventures'); ?>" data-vision-next>
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/investors/chevron-right.svg'); ?>" alt="" width="8" height="14">
            </button>
        </div>
    </div>
</section>
