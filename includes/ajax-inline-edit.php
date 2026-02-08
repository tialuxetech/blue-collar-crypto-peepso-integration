<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_bcc_inline_save', 'bcc_inline_save');

function bcc_inline_save(){

    if(!current_user_can('edit_posts')){
        wp_send_json_error('Permission denied');
    }

    $post_id   = intval($_POST['post_id']);
    $field     = sanitize_text_field($_POST['field']);
    $value     = wp_unslash($_POST['value']);

    $is_repeater = isset($_POST['repeater']);
    $row         = isset($_POST['row']) ? intval($_POST['row']) : 0;
    $sub         = isset($_POST['sub']) ? sanitize_text_field($_POST['sub']) : '';

    // ================================
    // NORMAL FIELD
    // ================================
    if(!$is_repeater){
        update_field($field, $value, $post_id);
        wp_send_json_success();
    }

    // ================================
    // REPEATER FIELD
    // ================================

    $rows = get_field($field, $post_id);
    if(!$rows){
        $rows = [];
    }

    if(!isset($rows[$row])){
        $rows[$row] = [];
    }

    $rows[$row][$sub] = $value;

    update_field($field, $rows, $post_id);

    wp_send_json_success();
}
