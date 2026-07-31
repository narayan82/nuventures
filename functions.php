<?php

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

add_action('wp_enqueue_scripts', 'nuventures_assets');
