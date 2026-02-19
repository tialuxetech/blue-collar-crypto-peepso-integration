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

    /* -----------------------------------------
       STYLES
    ----------------------------------------- */

    wp_enqueue_style(
        'bcc-validator-profile',
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

    /* -----------------------------------------
       SCRIPTS
    ----------------------------------------- */

    // Core utilities (always loaded first)
    wp_enqueue_script(
        'bcc-core',
        $base_js . 'bcc-core.js',
        ['jquery'],
        time(),
        true
    );

    // Localize ONCE for all scripts
    wp_localize_script('bcc-core', 'bcc_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('bcc_nonce')
    ]);

    // Make sure media library is loaded
    wp_enqueue_media();

    // Gallery slider script (load this before gallery)
    wp_enqueue_script(
        'bcc-gallery-slider',
        $base_js . 'bcc-gallery-slider.js',
        ['jquery'],
        time(),
        true
    );

    // Gallery script - depends on media-views and slider
    wp_enqueue_script(
        'bcc-gallery',
        $base_js . 'bcc-gallery.js',
        ['jquery', 'bcc-core', 'media-views', 'bcc-gallery-slider'],
        time(),
        true
    );

    // Inline editing (depends on core)
    wp_enqueue_script(
        'bcc-inline-edit',
        $base_js . 'bcc-inline-edit.js',
        ['jquery', 'bcc-core'],
        time(),
        true
    );

    // Visibility popover (depends on core)
    wp_enqueue_script(
        'bcc-visibility',
        $base_js . 'bcc-visibility.js',
        ['jquery', 'bcc-core'],
        time(),
        true
    );

    // Main entry point (depends on all)
    wp_enqueue_script(
        'bcc-main',
        $base_js . 'bcc-main.js',
        ['jquery', 'bcc-core', 'bcc-gallery', 'bcc-inline-edit', 'bcc-visibility'],
        time(),
        true
    );

    // Optional: Add inline script for debugging (remove in production)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        wp_add_inline_script('bcc-main', '
            console.log("BCC modules loaded:", {
                core: typeof bccParseOptions !== "undefined",
                gallery: typeof wp !== "undefined" && wp.media ? "loaded" : "missing",
                gallerySlider: typeof initGallerySlider !== "undefined",
                inlineEdit: typeof bccInlineEdit !== "undefined",
                ajax: typeof bcc_ajax !== "undefined"
            });
        ');
    }
}