<?php

class GO_Impact_Map_Insert
{
    public static function insert( $log ) {

        dt_write_log( __METHOD__ );
        dt_write_log($log);

        return true;
    }
}
