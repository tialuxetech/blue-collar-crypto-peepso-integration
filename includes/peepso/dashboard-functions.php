<?php
/**
 * Dashboard rendering functions
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!function_exists('bcc_get_tab_icon')) {
    function bcc_get_tab_icon($tab_key) {
        $icons = [
            'overview'   => 'gsi gsi-home',
            'settings'   => 'gsi gsi-cog',
            'validators' => 'gsi gsi-check-circle',
            'builder'    => 'gsi gsi-tools',
            'nft'        => 'gsi gsi-image',
        ];
        
        return isset($icons[$tab_key]) ? $icons[$tab_key] : 'gsi gsi-circle';
    }
}

if (!function_exists('bcc_render_overview_tab')) {
    function bcc_render_overview_tab($page, $user_id, $available_tabs) {
        ob_start();
        ?>
        <div class="bcc-overview">
            <h3><?php _e('Welcome to Your Crypto Dashboard', 'bcc-peepso-pages'); ?></h3>
            <p><?php _e('Manage all your cryptocurrency activities from one central location.', 'bcc-peepso-pages'); ?></p>
            
            <div class="bcc-dashboard-grid">
                <?php if (isset($available_tabs['validators'])): ?>
                <div class="bcc-dashboard-widget">
                    <h4><i class="gsi gsi-check-circle"></i> <?php echo esc_html($available_tabs['validators']); ?></h4>
                    <p><?php _e('Monitor and manage your validator nodes.', 'bcc-peepso-pages'); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (isset($available_tabs['builder'])): ?>
                <div class="bcc-dashboard-widget">
                    <h4><i class="gsi gsi-tools"></i> <?php echo esc_html($available_tabs['builder']); ?></h4>
                    <p><?php _e('Access developer tools and resources.', 'bcc-peepso-pages'); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (isset($available_tabs['nft'])): ?>
                <div class="bcc-dashboard-widget">
                    <h4><i class="gsi gsi-image"></i> <?php echo esc_html($available_tabs['nft']); ?></h4>
                    <p><?php _e('Create and manage your NFT collections.', 'bcc-peepso-pages'); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// ... include all other rendering functions (same as before)
if (!function_exists('bcc_render_settings_tab')) {
    function bcc_render_settings_tab($page, $user_id) {
        ob_start();
        ?>
        <div class="bcc-settings">
            <h3><?php _e('Dashboard Settings', 'bcc-peepso-pages'); ?></h3>
            <p><?php _e('Configure your dashboard preferences and display options.', 'bcc-peepso-pages'); ?></p>
            
            <div class="bcc-settings-form">
                <div class="ps-form__row">
                    <label><?php _e('Display Mode:', 'bcc-peepso-pages'); ?></label>
                    <select>
                        <option value="light"><?php _e('Light Mode', 'bcc-peepso-pages'); ?></option>
                        <option value="dark"><?php _e('Dark Mode', 'bcc-peepso-pages'); ?></option>
                    </select>
                </div>
                
                <div class="ps-form__row">
                    <label>
                        <input type="checkbox" checked>
                        <?php _e('Show notifications', 'bcc-peepso-pages'); ?>
                    </label>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

if (!function_exists('bcc_render_validators_tab')) {
    function bcc_render_validators_tab($page, $user_id) {
        ob_start();
        ?>
        <div class="bcc-validators">
            <h3><?php _e('Validator Information', 'bcc-peepso-pages'); ?></h3>
            <p><?php _e('Monitor and manage your validator nodes.', 'bcc-peepso-pages'); ?></p>
            
            <div class="bcc-stats-grid">
                <div class="bcc-stat-card">
                    <div class="bcc-stat-icon">
                        <i class="gsi gsi-server"></i>
                    </div>
                    <div class="bcc-stat-info">
                        <h4><?php _e('Active Nodes', 'bcc-peepso-pages'); ?></h4>
                        <p class="bcc-stat-value">3</p>
                    </div>
                </div>
                
                <div class="bcc-stat-card">
                    <div class="bcc-stat-icon">
                        <i class="gsi gsi-activity"></i>
                    </div>
                    <div class="bcc-stat-info">
                        <h4><?php _e('Uptime', 'bcc-peepso-pages'); ?></h4>
                        <p class="bcc-stat-value">99.8%</p>
                    </div>
                </div>
            </div>
            
            <div class="bcc-section">
                <h4><?php _e('Recent Activity', 'bcc-peepso-pages'); ?></h4>
                <ul class="bcc-activity-list">
                    <li><?php _e('Validator #1 synchronized', 'bcc-peepso-pages'); ?></li>
                    <li><?php _e('Rewards distributed', 'bcc-peepso-pages'); ?></li>
                    <li><?php _e('Node health check completed', 'bcc-peepso-pages'); ?></li>
                </ul>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

if (!function_exists('bcc_render_builder_tab')) {
    function bcc_render_builder_tab($page, $user_id) {
        ob_start();
        ?>
        <div class="bcc-builder">
            <h3><?php _e('Builder Information', 'bcc-peepso-pages'); ?></h3>
            <p><?php _e('Developer tools and resources for builders.', 'bcc-peepso-pages'); ?></p>
            
            <div class="bcc-tools-grid">
                <div class="bcc-tool-card">
                    <h4><i class="gsi gsi-code"></i> <?php _e('API Documentation', 'bcc-peepso-pages'); ?></h4>
                    <p><?php _e('Access our comprehensive API docs', 'bcc-peepso-pages'); ?></p>
                    <a href="#" class="ps-btn ps-btn--sm"><?php _e('View Docs', 'bcc-peepso-pages'); ?></a>
                </div>
                
                <div class="bcc-tool-card">
                    <h4><i class="gsi gsi-terminal"></i> <?php _e('SDK Tools', 'bcc-peepso-pages'); ?></h4>
                    <p><?php _e('Download our SDKs and libraries', 'bcc-peepso-pages'); ?></p>
                    <a href="#" class="ps-btn ps-btn--sm"><?php _e('Download', 'bcc-peepso-pages'); ?></a>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

if (!function_exists('bcc_render_nft_tab')) {
    function bcc_render_nft_tab($page, $user_id) {
        ob_start();
        ?>
        <div class="bcc-nft">
            <h3><?php _e('NFT Information', 'bcc-peepso-pages'); ?></h3>
            <p><?php _e('Create and manage your NFT collections.', 'bcc-peepso-pages'); ?></p>
            
            <div class="bcc-nft-stats">
                <div class="bcc-stat-row">
                    <span><?php _e('Collections:', 'bcc-peepso-pages'); ?></span>
                    <strong>2</strong>
                </div>
                <div class="bcc-stat-row">
                    <span><?php _e('Total NFTs:', 'bcc-peepso-pages'); ?></span>
                    <strong>15</strong>
                </div>
                <div class="bcc-stat-row">
                    <span><?php _e('Total Volume:', 'bcc-peepso-pages'); ?></span>
                    <strong>3.5 ETH</strong>
                </div>
            </div>
            
            <div class="bcc-section">
                <h4><?php _e('Quick Actions', 'bcc-peepso-pages'); ?></h4>
                <div class="bcc-action-buttons">
                    <button class="ps-btn ps-btn--primary">
                        <i class="gsi gsi-plus"></i>
                        <?php _e('Create NFT', 'bcc-peepso-pages'); ?>
                    </button>
                    <button class="ps-btn">
                        <i class="gsi gsi-upload"></i>
                        <?php _e('Upload Collection', 'bcc-peepso-pages'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
