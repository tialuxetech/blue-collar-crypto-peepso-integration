<?php
if (!defined('ABSPATH')) exit;

/**
 * =====================================================
 * PeepSo Page → Multi-CPT Shadow Sync (Production)
 * =====================================================
 * - Listens to save_post_peepso-page
 * - Queues page once per request
 * - Runs sync at shutdown
 * - Creates shadow CPTs per category
 *
 * Requires:
 * - bcc_get_category_map()
 */

/* ---------------------------------------------
   Queue peepso pages created/updated in request
--------------------------------------------- */

$GLOBALS['bcc_pending_peepso_pages'] = [];

add_action('save_post_peepso-page', function ($post_id, $post, $update) {

    if (!$post) return;

    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;

    // Prevent double-queue in same request
    static $queued = [];

    if (isset($queued[$post_id])) return;

    $queued[$post_id] = true;

    $GLOBALS['bcc_pending_peepso_pages'][$post_id] = [
        'title'  => $post->post_title,
        'author' => (int) $post->post_author,
    ];

}, 10, 3);


/* ---------------------------------------------
   Locate PeepSo page-category relation table
--------------------------------------------- */

function bcc_find_peepso_relation_table() {

    global $wpdb;

    $table = $wpdb->prefix . 'peepso_page_categories';

    $exists = $wpdb->get_var(
        $wpdb->prepare("SHOW TABLES LIKE %s", $table)
    );

    if (!$exists) return [null, null, null];

    $cols = $wpdb->get_results("DESCRIBE {$table}");
    if (!$cols) return [$table, null, null];

    $page_col = null;
    $cat_col  = null;

    foreach ($cols as $c) {

        if (!$page_col && stripos($c->Field, 'page') !== false) {
            $page_col = $c->Field;
        }

        if (!$cat_col && stripos($c->Field, 'cat') !== false) {
            $cat_col = $c->Field;
        }

    }

    return [$table, $page_col, $cat_col];
}


/* ---------------------------------------------
   Perform Sync After Request Finishes
--------------------------------------------- */

add_action('shutdown', function () {

    if (empty($GLOBALS['bcc_pending_peepso_pages'])) return;

    if (!function_exists('bcc_get_category_map')) return;

    global $wpdb;

    [$table, $page_col, $cat_col] = bcc_find_peepso_relation_table();

    if (!$table || !$page_col || !$cat_col) return;

    $map = bcc_get_category_map();

    foreach ($GLOBALS['bcc_pending_peepso_pages'] as $page_id => $data) {

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT {$cat_col} AS cat_id
                 FROM {$table}
                 WHERE {$page_col} = %d",
                $page_id
            )
        );

        if (empty($rows)) continue;

        /* Determine CPT targets */

        $targets = [];

        foreach ($rows as $row) {

            $cat_id = (int) $row->cat_id;

            if (!isset($map[$cat_id]['cpt'])) continue;

            $post_type = $map[$cat_id]['cpt'];

            // Deduplicate CPTs
            $targets[$post_type] = $cat_id;
        }

        if (empty($targets)) continue;

        /* Create shadow CPTs */

        $linked = [];

        foreach ($targets as $post_type => $cat_id) {

            if (!post_type_exists($post_type)) continue;

            $existing = get_post_meta($page_id, '_linked_' . $post_type . '_id', true);

            if ($existing && get_post($existing)) {
                $linked[$post_type] = (int) $existing;
                continue;
            }

            $cpt_id = wp_insert_post([
                'post_type'   => $post_type,
                'post_title'  => $data['title'],
                'post_status' => 'publish',
                'post_author' => (int) $data['author'],
            ]);

            if (!$cpt_id || is_wp_error($cpt_id)) continue;

            update_post_meta($cpt_id, '_peepso_page_id', (int) $page_id);
            update_post_meta($cpt_id, '_peepso_cat_id', (int) $cat_id);

            update_post_meta($page_id, '_linked_' . $post_type . '_id', (int) $cpt_id);

            $linked[$post_type] = (int) $cpt_id;
        }

        update_post_meta($page_id, '_linked_cpts', $linked);

    }

});


/* ---------------------------------------------
   Delete shadow CPTs when PeepSo page deleted
--------------------------------------------- */

add_action('before_delete_post', function ($post_id) {

    $post = get_post($post_id);

    if (!$post || $post->post_type !== 'peepso-page') return;

    $linked = get_post_meta($post_id, '_linked_cpts', true);

    if (!is_array($linked)) return;

    foreach ($linked as $post_type => $cpt_id) {

        if ($cpt_id && get_post($cpt_id)) {
            wp_trash_post($cpt_id);
        }

        delete_post_meta($post_id, '_linked_' . $post_type . '_id');
    }

    delete_post_meta($post_id, '_linked_cpts');

}, 10);
