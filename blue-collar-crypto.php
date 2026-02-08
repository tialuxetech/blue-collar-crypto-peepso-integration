<?php
/**
 * Plugin Name: Blue Collar Crypto -Peepso Integration
 * Description: Blue Collar Crypto is a plugin that integrates cryptocurrency features into the PeepSo social networking platform. It allows users to manage their crypto portfolios, view market data, and engage with the crypto community directly from their PeepSo profiles.
 * Version: 1.0.0
 * Author: Blue Collar Labs LLC
 * License: GPL v2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants
define('BCC_VERSION', '1.0.0');
define('BCC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('BCC_TEMPLATES_PATH', BCC_PLUGIN_PATH . 'templates/');
define('BCC_URL', plugin_dir_url(__FILE__));
define('BCC_INCLUDES_PATH', BCC_PLUGIN_PATH . 'includes/');

require_once BCC_INCLUDES_PATH . 'enqueue.php';
require_once BCC_INCLUDES_PATH . 'ajax-inline-edit.php';
require_once BCC_INCLUDES_PATH . 'sync/page-category-map.php';
require_once BCC_INCLUDES_PATH . 'sync/page-to-cpt-sync.php';


// Initialize
add_action('plugins_loaded', 'bcc_init');

function bcc_init() {
    if (!class_exists('PeepSo')) {
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-warning">
                <p><?php _e('Blue Collar Crypto requires PeepSo to be installed and activated.', 'blue-collar-crypto'); ?></p>
            </div>
            <?php
        });
        return;
    }
    
    // Add dashboard to PeepSo menu
    add_filter('peepso_page_segment_menu_links', function($segments) {
        if (isset($segments[0])) {
            $segments[0][] = array(
                'href' => 'dashboard',
                'title' => __('Dashboard', 'blue-collar-crypto'),
                'icon' => 'gsi gsi-dashboard',
            );
        }
        return $segments;
    });
    
    // Render dashboard
    add_action('peepso_page_segment_dashboard', function() {
        $template = BCC_TEMPLATES_PATH . 'peepso/dashboard.php';
        if (file_exists($template)) {
            include $template;
        } else {
            echo '<p>' . __('Dashboard template not found.', 'blue-collar-crypto') . '</p>';
        }
    });
    
    // Load textdomain for translations
    load_plugin_textdomain('blue-collar-crypto', false, dirname(plugin_basename(__FILE__)) . '/languages');
}