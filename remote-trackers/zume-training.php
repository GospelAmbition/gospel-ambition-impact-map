<?php 
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

// build option array with log events
add_action( 'zume_verify_encouragement_plan',  function( $user_id, $type, $subtype ){

    dt_write_log( 'zume_verify_encouragement_plan' );

    $profile = zume_get_user_profile( $user_id );

    add_log_to_queue( [
        'post_type' => 'zume',
        'type' => $type,
        'subtype' => $subtype,
        'time' => time(),
        'language_code' => $profile['language']['code'] ?? 'en',
        'location' => $profile['location'] ?? ['ip' => get_ip_address_for_log()],
    ] );

}, 10, 4 );