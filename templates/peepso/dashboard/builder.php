
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
<div id="bcc-visibility-popover" style="display:none;">
  <div class="bcc-vis-option" data-value="public">
    <span>🌍</span> Public
  </div>
  <div class="bcc-vis-option" data-value="members">
    <span>👥</span> Members
  </div>
  <div class="bcc-vis-option" data-value="private">
    <span>🔒</span> Private
  </div>
</div>
