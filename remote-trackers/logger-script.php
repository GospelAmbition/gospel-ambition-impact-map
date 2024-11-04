<?php

add_action( 'wp_head', 'gospel_ambition_coder_invitation' );
function gospel_ambition_coder_invitation(){
    $movement_keys =  apply_filters( 'go_log_trigger', [] );
    if ( ! empty( $movement_keys ) ) {
        ?>
        <script>
            window.addEventListener("load", log_go_impact_map);
            function log_go_impact_map() {
                fetch( '<?php echo esc_url( rest_url() ) ?>impact-map/v1/log', {
                    method: "POST",
                    body: JSON.stringify( <?php echo json_encode( $movement_keys ) ?> ),
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
