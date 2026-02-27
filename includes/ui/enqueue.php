<?php
if (!defined('ABSPATH')) {
    exit;
}

/* ======================================================
   FRONTEND ASSETS
====================================================== */

add_action('wp_enqueue_scripts', 'bcc_enqueue_assets');

function bcc_enqueue_assets() {

    // Never load on admin screens
    if (is_admin()) {
        return;
    }

    $base_css = plugin_dir_url(__FILE__) . '../../assets/css/';
    $base_js  = plugin_dir_url(__FILE__) . '../../assets/js/';
    
    // Cache busting
    $version = defined('WP_DEBUG') && WP_DEBUG ? time() : '1.0.0';

    /* -----------------------------------------
       STYLES
    ----------------------------------------- */

    wp_enqueue_style(
        'bcc-profile',
        $base_css . 'profile.css',
        [],
        $version
    );

    /* -----------------------------------------
       SCRIPTS - SINGLE CONSOLIDATED FILE
    ----------------------------------------- */

    // Make sure media library is loaded first
    wp_enqueue_media();

    // Single consolidated JS file
    wp_enqueue_script(
        'bcc-all',
        $base_js . 'bcc-all.js', // Rename the file I gave you to this
        ['jquery', 'media-views'], // Dependencies
        $version,
        true
    );

    // Localize ONCE for all scripts
    wp_localize_script('bcc-all', 'bcc_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('bcc_nonce')
    ]);
}