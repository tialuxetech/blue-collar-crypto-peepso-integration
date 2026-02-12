<?php
if (!defined('ABSPATH')) exit;

if (!function_exists('bcc_render_divider')) {
function bcc_render_divider() {
    echo '<hr class="bcc-divider">';
}
}

if (!function_exists('bcc_options_to_map')) {
function bcc_options_to_map($options_str) {

    $map = [];

    if (!$options_str) return $map;

    $parts = explode(',', $options_str);

    foreach ($parts as $p) {

        $pair = explode(':', $p, 2);

        if (count($pair) === 2) {
            $map[ trim($pair[0]) ] = trim($pair[1]);
        }
    }

    return $map;
}
}

if (!function_exists('bcc_render_row')) {
function bcc_render_row($args = []) {

    $a = wp_parse_args($args, [
        'post_id'  => 0,
        'field'    => '',
        'label'    => '',
        'value'    => null,
        'type'     => 'text',
        'options'  => '',
        'owner'    => false,
        'repeater' => false,
        'sub'      => '',
        'row'      => 0
    ]);

    if (!$a['post_id'] || !$a['field']) return;

    if ($a['value'] !== null) {
        $value = $a['value'];
    } else {
        $value = get_field($a['field'], $a['post_id']);
    }

    if (is_array($value)) {
        $value = implode(', ', $value);
    }

    if (is_object($value)) {
        $value = '';
    }

   $display = is_array($value) ? implode(', ', $value) : $value;

// SELECT FIELD DISPLAY
if ($a['type'] === 'select') {
    $map = bcc_options_to_map($a['options']);
    if (isset($map[$value])) {
        $display = $map[$value];
    }
}

// RELATIONSHIP / POST ID DISPLAY
if (is_numeric($value) && get_post($value)) {
    $display = get_the_title($value);
}


    echo '<div class="bcc-row">';
    echo '<div class="bcc-row-label">'.esc_html($a['label']).'</div>';
    echo '<div class="bcc-row-value">';

    
if (!$a['owner']) {

    if ($a['type'] === 'wysiwyg') {
        echo wp_kses_post($display ?: '');
    } else {
        echo esc_html($display ?: '—');
    }

} else {


        $class = ($a['type'] === 'select') ? 'bcc-inline-select' : 'bcc-inline-text';
echo '<span class="'.$class.'"
    data-post="'.esc_attr($a['post_id']).'"
    data-field="'.esc_attr($a['field']).'"
    data-options="'.esc_attr($a['options']).'"
    data-value="'.esc_attr($value).'"
    data-repeater="'.($a['repeater'] ? 1 : 0).'"
    data-sub="'.esc_attr($a['sub']).'"
    data-row="'.esc_attr($a['row']).'"
>';

if ($a['type'] === 'wysiwyg') {
    echo wp_kses_post($display ?: '');
    
} else {
    echo esc_html($display ?: 'Update Now');
}

echo '</span>';

    }

    echo '</div></div>';
}
}

if (!function_exists('bcc_render_rows')) {
function bcc_render_rows($post_id, $fields, $owner = false) {

    foreach ($fields as $field => $args) {

        bcc_render_row([
            'post_id' => $post_id,
            'field'   => $field,
            'label'   => $args['label'],
            'type'    => $args['type'] ?? 'text',
            'options' => $args['options'] ?? '',
            'owner'   => $owner
        ]);

    }
}
}

if (!function_exists('bcc_render_repeater_slider')) {
function bcc_render_repeater_slider($args = []) {

    if (!function_exists('get_field')) return;

    $a = wp_parse_args($args, [
        'post_id'      => 0,
        'repeater_key' => '',
        'title'        => '',
        'fields'       => [],
        'owner'        => false,
        'empty'        => 'No entries yet'
    ]);

    if (!$a['post_id'] || !$a['repeater_key']) return;

    $rows = get_field($a['repeater_key'], $a['post_id']);

    echo '<div class="bcc-slider-wrap"
        data-post="'.esc_attr($a['post_id']).'"
        data-field="'.esc_attr($a['repeater_key']).'">';

    if ($a['title']) {
        echo '<h3>'.esc_html($a['title']).'</h3>';
    }

    if (empty($rows) || !is_array($rows)) {

        echo '<div class="bcc-repeater-empty">'.esc_html($a['empty']).'</div>';

        if ($a['owner']) {
            echo '<button class="bcc-add-repeater"
                data-post="'.esc_attr($a['post_id']).'"
                data-field="'.esc_attr($a['repeater_key']).'">
                + Add Item
            </button>';
        }

        echo '</div>';
        return;
    }

    echo '<div class="bcc-slider">';

    foreach ($rows as $index => $row) {

      echo '<div class="bcc-slide" data-row="'.esc_attr($index).'">
        <span class="bcc-drag-handle">☰</span>';
 

        if ($a['owner']) {
            echo '<button class="bcc-delete-repeater"
                data-post="'.esc_attr($a['post_id']).'"
                data-field="'.esc_attr($a['repeater_key']).'"
                data-row="'.esc_attr($index).'">✕</button>';
        }

        foreach ($a['fields'] as $subkey => $config) {

            bcc_render_row([
                'post_id'  => $a['post_id'],
                'field'    => $a['repeater_key'],
                'label'    => $config['label'],
                'value'    => isset($row[$subkey]) ? $row[$subkey] : '',
                'type'     => $config['type'] ?? 'text',
                'options'  => $config['options'] ?? '',
                'owner'    => $a['owner'],
                'repeater' => true,
                'sub'      => $subkey,
                'row'      => $index
            ]);
        }

        echo '</div>';
    }

    echo '</div>';

    if ($a['owner']) {
        echo '<button class="bcc-add-repeater"
            data-post="'.esc_attr($a['post_id']).'"
            data-field="'.esc_attr($a['repeater_key']).'">
            + Add Item
        </button>';
    }

    echo '</div>';
}
}
