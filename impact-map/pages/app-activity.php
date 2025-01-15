<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class GO_Impact_Map_Magic_Map_App extends DT_Magic_Url_Base
{
    public $magic = false;
    public $parts = false;
    public $page_title = 'Impact Activity';
    public $root = 'app';
    public $type = 'activity';
    public $type_name = 'Impact Activity';
    public static $token = 'app_activity';
    private $meta_key = '';

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        $this->meta_key = $this->root . '_' . $this->type . '_magic_key';
        parent::__construct();

        /**
         * post type and module section
         */
        add_action( 'rest_api_init', [ $this, 'add_endpoints' ] );

        /**
         * tests if other URL
         */
        $url = dt_get_url_path();
        if ( strpos( $url, $this->root . '/' . $this->type ) === false ) {
            return;
        }
        /**
         * tests magic link parts are registered and have valid elements
         */
        if ( !$this->check_parts_match( false ) ){
            return;
        }

        // load if valid url
        add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ], 99 );
        add_action( 'dt_blank_body', [ $this, 'body' ] ); // body for no post key
        add_filter( 'dt_magic_url_base_allowed_css', [ $this, 'dt_magic_url_base_allowed_css' ], 10, 1 );
        add_filter( 'dt_magic_url_base_allowed_js', [ $this, 'dt_magic_url_base_allowed_js' ], 10, 1 );
    }

    public function dt_magic_url_base_allowed_js( $allowed_js ) {
        $allowed_js[] = 'jquery-cookie';
        $allowed_js[] = 'mapbox-cookie';
        $allowed_js[] = 'mapbox-gl';
        $allowed_js[] = 'last100-hours-js';
        return $allowed_js;
    }

    public function dt_magic_url_base_allowed_css( $allowed_css ) {
        $allowed_css[] = 'mapbox-gl-css';
        $allowed_css[] = 'vite_bundle_css';
        return $allowed_css;
    }

    public function scripts() {
        // wp_enqueue_script( 'last100-hours-js', trailingslashit( plugin_dir_url( __DIR__ ) ) . 'maps/cluster-1-last100.js', [ 'jquery' ],
        // filemtime( trailingslashit( plugin_dir_path( __DIR__ ) ) .'maps/cluster-1-last100.js' ), true );
    }

    public function header_javascript(){
        ?>
        <script>
            let mapObject = [<?php echo json_encode([
                'map_key' => DT_Mapbox_API::get_key(),
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'parts' => $this->parts,
                'translation' => zume_map_translation_strings(),
            ]) ?>][0]
            /* <![CDATA[ */

            window.post_request = ( action, data ) => {
                return jQuery.ajax({
                    type: "POST",
                    data: JSON.stringify({ action: action, parts: mapObject.parts, data: data }),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: mapObject.root + mapObject.parts.root + '/v1/' + mapObject.parts.type,
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', mapObject.nonce )
                    }
                })
                    .fail(function(e) {
                        console.log(e)
                        jQuery('#error').html(e)
                        jQuery('.loading-spinner').removeClass('active')
                    })
            }

            let container = jQuery('#activity-list');
            function load_map_activity() {

                container.empty()
                let spinner = jQuery('.loading-spinner')
                spinner.addClass('active')
                let data = {} // get_filters()

                window.post_request('activity_list', data )
                .done( data => {

                    let spinner = jQuery('.loading-spinner')
                    console.log('loaded_map_activity')
                    console.log(data)
                    "use strict";
                    window.activity_list = data

                    jQuery.each( window.activity_list.list, function(i,v){
                        if ( '' === v.note ) {
                            return
                        }
                        container.append(`<li class="${v.type} ${v.country} ${v.language}"><strong>(${v.time})</strong> ${v.note} </li>`)
                    })

                    if ( ! window.activity_list.list  ) {
                        container.append(`<li><strong>${mapObject.translation.results}</strong> 0</li>`)
                    }

                    if ( window.activity_list.count > 250 ) {
                        container.append(`<hr><li><strong>${window.activity_list.count - 250} ${mapObject.translation.additional_records}</strong></li><br><br>`)
                    }

                    spinner.removeClass('active')
                })
            }
            load_map_activity()
        </script>
        <?php
    }

    public function header_style() {
        ?>
        <style>
            body {
                background: white !important;
            }
            #activity-list li {
              font-size:.8em;
              list-style-type: none;
            }
            #activity-list h2 {
                font-size:1.2em;
                font-weight:bold;
            }
            .center {
                text-align: center;
            }
        </style>
        <?php
    }

    public function footer_javascript(){}

    public function body(){
        ?>
        <div class="grid-x grid-padding-x align-center">
            <div class="cell small-6 center">
                <h1>Impact Activity</h1>
                <span class="loading-spinner active"></span>
            </div>
            <div class="cell medium-6">
                <div id="activity-wrapper">
                    <ul id="activity-list"></ul>
                </div>
            </div>
        </div>
        <?php
    }


    public function add_endpoints() {
        $namespace = $this->root . '/v1';
        register_rest_route(
            $namespace,
            '/'.$this->type,
            [
                [
                    'methods'  => WP_REST_Server::CREATABLE,
                    'callback' => [ $this, 'endpoint' ],
                    'permission_callback' => '__return_true',
                ],
            ]
        );
    }

    public function endpoint( WP_REST_Request $request ) {
        $params = $request->get_params();

        if ( ! isset( $params['parts'], $params['action'] ) ) {
            return new WP_Error( __METHOD__, 'Missing parameters', [ 'status' => 400 ] );
        }

        $params = dt_recursive_sanitize_array( $params );

        switch ( $params['action'] ) {
            case 'activity_list':
                return GO_Funnel_App_Heatmap::get_activity_list( $params['data'], true, 'en' );
            default:
                return new WP_Error( __METHOD__, 'Missing valid action parameters', [ 'status' => 400 ] );
        }
    }


}
GO_Impact_Map_Magic_Map_App::instance();
