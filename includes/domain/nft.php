<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * Get NFT ID from PeepSo page
 */
function bcc_get_nft_id($page_id) {
    if (!$page_id) return 0;
    
    // Look for existing linked NFT
    $linked_id = get_post_meta($page_id, '_linked_nft_id', true);
    if ($linked_id && get_post($linked_id)) {
        return (int) $linked_id;
    }
    
    // Try to find by meta query
    $found = get_posts([
        'post_type' => 'nft',
        'meta_key' => '_peepso_page_id',
        'meta_value' => $page_id,
        'posts_per_page' => 1,
        'fields' => 'ids'
    ]);
    
    if (!empty($found)) {
        return (int) $found[0];
    }
    
    // Auto-create if needed
    $page = get_post($page_id);
    if (!$page) return 0;
    
    $nft_id = wp_insert_post([
        'post_type' => 'nft',
        'post_title' => $page->post_title,
        'post_status' => 'publish',
        'post_author' => (int) $page->post_author,
    ]);
    
    if (is_wp_error($nft_id) || !$nft_id) return 0;
    
    update_post_meta($nft_id, '_peepso_page_id', $page_id);
    update_post_meta($page_id, '_linked_nft_id', $nft_id);
    
    return (int) $nft_id;
}