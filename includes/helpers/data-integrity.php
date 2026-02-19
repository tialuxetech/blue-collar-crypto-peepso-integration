<?php
if (!defined('ABSPATH')) exit;

/**
 * ======================================================
 * DATA INTEGRITY GUARD RAILS
 * ======================================================
 */

/**
 * Auto-create missing shadow CPT on page load
 */
add_action('wp', function () {
    if (!is_singular('peepso-page')) return;

    global $post;
    if (!$post) return;

    $page_id = $post->ID;

    // Define CPT mappings
    $cpt_map = [
        'validators' => 'validators',
        'nft'        => 'nft',
        'builder'    => 'builder'
    ];

    foreach ($cpt_map as $cpt_key => $cpt_name) {
        $existing = get_post_meta($page_id, '_linked_' . $cpt_name . '_id', true);
        
        if ($existing && get_post($existing)) continue;

        // Create the shadow post
        $new_id = wp_insert_post([
            'post_type'    => $cpt_name,
            'post_title'   => $post->post_title,
            'post_status'  => 'publish',
            'post_author'  => (int) $post->post_author,
            'post_content' => ''
        ]);

        if (!$new_id || is_wp_error($new_id)) continue;

        // Link both ways
        update_post_meta($new_id, '_peepso_page_id', $page_id);
        update_post_meta($page_id, '_linked_' . $cpt_name . '_id', $new_id);
        
        // Set default visibility
        update_post_meta($new_id, '_bcc_visibility', 'public');
    }
});

/**
 * Prevent duplicate shadow CPTs
 */
add_action('save_post', function ($post_id, $post) {
    if (wp_is_post_revision($post_id)) return;

    $page_id = get_post_meta($post_id, '_peepso_page_id', true);
    if (!$page_id) return;

    $type = $post->post_type;
    $existing = get_post_meta($page_id, '_linked_' . $type . '_id', true);

    if ($existing && (int)$existing !== (int)$post_id) {
        wp_trash_post($post_id);
    }
}, 10, 2);

/**
 * Page Title sync to shadow CPTs
 */
add_action('save_post_peepso-page', function ($page_id, $page) {
    // Get all linked CPTs
    $cpt_map = ['validators', 'nft', 'builder'];
    
    foreach ($cpt_map as $cpt) {
        $linked_id = get_post_meta($page_id, '_linked_' . $cpt . '_id', true);
        if (!$linked_id || !get_post($linked_id)) continue;

        wp_update_post([
            'ID'         => $linked_id,
            'post_title' => $page->post_title,
            'post_name'  => sanitize_title($page->post_title)
        ]);
    }
}, 20, 2);

/**
 * Lock CPT title editing
 */
add_action('admin_init', function () {
    $cpts = ['validators', 'nft', 'builder'];
    foreach ($cpts as $cpt) {
        remove_post_type_support($cpt, 'title');
    }
});

/**
 * Admin notice for linked CPTs
 */
add_action('admin_notices', function () {
    global $post;
    if (!$post) return;

    $is_linked = false;
    $cpts = ['validators', 'nft', 'builder'];
    
    foreach ($cpts as $cpt) {
        if (get_post_meta($post->ID, '_peepso_page_id', true)) {
            $is_linked = true;
            break;
        }
    }

    if (!$is_linked) return;

    echo '<div class="notice notice-warning is-dismissible"><p>';
    echo '⚠️ This content is linked to a PeepSo Page. Title and some settings are managed by the source page.';
    echo '</p></div>';
});