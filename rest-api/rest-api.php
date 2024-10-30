<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class GA_Impact_Map_Endpoints
{
    public function add_api_routes() {
        $namespace = 'gospel-ambition-impact-map/v1';

        register_rest_route(
            $namespace, '/endpoint', [
                'methods'  => 'GET',
                'callback' => [ $this, 'endpoint' ],
                'permission_callback' => '__return_true',
            ]
        );

        self::add_cors_sites();
    }

    public static function add_cors_sites() {
        add_filter( 'rest_pre_serve_request', function( $value ) {
            header( 'Access-Control-Allow-Origin: ' . get_http_origin() );
            header( 'Access-Control-Allow-Methods: GET, POST, HEAD, OPTIONS' );
            header( 'Access-Control-Allow-Credentials: true' );
            header( 'Access-Control-Expose-Headers: Link', false );
            header( 'Access-Control-Allow-Headers: X-WP-Nonce', false );

            return $value;
        } );
    }

    public function endpoint( WP_REST_Request $request ) {

        // @todo run your function here

        dt_write_log(__METHOD__);

        return true;
    }

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
    }
}
GA_Impact_Map_Endpoints::instance();
