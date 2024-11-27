<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class GO_Impact_Map_Cron {

    public function __construct() {
        if ( ! wp_next_scheduled( 'send_queue' ) ) {
            wp_schedule_event( time(), 'hourly', 'send_queue' );
        }
        add_action( 'send_queue', array( $this, 'action' ) );
    }

    public function action(){
        dt_write_log(get_bloginfo( 'name' ) . ' ' . __METHOD__);
        GO_Impact_Send_Queue::send_queue();
    }

}
new GO_Impact_Map_Cron();