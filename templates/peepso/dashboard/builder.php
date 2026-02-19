
<?php
if (!defined('ABSPATH')) exit;

/* ======================================================
   BUILDER PROFILE TEMPLATE
   Matches NFT template styling
====================================================== */

// Get Builder ID from PeepSo page
$builder_id = function_exists('bcc_get_builder_id') ? bcc_get_builder_id($page->id) : 0;
$has_builder = $builder_id > 0;

// Permissions
$can_view = $has_builder ? bcc_user_can_view_post($builder_id) : false;
$can_edit = $has_builder ? bcc_user_can_edit_post($builder_id) : false;
?>

<div class="ps-builder-profile bcc-builder-profile">

<?php if (!is_user_logged_in()): ?>

    <p>Please log in to view this builder profile.</p>

<?php elseif (!$has_builder): ?>

    <p>No builder profile found for this page.</p>

<?php elseif (!$can_view): ?>

    <p>This builder profile is private.</p>

<?php else: ?>

    <!-- ======================================================
        BASIC INFORMATION
    ====================================================== -->
    <section class="bcc-section bcc-section-basic">
        <h3>Basic Information</h3>
        
        <?php
        bcc_render_rows($builder_id, [
            'builder_display_name' => ['label' => 'Builder Display Name', 'type' => 'text'],
            'builder_type' => ['label' => 'Builder Type', 'type' => 'select'],
            'builder_short_description' => ['label' => 'Short Description', 'type' => 'textarea'],
        ], $can_edit);
        ?>
    </section>

    <!-- ======================================================
        PRIMARY NETWORK
    ====================================================== -->
    <section class="bcc-section bcc-section-network">
        <h3>Primary Network</h3>
        
        <?php
        // Get primary network relationship
        $network_ids = get_field('network', $builder_id);
        
        if ($network_ids && !empty($network_ids)): ?>
            <div class="bcc-info-grid">
                <?php foreach ($network_ids as $network_id): 
                    $network_post = get_post($network_id);
                    if (!$network_post) continue;
                ?>
                    <div class="bcc-info-row">
                        <div class="bcc-info-label">Network</div>
                        <div class="bcc-info-value-wrapper">
                            <div class="bcc-info-value">
                                <?php echo esc_html($network_post->post_title); ?>
                            </div>
                            <?php if ($can_edit): ?>
                                <button type="button" class="bcc-edit-btn" data-field="network" data-post="<?php echo $builder_id; ?>">Edit</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bcc-info-grid">
                <div class="bcc-info-row">
                    <div class="bcc-info-label">Network</div>
                    <div class="bcc-info-value-wrapper">
                        <div class="bcc-info-value">—</div>
                        <?php if ($can_edit): ?>
                            <button type="button" class="bcc-edit-btn" data-field="network" data-post="<?php echo $builder_id; ?>">Add</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- ======================================================
        ORGANIZATION ASSOCIATION
    ====================================================== -->
    <section class="bcc-section bcc-section-organization">
        <h3>Organization Association</h3>
        
        <?php
        // Get associated organization relationship
        $org_ids = get_field('associated_organization', $builder_id);
        
        if ($org_ids && !empty($org_ids)): ?>
            <div class="bcc-info-grid">
                <?php foreach ($org_ids as $org_id): 
                    $org_post = get_post($org_id);
                    if (!$org_post) continue;
                ?>
                    <div class="bcc-info-row">
                        <div class="bcc-info-label">Organization</div>
                        <div class="bcc-info-value-wrapper">
                            <div class="bcc-info-value">
                                <?php echo esc_html($org_post->post_title); ?>
                            </div>
                            <?php if ($can_edit): ?>
                                <button type="button" class="bcc-edit-btn" data-field="associated_organization" data-post="<?php echo $builder_id; ?>">Edit</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bcc-info-grid">
                <div class="bcc-info-row">
                    <div class="bcc-info-label">Organization</div>
                    <div class="bcc-info-value-wrapper">
                        <div class="bcc-info-value">—</div>
                        <?php if ($can_edit): ?>
                            <button type="button" class="bcc-edit-btn" data-field="associated_organization" data-post="<?php echo $builder_id; ?>">Add</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- ======================================================
        SOCIAL LINKS
    ====================================================== -->
    <section class="bcc-section bcc-section-social">
        <h3>Links</h3>
        
        <?php
        bcc_render_rows($builder_id, [
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

</div> <!-- end builder-profile -->

<div id="bcc-visibility-popover" class="bcc-vis-popover" style="display: none;">
    <div class="bcc-vis-option" data-value="public">🌍 Public</div>
    <div class="bcc-vis-option" data-value="members">👥 Members</div>
    <div class="bcc-vis-option" data-value="private">🔒 Private</div>
</div>

<style>
/* Builder profile styles - matching NFT template */
.ps-builder-profile .bcc-section {
    margin-bottom: 24px;
}
.bcc-vis-option {
    padding: 8px 12px;
    cursor: pointer;
    font-size: 14px;
    color: var(--COLOR--TEXT, #33455a);
    transition: background 0.2s ease;
    align-items: left;
    display: flex;
}

.ps-builder-profile .bcc-section h3 {
    font-size: 16px;
    font-weight: 600;
    color: var(--COLOR--HEADING, #333);
    margin: 0 0 16px 0;
    padding: 0;
    border: none;
    letter-spacing: -0.2px;
}

.ps-builder-profile .bcc-info-grid {
    background: transparent;
    padding: 0;
}

.ps-builder-profile .bcc-info-row {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    background: var(--COLOR--APP, #fff);
    border-bottom: 1px solid var(--DIVIDER--LIGHT, #edf2f7);
    margin: 0;
    min-height: 50px;
}

.ps-builder-profile .bcc-info-row:first-child {
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

.ps-builder-profile .bcc-info-row:last-child {
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
    border-bottom: none;
}

.ps-builder-profile .bcc-info-label {
    width: 140px;
    font-size: 13px;
    font-weight: 400;
    color: var(--COLOR--TEXT--LIGHT, #6b7b8b);
    line-height: 24px;
    text-transform: none;
}

.ps-builder-profile .bcc-info-value-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.ps-builder-profile .bcc-info-value {
    font-size: 14px;
    color: var(--COLOR--TEXT, #33455a);
    line-height: 24px;
}

.ps-builder-profile .bcc-edit-btn {
    background: transparent !important;
    color: var(--COLOR--PRIMARY, #0a7d8c) !important;
    border: none !important;
    padding: 6px 12px !important;
    margin: 0 !important;
    font-size: 13px !important;
    font-weight: 400 !important;
    border-radius: 20px !important;
    cursor: pointer;
    transition: background 0.2s ease;
    box-shadow: none !important;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.ps-builder-profile .bcc-edit-btn:hover {
    background: var(--COLOR--PRIMARY--ULTRALIGHT, rgba(10,125,140,0.08)) !important;
    color: var(--COLOR--PRIMARY--DARK, #08616b) !important;
    text-decoration: none;
}

/* Dark mode support */
body.ps-theme-dark .ps-builder-profile .bcc-info-row {
    background: var(--COLOR--APP, #2a2a2a);
    border-bottom-color: var(--DIVIDER--LIGHT, rgba(255,255,255,0.1));
}

body.ps-theme-dark .ps-builder-profile .bcc-info-label {
    color: var(--COLOR--TEXT--LIGHT, #aaa);
}

body.ps-theme-dark .ps-builder-profile .bcc-info-value {
    color: var(--COLOR--TEXT, #eee);
}

body.ps-theme-dark .ps-builder-profile .bcc-edit-btn {
    color: var(--COLOR--PRIMARY, #0a7d8c) !important;
}

body.ps-theme-dark .ps-builder-profile .bcc-edit-btn:hover {
    background: var(--COLOR--PRIMARY--ULTRALIGHT, rgba(10,125,140,0.15)) !important;
}

/* Responsive */
@media (max-width: 768px) {
    .ps-builder-profile .bcc-info-row {
        flex-direction: column;
        align-items: flex-start;
        padding: 12px;
    }
    
    .ps-builder-profile .bcc-info-label {
        width: 100%;
        margin-bottom: 4px;
    }
    
    .ps-builder-profile .bcc-info-value-wrapper {
        width: 100%;
        flex-wrap: wrap;
    }
}
</style>