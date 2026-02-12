<?php
if (!defined('ABSPATH')) exit;

/* ======================================================
   CONTEXT
====================================================== */

$current_user_id = get_current_user_id();
$validator_id    = 0;

/* ======================================================
   FIND VALIDATOR VIA PEEPSO PAGE → SHADOW CPT
====================================================== */

if (isset($page->id)) {

    $found = get_posts([
        'post_type'      => 'validators',
        'meta_key'       => '_peepso_page_id',
        'meta_value'     => $page->id,
        'posts_per_page' => 1,
        'fields'         => 'ids'
    ]);

    if (!empty($found)) {
        $validator_id = (int) $found[0];
    }
}

/* ======================================================
   FLAGS
====================================================== */

$has_validator = $validator_id > 0;

$is_owner = $has_validator && (
    (int) get_post_field('post_author', $validator_id) === $current_user_id
);

/* ======================================================
   NETWORK OPTIONS
====================================================== */

$networks = get_posts([
    'post_type'      => 'network',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
    'post_status'    => 'publish'
]);

$network_options = [];

foreach ($networks as $n) {
    $network_options[] = $n->ID . ':' . $n->post_title;
}

$network_options_str = implode(',', $network_options);
?>

<div class="ps-validator-profile">

<?php if (!is_user_logged_in()): ?>

    <p>Please log in to view your validator profile.</p>

<?php elseif (!$has_validator): ?>

    <p>No validator profile found for this page.</p>

<?php else: ?>

<!-- ============================================
   BASIC INFORMATION
============================================ -->

<h3>Basic Information</h3>

<?php
bcc_render_rows($validator_id, [
    'validator_moniker' => [
        'label' => 'Validator Moniker'
    ],
    'node_name' => [
        'label' => 'Node Name'
    ]
], $is_owner);
?>

<?php bcc_render_divider(); ?>

<!-- ============================================
   CHAINS YOU VALIDATE FOR
============================================ -->

<h3>Chains You Validate For</h3>

<?php
bcc_render_repeater_slider([
    'post_id'      => $validator_id,
    'repeater_key' => 'chains_you_validate_for',
    'owner'        => $is_owner,
    'fields'       => [

        'networks' => [
            'label'   => 'Network',
            'type'    => 'select',
            'options' => $network_options_str
        ],

        'validators_comos' => [
            'label' => 'Validators Cosmos'
        ],

        'avarage_uptime' => [
            'label' => 'Average Uptime (%)'
        ],

        'validator_commission_rate' => [
            'label' => 'Commission Rate (%)'
        ],

        'validator_self_stake' => [
            'label' => 'Self Stake / Own Tokens'
        ],
    ]
]);
?>

<?php bcc_render_divider(); ?>

<!-- ============================================
   INFRASTRUCTURE
============================================ -->

<h3>Infrastructure</h3>

<?php
bcc_render_rows($validator_id, [
    'hardware__infrastructure' => [
        'label' => 'Hardware / Infrastructure'
    ],
    'monitoring_tools' => [
        'label' => 'Monitoring Tools'
    ],
    'redundancy_setup' => [
        'label' => 'Redundancy Setup'
    ]
], $is_owner);
?>

<?php bcc_render_divider(); ?>

<!-- ============================================
   LINKS
============================================ -->

<h3>Links</h3>

<?php
bcc_render_rows($validator_id, [
    'validator_delegation_link' => [
        'label' => 'Delegation Link',
        'type'  => 'url'
    ],
    'network_docs' => [
        'label' => 'Network Docs',
        'type'  => 'url'
    ],
    'network_github' => [
        'label' => 'GitHub',
        'type'  => 'url'
    ],
    'network_twitter' => [
        'label' => 'Twitter / X',
        'type'  => 'url'
    ],
    'network_discord' => [
        'label' => 'Discord',
        'type'  => 'url'
    ]
], $is_owner);
?>

<?php endif; ?>

</div>

<?php

