<?php

class GO_Impact_Send_Queue
{
    public $namespace = 'impact-map/v1';
    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/send_queue', [
                'methods'  => 'POST',
                'callback' => [ $this, 'send_queue' ],
                'permission_callback' => '__return_true',
            ]
        );
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
    public static function send_queue() {
        $logger_url = 'https://goimpactmap.com/wp-json/gospel-ambition-impact-map/v1/endpoint';

        $queue = get_log_queue();
        delete_log_queue();

        $json_body = [ 'method' => 'POST', 'body' => $queue ];

        $body = json_decode( wp_remote_retrieve_body( wp_remote_post( $logger_url, $json_body ) ), true );

        dt_write_log(__METHOD__ . ': POST');
        dt_write_log( $body );

        return $body;
    }
}
GO_Impact_Send_Queue::instance();
