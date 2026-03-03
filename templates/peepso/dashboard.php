<?php
if (!defined('ABSPATH')) {
    exit;
}
if (!isset($page) || !is_object($page) || empty($page->id)) {
    return;
}

$user_id = get_current_user_id();
if (!$user_id) {
    peepso_require_login();
    return;
}

$plugin_header_file = plugin_dir_path(__FILE__) . '../../includes/partials/page-header.php';

if (file_exists($plugin_header_file)) {
    include $plugin_header_file;
}

global $wpdb;

$category_ids = $wpdb->get_col(
    $wpdb->prepare(
        "SELECT pm_cat_id 
         FROM {$wpdb->prefix}peepso_page_categories 
         WHERE pm_page_id = %d",
        $page->id
    )
);

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

if (empty($tabs)) {
    $tabs = ['overview' => 'Overview'];
}

$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : key($tabs);

if (!isset($tabs[$active_tab])) {
    $active_tab = key($tabs);
}
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
}

?>
</div>