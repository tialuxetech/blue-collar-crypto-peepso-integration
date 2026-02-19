<?php
if (!defined('ABSPATH')) exit;

class BCC_Domain_DAO extends BCC_Domain_Abstract {

    public static function post_type(): string {
        return 'dao';
    }

    public static function fields(): array {
        return [
            'dao_name',
            'dao_description',
            'dao_governance_link',
            'dao_token',
            'dao_treasury_size',
            'network_docs',
            'network_github',
            'network_twitter',
            'network_discord',
            'dao_members'
        ];
    }

    public static function repeater_subfields(string $repeater): array {

        if ($repeater === 'dao_members') {
            return [
                'member_name',
                'member_role',
                'member_wallet'
            ];
        }

        return [];
    }
}

function bcc_get_dao_id($page_id) {
    return BCC_Domain_DAO::get_or_create_from_page((int) $page_id);
}
