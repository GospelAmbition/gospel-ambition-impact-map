<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

add_action( 'wp_head', 'go_url_logger' );
// add_action( 'zume_head', 'go_url_logger' );
function go_url_logger(){
    //filter to conditionally disable the logger
    if ( apply_filters( 'go_impact_map_disable_logger', false ) ) {
        return;
    }
    // page load logger
    do_action( 'go_log_trigger' );

    // lazy load queue
    if ( ! empty( GO_Impact_Map_Queue::get_queue() ) ) {
        // dt_write_log('send_queue');
        ?>
        <script>
            window.addEventListener("load", log_go_impact_map);
            function log_go_impact_map() {
                fetch( '<?php echo esc_url( rest_url() ) ?>impact-map/v1/send_queue', {
                    method: "POST",
                    headers: {
                        "Content-type": "application/json; charset=UTF-8"
                    }
                })
                    .then((response) => response.json())
            }
        </script>
        <?php
    }
}