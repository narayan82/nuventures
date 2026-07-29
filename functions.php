<?php

function nuventures_assets() {
    wp_enqueue_style(
        'nuventures-main',
        get_template_directory_uri() . '/assets/dist/css/main.css'
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