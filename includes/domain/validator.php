<?php
if (!defined('ABSPATH')) exit;

class BCC_Domain_Validator extends BCC_Domain_Abstract {

    public static function post_type(): string {
        return 'validators';
    }

    public static function fields(): array {
        return [
            'validator_moniker',
            'node_name',
            'chains_you_validate_for',
            'hardware__infrastructure',
            'monitoring_tools',
            'redundancy_setup',
            'validator_delegation_link',
            'network_docs',
            'network_github',
            'network_twitter',
            'network_discord',
            'average_uptime',
            'validator_commission_rate',
            'validator_self_stake',
            'validator_chains'
        ];
    }

    public static function repeater_subfields(string $repeater): array {

        if ($repeater === 'validator_chains') {
            return [
                'chain_name',
                'rpc_url',
                'rest_url',
                'snapshot_url',
                'addr_prefix',
                'staking_token'
            ];
        }

        return [];
    }
}

function bcc_get_validator_id($page_id) {
    return BCC_Domain_Validator::get_or_create_from_page((int) $page_id);
}
