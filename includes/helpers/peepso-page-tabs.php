<?php
if ( ! defined('ABSPATH') ) {
    exit;
}

/**
 * =====================================================
 * CATEGORY → TAB → CPT MAP
 * Single source of truth
 * =====================================================
 */
function bcc_get_category_map() {

    return [

        // NFT
        253 => [
            'tab'   => 'nft',
            'label' => 'NFT',
            'cpt'   => 'nft',
        ],

        // Validators
        254 => [
            'tab'   => 'validators',
            'label' => 'Validators',
            'cpt'   => 'validators',
        ],

        // Builder
        268 => [
            'tab'   => 'builder',
            'label' => 'Builder',
            'cpt'   => 'builder',
        ],

        1688 => [
            'tab'   => 'dao',
            'label' => 'DAO',
            'cpt'   => 'dao',
        ],

    ];
}

/**
 * =====================================================
 * GET DASHBOARD TABS FOR A PEEPSO PAGE
 * =====================================================
 */
function bcc_get_tabs_for_peepso_page( $page_id ) {

    global $wpdb;

    if ( ! $page_id ) {
        return ['overview' => 'Overview'];
    }

    // Pull category IDs from PeepSo relationship table
    $cat_ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT pm_cat_id
             FROM {$wpdb->prefix}peepso_page_categories
             WHERE pm_page_id = %d",
            $page_id
        )
    );

    if ( empty($cat_ids) ) {
        return ['overview' => 'Overview'];
    }

    $map  = bcc_get_category_map();
    $tabs = [];

    foreach ( (array) $cat_ids as $cat_id ) {

        if ( ! isset($map[$cat_id]) ) {
            continue;
        }

        $slug  = $map[$cat_id]['tab'];
        $label = $map[$cat_id]['label'];

        $tabs[$slug] = $label;

    }

    if ( empty($tabs) ) {
        $tabs = ['overview' => 'Overview'];
    }

    /**
     * Allow other plugins/modules to add tabs
     */
    return apply_filters(
        'bcc_peepso_page_tabs',
        $tabs,
        $page_id
    );
}
