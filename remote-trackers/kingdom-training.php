<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

// log registrations
add_action( 'user_register', function( $user_id ) {
    if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'signup' ) ) {
        add_log_to_queue([
            'post_type' => 'kingdom_training',
            'type' => 'training',
            'subtype' => 'kt_registered',
            'time' => time(), // time
            'language_code' => get_locale(), // language
            'location' => [
                'ip' => get_ip_address_for_log(),
            ],
        ]);
        dt_write_log('registered');
    }
});
// log courses completed
add_action( "learndash_course_completed", function ( $data ) {
    $user_id   = $data["user"]->ID;
    $course_id = $data["course"]->ID;

    add_log_to_queue([
        'post_type' => 'kingdom_training',
        'type' => 'training',
        'subtype' => 'course_completed',
        'time' => time(), // time
        'language_code' => get_locale(), // language
        'location' => [
            'ip' => get_ip_address_for_log(),
        ],
        'data' => [
            'title' => get_the_title( $course_id ),
        ],
    ]);
    dt_write_log('course completed');
    dt_write_log( get_the_title( $course_id ) );
    dt_write_log( $course_id );

}, 5, 1 );
// log course completed
add_action( "learndash_lesson_completed", function ( $data ) {
    $user_id   = $data["user"]->ID;
    $course_id = $data["course"]->ID;
    $lesson_id = $data["lesson"]->ID;

    add_log_to_queue([
        'post_type' => 'kingdom_training',
        'type' => 'training',
        'subtype' => 'lesson_completed',
        'time' => time(), // time
        'language_code' => get_locale(), // language
        'location' => [
            'ip' => get_ip_address_for_log(),
        ],
        'data' => [
            'title' => get_the_title( $lesson_id ),
            'course' => get_the_title( $course_id ),
        ],
    ]);
    dt_write_log('lesson completed');
    dt_write_log( get_the_title( $lesson_id ) );
    dt_write_log( get_the_title( $course_id ) );
    dt_write_log( $lesson_id );

}, 5, 1 );
// log coaching requests

