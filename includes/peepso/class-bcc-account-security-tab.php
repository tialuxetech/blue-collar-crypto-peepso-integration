<?php
namespace BCC\PeepSo;

defined('ABSPATH') || exit;

final class Account_Security_Tab {

    public static function init(): void {

        // Register tab
        add_filter(
            'peepso_navigation_profile',
            [self::class, 'register_tab']
        );

        // Optional: Control tab order
        add_filter(
            'peepso_filter_navigation_profile_order',
            [self::class, 'order_tab'],
            20
        );

        // Register segment render
        add_action(
            'peepso_profile_segment_security',
            [self::class, 'render']
        );
    }

    /**
     * Register Account Security menu
     */
    public static function register_tab(array $links): array {

        $profile_user_id = \PeepSoProfileShortcode::get_instance()->get_view_user_id();
    
        if (!\bcc_user_is_profile_owner($profile_user_id)) {
            return $links;
        }
    
        $links['security'] = [
            'label' => __('Account Security', 'bcc'),
            'href'  => 'security',
            'icon'  => 'ps-icon-shield',
        ];
    
        return $links;
    }

    /**
     * Place Security after About tab
     */
    public static function order_tab(array $order): array {

        $order = array_values(array_diff($order, ['security']));

        $new_order = [];

        foreach ($order as $key) {
            $new_order[] = $key;

            if ($key === 'about') {
                $new_order[] = 'security';
            }
        }

        return $new_order;
    }

    /**
     * Render Security segment
     */
    public static function render(): void {

        $view_user_id = \PeepSoUrlSegments::get_view_id(
            \PeepSoProfileShortcode::get_instance()->get_view_user_id()
        );

        // Strict owner-only access
        if (!\bcc_user_is_profile_owner($view_user_id)) {
            \PeepSo::redirect(
                \PeepSoUser::get_instance($view_user_id)->get_profileurl()
            );
            exit;
        }

        $template = BCC_PLUGIN_PATH . 'templates/peepso/dashboard/security.php';

        if (file_exists($template)) {
            include $template;
        } else {
            echo '<p>' . esc_html__('Security page not available.', 'bcc') . '</p>';
        }
    }
}