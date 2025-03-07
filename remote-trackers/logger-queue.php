<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

function get_log_queue() {
    return GO_Impact_Map_Queue::get_queue();
}
function add_log_to_queue( array $log_item ) {
    return GO_Impact_Map_Queue::add_to_queue( $log_item );
}
function get_ip_address_for_log() {
    return GO_Impact_Map_Queue::_get_ip_address_for_log();
}

class GO_Impact_Map_Queue {

    public static function get_queue() {
        return get_post_meta( self::_get_queue_id(), 'log_queue' );
    }
    public static function add_to_queue( $log_item ) {
        return add_post_meta( self::_get_queue_id(), 'log_queue', $log_item, false );
    }
    public static function delete_queue() {
        return delete_post_meta( self::_get_queue_id(), 'log_queue' );
    }
    public static function send_queue() {
        // dt_write_log(get_bloginfo( 'name' ) . ' ' . __METHOD__);

        $logger_url = 'https://goimpactmap.com/wp-json/gospel-ambition-impact-map/v1/endpoint';

        $queue = self::get_queue();
        if ( empty( $queue ) ) {
            return;
        }
        self::delete_queue();

        $json_body = [ 'method' => 'POST', 'body' => $queue ];

        $body = json_decode( wp_remote_retrieve_body( wp_remote_post( $logger_url, $json_body ) ), true );

        return $body;
    }

    public static function send_data( $data ) {
        $logger_url = 'https://goimpactmap.com/wp-json/gospel-ambition-impact-map/v1/endpoint';

        $json_body = [ 'method' => 'POST', 'body' => $data ];

        $body = json_decode( wp_remote_retrieve_body( wp_remote_post( $logger_url, $json_body ) ), true );

        return $body;
    }

    /** QUEUE STORAGE */
    public static function _get_queue_id() {
        $queue_id = get_option( 'go_logger_queue_id' );
        if ( ! $queue_id ) {
            $queue_id = self::_setup_queue();
        }
        return (int) $queue_id;
    }
    public static function _setup_queue() {
        global $wpdb;
        $queue_id = 0;

        // insert special queue post type record
        $wpdb->insert(
            $wpdb->prefix . 'posts',
            [
                'post_author' => 1,
                'post_date' => current_time( 'mysql' ),
                'post_date_gmt' => current_time( 'mysql', 1 ),
                'post_content' => '',
                'post_title' => 'Log Queue',
                'post_status' => 'publish',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_name' => 'log_queue',
                'post_type' => 'log_queue',
            ]
        );
        $queue_id = $wpdb->insert_id;

        // store id to option table
        update_option( 'go_logger_queue_id', $queue_id );

        return $queue_id;
    }
    public static function _get_ip_address_for_log() {
        $ip = '';
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) )   //check ip from share internet
        {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
        }
        else if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )   //to check ip is pass from proxy
        {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
        }
        else if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }

        $ip = apply_filters( 'get_real_ip_address', $ip );

        return filter_var( $ip, FILTER_VALIDATE_IP );
    }

    /** REST ROUTES */
    public $namespace = 'impact-map/v1';
    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
        add_filter( 'dt_allow_rest_access', [ $this, 'authorize_url' ], 10, 1 );
    }
    public function add_api_routes() {
        register_rest_route(
            $this->namespace, '/send_queue', [
                'methods'  => 'POST',
                'callback' => [ $this, 'send_queue' ],
                'permission_callback' => '__return_true',
            ]
        );
    }
    public function authorize_url( $authorized ) {
        if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->namespace ) !== false ) {
            $authorized = true;
        }
        return $authorized;
    }
}
GO_Impact_Map_Queue::instance();
