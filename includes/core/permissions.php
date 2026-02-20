<?php
if (!defined('ABSPATH')) exit;

/**
 * ======================================================
 * POST-LEVEL VIEW PERMISSION
 * ======================================================
 */

function bcc_user_can_view_post($post_id) {
    if (!$post_id) return false;
    
    $visibility = get_post_meta($post_id, '_bcc_visibility', true);
    if (!$visibility) $visibility = 'public';

    // Owner always sees
    if (bcc_user_is_owner($post_id)) {
        return true;
    }

    if ($visibility === 'public') {
        return true;
    }

    if ($visibility === 'members') {
        return is_user_logged_in();
    }

    // Private - only owner (already handled above)
    return false;
}

/**
 * ======================================================
 * POST-LEVEL EDIT PERMISSION
 * ======================================================
 */

function bcc_user_can_edit_post($post_id) {
    return bcc_user_is_owner($post_id);
}

/**
 * ======================================================
 * OWNER CHECK (centralized)
 * ======================================================
 */

function bcc_user_is_owner($post_id) {
    if (!is_user_logged_in() || !$post_id) return false;
    
    $author_id = (int) get_post_field('post_author', $post_id);
    $user_id = get_current_user_id();
    
    return $author_id === $user_id;
}

/**
 * ======================================================
 * PROFILE OWNER CHECK
 * ======================================================
 */

if (!function_exists('bcc_user_is_profile_owner')) {

function bcc_user_is_profile_owner($profile_user_id) {

    if (!is_user_logged_in()) {
        return false;
    }

    return (int) get_current_user_id() === (int) $profile_user_id;
}

}