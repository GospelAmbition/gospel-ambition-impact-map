<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

add_action('zume_log', function( $args ) {
    if ( in_array( $args['type'], [ 'system' ] ) ) {
        return;
    }

    add_log_to_queue( [
        'post_type' => 'impact_map',
        'type' => $args['type'],
        'subtype' => $args['subtype'],
        'time' => time(),
        'language_code' => get_locale() ?? $args['language_code'] ?? 'en',
        'location' => [
            'ip' => get_ip_address_for_log()
        ],
    ] );
}, 10, 1 );

add_action('zume_log_anonymous', function( $args ) {
    add_log_to_queue( [
        'post_type' => 'impact_map',
        'type' => $args['type'],
        'subtype' => $args['subtype'],
        'time' => time(),
        'language_code' => get_locale() ?? $args['language_code'] ?? 'en',
        'location' => [
            'ip' => get_ip_address_for_log()
        ],
    ] );
}, 10, 1 );
