<?php
if (!defined('ABSPATH')) exit;

/* ======================================================
FIND CURRENT USER VALIDATOR
====================================================== */

$current_user_id = get_current_user_id();
$validator_id = 0;

$validator = get_posts([
    'post_type'      => 'validator',
    'author'         => $current_user_id,
    'posts_per_page' => 1
]);

if ($validator) {
    $validator_id = $validator[0]->ID;
}

$has_validator = $validator_id > 0;

/* ======================================================
NETWORK OPTIONS
====================================================== */

$networks = get_posts([
    'post_type'      => 'network',
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC'
]);

$options = [];
foreach ($networks as $n) {
    $options[] = $n->ID . ':' . $n->post_title;
}

$network_options = implode(',', $options);

/* ======================================================
INLINE FIELD HELPERS
====================================================== */

function bcc_inline_text($field, $value, $validator, $repeater=false, $sub='', $row=0){ ?>
<div class="ps-validator-profile__value">
    <span class="bcc-inline-text"
        data-field="<?php echo esc_attr($field); ?>"
        data-validator="<?php echo esc_attr($validator); ?>"
        <?php if($repeater): ?>
            data-repeater="1"
            data-sub="<?php echo esc_attr($sub); ?>"
            data-row="<?php echo esc_attr($row); ?>"
        <?php endif; ?>
    >
        <?php echo esc_html($value ?: 'Update Now'); ?>
    </span>
</div>
<?php }

function bcc_inline_select($field, $value, $validator, $options, $repeater=false, $sub='', $row=0){ ?>
<div class="ps-validator-profile__value">
    <span class="bcc-inline-select"
        data-field="<?php echo esc_attr($field); ?>"
        data-validator="<?php echo esc_attr($validator); ?>"
        data-options="<?php echo esc_attr($options); ?>"
        <?php if($repeater): ?>
            data-repeater="1"
            data-sub="<?php echo esc_attr($sub); ?>"
            data-row="<?php echo esc_attr($row); ?>"
        <?php endif; ?>
    >
        <?php echo esc_html($value ?: 'Not Set'); ?>
    </span>
</div>
<?php }

/* ======================================================
CHAINS REPEATER
====================================================== */

$chains = get_field('chains_you_validate_for', $validator_id);

if (!$chains) {
    $chains = [[]];
}

?>

<div class="ps-validator-profile">

<?php if(!$has_validator): ?>

    <p>You have not created a validator yet.</p>

<?php else: ?>

<!-- ======================================================
BASIC INFORMATION
====================================================== -->

<h3>Basic Information</h3>

<div class="ps-validator-profile__grid">

<label>Validator Moniker</label>
<?php bcc_inline_text(
    'validator_moniker',
    get_field('validator_moniker', $validator_id),
    $validator_id
); ?>

<label>Node Name</label>
<?php bcc_inline_text(
    'node_name',
    get_field('node_name', $validator_id),
    $validator_id
); ?>

</div>

<hr>

<!-- ======================================================
VALIDATED CHAINS
====================================================== -->

<h3>Validated Chains</h3>

<div class="ps-validator-profile__grid">

<?php foreach($chains as $i => $c): ?>

<label>Network</label>
<?php bcc_inline_select(
    'chains_you_validate_for',
    $c['network'] ?? '',
    $validator_id,
    $network_options,
    true,
    'network',
    $i
); ?>

<label>Node Role</label>
<?php bcc_inline_select(
    'chains_you_validate_for',
    $c['node_role'] ?? '',
    $validator_id,
    'validator:Validator,fullnode:Full Node,archive:Archive',
    true,
    'node_role',
    $i
); ?>

<label>Commission Rate (%)</label>
<?php bcc_inline_text(
    'chains_you_validate_for',
    $c['chain_commission_rate'] ?? '',
    $validator_id,
    true,
    'chain_commission_rate',
    $i
); ?>

<label>Max Commission (%)</label>
<?php bcc_inline_text(
    'chains_you_validate_for',
    $c['max_commission'] ?? '',
    $validator_id,
    true,
    'max_commission',
    $i
); ?>

<label>Uptime (%)</label>
<?php bcc_inline_text(
    'chains_you_validate_for',
    $c['chain_uptime'] ?? '',
    $validator_id,
    true,
    'chain_uptime',
    $i
); ?>

<hr>

<?php endforeach; ?>

</div>

<!-- ======================================================
INFRASTRUCTURE
====================================================== -->

<h3>Infrastructure</h3>

<div class="ps-validator-profile__grid">

<label>Hardware / Infrastructure</label>
<?php bcc_inline_text(
    'hardware__infrastructure',
    get_field('hardware__infrastructure',$validator_id),
    $validator_id
); ?>

<label>Monitoring Tools</label>
<?php bcc_inline_text(
    'monitoring_tools',
    get_field('monitoring_tools',$validator_id),
    $validator_id
); ?>

<label>Redundancy Setup</label>
<?php bcc_inline_text(
    'redundancy_setup',
    get_field('redundancy_setup',$validator_id),
    $validator_id
); ?>

</div>

<hr>

<!-- ======================================================
LINKS
====================================================== -->

<h3>Links</h3>

<div class="ps-validator-profile__grid">

<label>Delegation Link</label>
<?php bcc_inline_text(
    'validator_delegation_link',
    get_field('validator_delegation_link',$validator_id),
    $validator_id
); ?>

<label>Network Docs</label>
<?php bcc_inline_text(
    'network_docs',
    get_field('network_docs',$validator_id),
    $validator_id
); ?>

<label>GitHub</label>
<?php bcc_inline_text(
    'network_github',
    get_field('network_github',$validator_id),
    $validator_id
); ?>

<label>Twitter / X</label>
<?php bcc_inline_text(
    'network_twitter',
    get_field('network_twitter',$validator_id),
    $validator_id
); ?>

<label>Discord</label>
<?php bcc_inline_text(
    'network_discord',
    get_field('network_discord',$validator_id),
    $validator_id
); ?>

</div>

<?php endif; ?>

</div>
