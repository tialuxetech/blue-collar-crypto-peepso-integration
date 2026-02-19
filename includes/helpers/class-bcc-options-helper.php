<?php
if (!defined('ABSPATH')) exit;

class BCC_Options_Helper {
    
    /**
     * Converts options string to key-value map
     * Format: "key1:value1,key2:value2"
     */
    public static function parse_options_string(string $options_str): array {
        $map = [];
        
        if (empty($options_str)) {
            return $map;
        }
        
        foreach (explode(',', $options_str) as $pair) {
            $parts = explode(':', trim($pair), 2);
            if (count($parts) === 2) {
                $map[trim($parts[0])] = trim($parts[1]);
            }
        }
        
        return $map;
    }
    
    /**
     * Build HTML select from options string
     */
    public static function build_select(string $options_str, $selected = ''): string {
        $map = self::parse_options_string($options_str);
        
        if (empty($map)) {
            return '<input class="bcc-inline-input" type="text" value="">';
        }
        
        $html = '<select class="bcc-inline-input">';
        
        // Add empty option if none selected
        if ($selected === '' && !isset($map[''])) {
            $html .= '<option value="">— Select —</option>';
        }
        
        foreach ($map as $key => $label) {
            $sel = (string) $key === (string) $selected ? ' selected' : '';
            $html .= sprintf(
                '<option value="%s"%s>%s</option>',
                esc_attr($key),
                $sel,
                esc_html($label)
            );
        }
        
        $html .= '</select>';
        return $html;
    }
}