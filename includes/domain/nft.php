<?php
if (!defined('ABSPATH')) exit;

class BCC_Domain_NFT extends BCC_Domain_Abstract {

    public static function post_type(): string {
        return 'nft';
    }

    public static function fields(): array {
        return [
            'artist_name',
            'artist_short_bio',
            'nft_team_size_',
            'nft_total_volume',
            'nft_total_sales',
            'holderscollectors',
            'nft_collections',
            'network_docs',
            'network_github',
            'network_twitter',
            'network_discord',
            'network_telegram',
            'network_youtube',
            'network_linkedin',
            'medium',
            'reddit'
        ];
    }

    public static function repeater_subfields(string $repeater): array {

        if ($repeater === 'nft_collections') {
            return [
                'collection_name',
                'collection_gallery',
                'collection_description',
                'post_type',
                'collection_mint_url',
                'collection_marketplace_url',
                'collection_x_account'
            ];
        }

        return [];
    }
}

/* Backwards compatibility */

function bcc_get_nft_id($page_id) {
    return BCC_Domain_NFT::get_or_create_from_page((int) $page_id);
}
