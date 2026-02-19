<?php
if (!defined('ABSPATH')) exit;

/* ======================================================
   INLINE SAVE ENDPOINT
====================================================== */

add_action('wp_ajax_bcc_inline_save', 'bcc_inline_save');

function bcc_inline_save() {

    check_ajax_referer('bcc_nonce', 'nonce');

    if (!function_exists('update_field')) {
        wp_send_json_error('ACF not active');
    }

    $post_id  = absint($_POST['post_id'] ?? 0);
    $field    = sanitize_text_field($_POST['field'] ?? '');
    $value    = wp_unslash($_POST['value'] ?? '');
    $type     = sanitize_text_field($_POST['type'] ?? 'text');

    $repeater = intval($_POST['repeater'] ?? 0);
    $row      = absint($_POST['row'] ?? 0);
    $sub      = sanitize_text_field($_POST['sub'] ?? '');

    if (!$post_id || !$field) {
        wp_send_json_error('Missing data');
    }

    if (!current_user_can('edit_post', $post_id)) {
        wp_send_json_error('Permission denied');
    }

    /* ===========================
       ADD NEW REPEATER ROW
    =========================== */
    if ($repeater && $sub === 'add_new') {
        $rows = get_field($field, $post_id);
        if (!is_array($rows)) {
            $rows = [];
        }
        $rows[] = [];
        update_field($field, $rows, $post_id);
        wp_send_json_success([
            'message' => 'New item added',
            'rows'    => count($rows)
        ]);
    }

    /* ===========================
       PROCESS VALUE BASED ON TYPE
    =========================== */
    if ($type === 'gallery') {
        if (is_string($value) && !empty($value)) {
            $value = array_map('intval', explode(',', $value));
        } elseif (empty($value)) {
            $value = [];
        }
    }

    /* ===========================
       NORMAL FIELD
    =========================== */
    if (!$repeater) {
        update_field($field, $value, $post_id);
        wp_send_json_success(['value' => $value]);
    }

    /* ===========================
       REPEATER FIELD (EXISTING ROW)
    =========================== */
    if (!$sub) {
        wp_send_json_error('Missing sub field');
    }

    $rows = get_field($field, $post_id);
    if (!is_array($rows)) {
        $rows = [];
    }
    if (!isset($rows[$row])) {
        $rows[$row] = [];
    }
    $rows[$row][$sub] = $value;

    update_field($field, $rows, $post_id);
    wp_send_json_success(['value' => $value]);
}

/* ======================================================
   FIELD VISIBILITY SAVE ENDPOINT
====================================================== */

add_action('wp_ajax_bcc_save_field_visibility', 'bcc_save_field_visibility');

function bcc_save_field_visibility() {
    
    if (!check_ajax_referer('bcc_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Security check failed']);
    }

    $post_id = absint($_POST['post_id'] ?? 0);
    $field   = sanitize_text_field($_POST['field'] ?? '');
    $visibility = sanitize_text_field($_POST['visibility'] ?? '');

    if (!$post_id || !$field || !$visibility) {
        wp_send_json_error(['message' => 'Missing required data']);
    }

    if (!in_array($visibility, ['public', 'members', 'private'], true)) {
        wp_send_json_error(['message' => 'Invalid visibility value']);
    }

    if (!function_exists('bcc_user_can_edit_post') || !bcc_user_can_edit_post($post_id)) {
        wp_send_json_error(['message' => 'You do not have permission to edit this field']);
    }

    $saved = bcc_set_field_visibility($post_id, $field, $visibility);

    if ($saved) {
        wp_send_json_success([
            'message'    => 'Visibility updated',
            'visibility' => $visibility,
            'field'      => $field
        ]);
    } else {
        wp_send_json_error(['message' => 'Failed to save visibility']);
    }
}

/* ======================================================
   GALLERY IMAGE UPLOAD - Custom user folders, no media library
====================================================== */

add_action('wp_ajax_bcc_upload_gallery_images', 'bcc_upload_gallery_images');

function bcc_upload_gallery_images() {
    
    // Enable error logging
    error_log('BCC Upload: Starting custom file upload');
    
    if (!check_ajax_referer('bcc_nonce', 'nonce', false)) {
        error_log('BCC Upload: Nonce check failed');
        wp_send_json_error(['message' => 'Security check failed']);
    }
    
    $post_id = absint($_POST['post_id'] ?? 0);
    $repeater_row = absint($_POST['row'] ?? 0);
    
    error_log('BCC Upload: Post ID: ' . $post_id . ', Row: ' . $repeater_row);
    
    if (!current_user_can('edit_post', $post_id)) {
        error_log('BCC Upload: Permission denied');
        wp_send_json_error(['message' => 'Permission denied']);
    }
    
    if (empty($_FILES['files'])) {
        error_log('BCC Upload: No files');
        wp_send_json_error(['message' => 'No files uploaded']);
    }
    
    // Allowed image types
    $allowed_types = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'
    ];
    
    // Get current user for folder naming
    $user = wp_get_current_user();
    $user_id = $user->ID;
    $user_name = sanitize_user($user->user_login, true);
    
    // Create user folder structure
    $upload_dir = wp_upload_dir();
    $base_dir = $upload_dir['basedir'] . '/bcc-gallery/';
    $user_dir = $base_dir . 'user-' . $user_id . '-' . $user_name . '/';
    $post_dir = $user_dir . 'post-' . $post_id . '/';
    $collection_dir = $post_dir . 'collection-' . $repeater_row . '/';
    
    error_log('BCC Upload: Directory: ' . $collection_dir);
    
    // Create directories if they don't exist
    if (!file_exists($base_dir)) {
        wp_mkdir_p($base_dir);
    }
    if (!file_exists($user_dir)) {
        wp_mkdir_p($user_dir);
    }
    if (!file_exists($post_dir)) {
        wp_mkdir_p($post_dir);
    }
    if (!file_exists($collection_dir)) {
        wp_mkdir_p($collection_dir);
    }
    
    $files = $_FILES['files'];
    $uploaded = [];
    $errors = [];
    
    // Get or create collection record ONCE at the beginning
    global $wpdb;
    $collections_table = $wpdb->prefix . 'bcc_nft_collections';
    
    // Check if collection exists
    $collection = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM $collections_table WHERE post_id = %d AND sort_order = %d",
        $post_id,
        $repeater_row
    ));
    
    $collection_id = 0;
    
    if (!$collection) {
        // Create new collection
        $wpdb->insert(
            $collections_table,
            [
                'post_id' => $post_id,
                'user_id' => $user_id,
                'collection_name' => 'Collection ' . ($repeater_row + 1),
                'gallery_images' => '[]',
                'gallery_count' => 0,
                'sort_order' => $repeater_row
            ],
            ['%d', '%d', '%s', '%s', '%d', '%d']
        );
        $collection_id = $wpdb->insert_id;
        error_log('BCC Upload: Created new collection with ID: ' . $collection_id);
    } else {
        $collection_id = $collection->id;
        error_log('BCC Upload: Using existing collection ID: ' . $collection_id);
    }
    
    // Handle multiple files - ONE FILE AT A TIME, SAVE AFTER EACH
    if (is_array($files['name'])) {
        for ($i = 0; $i < count($files['name']); $i++) {
            
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $errors[] = $files['name'][$i] . ': Upload error';
                continue;
            }
            
            // Validate file type
            $file_type = $files['type'][$i];
            if (!in_array($file_type, $allowed_types, true)) {
                $errors[] = $files['name'][$i] . ': Not an allowed image type';
                continue;
            }
            
            // Create safe filename
            $file_info = pathinfo($files['name'][$i]);
            $ext = isset($file_info['extension']) ? '.' . $file_info['extension'] : '';
            $base_name = sanitize_title($file_info['filename']);
            $filename = $base_name . '-' . uniqid() . $ext;
            $filepath = $collection_dir . $filename;
            
            // For URL, use the same structure but with baseurl
            $fileurl = $upload_dir['baseurl'] . '/bcc-gallery/user-' . $user_id . '-' . $user_name . '/post-' . $post_id . '/collection-' . $repeater_row . '/' . $filename;
            
            error_log('BCC Upload: Processing: ' . $filename);
            
            // Move the file
            if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
                
                // Create thumbnail
                $thumb_path = $collection_dir . 'thumb-' . $filename;
                $thumb_url = $upload_dir['baseurl'] . '/bcc-gallery/user-' . $user_id . '-' . $user_name . '/post-' . $post_id . '/collection-' . $repeater_row . '/thumb-' . $filename;
                
                // Create thumbnail if GD is available
                if (function_exists('imagecreatefromjpeg')) {
                    bcc_create_thumbnail($filepath, $thumb_path, 150, 150);
                }
                
                // Create image data array
                $image_data = [
                    'id' => uniqid('img_', true),
                    'file' => $filename,
                    'url' => $fileurl,
                    'thumbnail' => file_exists($thumb_path) ? $thumb_url : $fileurl,
                    'name' => $files['name'][$i],
                    'size' => $files['size'][$i],
                    'uploaded' => current_time('mysql')
                ];
                
                // Get current gallery data
                $current_data = $wpdb->get_var($wpdb->prepare(
                    "SELECT gallery_images FROM $collections_table WHERE id = %d",
                    $collection_id
                ));
                
                $gallery = [];
                if ($current_data) {
                    $gallery = json_decode($current_data, true);
                    if (!is_array($gallery)) {
                        $gallery = [];
                    }
                }
                
                // Add new image
                $gallery[] = $image_data;
                
                // Update database for THIS FILE
                $wpdb->update(
                    $collections_table,
                    [
                        'gallery_images' => json_encode($gallery),
                        'gallery_count' => count($gallery)
                    ],
                    ['id' => $collection_id],
                    ['%s', '%d'],
                    ['%d']
                );
                
                $uploaded[] = [
                    'id' => $image_data['id'],
                    'url' => $image_data['url'],
                    'thumbnail' => $image_data['thumbnail'],
                    'name' => $image_data['name']
                ];
                
                error_log('BCC Upload: Added and saved: ' . $image_data['id']);
                
            } else {
                $errors[] = $files['name'][$i] . ': Failed to save';
            }
        }
    }
    
    if (empty($uploaded)) {
        error_log('BCC Upload: Failed - ' . print_r($errors, true));
        wp_send_json_error([
            'message' => 'Upload failed',
            'errors' => $errors
        ]);
    }
    
    error_log('BCC Upload: Success - ' . count($uploaded) . ' images');
    
    wp_send_json_success([
        'message' => count($uploaded) . ' image(s) uploaded',
        'images' => $uploaded,
        'errors' => $errors
    ]);
}

/**
 * Simple thumbnail creator
 */
function bcc_create_thumbnail($src, $dest, $width, $height) {
    if (!file_exists($src)) return false;
    
    $info = getimagesize($src);
    if (!$info) return false;
    
    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($src);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($src);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($src);
            break;
        case IMAGETYPE_WEBP:
            $image = imagecreatefromwebp($src);
            break;
        default:
            return false;
    }
    
    $thumb = imagecreatetruecolor($width, $height);
    
    // Preserve transparency for PNG
    if ($info[2] == IMAGETYPE_PNG) {
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
        imagefilledrectangle($thumb, 0, 0, $width, $height, $transparent);
    }
    
    imagecopyresampled($thumb, $image, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
    
    // Save based on type
    switch ($info[2]) {
        case IMAGETYPE_JPEG:
            imagejpeg($thumb, $dest, 85);
            break;
        case IMAGETYPE_PNG:
            imagepng($thumb, $dest, 9);
            break;
        case IMAGETYPE_GIF:
            imagegif($thumb, $dest);
            break;
        case IMAGETYPE_WEBP:
            imagewebp($thumb, $dest, 85);
            break;
    }
    
    imagedestroy($image);
    imagedestroy($thumb);
    
    return true;
}

/* ======================================================
   DELETE GALLERY IMAGE
====================================================== */

add_action('wp_ajax_bcc_delete_gallery_image', 'bcc_delete_gallery_image');

function bcc_delete_gallery_image() {
    
    error_log('BCC Delete: Starting');
    
    if (!check_ajax_referer('bcc_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Security check failed']);
    }
    
    $image_id = sanitize_text_field($_POST['image_id'] ?? '');
    $post_id = absint($_POST['post_id'] ?? 0);
    $repeater_row = absint($_POST['row'] ?? 0);
    
    error_log('BCC Delete: Image ID: ' . $image_id . ', Post: ' . $post_id . ', Row: ' . $repeater_row);
    
    if (!$image_id || !$post_id) {
        wp_send_json_error(['message' => 'Missing data']);
    }
    
    global $wpdb;
    $collections_table = $wpdb->prefix . 'bcc_nft_collections';
    
    // Get collection
    $collection = $wpdb->get_row($wpdb->prepare(
        "SELECT id, gallery_images FROM $collections_table WHERE post_id = %d AND sort_order = %d",
        $post_id,
        $repeater_row
    ));
    
    if (!$collection || empty($collection->gallery_images)) {
        wp_send_json_error(['message' => 'Collection not found']);
    }
    
    $gallery = json_decode($collection->gallery_images, true);
    if (!is_array($gallery)) {
        wp_send_json_error(['message' => 'Invalid gallery data']);
    }
    
    // Find and remove the image
    $new_gallery = [];
    $deleted_file = '';
    
    foreach ($gallery as $img) {
        if ($img['id'] == $image_id) {
            $deleted_file = $img['file'] ?? '';
            continue;
        }
        $new_gallery[] = $img;
    }
    
    // Delete physical file
    if ($deleted_file) {
        $user = wp_get_current_user();
        $user_name = sanitize_user($user->user_login, true);
        $upload_dir = wp_upload_dir();
        $filepath = $upload_dir['basedir'] . '/bcc-gallery/user-' . $user->ID . '-' . $user_name . '/post-' . $post_id . '/collection-' . $repeater_row . '/' . $deleted_file;
        $thumbpath = $upload_dir['basedir'] . '/bcc-gallery/user-' . $user->ID . '-' . $user_name . '/post-' . $post_id . '/collection-' . $repeater_row . '/thumb-' . $deleted_file;
        
        if (file_exists($filepath)) unlink($filepath);
        if (file_exists($thumbpath)) unlink($thumbpath);
        
        error_log('BCC Delete: Deleted files');
    }
    
    // Update database
    $wpdb->update(
        $collections_table,
        [
            'gallery_images' => json_encode($new_gallery),
            'gallery_count' => count($new_gallery)
        ],
        ['id' => $collection->id],
        ['%s', '%d'],
        ['%d']
    );
    
    wp_send_json_success(['message' => 'Image deleted']);
}