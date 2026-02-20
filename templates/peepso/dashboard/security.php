<?php
defined('ABSPATH') || exit;

use PeepSoProfileShortcode;

// --------------------------------------------------
// PROFILE CONTEXT
// --------------------------------------------------
$profile = PeepSoProfileShortcode::get_instance();
$profile_user_id = $profile->get_view_user_id();

if (!$profile_user_id) {
    echo '<p>' . esc_html__('Invalid profile context.', 'bcc') . '</p>';
    return;
}
?>

<div class="peepso">
    <div class="ps-page ps-page--profile ps-page--profile-about">

        <?php PeepSoTemplate::exec_template('general', 'navbar'); ?>

        <div class="ps-profile ps-profile--edit ps-profile--about">

            <?php
            PeepSoTemplate::exec_template(
                'profile',
                'focus',
                ['current' => 'security']
            );
            ?>

            <div class="ps-profile__edit">

                <div class="ps-profile__about-fields ps-js-profile-list ps-profile__edit-tab--about">
                    <div class="ps-card__body">

                        <div class="bcc-security-content">
                            <?php echo do_shortcode('[wp-2fa-setup-form]'); ?>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>