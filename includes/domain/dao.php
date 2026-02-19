<?php
if (!defined('ABSPATH')) exit;

/**
 * ======================================================
 * DAO DATA HANDLER
 * ======================================================
 */

class BCC_DAO_Data {
    
    /**
     * Get DAO ID from PeepSo Page
     */
    public static function get_id_from_page($page_id) {
        if (!$page_id) return 0;

        $found = get_posts([
            'post_type'      => 'dao',
            'meta_key'       => '_peepso_page_id',
            'meta_value'     => $page_id,
            'posts_per_page' => 1,
            'fields'         => 'ids'
        ]);

        return !empty($found) ? (int) $found[0] : 0;
    }

    /**
     * Auto-create DAO CPT if missing
     */
    public static function repair_from_page($page_id) {
        if (!$page_id) return 0;

        $existing = self::get_id_from_page($page_id);
        if ($existing) return $existing;

        $page = get_post($page_id);
        if (!$page) return 0;

        $dao_id = wp_insert_post([
            'post_type'    => 'dao',
            'post_title'   => $page->post_title,
            'post_status'  => 'publish',
            'post_author'  => (int) $page->post_author,
            'post_content' => ''
        ]);

        if (is_wp_error($dao_id) || !$dao_id) {
            return 0;
        }

        update_post_meta($dao_id, '_peepso_page_id', $page_id);
        update_post_meta($page_id, '_linked_dao_id', $dao_id);
        update_post_meta($dao_id, '_bcc_visibility', 'public');

        return (int) $dao_id;
    }

    /**
     * Smart Resolver - Get or create DAO CPT automatically
     */
    public static function get_id($page_id) {
        $dao_id = self::get_id_from_page($page_id);
        return $dao_id ?: self::repair_from_page($page_id);
    }
}

/**
 * Legacy function for backward compatibility
 */
function bcc_get_dao_id($page_id) {
    return BCC_DAO_Data::get_id($page_id);
}