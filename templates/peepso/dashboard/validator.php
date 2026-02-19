<?php
if (!defined('ABSPATH')) exit;

/* ======================================================
   VALIDATOR PROFILE TEMPLATE
====================================================== */

$validator_id = bcc_get_validator_id($page->id);
$has_validator = $validator_id > 0;

$can_view = $has_validator ? bcc_user_can_view_post($validator_id) : false;
$can_edit = $has_validator ? bcc_user_can_edit_post($validator_id) : false;

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

<div class="ps-validator-profile bcc-validator-profile">

<?php if (!is_user_logged_in()): ?>

    <p>Please log in to view this validator profile.</p>

<?php elseif (!$has_validator): ?>

    <p>No validator profile found for this page.</p>

<?php elseif (!$can_view): ?>

    <p>This validator profile is private.</p>

<?php else: ?>

    <h3>Basic Information</h3>

    <?php
    bcc_render_rows($validator_id, [
        'validator_moniker' => ['label' => 'Validator Moniker'],
        'node_name' => ['label' => 'Node Name']
    ], $can_edit);
    ?>

    <?php bcc_render_divider(); ?>

    <h3>Chains You Validate For</h3>

    <?php
    bcc_render_repeater_slider([
        'post_id' => $validator_id,
        'repeater_key' => 'chains_you_validate_for',
        'can_edit' => $can_edit,
        'fields' => [
            'networks' => [
                'label' => 'Network',
                'type' => 'select',
                'options' => $network_options_str
            ],
            'validators_cosmos' => [  // FIXED: was 'validators_comos'
                'label' => 'Validators Cosmos'
            ],
            'average_uptime' => [      // FIXED: was 'avarage_uptime'
                'label' => 'Average Uptime (%)'
            ],
            'validator_commission_rate' => [
                'label' => 'Commission Rate (%)'
            ],
            'validator_self_stake' => [
                'label' => 'Self Stake'
            ]
        ]
    ]);
    ?>

    <?php bcc_render_divider(); ?>

    <h3>Infrastructure</h3>

    <?php
    bcc_render_rows($validator_id, [
        'hardware_infrastructure' => [  // FIXED: was 'hardware__infrastructure'
            'label' => 'Hardware / Infrastructure',
            'type' => 'wysiwyg'
        ],
        'monitoring_tools' => [
            'label' => 'Monitoring Tools'
        ],
        'redundancy_setup' => [
            'label' => 'Redundancy Setup'
        ]
    ], $can_edit);
    ?>

    <?php bcc_render_divider(); ?>

    <h3>Links</h3>

    <?php
    bcc_render_rows($validator_id, [
        'validator_delegation_link' => [
            'label' => 'Delegation Link', 
            'type' => 'url'
        ],
        'network_docs' => [
            'label' => 'Network Docs', 
            'type' => 'url'
        ],
        'network_github' => [
            'label' => 'GitHub', 
            'type' => 'url'
        ],
        'network_twitter' => [
            'label' => 'Twitter / X', 
            'type' => 'url'
        ],
        'network_discord' => [
            'label' => 'Discord', 
            'type' => 'url'
        ]
    ], $can_edit);
    ?>

<?php endif; ?>

</div> <!-- end validator-profile -->

<div id="bcc-visibility-popover" class="bcc-vis-popover" style="display: none;">
    <div class="bcc-vis-option" data-value="public">🌍 Public</div>
    <div class="bcc-vis-option" data-value="members">👥 Members</div>
    <div class="bcc-vis-option" data-value="private">🔒 Private</div>
</div>