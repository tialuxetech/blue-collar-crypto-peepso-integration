<?php
if (!defined('ABSPATH')) exit;

function bcc_category_id_to_slug() {
    return [
        254 => 'validators',
        253 => 'nft',
        268 => 'builder',
        269 => 'builder',
    ];
}

function bcc_slug_to_cpt() {
    return [
        'validators' => 'validators',
        'builder'    => 'builder',
        'nft'        => 'nft',
    ];
}

