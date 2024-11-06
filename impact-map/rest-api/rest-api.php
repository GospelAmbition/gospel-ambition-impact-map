<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class GO_Impact_Map_Endpoints
{
    private static $_instance = null;
    public $namespace = 'gospel-ambition-impact-map/v1';
    public function add_api_routes() {
        $namespace = $this->namespace;

        register_rest_route(
            $namespace, '/endpoint', [
                'methods'  => 'POST',
                'callback' => [ $this, 'endpoint' ],
                'permission_callback' => '__return_true',
            ]
        );
    }
    public function endpoint( WP_REST_Request $request ) {
        $logs = dt_recursive_sanitize_array( $request->get_params() );

        if ( ! is_array( $logs ) ) {
            return false;
        }

        $current_time = time();
        foreach( $logs as $i => $v ) {
            $logs[$i]['time'] = $current_time + ( $current_time - (int) $v['time'] );
        }

        foreach( $logs as $i => $v ) {
            if ( isset( $v['location'] ) && empty( $v['location'] ) ) {
                // todo
            }
            else if ( $v['ip'] ) {
                // todo
            }
        }

        $needs_location = [];

        // time adjustment

        // if has grid id
        // if has lng/lat
        // if has ip


        foreach( $logs as $i => $v ) {
            GO_Impact_Map_Insert::insert( $v );
        }

        return true;
    }

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
    public function authorize_url( $authorized )
    {
        if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->namespace ) !== false ) {
            $authorized = true;
        }
        return $authorized;
    }
}
GO_Impact_Map_Endpoints::instance();

