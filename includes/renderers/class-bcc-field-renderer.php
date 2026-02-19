<?php
if (!defined('ABSPATH')) exit;

class BCC_Field_Renderer {
    
    private $args;
    private $post_id;
    private $field;
    private $label;
    private $value;
    private $type;
    private $options;
    private $can_edit;
    private $repeater;
    private $sub;
    private $row;
    private $display_value;
    public function __construct(array $args = []) {
        $this->args = wp_parse_args($args, [
            'post_id'   => 0,
            'field'     => '',
            'label'     => '',
            'value'     => null,
            'type'      => 'text',
            'options'   => '',
            'can_edit'  => false,
            'repeater'  => false,
            'sub'       => '',
            'row'       => 0
        ]);
        
        // Assign to properties
        $this->post_id  = $this->args['post_id'];
        $this->field    = $this->args['field'];
        $this->label    = $this->args['label'];
        $this->value    = $this->args['value'];
        $this->type     = $this->args['type'];
        $this->options  = $this->args['options'];
        $this->can_edit = $this->args['can_edit'];
        $this->repeater = $this->args['repeater'];
        $this->sub      = $this->args['sub'];
        $this->row      = $this->args['row'];
    }
    
    public function render(): void {
        // Validate required fields
        if (empty($this->post_id) || empty($this->field)) {
            return;
        }
        
        // Get field value if not provided
        if ($this->value === null) {
            $this->value = get_field($this->field, $this->post_id);
        }
        
        // Process value based on type
        $this->process_value();
        
        // Check visibility
        if (!$this->check_visibility()) {
            return;
        }
        
        // Check edit permission
        $can_edit_field = $this->check_edit_permission();
        
        // Start output
        $this->open_row();
        
        if (!$can_edit_field) {
            $this->render_view_mode();
        } else {
            $this->render_edit_mode();
        }
        
        $this->close_row();
    }
    
    private function process_value(): void {
        // Handle arrays and objects
        if (is_array($this->value) && $this->type !== 'gallery') {
            $this->value = implode(', ', array_filter($this->value));
        } elseif (is_object($this->value)) {
            $this->value = '';
        }
        
        // Handle select field display mapping
        if ($this->type === 'select' && !empty($this->options)) {
            $map = BCC_Options_Helper::parse_options_string($this->options);
            if (isset($map[$this->value])) {
                $this->display_value = $map[$this->value];
            } else {
                $this->display_value = $this->value;
            }
        } else {
            $this->display_value = $this->value;
        }
    }
    
    private function check_visibility(): bool {
        if (!function_exists('bcc_user_can_view_field')) {
            return true;
        }
        
        return bcc_user_can_view_field($this->post_id, $this->field);
    }
    
    private function check_edit_permission(): bool {
        $can_edit_field = $this->can_edit;
        
        if (function_exists('bcc_user_can_edit_field')) {
            $can_edit_field = bcc_user_can_edit_field($this->post_id, $this->field);
        }
        
        return $can_edit_field;
    }
    
    private function open_row(): void {
        printf(
            '<div class="bcc-row bcc-row-type-%s">',
            esc_attr($this->type)
        );
        echo '<div class="bcc-row-label">' . esc_html($this->label) . '</div>';
        echo '<div class="bcc-row-value">';
    }
    
    private function close_row(): void {
        echo '</div></div>'; // Close row-value and row
    }
    
    private function render_view_mode(): void {
        switch ($this->type) {
            case 'gallery':
                $this->render_gallery_view();
                break;
            case 'wysiwyg':
                $this->render_wysiwyg_view();
                break;
            case 'url':
                $this->render_url_view();
                break;
            default:
                $this->render_text_view();
                break;
        }
    }
    
    private function render_gallery_view(): void {
        if (empty($this->value) || !is_array($this->value)) {
            echo '—';
            return;
        }
        
        BCC_Gallery_Renderer::render_view($this->value);
    }
    
    private function render_wysiwyg_view(): void {
        echo wp_kses_post($this->display_value ?: '');
    }
    
    private function render_url_view(): void {
        if (!empty($this->display_value)) {
            printf(
                '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                esc_url($this->display_value),
                esc_html($this->display_value)
            );
        } else {
            echo '—';
        }
    }
    
    private function render_text_view(): void {
        echo esc_html($this->display_value ?: '—');
    }
    
    private function render_edit_mode(): void {
        // Build data attributes
        $data_attrs = $this->build_data_attributes();
        
        // Handle gallery separately
        if ($this->type === 'gallery') {
            BCC_Gallery_Renderer::render_edit($this->value, $data_attrs);
        } else {
            $this->render_regular_edit_field($data_attrs);
            
            // Edit button
            echo '<button type="button" class="bcc-inline-edit-btn" aria-label="Edit field">Edit</button>';
        }
        
        // Visibility pill
        $this->render_visibility_pill();
    }
    
    private function build_data_attributes(): string {
        $stored_value = $this->get_stored_value();
        
        return sprintf(
            ' data-post="%d" data-field="%s" data-options="%s" data-value="%s" data-type="%s" data-repeater="%d" data-sub="%s" data-row="%d"',
            esc_attr($this->post_id),
            esc_attr($this->field),
            esc_attr($this->options),
            esc_attr($stored_value),
            esc_attr($this->type),
            $this->repeater ? 1 : 0,
            esc_attr($this->sub),
            esc_attr($this->row)
        );
    }
    
    private function get_stored_value() {
        if ($this->type === 'gallery') {
            if (is_array($this->value)) {
                $ids = wp_list_pluck($this->value, 'id');
                return implode(',', $ids);
            }
            return '';
        } elseif (is_array($this->value)) {
            return implode(', ', array_filter($this->value));
        }
        
        return $this->value;
    }
    
    private function render_regular_edit_field($data_attrs): void {
        $class = ($this->type === 'select') ? 'bcc-inline-select' : 'bcc-inline-text';
        
        printf(
            '<span class="%s" %s>',
            esc_attr($class),
            $data_attrs
        );
        
        if ($this->type === 'wysiwyg') {
            echo wp_kses_post($this->display_value ?: '');
        } else {
            echo esc_html($this->display_value ?: 'Update Now');
        }
        
        echo '</span>';
    }
    
    private function render_visibility_pill(): void {
        if (!function_exists('bcc_get_field_visibility') || !function_exists('bcc_user_can_edit_post')) {
            return;
        }
        
        // Only show visibility controls to post owners
        if (!bcc_user_can_edit_post($this->post_id)) {
            return;
        }
        
        $vis = bcc_get_field_visibility($this->post_id, $this->field);
        
        $labels = [
            'public'  => '🌍 Public',
            'members' => '👥 Members',
            'private' => '🔒 Private'
        ];
        
        $vis_label = $labels[$vis] ?? $labels['public'];
        
        printf(
            '<button type="button" class="bcc-visibility-pill %s" data-post="%d" data-field="%s" data-current="%s" aria-label="Change visibility">%s</button>',
            esc_attr($vis),
            esc_attr($this->post_id),
            esc_attr($this->field),
            esc_attr($vis),
            esc_html($vis_label)
        );
    }
}