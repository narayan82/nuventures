<?php
/**
 * About page introduction.
 *
 * @package NuVentures
 */
?>

<section class="about-intro" aria-labelledby="about-intro-title">
    <img
        class="about-intro__icon"
        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/about/building.png'); ?>"
        alt=""
        width="64"
        height="64"
        aria-hidden="true"
    >

    <h1 class="about-intro__title" id="about-intro-title"><?php esc_html_e('About Us', 'nuventures'); ?></h1>

    <div class="about-intro__copy">
        <p><?php esc_html_e('NuVentures is an early-stage venture fund partnering with founders building what is next.', 'nuventures'); ?></p>
        <p><?php esc_html_e('Across New Jersey and Bengaluru, we bring capital, strategic guidance, and trusted networks to every stage of the journey. Driven by collaboration, conviction, and founder-first support.', 'nuventures'); ?></p>
    </div>

    <div class="about-intro__map" aria-hidden="true">
        <img
            src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/about/map.jpg'); ?>"
            alt=""
            width="1600"
            height="508"
            decoding="async"
        >
    </div>

    <a class="about-intro__cta" href="<?php echo esc_url(home_url('/initiatives/')); ?>"><?php esc_html_e('See Our Initiatives', 'nuventures'); ?></a>
</section>
