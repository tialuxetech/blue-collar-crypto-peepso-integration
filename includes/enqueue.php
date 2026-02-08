<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', function () {

    if (is_admin()) {
        return;
    }

    $base_url = plugin_dir_url(__FILE__) . '../assets/css/';

    wp_enqueue_style(
        'bcc-forms',
        $base_url . 'validator-profile.css',
        [],
        '1.0'
    );

    wp_enqueue_style(
        'bcc-cards',
        $base_url . 'cards.css',
        [],
        '1.0'
    );

    wp_enqueue_style(
        'bcc-tabs',
        $base_url . 'bcc-tabs.css',
        [],
        '1.0'
    );

    wp_enqueue_script(
        'bcc-inline-edit',
        plugin_dir_url(__FILE__) . '../assets/js/inline-edit.js',
        [],
        '1.0',
        true
    );

    wp_localize_script(
        'bcc-inline-edit',
        'bccInline',
        [
            'ajax_url' => admin_url('admin-ajax.php')
        ]
    );

}, 20);
