<?php
if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/page-category-map.php';

/**
 * =====================================================
 *  PeepSo Page → Multi-CPT Shadow Sync (Production)
 * =====================================================
 * - Captures new peepso-page IDs at insert time
 * - Runs sync at shutdown (after PeepSo writes relations)
 * - Auto-detects PeepSo relation table & columns
 * - Creates multiple CPT shadows if multiple categories selected
 */

/* ---------------------------------------------
   Queue peepso pages created in this request
--------------------------------------------- */

$GLOBALS['bcc_pending_peepso_pages'] = [];

add_action('wp_insert_post', function ($post_id, $post, $update) {

    if (!$post) return;

    if ($post->post_type !== 'peepso-page') return;

    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;

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

    $like = '%' . $wpdb->esc_like('peepso_page_categories') . '%';

    $tables = $wpdb->get_col(
        $wpdb->prepare("SHOW TABLES LIKE %s", $like)
    );

    if (empty($tables)) return [null, null, null];

    $table = $tables[0];

    $cols = $wpdb->get_results("DESCRIBE {$table}");
    if (!$cols) return [$table, null, null];

    $col_names = array_map(fn($c) => $c->Field, $cols);

    $page_col = null;
    $cat_col  = null;

    foreach ($col_names as $c) {
        if (!$page_col && stripos($c,'page') !== false && stripos($c,'id') !== false) {
            $page_col = $c;
        }
        if (!$cat_col && stripos($c,'cat') !== false && stripos($c,'id') !== false) {
            $cat_col = $c;
        }
    }

    return [$table, $page_col, $cat_col];
}


/* ---------------------------------------------
   Perform Sync After Request Finishes
--------------------------------------------- */

add_action('shutdown', function () {

    if (empty($GLOBALS['bcc_pending_peepso_pages'])) return;

    global $wpdb;

    [$table, $page_col, $cat_col] = bcc_find_peepso_relation_table();

    if (!$table || !$page_col || !$cat_col) {
        error_log("BCC SYNC: Could not locate PeepSo category relation table.");
        return;
    }

    $id_map   = bcc_category_id_to_slug();   // PeepSo Cat ID → slug
    $slug_map = bcc_slug_to_cpt();            // slug → CPT key

    foreach ($GLOBALS['bcc_pending_peepso_pages'] as $page_id => $data) {

        // Pull category rows
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT {$cat_col} AS cat_id FROM {$table} WHERE {$page_col} = %d",
                $page_id
            )
        );

        if (empty($rows)) continue;

        /* ---------------------------------------------
           Determine all CPT targets
        --------------------------------------------- */

        $targets = [];

        foreach ($rows as $row) {

            $cat_id = (int) $row->cat_id;

            if (!isset($id_map[$cat_id])) continue;

            $slug = $id_map[$cat_id];

            if (!isset($slug_map[$slug])) continue;

            $targets[$slug_map[$slug]] = $cat_id;
        }

        if (empty($targets)) continue;

        /* ---------------------------------------------
           Create CPT shadows
        --------------------------------------------- */

        $linked = [];

        foreach ($targets as $post_type => $cat_id) {

            if (!post_type_exists($post_type)) {
                error_log("BCC SYNC: CPT {$post_type} not registered.");
                continue;
            }

            $existing = get_post_meta($page_id, '_linked_' . $post_type . '_id', true);
            if ($existing && get_post($existing)) continue;

            $cpt_id = wp_insert_post([
                'post_type'   => $post_type,
                'post_title'  => $data['title'],
                'post_status' => 'publish',
                'post_author' => (int) $data['author'],
            ]);

            if (!$cpt_id || is_wp_error($cpt_id)) continue;

            update_post_meta($cpt_id, '_peepso_page_id', $page_id);
            update_post_meta($page_id, '_linked_' . $post_type . '_id', $cpt_id);

            $linked[$post_type] = $cpt_id;
        }

        update_post_meta($page_id, '_linked_cpts', $linked);

    }

});
/**
 * =====================================================
 * Delete Shadow CPTs when PeepSo Page is Deleted
 * =====================================================
 */

add_action('before_delete_post', function ($post_id) {

    $post = get_post($post_id);
    if (!$post) return;

    if ($post->post_type !== 'peepso-page') return;

    // Grab all linked CPTs
    $linked = get_post_meta($post_id, '_linked_cpts', true);

    if (!is_array($linked)) return;

    foreach ($linked as $post_type => $cpt_id) {

        if ($cpt_id && get_post($cpt_id)) {

            // Force delete shadow CPT
            wp_delete_post($cpt_id, true);
        }
    }

    // Cleanup meta
    delete_post_meta($post_id, '_linked_cpts');

});

