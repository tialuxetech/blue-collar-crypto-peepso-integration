<?php
if (!defined('ABSPATH')) exit;

/**
 * ======================================================
 * BUILDER DATA HANDLER
 * ======================================================
 */

class BCC_Builder_Data {
    
    /**
     * Get Builder ID from PeepSo Page
     */
    public static function get_id_from_page($page_id) {
        if (!$page_id) return 0;

        $found = get_posts([
            'post_type'      => 'builder',
            'meta_key'       => '_peepso_page_id',
            'meta_value'     => $page_id,
            'posts_per_page' => 1,
            'fields'         => 'ids'
        ]);

        return !empty($found) ? (int) $found[0] : 0;
    }

    /**
     * Auto-create Builder CPT if missing
     */
    public static function repair_from_page($page_id) {
        if (!$page_id) return 0;

        $existing = self::get_id_from_page($page_id);
        if ($existing) return $existing;

        $page = get_post($page_id);
        if (!$page) return 0;

        $builder_id = wp_insert_post([
            'post_type'    => 'builder',
            'post_title'   => $page->post_title,
            'post_status'  => 'publish',
            'post_author'  => (int) $page->post_author,
            'post_content' => ''
        ]);

        if (is_wp_error($builder_id) || !$builder_id) {
            return 0;
        }

        update_post_meta($builder_id, '_peepso_page_id', $page_id);
        update_post_meta($page_id, '_linked_builder_id', $builder_id);
        update_post_meta($builder_id, '_bcc_visibility', 'public');

        return (int) $builder_id;
    }

    /**
     * Smart Resolver - Get or create Builder CPT automatically
     */
    public static function get_id($page_id) {
        $builder_id = self::get_id_from_page($page_id);
        return $builder_id ?: self::repair_from_page($page_id);
    }
}

/**
 * Legacy function for backward compatibility
 */
function bcc_get_builder_id($page_id) {
    return BCC_Builder_Data::get_id($page_id);
}