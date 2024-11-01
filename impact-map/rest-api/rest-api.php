<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class GO_Impact_Map_Endpoints
{
    public $namespace = 'gospel-ambition-impact-map/v1';
    public function add_api_routes() {
        $namespace = $this->namespace;

        register_rest_route(
            $namespace, '/endpoint', [
                'methods'  => 'GET, POST',
                'callback' => [ $this, 'endpoint' ],
                'permission_callback' => '__return_true',
            ]
        );

    }

    public function endpoint( WP_REST_Request $request ) {

        // @todo run your function here

        dt_write_log(__METHOD__);

        return true;
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
    } // End instance()
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
        add_filter( 'dt_allow_rest_access', [$this, 'authorize_url'], 100, 1 );
    }
}
GO_Impact_Map_Endpoints::instance();

