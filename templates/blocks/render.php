<?php
if (!defined('ABSPATH')) exit;

/**
 * BCC Validator Chains Block
 */

$validator_id = 0;

/**
 * TEMP logic:
 * If viewing validator CPT, use it.
 * Later we’ll swap this with your PeepSo resolver.
 */
if (is_singular('validator')) {
    $validator_id = get_the_ID();
}

if (!$validator_id) {
    return '<p>No validator found.</p>';
}

if (!function_exists('have_rows')) {
    return '<p>ACF not active.</p>';
}

if (!have_rows('validated_chains', $validator_id)) {
    return '<p>No chains added yet.</p>';
}

ob_start();
?>

<div class="bcc-validator-chains">

  <?php while (have_rows('validated_chains', $validator_id)) : the_row(); ?>

    <?php
      $name = get_sub_field('chain_name');
      $ticker = get_sub_field('chain_ticker');
    ?>

    <div class="bcc-chain-card">
      <strong><?php echo esc_html($name); ?></strong>
      <?php if ($ticker): ?>
        <span>(<?php echo esc_html($ticker); ?>)</span>
      <?php endif; ?>
    </div>

  <?php endwhile; ?>

</div>

<?php
return ob_get_clean();
