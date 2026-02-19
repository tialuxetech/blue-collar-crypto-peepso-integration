<?php
if (!defined('ABSPATH')) exit;

/**
 * ======================================================
 *  Gallery Upload + Delete + List + Reorder AJAX
 * ======================================================
 */

class BCC_Ajax_Gallery {

    public static function register(): void {
        add_action('wp_ajax_bcc_upload_gallery_images', [__CLASS__, 'upload']);
        add_action('wp_ajax_bcc_delete_gallery_image', [__CLASS__, 'delete']);

        // MISSING BEFORE — required for lazy-load + drag reorder
        add_action('wp_ajax_bcc_gallery_list_images', [__CLASS__, 'list_images']);
        add_action('wp_ajax_bcc_gallery_reorder_images', [__CLASS__, 'reorder_images']);
    }

    /* ======================================================
       HELPERS
    ====================================================== */

    private static function get_collection_or_fail(int $post_id, int $row) {
        $collection = BCC_Gallery_Repository::get_or_create_collection(
            $post_id,
            get_current_user_id(),
            $row
        );

        if (!$collection) {
            wp_send_json_error(['message' => 'Unable to load collection']);
        }

        return $collection;
    }

    private static function can_edit_or_fail(int $post_id): void {
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error(['message' => 'Permission denied']);
        }
    }

    /* ======================================================
       UPLOAD
    ====================================================== */

    public static function upload(): void {

        check_ajax_referer('bcc_nonce', 'nonce');

        $post_id = absint($_POST['post_id'] ?? 0);
        $row     = absint($_POST['row'] ?? 0);

        if (!$post_id || empty($_FILES['files'])) {
            wp_send_json_error(['message' => 'Missing data']);
        }

        self::can_edit_or_fail($post_id);

        $collection = self::get_collection_or_fail($post_id, $row);

        $upload_dir = wp_upload_dir();
        $base_dir   = trailingslashit($upload_dir['basedir']) . 'bcc-gallery/';
        $base_url   = trailingslashit($upload_dir['baseurl']) . 'bcc-gallery/';

        if (!file_exists($base_dir)) {
            wp_mkdir_p($base_dir);
        }

        $allowed_mimes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'image/heic',
            'image/heif'
        ];

        $uploaded = [];

        $names = $_FILES['files']['name'] ?? [];
        $tmp   = $_FILES['files']['tmp_name'] ?? [];
        $errs  = $_FILES['files']['error'] ?? [];

        foreach ($names as $i => $name) {

            if (!isset($errs[$i]) || $errs[$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $tmp_name = $tmp[$i] ?? '';
            if (!$tmp_name || !file_exists($tmp_name)) {
                continue;
            }

            $file_info = wp_check_filetype_and_ext($tmp_name, $name);
            $mime = $file_info['type'] ?? '';

            if (!$mime || !in_array($mime, $allowed_mimes, true)) {
                continue;
            }

            $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $safe = sanitize_file_name(pathinfo($name, PATHINFO_FILENAME));
            $file = $safe . '-' . uniqid('', true) . ($ext ? '.' . $ext : '');

            $path = $base_dir . $file;
            $url  = $base_url . $file;

            if (!move_uploaded_file($tmp_name, $path)) {
                continue;
            }

            // Thumbnail
            $thumb     = $base_dir . 'thumb-' . $file;
            $thumb_url = $base_url . 'thumb-' . $file;

            self::create_thumbnail($path, $thumb);

            $image_id = BCC_Gallery_Repository::insert_image(
                (int) $collection->id,
                [
                    'file'      => $file,
                    'url'       => $url,
                    'thumbnail' => file_exists($thumb) ? $thumb_url : $url,
                    'size'      => @filesize($path) ?: 0,
                ]
            );

            if (!$image_id) {
                continue;
            }

            $uploaded[] = [
                'id'        => (int) $image_id,
                'url'       => $url,
                'thumbnail' => file_exists($thumb) ? $thumb_url : $url
            ];
        }

        if (!$uploaded) {
            wp_send_json_error(['message' => 'No valid images uploaded']);
        }

        // Return updated total so JS can show correct count
        $total = BCC_Gallery_Repository::count_images((int) $collection->id);

        wp_send_json_success([
            'images' => $uploaded,
            'total'  => (int) $total
        ]);
    }

    /* ======================================================
       DELETE
    ====================================================== */

    public static function delete(): void {

        check_ajax_referer('bcc_nonce', 'nonce');

        $image_id = absint($_POST['image_id'] ?? 0);
        $post_id  = absint($_POST['post_id'] ?? 0);
        $row      = absint($_POST['row'] ?? 0);

        if (!$image_id || !$post_id) {
            wp_send_json_error(['message' => 'Missing data']);
        }

        self::can_edit_or_fail($post_id);

        $collection = self::get_collection_or_fail($post_id, $row);

        $image = BCC_Gallery_Repository::delete_image($image_id);
        if (!$image) {
            wp_send_json_error(['message' => 'Image not found']);
        }

        // Physical files
        $upload_dir = wp_upload_dir();
        $base_dir = trailingslashit($upload_dir['basedir']) . 'bcc-gallery/';

        @unlink($base_dir . $image->file);
        @unlink($base_dir . 'thumb-' . $image->file);

        $total = BCC_Gallery_Repository::count_images((int) $collection->id);

        wp_send_json_success([
            'message' => 'Deleted',
            'total'   => (int) $total
        ]);
    }

    /* ======================================================
       LIST (PAGED) — REQUIRED FOR LAZY LOAD
    ====================================================== */

    public static function list_images(): void {

        check_ajax_referer('bcc_nonce', 'nonce');

        $post_id   = absint($_POST['post_id'] ?? 0);
        $row       = absint($_POST['row'] ?? 0);
        $page      = absint($_POST['page'] ?? 1);
        $per_page  = absint($_POST['per_page'] ?? 12);

        if (!$post_id) {
            wp_send_json_error(['message' => 'Missing post_id']);
        }

        self::can_edit_or_fail($post_id);

        $collection = self::get_collection_or_fail($post_id, $row);

        $page = max(1, $page);
        $per_page = max(1, min(50, $per_page));

        $result = BCC_Gallery_Repository::get_images_paged((int) $collection->id, $page, $per_page);

        $items = [];
        foreach (($result['items'] ?? []) as $img) {
            $items[] = [
                'id'        => (int) $img->id,
                'url'       => (string) $img->url,
                'thumbnail' => (string) ($img->thumbnail ?: $img->url),
            ];
        }

        wp_send_json_success([
            'items' => $items,
            'page'  => $page,
            'total' => (int) ($result['total'] ?? 0)
        ]);
    }

    /* ======================================================
       REORDER — REQUIRED FOR DRAG & DROP SAVE
    ====================================================== */

    public static function reorder_images(): void {

        check_ajax_referer('bcc_nonce', 'nonce');

        $post_id = absint($_POST['post_id'] ?? 0);
        $row     = absint($_POST['row'] ?? 0);
        $order   = $_POST['order'] ?? [];

        if (!$post_id || !is_array($order)) {
            wp_send_json_error(['message' => 'Missing data']);
        }

        self::can_edit_or_fail($post_id);

        $collection = self::get_collection_or_fail($post_id, $row);

        $ok = BCC_Gallery_Repository::update_sort_orders((int) $collection->id, $order);

        if (!$ok) {
            wp_send_json_error(['message' => 'Reorder failed']);
        }

        wp_send_json_success(['message' => 'Reordered']);
    }

    /* ======================================================
       THUMBNAIL
    ====================================================== */

    private static function create_thumbnail(string $src, string $dest): void {

        if (!file_exists($src)) return;

        $info = @getimagesize($src);
        if (!$info) return;

        [$w, $h, $type] = $info;

        switch ($type) {
            case IMAGETYPE_JPEG: $img = @imagecreatefromjpeg($src); break;
            case IMAGETYPE_PNG:  $img = @imagecreatefrompng($src);  break;
            case IMAGETYPE_GIF:  $img = @imagecreatefromgif($src);  break;
            case IMAGETYPE_WEBP: $img = @imagecreatefromwebp($src); break;
            default: return;
        }

        if (!$img) return;

        $size = 200;
        $thumb = imagecreatetruecolor($size, $size);

        imagecopyresampled($thumb, $img, 0, 0, 0, 0, $size, $size, $w, $h);

        switch ($type) {
            case IMAGETYPE_JPEG: imagejpeg($thumb, $dest, 85); break;
            case IMAGETYPE_PNG:  imagepng($thumb, $dest, 9);   break;
            case IMAGETYPE_GIF:  imagegif($thumb, $dest);      break;
            case IMAGETYPE_WEBP: imagewebp($thumb, $dest, 85); break;
        }

        imagedestroy($img);
        imagedestroy($thumb);
    }
}

BCC_Ajax_Gallery::register();

