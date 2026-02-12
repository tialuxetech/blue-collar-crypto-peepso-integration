<?php
if (!defined('ABSPATH')) {
    exit;
}

/* =====================================================
   FRONTEND ASSETS
===================================================== */

add_action('wp_enqueue_scripts', 'bcc_enqueue_assets');

function bcc_enqueue_assets() {

    if (is_admin()) {
        return;
    }

    $base_css = plugin_dir_url(__FILE__) . '../assets/css/';
    $base_js  = plugin_dir_url(__FILE__) . '../assets/js/';

    /* ---------------------------
       STYLES
    --------------------------- */

    wp_enqueue_style(
        'bcc-forms',
        $base_css . 'validator-profile.css',
        [],
        '1.0'
    );

    wp_enqueue_style(
        'bcc-cards',
        $base_css . 'cards.css',
        [],
        '1.0'
    );

    wp_enqueue_style(
        'bcc-tabs',
        $base_css . 'bcc-tabs.css',
        [],
        '1.0'
    );

    /* ---------------------------
       SCRIPTS
    --------------------------- */

    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-sortable');


    wp_enqueue_script(
        'bcc-inline-editor',
        $base_js . 'bcc-inline.js',
        ['jquery'],
        time(),     // cache-busting during dev
        true
    );

    wp_localize_script('bcc-inline-editor', 'bcc_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('bcc_nonce')
    ]);
}
