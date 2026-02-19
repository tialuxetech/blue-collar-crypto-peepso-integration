<?php
if (!defined('ABSPATH')) exit;

/* ======================================================
   NFT CREATOR PROFILE TEMPLATE
   Uses existing renderer system - ONLY FIELD KEYS CHANGED
====================================================== */

// Get NFT CPT shadow from PeepSo page
$nft_id = bcc_get_nft_id($page->id);
$has_nft = $nft_id > 0;

// Permissions
$can_view = $has_nft ? bcc_user_can_view_post($nft_id) : false;
$can_edit = $has_nft ? bcc_user_can_edit_post($nft_id) : false;

// Get networks for select fields
$networks = get_posts([
    'post_type' => 'network',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC'
]);

$network_options = [];
foreach ($networks as $n) {
    $network_options[] = $n->ID . ':' . $n->post_title;
}
$network_options_str = implode(',', $network_options);
?>

<div class="ps-nft-profile bcc-nft-profile">

<?php if (!is_user_logged_in()): ?>

    <p>Please log in to view this NFT creator profile.</p>

<?php elseif (!$has_nft): ?>

    <p>No NFT creator profile found for this page.</p>

<?php elseif (!$can_view): ?>

    <p>This NFT creator profile is private.</p>

<?php else: ?>

    <!-- ======================================================
        BASIC INFORMATION - Just change field keys here
    ====================================================== -->
    <section class="bcc-section bcc-section-basic">
        <h3>Basic Information</h3>
        
        <?php
        bcc_render_rows($nft_id, [
            'artist_name' => ['label' => 'Artist Name', 'type' => 'text'],
            'artist_short_bio' => ['label' => 'Bio', 'type' => 'textarea'],
            'nft_team_size_' => ['label' => 'Team Size', 'type' => 'text'],
            'nft_total_volume' => ['label' => 'Total Volume', 'type' => 'text'],
            'nft_total_sales' => ['label' => 'Total Sales', 'type' => 'text'],
            'holderscollectors' => ['label' => 'Holders', 'type' => 'text']
        ], $can_edit);
        ?>
    </section>

    <!-- ======================================================
        NFT COLLECTIONS - Uses repeater renderer, just field keys changed
    ====================================================== -->
    <section class="bcc-section bcc-section-collections">
        <h3>NFT Collections</h3>
        
        <?php
        bcc_render_repeater_slider([
            'post_id' => $nft_id,
            'repeater_key' => 'nft_collections',
            'can_edit' => $can_edit,
            'empty' => 'No collections created yet',
            'fields' => [
                'collection_name' => [
                    'label' => 'Collection Name',
                    'type' => 'text'
                ],
                'collection_gallery' => [
                    'label' => 'Gallery',
                    'type' => 'gallery'
                ],
                'collection_description' => [
                    'label' => 'Description',
                    'type' => 'textarea'
                ],
                'post_type' => [  // This is your chain field
                    'label' => 'Chain',
                    'type' => 'select',
                    'options' => $network_options_str
                ],
                'collection_mint_url' => [
                    'label' => 'Mint URL',
                    'type' => 'url'
                ],
                'collection_marketplace_url' => [
                    'label' => 'Marketplace URL',
                    'type' => 'url'
                ],
                'collection_x_account' => [
                    'label' => 'X Account',
                    'type' => 'url'
                ]
            ]
        ]);
        ?>
    </section>

    <!-- ======================================================
        SOCIAL LINKS - Uses same field keys from your screenshot
    ====================================================== -->
    <section class="bcc-section bcc-section-social">
        <h3>Links</h3>
        
        <?php
        bcc_render_rows($nft_id, [
            'network_docs' => ['label' => 'Documentation', 'type' => 'url'],
            'network_github' => ['label' => 'GitHub', 'type' => 'url'],
            'network_twitter' => ['label' => 'Twitter / X', 'type' => 'url'],
            'network_discord' => ['label' => 'Discord', 'type' => 'url'],
            'network_telegram' => ['label' => 'Telegram', 'type' => 'url'],
            'network_youtube' => ['label' => 'YouTube', 'type' => 'url'],
            'network_linkedin' => ['label' => 'LinkedIn', 'type' => 'url'],
            'medium' => ['label' => 'Medium', 'type' => 'url'],
            'reddit' => ['label' => 'Reddit', 'type' => 'url']
        ], $can_edit);
        ?>
    </section>

<?php endif; ?>

</div> <!-- end nft-profile -->

<div id="bcc-visibility-popover" class="bcc-vis-popover" style="display: none;">
    <div class="bcc-vis-option" data-value="public">🌍 Public</div>
    <div class="bcc-vis-option" data-value="members">👥 Members</div>
    <div class="bcc-vis-option" data-value="private">🔒 Private</div>
</div>