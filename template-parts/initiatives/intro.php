<?php
/**
 * Initiatives page introduction.
 *
 * @package NuVentures
 */
?>

<section class="initiatives-intro" aria-labelledby="initiatives-title">
    <img
        class="initiatives-intro__icon"
        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/initiatives/light-bulb.png'); ?>"
        alt=""
        width="64"
        height="64"
        aria-hidden="true"
    >
    <h1 class="initiatives-intro__title" id="initiatives-title"><?php esc_html_e('Our Initiatives', 'nuventures'); ?></h1>
    <p class="initiatives-intro__description">
        <?php esc_html_e('Your front row seat to venture capital, innovation, tech and the startups shaping tomorrow.', 'nuventures'); ?>
    </p>
</section>
