<?php
if (!defined('ABSPATH')) exit;

/**
 * ======================================================
 *  ABSTRACT DOMAIN BASE
 * ======================================================
 * Shared logic for all domain models.
 */

abstract class BCC_Domain_Abstract {

    /* ======================================================
       REQUIRED OVERRIDES
    ====================================================== */

    // CPT slug
    abstract public static function post_type(): string;

    // Whitelisted top-level fields
    abstract public static function fields(): array;

    // Repeater => subfield map
    public static function repeater_subfields(string $repeater): array {
        return [];
    }

    /* ======================================================
       VALIDATION
    ====================================================== */

    public static function is_valid_field(string $field): bool {
        return in_array($field, static::fields(), true);
    }

    public static function is_valid_subfield(string $repeater, string $sub): bool {
        return in_array($sub, static::repeater_subfields($repeater), true);
    }

    /* ======================================================
       ID RESOLUTION
    ====================================================== */

    public static function get_id_from_page(int $page_id): int {

        if (!$page_id) return 0;

        $meta_key = '_linked_' . static::post_type() . '_id';

        $linked = (int) get_post_meta($page_id, $meta_key, true);
        if ($linked && get_post($linked)) {
            return $linked;
        }

        $found = get_posts([
            'post_type'      => static::post_type(),
            'meta_key'       => '_peepso_page_id',
            'meta_value'     => $page_id,
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true
        ]);

        if (!empty($found)) {
            return (int) $found[0];
        }

        return 0;
    }

    public static function create_from_page(int $page_id): int {

        if (!$page_id) return 0;

        $page = get_post($page_id);
        if (!$page) return 0;

        $id = wp_insert_post([
            'post_type'   => static::post_type(),
            'post_title'  => $page->post_title,
            'post_status' => 'publish',
            'post_author' => (int) $page->post_author
        ]);

        if (!$id || is_wp_error($id)) {
            return 0;
        }

        update_post_meta($id, '_peepso_page_id', $page_id);
        update_post_meta($page_id, '_linked_' . static::post_type() . '_id', $id);

        update_post_meta($id, '_bcc_visibility', 'public');

        return (int) $id;
    }

    public static function get_or_create_from_page(int $page_id): int {

        $id = static::get_id_from_page($page_id);

        if ($id) return $id;

        return static::create_from_page($page_id);
    }

    /* ======================================================
       PERMISSIONS
    ====================================================== */

    public static function can_edit(int $post_id, int $user_id = 0): bool {
        $user_id = $user_id ?: get_current_user_id();
        return user_can($user_id, 'edit_post', $post_id);
    }

    public static function user_owns(int $post_id): bool {
        return function_exists('bcc_user_is_owner')
            ? bcc_user_is_owner($post_id)
            : static::can_edit($post_id);
    }
}
