<?php

add_action( 'wp_head', 'gospel_ambition_coder_invitation' );
function gospel_ambition_coder_invitation(){
    ?>
    <script>
        window.addEventListener("load", log_go_impact_map);
        function log_go_impact_map() {
            console.log('system loaded')
            let rest_url = '<?php echo rest_url() ?>'
            fetch( rest_url + 'impact-map/v1/log', {
                method: "POST",
                body: JSON.stringify({
                    userId: 1,
                    title: "Fix my bugs",
                    completed: false
                }),
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

class GO_Impact_Map_Logger
{
    public $log_url = 'https://goimpactmap.com/wp-json/gospel-ambition-impact-map/v1/endpoint';
    public function add_api_routes() {
        $namespace = 'impact-map/v1';

        register_rest_route(
            $namespace, '/log', [
                'methods'  => 'GET, POST',
                'callback' => [ $this, 'log' ],
                'permission_callback' => '__return_true',
            ]
        );
    }
    public function log( WP_REST_Request $request ) {
        $params = dt_recursive_sanitize_array( $request->get_params() );

        dt_write_log(__METHOD__);

        $array = [
            'ip_address' => '',
            'log_key' => ''
        ];

        return wp_remote_get( $this->log_url, $array );
    }
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }
}
GO_Impact_Map_Logger::instance();
