<?php
if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
if (!$user_id) {
    peepso_require_login();
    return;
}

global $wpdb;

/* ============================================================
   FIND CURRENT PEEPSO PAGE
============================================================ */

$peepso_page_id = null;

/* Try from URL slug */
$url_parts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

foreach ($url_parts as $part) {

    if (!$part || $part === 'pages' || $part === 'dashboard') {
        continue;
    }

    $part = strtok($part, '?');

    $found = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT ID 
             FROM {$wpdb->posts} 
             WHERE post_name = %s 
               AND post_type = 'peepso-page'",
            $part
        )
    );

    if ($found) {
        $peepso_page_id = intval($found);
        break;
    }
}

/* Fallback from global post */
if (!$peepso_page_id) {
    global $post;
    if ($post && $post->post_type === 'peepso-page') {
        $peepso_page_id = $post->ID;
    }
}

if ($peepso_page_id && class_exists('PeepSoPage')) {

    $page = new PeepSoPage($peepso_page_id);

} else {

    $page = (object)[
        'id' => 0,
        'name' => 'Dashboard',
        'description' => '',
        'members_count' => 0
    ];
}

$page_segment = 'dashboard';

$plugin_header_file =
    plugin_dir_path(__FILE__) .
    '../../includes/partials/page-header.php';

if (file_exists($plugin_header_file)) {

    include $plugin_header_file;

} else {

    error_log('BCC HEADER NOT FOUND: ' . $plugin_header_file);

    echo '<div class="bcc-dashboard-header">';
    echo '<h1>' . esc_html($page->name) . '</h1>';
    echo '<p>Blue Collar Crypto Dashboard</p>';
    echo '</div>';
}

/* ============================================================
   GET PAGE CATEGORIES
============================================================ */

$category_ids = [];

if ($page->id > 0) {

    $category_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT pm_cat_id 
             FROM {$wpdb->prefix}peepso_page_categories 
             WHERE pm_page_id = %d",
            $page->id
        )
    );
}

/* ============================================================
   TAB DEFINITIONS
============================================================ */

$category_tabs = [
    'validators' => 'Validators',
    'builder'    => 'Builder',
    'nft'        => 'NFT',
    'dao'        => 'DAO',
];

$category_tab_map = [
    254 => 'validators',
    268 => 'builder',
    269 => 'builder',
    253 => 'nft',
    270 => 'dao',
];

$tabs = [];

foreach ($category_ids as $cat_id) {

    if (isset($category_tab_map[$cat_id])) {

        $key = $category_tab_map[$cat_id];

        if (!isset($tabs[$key])) {
            $tabs[$key] = $category_tabs[$key];
        }
    }
}

/* Default tab */
if (empty($tabs)) {
    $tabs = ['overview' => 'Overview'];
}

/* Active tab */
$active_tab = isset($_GET['tab'])
    ? sanitize_key($_GET['tab'])
    : key($tabs);

if (!isset($tabs[$active_tab])) {
    $active_tab = key($tabs);
}

/* ============================================================
   TABS UI
============================================================ */
?>

<div class="bcc-dashboard-tabs ps-focus__menu ps-js-focus__menu">
    <div class="bcc-dashboard-tabs-inner ps-focus__menu-inner ps-js-focus__menu-inner">

        <?php foreach ($tabs as $key => $label) : ?>

            <a href="<?php echo esc_url(add_query_arg('tab', $key)); ?>"
               class="bcc-tab ps-focus__menu-item bcc-tab-<?php echo esc_attr($key); ?> <?php echo $active_tab === $key ? 'active' : ''; ?>">

                <span class="bcc-tab-label"><?php echo esc_html($label); ?></span>

            </a>

        <?php endforeach; ?>

    </div>
</div>

<!-- ============================================================
     TAB CONTENT
============================================================ -->

<div class="bcc-dashboard-content">

<?php


$tab_files = [
    'overview'   => __DIR__ . '/dashboard/overview.php',
    'validators' => __DIR__ . '/dashboard/validator.php',
    'builder'    => __DIR__ . '/dashboard/builder.php',
    'nft'        => __DIR__ . '/dashboard/nft.php',
    'dao'        => __DIR__ . '/dashboard/dao.php',
];

if (isset($tab_files[$active_tab]) && file_exists($tab_files[$active_tab])) {

    /* -----------------------------------------
     * Resolve Domain Object ID - USE EXISTING FUNCTIONS
     * ----------------------------------------- */

    switch ($active_tab) {

        case 'nft':
            $nft_id = function_exists('bcc_get_nft_id') ? bcc_get_nft_id($page->id) : 0;
            break;

        case 'validators':
            $validator_id = function_exists('bcc_get_validator_id') ? bcc_get_validator_id($page->id) : 0;
            break;

        case 'builder':
            $builder_id = function_exists('bcc_get_builder_id') ? bcc_get_builder_id($page->id) : 0;
            break;

        case 'dao':
            $dao_id = function_exists('bcc_get_dao_id') ? bcc_get_dao_id($page->id) : 0;
            break;
    }

    include $tab_files[$active_tab];

} else {
    // ... rest of your code
}

?>
</div>
