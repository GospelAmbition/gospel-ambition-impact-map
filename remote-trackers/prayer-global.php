<?php

/**
 *
 */
//add_filter('go_log_trigger', function( $keys ) {
//
////    $url = dt_get_url_path();
////    if ( str_starts_with( $url, 'prayer_app/global' ) || str_starts_with( $url, 'prayer_app/custom' ) ) {
////
////        dt_write_log('go_log_trigger of prayer_app/global');
////
////    }
//
//    return $keys;
//
//}, 10, 1 );

/**
 *
 */
add_action( 'wp_insert_post', function( $post_ID, $post, $update ) {
    if ( ! $update && 'contacts' === $post->post_type ) {
        dt_write_log('wp_insert_post');
        add_log_to_queue( [ 'post_type' => 'prayer_global', 'subtype' => 'registered', 'time' => time() ] );
    }
}, 10, 3 );

/**
 *
 */
add_action( 'updated_post_meta',  function( $meta_id, $object_id, $meta_key, $meta_value, $new = false, $deleted = false ){
    if ( $meta_key === 'status' && $meta_value === 'complete' ) {
        dt_write_log('updated_post_meta');
        add_log_to_queue( [ 'post_type' => 'prayer_global', 'subtype' => 'lap_completed', 'time' => time() ] );
    }
}, 10, 4 );

/**
 *
 */
add_action('dt_insert_report', function( $args ) {
    if ( $args['post_type'] === 'laps' && $args['type'] === 'prayer_app' ) {
        dt_write_log('dt_insert_report');
        add_log_to_queue( [ 'post_type' => 'prayer_global', 'subtype' => 'prayer_for_location', 'time' => time(), 'args' => $args ] );
    }
} );
