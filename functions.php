<?php

require_once get_template_directory() . '/inc/pitch-api.php';
require_once get_template_directory() . '/inc/person-helpers.php';

function nuventures_assets() {
    wp_enqueue_style(
        'nuventures-google-fonts',
        'https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Lora:ital,wght@0,400;0,500;1,400&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'nuventures-main',
        get_template_directory_uri() . '/assets/dist/css/main.css',
        ['nuventures-google-fonts']
    );

    wp_enqueue_script(
        'nuventures-main',
        get_template_directory_uri() . '/assets/dist/js/main.js',
        [],
        null,
        true
    );
}

function nuventures_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');

    register_nav_menus([
        'primary' => __('Primary Menu', 'nuventures'),
        'footer'  => __('Footer Menu', 'nuventures'),
    ]);
}

/**
 * Output the theme-owned favicon and web-app metadata.
 */
function nuventures_favicon_meta() {
    $favicon_url = get_template_directory_uri() . '/assets/images/favicon/';
    ?>
    <link rel="icon" type="image/svg+xml" href="<?php echo esc_url($favicon_url . 'favicon.svg'); ?>">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo esc_url($favicon_url . 'favicon-96x96.png'); ?>">
    <link rel="shortcut icon" href="<?php echo esc_url($favicon_url . 'favicon.ico'); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url($favicon_url . 'apple-touch-icon.png'); ?>">
    <link rel="manifest" href="<?php echo esc_url($favicon_url . 'site.webmanifest'); ?>">
    <meta name="theme-color" content="#ffffff">
    <?php
}

add_action('after_setup_theme', 'nuventures_setup');

add_action('wp_enqueue_scripts', 'nuventures_assets');
add_action('wp_head', 'nuventures_favicon_meta', 2);
