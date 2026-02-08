<?php
/**
 * PeepSo Segments Integration
 * This file handles the integration with PeepSo's page segments system
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BCC_PeepSo_Segments {
    
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
        // Only run if PeepSo is active
        if (!class_exists('PeepSo')) {
            return;
        }
        
        // Register our dashboard as a page segment
        add_action('peepso_page_segment_dashboard', array($this, 'render_dashboard'));
    }
    
    public function render_dashboard() {
        // Get the dashboard template
        $dashboard_template = BCC_TEMPLATES_PATH . 'peepso/dashboard.php';
        
        if (file_exists($dashboard_template)) {
            // Include the dashboard template
            include $dashboard_template;
        } else {
            // Fallback message if template not found
            echo '<div class="ps-alert ps-alert--warning">';
            echo '<p>' . __('Dashboard template not found.', 'bcc-peepso-pages') . '</p>';
            echo '</div>';
        }
    }
}

// Initialize segments
add_action('plugins_loaded', function() {
    BCC_PeepSo_Segments::get_instance();
}, 20);