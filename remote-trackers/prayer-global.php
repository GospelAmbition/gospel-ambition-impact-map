<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.
/*****************************
 * HOOKS
 *****************************/


//disable the ajax trigger for the logged
//replace with cron on Prayer.Global
add_filter( 'go_impact_map_disable_logger', '__return_true' );

add_filter( 'cron_schedules', function ( $schedules ) {
    $schedules['1min'] = array(
        'interval' => 60,
        'display'  => __( 'Once every minute' )
    );
    return $schedules;
} );

if ( ! wp_next_scheduled( 'pg_send_reports' ) ) {
    wp_schedule_event( time(), '1min', 'pg_send_reports' );
}
add_action( 'pg_send_reports', 'pg_reports_go_map_cron_send' );

/**
 * When a user registers, log the event
 */
add_action( 'wp_insert_post', function( $post_ID, $post, $update ) {
    if ( ! $update && 'contacts' === $post->post_type ) {
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
 * When a lap is completed, log the event
 */
add_action( 'updated_post_meta', function( $meta_id, $object_id, $meta_key, $meta_value, $new = false, $deleted = false ){
    //@todo add to update-location.php
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




function pg_reports_go_map_cron_send(){
    if ( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) || home_url() !== 'https://prayer.global' ) {
        return; //only run on prayer.global
    }
    global $wpdb;
    $last_id_sent = get_option( 'go_logger_last_sent', 0 );
    if ( empty( $last_id_sent ) ) {
        $last_id_sent = $wpdb->get_var( "SELECT MAX(id) FROM $wpdb->dt_reports WHERE post_type = 'pg_relays' and type = 'prayer_app'" );
    }
    //get 100 oldest reports since last sent
    $reports = $wpdb->get_results( $wpdb->prepare(
        "SELECT * FROM $wpdb->dt_reports
        WHERE post_type = 'pg_relays'
        and type = 'prayer_app'
        AND id > %d
        ORDER BY id ASC
        LIMIT 100
    ", $last_id_sent ), ARRAY_A );

    $data = [];
    foreach ( $reports as $report ){
        $payload = maybe_unserialize( $report['payload'] );

        /**
         * report for the locations prayer for
         */
        $data[] = [
            'post_type' => 'prayer_global',
            'type' => 'praying',
            'subtype' => 'prayer_for_location',
            'time' => $report['timestamp'],
            'language_code' => $payload['user_language'] ?? 'en_US',
            'location' => [
                'grid_id' => $report['grid_id'],
            ],
            'data' => [
                'location' => [
                    'lat' => $report['lat'],
                    'lng' => $report['lng'],
                ]
            ],
        ];

        /**
         * report for the people who prayed
         */
        $data[] = [
            'post_type' => 'prayer_global',
            'type' => 'praying',
            'subtype' => 'prayer_person_location',
            'time' => $report['timestamp'],
            'language_code' => $payload['user_language'] ?? 'en_US',
            'location' => [
                'lat' => $report['lat'],
                'lng' => $report['lng'],
            ],
        ];
    }

    //update the last sent id
    $last_id_sent = $reports ? $reports[ count( $reports ) - 1 ]['id'] : $last_id_sent;
    update_option( 'go_logger_last_sent', $last_id_sent );


    if ( !empty( $data ) ) {
        GO_Impact_Map_Queue::send_data( $data );
    }
}
