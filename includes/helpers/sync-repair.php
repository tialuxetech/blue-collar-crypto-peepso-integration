<?php
if (!defined('ABSPATH')) exit;

/**
 * =====================================================
 * BCC Sync Repair System
 * =====================================================
 * - Admin repair UI
 * - Pure repair engine
 * - Frontend-safe regenerate function
 */

function bcc_repair_engine($page_id = null) {

    global $wpdb;

    if (!function_exists('bcc_get_category_map')) {
        return ['error' => 'Mapping function bcc_get_category_map() not found'];
    }

    $map = bcc_get_category_map();
    $log = [];

    // Determine pages
    if ($page_id) {

        $page = get_post($page_id);

        if (!$page || $page->post_type !== 'peepso-page') {
            return ['error' => 'Invalid PeepSo Page ID'];
        }

        $pages = [$page];

    } else {

        $pages = get_posts([
            'post_type'      => 'peepso-page',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);
    }

    if (!$pages) {
        return ['error' => 'No PeepSo pages found'];
    }

    foreach ($pages as $page) {

        $log[] = "Checking page {$page->ID} ({$page->post_title})";

        $cat_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT pm_cat_id 
                 FROM {$wpdb->prefix}peepso_page_categories 
                 WHERE pm_page_id = %d",
                $page->ID
            )
        );

        if (!$cat_ids) {
            $log[] = " - No categories";
            continue;
        }

        foreach ($cat_ids as $cat_id) {

            if (!isset($map[$cat_id]['cpt'])) {
                $log[] = " - Category {$cat_id} not mapped";
                continue;
            }

            $cpt = $map[$cat_id]['cpt'];

            // Check existing CPT shadow
            $existing = get_posts([
                'post_type'      => $cpt,
                'meta_key'       => '_peepso_page_id',
                'meta_value'     => $page->ID,
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ]);

            if ($existing) {
                $log[] = " - {$cpt} already linked";
                continue;
            }

            // Create missing CPT
            $new_id = wp_insert_post([
                'post_type'   => $cpt,
                'post_title'  => $page->post_title,
                'post_status' => 'publish',
                'post_author' => $page->post_author,
            ]);

            if (!$new_id || is_wp_error($new_id)) {
                $log[] = " - FAILED creating {$cpt}";
                continue;
            }

            update_post_meta($new_id, '_peepso_page_id', $page->ID);
            update_post_meta($new_id, '_peepso_cat_id', $cat_id);
            update_post_meta($page->ID, '_linked_' . $cpt . '_id', $new_id);

            $log[] = " - CREATED {$cpt} ({$new_id})";
        }

        $log[] = "";
    }

    return $log;
}

/* =====================================================
   ADMIN OUTPUT WRAPPER
===================================================== */

function bcc_run_repair($page_id = null) {

    $results = bcc_repair_engine($page_id);

    if (isset($results['error'])) {
        echo esc_html($results['error']);
        return;
    }

    foreach ($results as $line) {
        echo esc_html($line) . "\n";
    }

    echo "Repair complete.\n";
}

/* =====================================================
   FRONTEND SAFE HELPER
===================================================== */

function bcc_regenerate_validator_from_page($page_id) {

    if (!$page_id) return false;

    bcc_repair_engine($page_id);

    // Fetch validator CPT after repair
    $validator = get_posts([
        'post_type'      => 'validators',
        'meta_key'       => '_peepso_page_id',
        'meta_value'     => $page_id,
        'posts_per_page' => 1,
        'fields'         => 'ids'
    ]);

    return $validator ? $validator[0] : false;
}

/* =====================================================
   ADMIN PAGE UI
===================================================== */

function bcc_render_repair_page() {

    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>BCC Repair Tool</h1>';

    if (isset($_POST['bcc_run_repair'])) {

        $page_id = isset($_POST['bcc_page_id']) && $_POST['bcc_page_id'] !== ''
            ? absint($_POST['bcc_page_id'])
            : null;

        echo '<pre style="background:#111;color:#0f0;padding:15px;">';
        bcc_run_repair($page_id);
        echo '</pre>';

    } else {

        echo '<form method="post">';

        echo '<p><strong>Optional:</strong> Enter a PeepSo Page ID to repair a single page.</p>';

        echo '<input type="number" name="bcc_page_id" placeholder="PeepSo Page ID" style="width:200px;" />';

        echo '<p style="margin-top:15px;">';
        echo '<button class="button button-primary" name="bcc_run_repair">';
        echo 'Run Repair';
        echo '</button>';
        echo '</p>';

        echo '<p style="color:#666;">Leave empty to repair all PeepSo pages.</p>';

        echo '</form>';
    }

    echo '</div>';
}

add_action('wp_ajax_bcc_regenerate_validator', 'bcc_ajax_regenerate_validator');

function bcc_ajax_regenerate_validator() {

    check_ajax_referer('bcc_nonce', 'nonce');

    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied');
    }

    $page_id = intval($_POST['page_id']);

    if (!$page_id) {
        wp_send_json_error('Missing page id');
    }

    $validator_id = bcc_regenerate_validator_from_page($page_id);

    if ($validator_id) {
        wp_send_json_success([
            'validator_id' => $validator_id
        ]);
    }

    wp_send_json_error('Repair failed');
}


/* =====================================================
   ADMIN MENU REGISTRATION
===================================================== */

add_action('admin_menu', function() {

    add_submenu_page(
        'tools.php',
        'BCC Repair Tool',
        'BCC Repair Tool',
        'manage_options',
        'bcc-repair-tool',
        'bcc_render_repair_page'
    );

    });