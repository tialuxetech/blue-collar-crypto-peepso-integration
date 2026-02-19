<?php
/**
 * Plugin Name: Blue Collar Crypto – PeepSo Integration
 * Description: Core integration layer between Blue Collar Crypto and the PeepSo social platform.
 * Version: 1.0.0
 * Author: Blue Collar Labs LLC
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) exit;

/**
 * ==========================================================
 * Constants
 * ==========================================================
 */
define('BCC_VERSION', '1.0.0');
define('BCC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('BCC_INCLUDES_PATH', BCC_PLUGIN_PATH . 'includes/');
define('BCC_TEMPLATES_PATH', BCC_PLUGIN_PATH . 'templates/');
define('BCC_URL', plugin_dir_url(__FILE__));

/**
 * ==========================================================
 * Bootstrap
 * ==========================================================
 */
$bootstrap = BCC_INCLUDES_PATH . 'core/bootstrap.php';

if (file_exists($bootstrap)) {
    require_once $bootstrap;
} else {
    // fallback: do not fatal the site if file missing
    add_action('admin_notices', function () use ($bootstrap) {
        echo '<div class="notice notice-error"><p>';
        echo esc_html('BCC Bootstrap missing: ' . $bootstrap);
        echo '</p></div>';
    });
    return;
}

/**
 * ==========================================================
 * Initialize Plugin
 * ==========================================================
 */
add_action('plugins_loaded', 'bcc_init', 20);

function bcc_init() {

    if (!class_exists('PeepSo')) {

        add_action('admin_notices', function () {
            echo '<div class="notice notice-warning"><p>';
            echo esc_html__('Blue Collar Crypto – PeepSo Integration requires PeepSo to be installed and activated.', 'blue-collar-crypto');
            echo '</p></div>';
        });

        return;
    }

    // Register Dashboard Tab
    add_filter('peepso_page_segment_menu_links', 'bcc_register_dashboard_tab');

    // Register Dashboard Renderer
    add_action('peepso_page_segment_dashboard', 'bcc_render_dashboard_segment', 10, 2);

    // Translations
    load_plugin_textdomain(
        'blue-collar-crypto',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}

/**
 * ==========================================================
 * Dashboard Tab Registration
 * ==========================================================
 */
function bcc_register_dashboard_tab($segments) {

    if (isset($segments[0])) {
        $segments[0][] = [
            'href'  => 'dashboard',
            'title' => __('Dashboard', 'blue-collar-crypto'),
            'icon'  => 'gsi gsi-dashboard',
        ];
    }

    return $segments;
}

/**
 * ==========================================================
 * Dashboard Renderer
 * ==========================================================
 */
function bcc_render_dashboard_segment($page_data = null, $url_segments = null) {

    $template = BCC_TEMPLATES_PATH . 'peepso/dashboard.php';

    if (!file_exists($template)) {
        echo '<p>Dashboard template not found.</p>';
        return;
    }

    include $template;
}

