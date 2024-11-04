<?php


add_filter('go_log_trigger', function( $keys ) {
    if ( 'test_key' ) {
        $keys[] = 'test_key';
    }

    $keys[] = 'another_test_key';

    return $keys;
}, 10, 1 );
