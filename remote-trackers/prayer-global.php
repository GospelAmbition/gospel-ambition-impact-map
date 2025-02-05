<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.
/*****************************
 * HOOKS
 *****************************/




/**
 * wp_insert_post
 */
add_action( 'wp_insert_post', function( $post_ID, $post, $update ) {
    if ( ! $update && 'contacts' === $post->post_type ) {
        // dt_write_log('wp_insert_post');
        add_log_to_queue( [
            'post_type' => 'prayer_global',
            'type' => 'praying',
            'subtype' => 'pg_registered',
            'time' => time(), // time
            'language_code' => get_locale(), // language
            'location' => [
                'ip' => get_ip_address_for_log(),
            ],
        ] );
    }
}, 10, 3 );

/**
 * updated_post_meta
 */
add_action( 'updated_post_meta', function( $meta_id, $object_id, $meta_key, $meta_value, $new = false, $deleted = false ){
    if ( $meta_key === 'status' && $meta_value === 'complete' ) {
        // dt_write_log('updated_post_meta');
        add_log_to_queue( [
            'post_type' => 'prayer_global',
            'type' => 'praying',
            'subtype' => 'lap_completed',
            'time' => time(),
            'language_code' => get_locale(),
            'location' => [
                'ip' => get_ip_address_for_log(),
            ],
        ] );
    }
}, 10, 4 );

/**
 * dt_insert_report
 */
add_action('dt_insert_report', function( $args ) {
    if ( $args['post_type'] === 'laps' && $args['type'] === 'prayer_app' ) {
        // dt_write_log('dt_insert_report');
        $args['payload'] = unserialize( $args['payload'] );
        add_log_to_queue( [
            'post_type' => 'prayer_global',
            'type' => 'praying',
            'subtype' => 'prayer_for_location',
            'time' => time(),
            'language_code' => get_locale(),
            'location' => [
                'grid_id' => $args['grid_id'],
            ],
            'data' => [
                'location' => [
                    'ip' => get_ip_address_for_log(),
                ]
            ],
        ] );
    }
}, 10, 1 );

// trigger on all prayer pages
add_action('go_log_trigger', function() {
    $url = dt_get_url_path();
    if ( str_starts_with( $url, 'prayer_app/global' ) || str_starts_with( $url, 'prayer_app/custom' ) ) {

        add_log_to_queue( [
            'post_type' => 'prayer_global',
            'type' => 'praying',
            'subtype' => 'prayer_person_location',
            'time' => time(),
            'language_code' => get_locale(),
            'location' => [
                'ip' => get_ip_address_for_log(),
            ],
        ] );
    }
}, 10, 1 );
