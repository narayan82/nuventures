<?php
/**
 * NuPOD listing introduction.
 *
 * @package NuVentures
 */
?>

<section class="nupod-intro" aria-labelledby="nupod-title">
    <img
        class="nupod-intro__icon"
        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/podcast/microphone.png'); ?>"
        alt=""
        width="64"
        height="64"
        aria-hidden="true"
    >
    <h1 class="nupod-intro__title" id="nupod-title">
        <span class="nupod-intro__title-text"><?php esc_html_e('NuPOD', 'nuventures'); ?></span>
        <img
            class="nupod-intro__logo"
            src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/about/nupod_logo.svg'); ?>"
            alt=""
            width="180"
            height="60"
            aria-hidden="true"
        >
    </h1>
    <p class="nupod-intro__description">
        <?php esc_html_e('Your front row seat to venture capital, innovation, tech and the startups shaping tomorrow.', 'nuventures'); ?>
    </p>
</section>
