<?php
if (!defined('ABSPATH')) exit;

/**
 * Legacy template functions for backward compatibility
 */

if (!function_exists('bcc_render_divider')) {
    function bcc_render_divider(): void {
        echo '<hr class="bcc-divider">';
    }
}

if (!function_exists('bcc_options_to_map')) {
    function bcc_options_to_map(string $options_str): array {
        return BCC_Options_Helper::parse_options_string($options_str);
    }
}

if (!function_exists('bcc_render_row')) {
    function bcc_render_row(array $args = []): void {
        $renderer = new BCC_Field_Renderer($args);
        $renderer->render();
    }
}

if (!function_exists('bcc_render_rows')) {
    function bcc_render_rows(int $post_id, array $fields, bool $can_edit = false): void {
        foreach ($fields as $field => $args) {
            if (empty($args['label'])) continue;
            
            bcc_render_row([
                'post_id'  => $post_id,
                'field'    => $field,
                'label'    => $args['label'],
                'type'     => $args['type'] ?? 'text',
                'options'  => $args['options'] ?? '',
                'can_edit' => $can_edit
            ]);
        }
    }
}

/**
 * NEW: NFT Collections renderer - Grid layout with gallery at top
 */
if (!function_exists('bcc_render_nft_collections')) {
    function bcc_render_nft_collections(array $args = []): void {
        if (class_exists('BCC_NFT_Repeater_Renderer')) {
            BCC_NFT_Repeater_Renderer::render($args);
        } else {
            echo '<p>NFT Collections renderer not available. Please check that class-bcc-nft-repeater-renderer.php is loaded.</p>';
        }
    }
}

/**
 * NEW: Validator Chains renderer - Horizontal slider with stats
 */
if (!function_exists('bcc_render_validator_chains')) {
    function bcc_render_validator_chains(array $args = []): void {
        if (class_exists('BCC_Validator_Repeater_Renderer')) {
            BCC_Validator_Repeater_Renderer::render($args);
        } else {
            echo '<p>Validator Chains renderer not available. Please check that class-bcc-validator-repeater-renderer.php is loaded.</p>';
        }
    }
}

/**
 * Updated: Keep old function for backward compatibility
 * Now auto-detects which renderer to use based on post type
 */
if (!function_exists('bcc_render_repeater_slider')) {
    function bcc_render_repeater_slider(array $args = []): void {
        // Try to detect which renderer to use
        $post_id = $args['post_id'] ?? 0;
        $post_type = $post_id ? get_post_type($post_id) : '';
        
        // For NFT post type, use the new NFT renderer
        if ($post_type === 'nft' && class_exists('BCC_NFT_Repeater_Renderer')) {
            BCC_NFT_Repeater_Renderer::render($args);
        } 
        // For Validator post type, use the new Validator renderer
        elseif ($post_type === 'validators' && class_exists('BCC_Validator_Repeater_Renderer')) {
            BCC_Validator_Repeater_Renderer::render($args);
        }
        // Fallback to original renderer
        elseif (class_exists('BCC_Repeater_Renderer')) {
            BCC_Repeater_Renderer::render($args);
        }
        // Ultimate fallback
        else {
            echo '<p>Repeater renderer not available.</p>';
        }
    }
}

if (!function_exists('bcc_get_repeater_count')) {
    function bcc_get_repeater_count(int $post_id, string $repeater_key): int {
        $post_type = get_post_type($post_id);
        
        if ($post_type === 'nft' && class_exists('BCC_NFT_Repeater_Renderer')) {
            return BCC_NFT_Repeater_Renderer::get_count($post_id, $repeater_key);
        } elseif (class_exists('BCC_Repeater_Renderer')) {
            return BCC_Repeater_Renderer::get_count($post_id, $repeater_key);
        }
        
        return 0;
    }
}

if (!function_exists('bcc_repeater_has_rows')) {
    function bcc_repeater_has_rows(int $post_id, string $repeater_key): bool {
        return bcc_get_repeater_count($post_id, $repeater_key) > 0;
    }
}