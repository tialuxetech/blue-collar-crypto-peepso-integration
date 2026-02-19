<?php
if (!defined('ABSPATH')) exit;


/* ----------------------------------------------------
   CORE REPAIR ENGINE
---------------------------------------------------- */

if (!function_exists('bcc_repair_engine')) {

function bcc_repair_engine($page_id = null) {

    global $wpdb;

    if (!function_exists('bcc_get_category_map')) {
        return ['error' => 'bcc_get_category_map() missing'];
    }

    $map = bcc_get_category_map();
    $log = [];

    /* ----------------------------
       Fetch pages
    ---------------------------- */

    if ($page_id) {

        $page = get_post($page_id);

        if (!$page || $page->post_type !== 'peepso-page') {
            return ['error' => 'Invalid PeepSo Page'];
        }

        $pages = [$page];

    } else {

        $pages = get_posts([
            'post_type'      => 'peepso-page',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);
    }

    foreach ($pages as $page) {

        $log[] = "🔍 Page {$page->ID}: {$page->post_title}";

        $cat_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT pm_cat_id 
                 FROM {$wpdb->prefix}peepso_page_categories 
                 WHERE pm_page_id = %d",
                $page->ID
            )
        );

        if (!$cat_ids) {
            $log[] = "  ⚠ No categories";
            continue;
        }

        $linked = [];

        foreach ($cat_ids as $cat_id) {

            if (!isset($map[$cat_id]['cpt'])) {
                continue;
            }

            $cpt = $map[$cat_id]['cpt'];

            /* ----------------------------
               Find existing shadow CPT
            ---------------------------- */

            $existing = get_posts([
                'post_type'      => $cpt,
                'meta_key'       => '_peepso_page_id',
                'meta_value'     => $page->ID,
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ]);

            if ($existing) {

                $cpt_id = (int) $existing[0];

                // Repair title
                if (get_the_title($cpt_id) !== $page->post_title) {

                    wp_update_post([
                        'ID'         => $cpt_id,
                        'post_title'=> $page->post_title,
                        'post_name' => sanitize_title($page->post_title)
                    ]);

                    $log[] = "  🔧 Fixed title for {$cpt} ({$cpt_id})";
                }

            } else {

                /* ----------------------------
                   Create missing CPT
                ---------------------------- */

                $cpt_id = wp_insert_post([
                    'post_type'   => $cpt,
                    'post_title'  => $page->post_title,
                    'post_status' => 'publish',
                    'post_author' => $page->post_author,
                ]);

                if (!$cpt_id || is_wp_error($cpt_id)) {
                    $log[] = "  ❌ Failed creating {$cpt}";
                    continue;
                }

                update_post_meta($cpt_id, '_peepso_page_id', $page->ID);
                update_post_meta($cpt_id, '_peepso_cat_id', $cat_id);
                update_post_meta($cpt_id, '_bcc_visibility', 'public');

                $log[] = "  ✅ Created {$cpt} ({$cpt_id})";
            }

            update_post_meta($page->ID, '_linked_' . $cpt . '_id', $cpt_id);
            $linked[$cpt] = $cpt_id;
        }

        update_post_meta($page->ID, '_linked_cpts', $linked);
        $log[] = "  ✔ Page repaired";
        $log[] = "";
    }

    return $log;
}

}



/* ----------------------------------------------------
   FRONTEND SAFE REGEN
---------------------------------------------------- */

if (!function_exists('bcc_regenerate_validator_from_page')) {

function bcc_regenerate_validator_from_page($page_id) {

    if (!$page_id) return false;

    bcc_repair_engine($page_id);

    $validator = get_posts([
        'post_type'      => 'validators',
        'meta_key'       => '_peepso_page_id',
        'meta_value'     => $page_id,
        'posts_per_page' => 1,
        'fields'         => 'ids'
    ]);

    return $validator ? (int) $validator[0] : false;
}

}



/* ----------------------------------------------------
   ADMIN UI
---------------------------------------------------- */

function bcc_render_repair_page() {

    if (!current_user_can('manage_options')) return;

    echo '<div class="wrap"><h1>BCC Repair Tool</h1>';

    if (isset($_POST['bcc_run_repair'])) {

        $page_id = !empty($_POST['bcc_page_id'])
            ? absint($_POST['bcc_page_id'])
            : null;

        echo '<pre style="background:#111;color:#0f0;padding:15px;">';
        foreach (bcc_repair_engine($page_id) as $line) {
            echo esc_html($line) . "\n";
        }
        echo "</pre>";

    } else {

        echo '<form method="post">
                <p>Optional: Repair single PeepSo Page ID</p>
                <input type="number" name="bcc_page_id" />
                <p>
                    <button class="button button-primary" name="bcc_run_repair">
                        Run Repair
                    </button>
                </p>
              </form>';
    }

    echo '</div>';
}

add_action('admin_menu', function () {

    add_submenu_page(
        'tools.php',
        'BCC Repair Tool',
        'BCC Repair Tool',
        'manage_options',
        'bcc-repair-tool',
        'bcc_render_repair_page'
    );
});
