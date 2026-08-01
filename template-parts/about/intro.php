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
        <p><?php esc_html_e('At NuVentures, we are more than investors we are partners in possibility. We back visionary founders at the seed and early-growth stages, helping transform bold ideas into enduring businesses. With offices in New Jersey and Bengaluru, we pair capital with mentorship, global networks and strategic guidance to create lasting impact.', 'nuventures'); ?></p>
        <p><?php esc_html_e('For NuVentures, the foundational values driving our partnerships with entrepreneurs are Collaboration, Conviction, and Support—they emphasize working side-by-side with founders, investing with deep belief in their vision, and providing strategic, hands-on assistance throughout the journey.', 'nuventures'); ?></p>
    </div>

    <?php // TODO: Replace the placeholder URL when the Initiatives page destination is confirmed. ?>
    <a class="about-intro__cta" href="#"><?php esc_html_e('See Our Intiatives', 'nuventures'); ?></a>
</section>
