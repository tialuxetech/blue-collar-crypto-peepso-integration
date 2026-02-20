<?php
/**
 * Dashboard Hooks for PeepSo Integration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BCC_PeepSo_Dashboard {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'setup_hooks'), 20);
    }
    
    public function setup_hooks() {
        // Check if PeepSo is loaded
        if (!class_exists('PeepSo')) {
            return;
        }
        
        // Add dashboard to PeepSo page menu
        add_filter('peepso_page_segment_menu_links', array($this, 'add_dashboard_menu_link'), 10, 1);
        
        // Register dashboard segment
        add_action('peepso_page_segment_dashboard', array($this, 'render_dashboard_segment'));
    }
    
    public function add_dashboard_menu_link($segments) {
        if (isset($segments[0])) {
            $segments[0][] = array(
                'href' => 'dashboard',
                'title' => __('Dashboard', 'bcc-peepso-pages'),
                'icon' => 'gsi gsi-dashboard',
            );
        }
        return $segments;
    }
    
    public function render_dashboard_segment() {
        // Get the dashboard template
        $dashboard_template = BCC_TEMPLATES_PATH . 'peepso/dashboard.php';
        
        if (file_exists($dashboard_template)) {
            // Output the dashboard
            include $dashboard_template;
        } else {
            echo '<div class="ps-alert ps-alert--warning">';
            echo '<p>' . __('Dashboard template not found.', 'bcc-peepso-pages') . '</p>';
            echo '</div>';
        }
    }
}

// Initialize
add_action('plugins_loaded', function() {
    BCC_PeepSo_Dashboard::get_instance();
}, 20);
