<?php
if (!defined('ABSPATH')) exit;

class BCC_Domain_Builder extends BCC_Domain_Abstract {

    public static function post_type(): string {
        return 'builder';
    }

    public static function fields(): array {
        return [
            'builder_name',
            'builder_description',
            'builder_specialty',
            'builder_website',
            'network_docs',
            'network_github',
            'network_twitter',
            'network_discord',
            'builder_projects'
        ];
    }

    public static function repeater_subfields(string $repeater): array {

        if ($repeater === 'builder_projects') {
            return [
                'project_name',
                'project_description',
                'project_link'
            ];
        }

        return [];
    }
}

function bcc_get_builder_id($page_id) {
    return BCC_Domain_Builder::get_or_create_from_page((int) $page_id);
}
