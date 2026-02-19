<?php
if (!defined('ABSPATH')) exit;

/*
|--------------------------------------------------------------------------
| FIELD VISIBILITY ENGINE
|--------------------------------------------------------------------------
| Controls who can VIEW and EDIT individual fields.
| Visibility values:
| - public   → anyone
| - members  → logged-in users
| - private  → owner only
*/

if (!function_exists('bcc_get_field_visibility')) {

function bcc_get_field_visibility($post_id, $field) {
    
    // Use a static cache to avoid multiple DB queries for same field
    static $cache = [];
    $cache_key = $post_id . '_' . $field;
    
    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    $vis = get_post_meta($post_id, '_bcc_vis_' . $field, true);

    if (!$vis) {
        $vis = 'public'; // default
    }
    
    $cache[$cache_key] = $vis;

    return $vis;
}

}

/* ======================================================
   CAN CURRENT USER VIEW FIELD
====================================================== */

if (!function_exists('bcc_user_can_view_field')) {

function bcc_user_can_view_field($post_id, $field) {

    $visibility = bcc_get_field_visibility($post_id, $field);

    // Public → everyone
    if ($visibility === 'public') {
        return true;
    }

    // Members → logged in only
    if ($visibility === 'members') {
        return is_user_logged_in();
    }

    // Private → owner only
    if ($visibility === 'private') {
        return bcc_user_is_owner($post_id);
    }

    // Fallback safe allow
    return true;
}

}

/* ======================================================
   CAN CURRENT USER EDIT FIELD
====================================================== */

if (!function_exists('bcc_user_can_edit_field')) {

function bcc_user_can_edit_field($post_id, $field) {

    // First check if user can edit the post at all
    if (!bcc_user_can_edit_post($post_id)) {
        return false;
    }

    // Then check field-specific visibility for edit permissions
    $visibility = bcc_get_field_visibility($post_id, $field);

    // If field is private, only owner can edit (already checked via bcc_user_can_edit_post)
    // If field is members/public, owner can edit (already checked)
    // So we don't need additional checks here, but we're keeping the function
    // for future expansion (e.g., role-based edit permissions)

    return true;
}

}

/* ======================================================
   SET FIELD VISIBILITY (with validation)
====================================================== */

if (!function_exists('bcc_set_field_visibility')) {

function bcc_set_field_visibility($post_id, $field, $visibility) {
    
    // Validate post exists
    if (!get_post($post_id)) {
        return false;
    }
    
    // Validate field name isn't empty
    if (empty($field)) {
        return false;
    }

    if (!in_array($visibility, ['public', 'members', 'private'], true)) {
        return false;
    }

    $result = update_post_meta($post_id, '_bcc_vis_' . $field, $visibility);
    
    // Clear cache if we're using caching
    if (function_exists('bcc_clear_visibility_cache')) {
        bcc_clear_visibility_cache($post_id, $field);
    }

    return (bool) $result;
}

}

/* ======================================================
   CLEAR VISIBILITY CACHE (optional helper)
====================================================== */

if (!function_exists('bcc_clear_visibility_cache')) {

function bcc_clear_visibility_cache($post_id = null, $field = null) {
    // This is a placeholder for when you want to implement
    // more sophisticated cache clearing. For now, the static
    // cache will clear automatically on page reload.
    
    // You could implement transients, object cache, etc. here
    return true;
}

}

/* ======================================================
   GET ALL FIELD VISIBILITY SETTINGS FOR A POST (debug helper)
====================================================== */

if (!function_exists('bcc_get_all_field_visibility')) {

function bcc_get_all_field_visibility($post_id) {
    global $wpdb;
    
    if (!$post_id || !is_numeric($post_id)) {
        return [];
    }
    
    $meta_key_prefix = '_bcc_vis_';
    $meta_key_pattern = $meta_key_prefix . '%';
    
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_key, meta_value FROM {$wpdb->postmeta} 
         WHERE post_id = %d AND meta_key LIKE %s",
        $post_id,
        $meta_key_pattern
    ));
    
    $visibility = [];
    foreach ($results as $row) {
        $field = str_replace($meta_key_prefix, '', $row->meta_key);
        $visibility[$field] = $row->meta_value;
    }
    
    return $visibility;
}

}

/* ======================================================
   BULK SET FIELD VISIBILITY (utility helper)
====================================================== */

if (!function_exists('bcc_bulk_set_field_visibility')) {

function bcc_bulk_set_field_visibility($post_id, $visibility_map) {
    
    if (!is_array($visibility_map) || empty($visibility_map)) {
        return false;
    }
    
    $success = 0;
    $total = 0;
    
    foreach ($visibility_map as $field => $visibility) {
        $total++;
        if (bcc_set_field_visibility($post_id, $field, $visibility)) {
            $success++;
        }
    }
    
    return [
        'success' => $success,
        'total' => $total,
        'message' => "Set {$success} of {$total} visibility settings"
    ];
}

}

/* ======================================================
   DELETE FIELD VISIBILITY (cleanup helper)
====================================================== */

if (!function_exists('bcc_delete_field_visibility')) {

function bcc_delete_field_visibility($post_id, $field) {
    
    if (!$post_id || empty($field)) {
        return false;
    }
    
    $result = delete_post_meta($post_id, '_bcc_vis_' . $field);
    
    // Clear cache if we're using caching
    if (function_exists('bcc_clear_visibility_cache')) {
        bcc_clear_visibility_cache($post_id, $field);
    }
    
    return (bool) $result;
}

}

/* ======================================================
   DELETE ALL FIELD VISIBILITY FOR A POST (cleanup helper)
====================================================== */

if (!function_exists('bcc_delete_all_field_visibility')) {

function bcc_delete_all_field_visibility($post_id) {
    global $wpdb;
    
    if (!$post_id || !is_numeric($post_id)) {
        return false;
    }
    
    $meta_key_prefix = '_bcc_vis_';
    $meta_key_pattern = $meta_key_prefix . '%';
    
    $result = $wpdb->delete(
        $wpdb->postmeta,
        [
            'post_id' => $post_id,
            'meta_key' => $meta_key_pattern
        ],
        ['%d', '%s']
    );
    
    return (bool) $result;
}

}