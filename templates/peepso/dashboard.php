<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current user
$user_id = get_current_user_id();
if ( ! $user_id ) {
    peepso_require_login();
    return;
}

global $wpdb;

/**
 * --------------------------------------------------
 * FIND THE CORRECT PEEPSO PAGE
 * --------------------------------------------------
 */
$peepso_page_id = null;

// Try to extract from URL slug
$url_parts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
foreach ($url_parts as $part) {
    if ($part && $part != 'pages' && $part != 'dashboard') {
        // Remove query string if present
        $part = strtok($part, '?');
        
        $page_from_slug = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'peepso-page'",
            $part
        ));
        
        if ($page_from_slug) {
            $peepso_page_id = $page_from_slug;
            break;
        }
    }
}

// Fallback: Check if current $post is a PeepSo page
if (!$peepso_page_id) {
    global $post;
    if ($post && $post->post_type === 'peepso-page') {
        $peepso_page_id = $post->ID;
    }
}

// Create PeepSo page object
$page = null; // Rename to $page for header compatibility
if ($peepso_page_id && class_exists('PeepSoPage')) {
    $page = new PeepSoPage($peepso_page_id);
} else {
    // Ultimate fallback
    $page = new stdClass();
    $page->id = $peepso_page_id ?: 0;
    $page->name = 'Dashboard';
    $page->description = '';
    $page->members_count = 0;
}

/**
 * --------------------------------------------------
 * GET CATEGORIES FOR THE PEEPSO PAGE
 * --------------------------------------------------
 */
$category_ids = [];
if ($page->id > 0) {
    $category_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT pm_cat_id FROM {$wpdb->prefix}peepso_page_categories WHERE pm_page_id = %d",
        $page->id
    ));
}

/**
 * --------------------------------------------------
 * BUILD TABS BASED ON CATEGORIES 
 * --------------------------------------------------
 */
// Define all possible category tabs
$category_tabs = [
    'validators' => 'Validators',
    'nft'        => 'NFT',
    'builder'    => 'Builder',
    
];

// Category ID to Tab mapping
$category_tab_map = [
    254 => 'validators',  // Validator category
    268 => 'builder',     // Builder category  
    269 => 'builder',     // Another Builder category
    253 => 'nft',         // NFT category
];

// Build tabs based on categories
$tabs = [];

if (!empty($category_ids)) {
    foreach ($category_ids as $cat_id) {
        if (isset($category_tab_map[$cat_id])) {
            $tab_key = $category_tab_map[$cat_id];
            // Only add if not already added (prevents duplicates)
            if (!isset($tabs[$tab_key])) {
                $tabs[$tab_key] = $category_tabs[$tab_key];
            }
        }
    }
}

// If no category tabs were found, show Overview tab
if (empty($tabs)) {
    $tabs = ['overview' => 'Overview'];
}

// Get active tab - default to first tab
$active_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : key($tabs);
if (!isset($tabs[$active_tab])) {
    $active_tab = key($tabs);
}

/**
 * --------------------------------------------------
 * DISPLAY HEADER - FROM YOUR PLUGIN DIRECTORY
 * --------------------------------------------------
 */
// Path to your plugin's header file
$plugin_header_file = WP_PLUGIN_DIR . '/blue-collar-crypto/templates/peepso/page-header.php';

if (file_exists($plugin_header_file)) {
    // Check if we have a proper PeepSoPage object
    if ($page instanceof PeepSoPage) {
        // Set the page_segment variable that the header expects
        $page_segment = 'dashboard'; // This should match your current segment
        
        // Include the header file from your plugin
        include $plugin_header_file;
    } else {
        // Fallback: Show simplified header
        ?>
        <div class="bcc-dashboard-header">
            <h1><?php echo esc_html($page->name); ?></h1>
            <p>Blue Collar Crypto Dashboard</p>
        </div>
        <?php
    }
} else {
    // Header file doesn't exist, show fallback
    ?>
    <div class="bcc-dashboard-header">
        <h1><?php echo esc_html($page->name); ?></h1>
        <p>Blue Collar Crypto Dashboard</p>
    </div>
    <?php
}
?>
<!-- =========================
     DASHBOARD TABS
========================== -->

<div class="bcc-dashboard-tabs ps-focus__menu ps-js-focus__menu">

    <div class="bcc-dashboard-tabs-inner ps-focus__menu-inner ps-js-focus__menu-inner ">

        <?php if (empty($tabs)): ?>

            <div class="bcc-no-tabs">
                No tabs available. This page has no categories assigned.
            </div>

        <?php else: ?>

            <?php foreach ($tabs as $key => $label) : ?>

              <a href="<?php echo esc_url(add_query_arg('tab', $key)); ?>"
   class="bcc-tab ps-focus__menu-item bcc-tab-<?php echo esc_attr($key); ?> <?php echo $active_tab === $key ? 'active' : ''; ?>">

    <span class="bcc-tab-icon"></span>
    <span class="bcc-tab-label"><?php echo esc_html($label); ?></span>

</a>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</div>

<!-- =========================
     TAB CONTENT
========================== -->

<div class="bcc-dashboard-content">

<?php
$tab_files = [
    'overview'   => __DIR__ . '/dashboard/overview.php',
    'validators' => __DIR__ . '/dashboard/validators.php',
    'nft'        => __DIR__ . '/dashboard/nft.php',
    'builder'    => __DIR__ . '/dashboard/builder.php',
];

// Load tab file if exists
if (isset($tab_files[$active_tab]) && file_exists($tab_files[$active_tab])) {

    include $tab_files[$active_tab];

} else {

    // ---------- FALLBACK UI ----------
    if ($active_tab === 'overview') : ?>

        <div class="bcc-overview-message">
            <h3>Create Your First Project</h3>
            <p>Choose a category above to begin creating and managing your first project.</p>

            <div class="bcc-tab-grid">

                <?php if (isset($tabs['validators'])): ?>
                    <div class="bcc-tab-card">
                        <h4>Validators</h4>
                        <p>Monitor and manage your validator nodes.</p>
                        <a href="?tab=validators">Go to Validators</a>
                    </div>
                <?php endif; ?>

                <?php if (isset($tabs['builder'])): ?>
                    <div class="bcc-tab-card">
                        <h4>Builder</h4>
                        <p>Access developer tools and deployment resources.</p>
                        <a href="?tab=builder">Go to Builder</a>
                    </div>
                <?php endif; ?>

                <?php if (isset($tabs['nft'])): ?>
                    <div class="bcc-tab-card">
                        <h4>NFT</h4>
                        <p>Create and manage your NFT collections.</p>
                        <a href="?tab=nft">Go to NFT</a>
                    </div>
                <?php endif; ?>

            </div>

        </div>

    <?php else : ?>

        <div class="bcc-coming-soon">
            <p>
                Content for the <strong><?php echo esc_html($tabs[$active_tab]); ?></strong> tab is coming soon.
                <br>
                Create file:
                <code>dashboard/<?php echo esc_html($active_tab); ?>.php</code>
            </p>
        </div>

    <?php endif;

}
?>

</div>
</div>