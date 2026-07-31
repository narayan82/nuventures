<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<header class="site-header">
    <div class="site-header__inner">
        <div class="site-header__left">
            <button
                class="site-header__menu-button"
                type="button"
                aria-label="<?php esc_attr_e('Menu', 'nuventures'); ?>"
                aria-expanded="false"
                aria-controls="slide-menu"
            >
                <img
                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/header/menu.svg'); ?>"
                    alt=""
                    width="24"
                    height="24"
                >
            </button>

            <nav class="site-header__socials" aria-label="<?php esc_attr_e('Social media', 'nuventures'); ?>">
                <?php // TODO: Replace placeholder social URLs when the final destinations are provided. ?>
                <a class="site-header__social-link" href="#" aria-label="<?php esc_attr_e('NuVentures on YouTube', 'nuventures'); ?>">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/header/youtube.svg'); ?>" alt="" width="24" height="24">
                </a>
                <a class="site-header__social-link" href="#" aria-label="<?php esc_attr_e('NuVentures on X', 'nuventures'); ?>">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/header/x.svg'); ?>" alt="" width="24" height="24">
                </a>
                <a class="site-header__social-link" href="#" aria-label="<?php esc_attr_e('NuVentures on Instagram', 'nuventures'); ?>">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/header/instagram.svg'); ?>" alt="" width="24" height="24">
                </a>
                <a class="site-header__social-link" href="#" aria-label="<?php esc_attr_e('NuVentures on LinkedIn', 'nuventures'); ?>">
                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/header/linkedin.svg'); ?>" alt="" width="24" height="24">
                </a>
            </nav>
        </div>

        <a class="site-header__logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('NuVentures home', 'nuventures'); ?>">
            <img
                src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/header/nuventures-logo.svg'); ?>"
                alt="<?php esc_attr_e('NuVentures', 'nuventures'); ?>"
                width="160"
                height="22"
            >
        </a>

        <?php // TODO: Replace the placeholder CTA URL when the pitch destination is provided. ?>
        <a class="site-header__cta" href="#" aria-label="<?php esc_attr_e('Pitch your Idea', 'nuventures'); ?>">
            <img
                src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/header/rocket.svg'); ?>"
                alt=""
                width="16"
                height="16"
            >
            <span class="site-header__cta-label"><?php esc_html_e('Pitch your Idea', 'nuventures'); ?></span>
        </a>
    </div>
</header>

<?php get_template_part('template-parts/components/slide-menu'); ?>
