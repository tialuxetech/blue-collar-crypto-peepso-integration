<?php
if (!defined('ABSPATH')) exit;

class BCC_Repeater_Renderer {
    
    /**
     * Render a repeater field as a slider
     * 
     * @param array $args {
     *     @type int    $post_id       Post ID
     *     @type string $repeater_key  Repeater field name
     *     @type array  $fields        Sub-field configurations
     *     @type bool   $can_edit      Whether user can edit
     *     @type string $empty         Empty state message
     * }
     */
    public static function render(array $args = []): void {
        if (!function_exists('get_field')) {
            return;
        }
        
        $a = wp_parse_args($args, [
            'post_id'      => 0,
            'repeater_key' => '',
            'fields'       => [],
            'can_edit'     => false,
            'empty'        => 'No entries yet'
        ]);
        
        // Validate required fields
        if (empty($a['post_id']) || empty($a['repeater_key'])) {
            return;
        }
        
        $rows = get_field($a['repeater_key'], $a['post_id']);
        $has_rows = !empty($rows) && is_array($rows);
        
        // Start wrapper
        self::open_wrapper($a['post_id'], $a['repeater_key']);
        
        if (!$has_rows) {
            self::render_empty_state($a);
        } else {
            self::render_rows($rows, $a);
        }
        
        self::close_wrapper($a);
    }
    
    /**
     * Open the slider wrapper
     */
    private static function open_wrapper(int $post_id, string $repeater_key): void {
        printf(
            '<div class="bcc-slider-wrap" data-post="%d" data-field="%s">',
            esc_attr($post_id),
            esc_attr($repeater_key)
        );
    }
    
    /**
     * Render empty state
     */
    private static function render_empty_state(array $args): void {
        echo '<div class="bcc-repeater-empty">' . esc_html($args['empty']) . '</div>';
        
        if ($args['can_edit']) {
            printf(
                '<button class="bcc-add-repeater button" data-post="%d" data-field="%s">+ Add Item</button>',
                esc_attr($args['post_id']),
                esc_attr($args['repeater_key'])
            );
        }
    }
    
    /**
     * Render all rows
     */
    private static function render_rows(array $rows, array $args): void {
        echo '<div class="bcc-slider">';
        
        foreach ($rows as $index => $row) {
            if (!is_array($row)) continue;
            
            self::render_single_row($row, $index, $args);
        }
        
        echo '</div>';
        
        // Add item button
        if ($args['can_edit']) {
            printf(
                '<button class="bcc-add-repeater button" data-post="%d" data-field="%s">+ Add Item</button>',
                esc_attr($args['post_id']),
                esc_attr($args['repeater_key'])
            );
        }
    }
    
    /**
     * Render a single row
     */
    private static function render_single_row(array $row, int $index, array $args): void {
        $row_classes = ['bcc-slide'];
        if ($args['can_edit']) {
            $row_classes[] = 'bcc-slide-editable';
        }
        
        printf(
            '<div class="%s" data-row="%d">',
            esc_attr(implode(' ', $row_classes)),
            esc_attr($index)
        );
        
        // Drag handle and delete button (edit mode only)
        if ($args['can_edit']) {
            echo '<span class="bcc-drag-handle" aria-label="Drag to reorder">☰</span>';
            printf(
                '<button class="bcc-delete-repeater" data-post="%d" data-field="%s" data-row="%d" aria-label="Delete item">✕</button>',
                esc_attr($args['post_id']),
                esc_attr($args['repeater_key']),
                esc_attr($index)
            );
        }
        
        // Render sub-fields
        self::render_sub_fields($row, $index, $args);
        
        echo '</div>'; // Close bcc-slide
    }
    
    /**
     * Render sub-fields for a row
     */
    private static function render_sub_fields(array $row, int $index, array $args): void {
        foreach ($args['fields'] as $subkey => $config) {
            // Skip if config is invalid
            if (empty($config['label'])) continue;
            
            // Use the field renderer for each sub-field
            $renderer = new BCC_Field_Renderer([
                'post_id'  => $args['post_id'],
                'field'    => $args['repeater_key'],
                'label'    => $config['label'],
                'value'    => $row[$subkey] ?? '',
                'type'     => $config['type'] ?? 'text',
                'options'  => $config['options'] ?? '',
                'can_edit' => $args['can_edit'],
                'repeater' => true,
                'sub'      => $subkey,
                'row'      => $index
            ]);
            
            $renderer->render();
        }
    }
    
    /**
     * Close the wrapper
     */
    private static function close_wrapper(array $args): void {
        echo '</div>'; // Close bcc-slider-wrap
    }
    
    /**
     * Get the number of rows in a repeater
     */
    public static function get_count(int $post_id, string $repeater_key): int {
        if (!function_exists('get_field')) {
            return 0;
        }
        
        $rows = get_field($repeater_key, $post_id);
        return is_array($rows) ? count($rows) : 0;
    }
    
    /**
     * Check if repeater has any rows
     */
    public static function has_rows(int $post_id, string $repeater_key): bool {
        return self::get_count($post_id, $repeater_key) > 0;
    }
}