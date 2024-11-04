<?php

class GO_Impact_Map_Logger
{
    public $log_url = 'https://goimpactmap.com/wp-json/gospel-ambition-impact-map/v1/endpoint';

    public $namespace = 'impact-map/v1';
    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/log', [
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
    public function authorize_url( $authorized )
    {
        if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->namespace ) !== false ) {
            $authorized = true;
        }
        return $authorized;
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
        add_filter( 'dt_allow_rest_access', [$this, 'authorize_url'], 10, 1 );
    }
}
GO_Impact_Map_Logger::instance();
