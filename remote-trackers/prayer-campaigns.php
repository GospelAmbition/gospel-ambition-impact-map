<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

add_action('go_log_trigger', function( $keys ) {
    $url = dt_get_url_path();

    // trigger on all prayer pages
    if ( str_contains( $url, 'fuel' ) ) {
        // dt_write_log('prayer_person_location');
        add_log_to_queue( [
            'post_type' => 'prayer_tools',
            'type' => 'praying',
            'subtype' => 'actively_praying',
            'time' => time(),
            'language_code' => get_locale(),
            'location' => [
                'ip' => get_ip_address_for_log(),
            ],
        ] );
    }

    return $keys;

}, 10, 1 );

/**
 * wp_insert_post
 */
add_action( 'wp_insert_post', function( $post_ID, $post, $update ) {
    if ( ! $update && 'subscriptions' === $post->post_type ) {
        // dt_write_log('wp_insert_post');
        add_log_to_queue( [
            'post_type' => 'prayer_tools',
            'type' => 'praying',
            'subtype' => 'pt_registered',
            'time' => time(), // time
            'language_code' => get_locale(), // language
            'location' => [
                'ip' => get_ip_address_for_log(),
            ],
        ] );
    }
}, 10, 3 );


/**
 * dt_insert_report
 */
add_action('dt_insert_report', function( $args ) {
    if ( $args['post_type'] === 'subscriptions' && $args['type'] === 'recurring_signup' ) {

        $payload = maybe_unserialize( $args['payload'] );

        $title = '';
        if ( class_exists( 'DT_Campaign_Landing_Settings' ) ) {
            $campaign_fields = DT_Campaign_Landing_Settings::get_campaign();
            dt_write_log( $campaign_fields );

            $title = $campaign_fields['title'] ?? '';
        }

        if ( isset( $payload['selected_times'] ) ) {
            foreach( $payload['selected_times'] as $time ) {
                add_log_to_queue( [
                    'post_type' => 'prayer_tools',
                    'type' => 'praying',
                    'subtype' => 'recurring_signup',
                    'time' => $time['time'],
                    'language_code' => get_locale(),
                    'location' => [
                        'ip' => get_ip_address_for_log(),
                    ],
                    'data' => [
                        'title' => $title,
                    ],
                ] );
            }
        }
    }
}, 10, 1 );
