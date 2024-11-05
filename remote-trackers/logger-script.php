<?php

add_action( 'wp_head', 'go_send_queue' );
function go_send_queue(){
    if ( ! empty( get_log_queue() ) ) {
        dt_write_log('send_queue');
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
                    .then((json) => console.log(json));
            }
        </script>
        <?php
    }
}
