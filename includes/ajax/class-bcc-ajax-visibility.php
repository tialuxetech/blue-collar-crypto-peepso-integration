<?php
/**
 * ======================================================
 *  BCC AJAX – Field Visibility Controller
 * ======================================================
 * Handles saving visibility state (public / members / private)
 * for individual ACF fields.
 */

if (!defined('ABSPATH')) {
    exit;
}

class BCC_Ajax_Visibility {

    /**
     * Bootstraps AJAX hooks
     */
    public static function register() {
        add_action('wp_ajax_bcc_save_field_visibility', [__CLASS__, 'handle']);
    }

    /**
     * Main AJAX handler
     */
    public static function handle() {

        /* ----------------------------------------
         * Security
         * ------------------------------------- */

        if (!check_ajax_referer('bcc_nonce', 'nonce', false)) {
            wp_send_json_error([
                'message' => 'Security check failed'
            ]);
        }

        /* ----------------------------------------
         * Input
         * ------------------------------------- */

        $post_id    = absint($_POST['post_id'] ?? 0);
        $field      = sanitize_text_field($_POST['field'] ?? '');
        $visibility = sanitize_text_field($_POST['visibility'] ?? '');

        if (!$post_id || !$field || !$visibility) {
            wp_send_json_error([
                'message' => 'Missing required data'
            ]);
        }

        /* ----------------------------------------
         * Validate visibility value
         * ------------------------------------- */

        $allowed = ['public', 'members', 'private'];

        if (!in_array($visibility, $allowed, true)) {
            wp_send_json_error([
                'message' => 'Invalid visibility value'
            ]);
        }

        /* ----------------------------------------
         * Permission
         * ------------------------------------- */

        if (function_exists('bcc_user_can_edit_post')) {
            if (!bcc_user_can_edit_post($post_id)) {
                wp_send_json_error([
                    'message' => 'Permission denied'
                ]);
            }
        } else {
            if (!current_user_can('edit_post', $post_id)) {
                wp_send_json_error([
                    'message' => 'Permission denied'
                ]);
            }
        }

        /* ----------------------------------------
         * Save visibility
         * ------------------------------------- */

        if (!function_exists('bcc_set_field_visibility')) {
            wp_send_json_error([
                'message' => 'Visibility system not available'
            ]);
        }

        $saved = bcc_set_field_visibility($post_id, $field, $visibility);

        if (!$saved) {
            wp_send_json_error([
                'message' => 'Failed to save visibility'
            ]);
        }

        /* ----------------------------------------
         * Success
         * ------------------------------------- */

        wp_send_json_success([
            'message'    => 'Visibility updated',
            'post_id'    => $post_id,
            'field'      => $field,
            'visibility' => $visibility
        ]);
    }
}

/* ======================================================
   Boot
====================================================== */

BCC_Ajax_Visibility::register();
