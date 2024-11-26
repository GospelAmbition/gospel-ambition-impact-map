<?php 
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

/**
 * updated_post_meta
 */
// add_action( 'updated_post_meta',  function( $meta_id, $object_id, $meta_key, $meta_value, $new = false, $deleted = false ){
//     dt_write_log('updated_post_meta');
//     dt_write_log($meta_key);
//     dt_write_log($meta_value);
//     if ( $meta_key === 'status' && $meta_value === 'complete' ) {
//         dt_write_log('updated_post_meta');
//         add_log_to_queue( [
//             'post_type' => 'zume',
//             'type' => 'praying',
//             'subtype' => 'lap_completed',
//             'time' => time(),
//             'language_code' => get_locale(),
//             'location' => [
//                 'ip' => get_ip_address_for_log(),
//             ],
//         ] );
//     }
// }, 10, 4 );