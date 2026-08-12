<?php
/**
 * Global slide-out navigation.
 *
 * @package NuVentures
 */
?>

<div class="slide-menu" data-slide-menu aria-hidden="true">
    <button
        class="slide-menu__backdrop"
        type="button"
        tabindex="-1"
        aria-label="<?php esc_attr_e('Close menu', 'nuventures'); ?>"
        data-slide-menu-backdrop
    ></button>

    <aside class="slide-menu__panel" id="slide-menu">
        <div class="slide-menu__header">
            <button
                class="slide-menu__close"
                type="button"
                aria-label="<?php esc_attr_e('Close menu', 'nuventures'); ?>"
                data-slide-menu-close
            >
                <img
                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/menu/close.svg'); ?>"
                    alt=""
                    width="25"
                    height="25"
                >
            </button>
        </div>

        <nav class="slide-menu__nav" aria-label="<?php esc_attr_e('Primary navigation', 'nuventures'); ?>">
            <a class="slide-menu__link" href="<?php echo esc_url(home_url('/')); ?>">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/menu/home.svg'); ?>" alt="" width="24" height="24">
                <span><?php esc_html_e('Home', 'nuventures'); ?></span>
            </a>

            <a class="slide-menu__link" href="<?php echo esc_url(home_url('/portfolio/')); ?>">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/menu/portfolio.svg'); ?>" alt="" width="24" height="24">
                <span><?php esc_html_e('Portfolio', 'nuventures'); ?></span>
            </a>

            <a class="slide-menu__link" href="<?php echo esc_url(home_url('/about/')); ?>">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/menu/about.svg'); ?>" alt="" width="24" height="24">
                <span><?php esc_html_e('About', 'nuventures'); ?></span>
            </a>
            <a class="slide-menu__link" href="<?php echo esc_url(home_url('/initiatives/')); ?>">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/menu/initiatives.svg'); ?>" alt="" width="24" height="24">
                <span><?php esc_html_e('Initiatives', 'nuventures'); ?></span>
            </a>
            <a class="slide-menu__link" href="<?php echo esc_url(home_url('/nupod/')); ?>">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/menu/nupod.svg'); ?>" alt="" width="24" height="24">
                <span><?php esc_html_e('NuPOD', 'nuventures'); ?></span>
            </a>
        </nav>
    </aside>
</div>
