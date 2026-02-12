<?php
if (!defined('ABSPATH')) exit;

add_action('wp_ajax_bcc_inline_save', 'bcc_inline_save');

function bcc_inline_save(){

    check_ajax_referer('bcc_nonce', 'nonce');

    if(!current_user_can('edit_posts')){
        wp_send_json_error('Permission denied');
    }

    $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
    $field   = isset($_POST['field']) ? sanitize_text_field($_POST['field']) : '';
    $value   = isset($_POST['value']) ? wp_unslash($_POST['value']) : '';

    $repeater = isset($_POST['repeater']) ? absint($_POST['repeater']) : 0;
    $row      = isset($_POST['row']) ? absint($_POST['row']) : 0;
    $sub      = isset($_POST['sub']) ? sanitize_text_field($_POST['sub']) : '';

    if(!$post_id || !$field){
        wp_send_json_error('Missing post_id or field');
    }

    if(!$repeater){

        if(function_exists('update_field')){

            $field_object = get_field_object($field, $post_id);

            if($field_object && isset($field_object['key'])){
                update_field($field_object['key'], $value, $post_id);
            } else {
                update_post_meta($post_id, $field, $value);
            }

        } else {

            update_post_meta($post_id, $field, $value);

        }

        wp_send_json_success([
            'value' => $value
        ]);
    }


    if(!function_exists('get_field') || !function_exists('update_field')){
        wp_send_json_error('ACF not available');
    }

    if(!$sub){
        wp_send_json_error('Missing sub field');
    }

    $rows = get_field($field, $post_id);

    if(!is_array($rows)){
        $rows = [];
    }

    if(!isset($rows[$row]) || !is_array($rows[$row])){
        $rows[$row] = [];
    }

    $rows[$row][$sub] = $value;

    $field_object = get_field_object($field, $post_id);

    if($field_object && isset($field_object['key'])){
        update_field($field_object['key'], $rows, $post_id);
    } else {
        update_post_meta($post_id, $field, $rows);
    }

    wp_send_json_success([
        'value' => $value
    ]);
}

add_action('wp_ajax_bcc_delete_repeater_row', 'bcc_delete_repeater_row');

function bcc_delete_repeater_row(){

    check_ajax_referer('bcc_nonce', 'nonce');

    if(!current_user_can('edit_posts')){
        wp_send_json_error('Permission denied');
    }

    $post_id = absint($_POST['post_id'] ?? 0);
    $field   = sanitize_text_field($_POST['field'] ?? '');
    $row     = absint($_POST['row'] ?? -1);

    if(!$post_id || !$field || $row < 0){
        wp_send_json_error('Missing data');
    }

    if(!function_exists('get_field') || !function_exists('update_field')){
        wp_send_json_error('ACF missing');
    }

    $rows = get_field($field, $post_id);

    if(!is_array($rows) || !isset($rows[$row])){
        wp_send_json_error('Row not found');
    }

    unset($rows[$row]);

    // Reindex array
    $rows = array_values($rows);

    $field_object = get_field_object($field, $post_id);

    if($field_object && isset($field_object['key'])){
        update_field($field_object['key'], $rows, $post_id);
    } else {
        update_post_meta($post_id, $field, $rows);
    }

    wp_send_json_success();
}


add_action('wp_ajax_bcc_add_repeater_row', 'bcc_add_repeater_row');

function bcc_add_repeater_row(){

    check_ajax_referer('bcc_nonce', 'nonce');

    if(!current_user_can('edit_posts')){
        wp_send_json_error('Permission denied');
    }

    $post_id = absint($_POST['post_id'] ?? 0);
    $field   = sanitize_text_field($_POST['field'] ?? '');

    if(!$post_id || !$field){
        wp_send_json_error('Missing data');
    }

    if(!function_exists('get_field') || !function_exists('update_field')){
        wp_send_json_error('ACF missing');
    }

    $rows = get_field($field, $post_id);

    if(!is_array($rows)){
        $rows = [];
    }

    // Push empty row
    $rows[] = [];

    $field_object = get_field_object($field, $post_id);

    if($field_object && isset($field_object['key'])){
        update_field($field_object['key'], $rows, $post_id);
    } else {
        update_post_meta($post_id, $field, $rows);
    }

    wp_send_json_success([
        'row_index' => count($rows) - 1
    ]);
}

add_action('wp_ajax_bcc_reorder_repeater', 'bcc_reorder_repeater');

function bcc_reorder_repeater(){

    check_ajax_referer('bcc_nonce', 'nonce');

    if(!current_user_can('edit_posts')){
        wp_send_json_error('Permission denied');
    }

    $post_id = absint($_POST['post_id'] ?? 0);
    $field   = sanitize_text_field($_POST['field'] ?? '');
    $order   = $_POST['order'] ?? [];

    if(!$post_id || !$field || !is_array($order)){
        wp_send_json_error('Missing data');
    }

    if(!function_exists('get_field') || !function_exists('update_field')){
        wp_send_json_error('ACF missing');
    }

    $rows = get_field($field, $post_id);

    if(!is_array($rows)){
        wp_send_json_error('No rows');
    }

    $new_rows = [];

    foreach($order as $index){
        if(isset($rows[$index])){
            $new_rows[] = $rows[$index];
        }
    }

    $field_object = get_field_object($field, $post_id);

    if($field_object && isset($field_object['key'])){
        update_field($field_object['key'], $new_rows, $post_id);
    } else {
        update_post_meta($post_id, $field, $new_rows);
    }

    wp_send_json_success();
}
