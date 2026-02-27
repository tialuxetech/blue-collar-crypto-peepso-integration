<?php
/**
 * BCC PeepSo Segment Integration
 */

if (!defined('ABSPATH')) {
    exit;
}

add_filter('peepso_page_segments', function ($segments, $page_id) {

    $segments['dashboard'] = [
        'label' => 'Dashboard',
        'icon'  => 'gsi gsi-home',
    ];

    return $segments;

}, 10, 2);


add_action('peepso_page_segment_dashboard', function ($args, $url) {

    if (
        empty($args['page']) ||
        !is_object($args['page']) ||
        empty($args['page']->id)
    ) {
        return; // fail silently
    }

    $page = $args['page'];

    $template = BCC_TEMPLATES_PATH . 'peepso/dashboard.php';

    if (file_exists($template)) {
        include $template;
    }

}, 10, 2);