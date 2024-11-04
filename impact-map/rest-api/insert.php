<?php

class GO_Impact_Map_Insert
{
    public static function insert( $params ) {

        dt_write_log( __METHOD__ );
        dt_write_log($params);

        return 'Success : ' . __METHOD__;
    }
}
