<?php

function get_log_queue() {
    return get_option( 'go_logger_queue' );
}
function add_log_to_queue( array $log_item ) {
    $list = get_log_queue();

    if ( ! is_array( $list ) ) {
        $list = [];
    }
    $list[] = $log_item;

    update_option( 'go_logger_queue', $list );

    dt_write_log( get_log_queue() );

}
function delete_log_queue() {
    return update_option( 'go_logger_queue', [] );
}
function get_ip_address_for_log() {
    $ip = '';
    if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) )   //check ip from share internet
    {
        $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
    } else if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )   //to check ip is pass from proxy
    {
        $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
    } else if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
        $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
    }

    $ip = apply_filters( 'get_real_ip_address', $ip );

    return filter_var( $ip, FILTER_VALIDATE_IP );
}
