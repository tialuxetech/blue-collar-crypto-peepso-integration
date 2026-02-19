<?php
if (!defined('ABSPATH')) exit;

/**
 * ======================================================
 * VALIDATOR HELPER FUNCTIONS (SINGLE SOURCE OF TRUTH)
 * ======================================================
 */

/**
 * Get Validator ID from PeepSo Page
 */
function bcc_get_validator_id_from_page($page_id) {
    if (!$page_id) return 0;

    $found = get_posts([
        'post_type'      => 'validators',
        'meta_key'       => '_peepso_page_id',
        'meta_value'     => $page_id,
        'posts_per_page' => 1,
        'fields'         => 'ids'
    ]);

    return !empty($found) ? (int) $found[0] : 0;
}

/**
 * Auto-repair: Create shadow CPT if missing
 */
function bcc_repair_validator_from_page($page_id) {
    if (!$page_id) return 0;
    
    // Check if validator already exists
    $existing = bcc_get_validator_id_from_page($page_id);
    if ($existing) return $existing;

    // Get the page
    $page = get_post($page_id);
    if (!$page) return 0;

    // Create validator
    $validator_id = wp_insert_post([
        'post_type'    => 'validators',
        'post_title'   => $page->post_title,
        'post_status'  => 'publish',
        'post_author'  => (int) $page->post_author,
        'post_content' => '' // Empty content
    ]);

    if (is_wp_error($validator_id) || !$validator_id) {
        return 0;
    }

    // Link both ways
    update_post_meta($validator_id, '_peepso_page_id', $page_id);
    update_post_meta($page_id, '_linked_validators_id', $validator_id);

    // Set default visibility
    update_post_meta($validator_id, '_bcc_visibility', 'public');

    return (int) $validator_id;
}

/**
 * Smart resolver - get or create
 */
function bcc_get_validator_id($page_id) {
    $validator_id = bcc_get_validator_id_from_page($page_id);
    return $validator_id ?: bcc_repair_validator_from_page($page_id);
}

/**
 * Check if current user owns validator
 */
function bcc_user_owns_validator($validator_id) {
    return bcc_user_is_owner($validator_id);
}
