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
