<?php
if (!defined('ABSPATH')) exit;

class BCC_Ajax_Gallery_Meta {

    public static function register() {
        add_action('wp_ajax_bcc_gallery_list_images', [__CLASS__, 'list_images']);
        add_action('wp_ajax_bcc_gallery_reorder_images', [__CLASS__, 'reorder']);
    }

    public static function list_images() {

        check_ajax_referer('bcc_nonce', 'nonce');

        $post_id = absint($_POST['post_id'] ?? 0);
        $row     = absint($_POST['row'] ?? 0);
        $page    = absint($_POST['page'] ?? 1);
        $per     = absint($_POST['per_page'] ?? 12);

        if (!$post_id) {
            wp_send_json_error(['message' => 'Invalid post']);
        }

        // View permission (if you have it)
        if (function_exists('bcc_user_can_view_post') && !bcc_user_can_view_post($post_id)) {
            wp_send_json_error(['message' => 'Not allowed']);
        }

        $collection = BCC_Gallery_Repository::get_or_create_collection(
            $post_id,
            get_current_user_id(),
            $row
        );

        if (!$collection) {
            wp_send_json_success(['items' => [], 'total' => 0, 'page' => $page]);
        }

        $result = BCC_Gallery_Repository::get_images_paged((int)$collection->id, $page, $per);

        $items = [];
        foreach ($result['items'] as $img) {
            $items[] = [
                'id' => (int) $img->id,
                'url' => (string) $img->url,
                'thumbnail' => (string) ($img->thumbnail ?: $img->url),
            ];
        }

        wp_send_json_success([
            'items' => $items,
            'total' => (int) $result['total'],
            'page'  => (int) $page,
            'per_page' => (int) $per,
        ]);
    }

    public static function reorder() {

        check_ajax_referer('bcc_nonce', 'nonce');

        $post_id = absint($_POST['post_id'] ?? 0);
        $row     = absint($_POST['row'] ?? 0);
        $order   = $_POST['order'] ?? [];

        if (!$post_id || !is_array($order)) {
            wp_send_json_error(['message' => 'Missing data']);
        }

        // Edit permission
        if (function_exists('bcc_user_can_edit_post')) {
            if (!bcc_user_can_edit_post($post_id)) {
                wp_send_json_error(['message' => 'Permission denied']);
            }
        } else {
            if (!current_user_can('edit_post', $post_id)) {
                wp_send_json_error(['message' => 'Permission denied']);
            }
        }

        $collection = BCC_Gallery_Repository::get_or_create_collection(
            $post_id,
            get_current_user_id(),
            $row
        );

        if (!$collection) {
            wp_send_json_error(['message' => 'Collection not found']);
        }

        $ok = BCC_Gallery_Repository::update_sort_orders((int)$collection->id, $order);

        if (!$ok) {
            wp_send_json_error(['message' => 'Invalid order']);
        }

        wp_send_json_success(['message' => 'Reordered']);
    }
}

BCC_Ajax_Gallery_Meta::register();
