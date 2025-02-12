<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class GO_Impact_Map_App_Template extends DT_Magic_Url_Base
{
    public $magic = false;
    public $parts = false;
    public $page_title = 'Gospel Ambition Impact Map';
    public $root = 'app';
    public $type = 'map_template';
    public $type_name = 'Gospel Ambition Impact Map';
    public static $token = 'app_map_template';

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()

    public function __construct() {
        parent::__construct();

        $url = dt_get_url_path();
        if ( ( $this->root . '/' . $this->type ) === $url ) {

            $this->magic = new DT_Magic_URL( $this->root );
            $this->parts = $this->magic->parse_url_parts();

            // register url and access
            add_action( 'template_redirect', [ $this, 'theme_redirect' ] );
            add_filter( 'dt_blank_access', function (){ return true;
            }, 100, 1 );
            add_filter( 'dt_allow_non_login_access', function (){ return true;
            }, 100, 1 );
            add_filter( 'dt_override_header_meta', function (){ return true;
            }, 100, 1 );

            // header content
            add_filter( 'dt_blank_title', [ $this, 'page_tab_title' ] ); // adds basic title to browser tab
            add_action( 'wp_print_scripts', [ $this, 'print_scripts' ], 1500 ); // authorizes scripts
            add_action( 'wp_print_styles', [ $this, 'print_styles' ], 1500 ); // authorizes styles


            // page content
            add_action( 'dt_blank_head', [ $this, '_header' ] );
            add_action( 'dt_blank_footer', [ $this, '_footer' ] );
            add_action( 'dt_blank_body', [ $this, 'body' ] ); // body for no post key

            add_filter( 'dt_magic_url_base_allowed_css', [ $this, 'dt_magic_url_base_allowed_css' ], 10, 1 );
            add_filter( 'dt_magic_url_base_allowed_js', [ $this, 'dt_magic_url_base_allowed_js' ], 10, 1 );
            add_action( 'wp_enqueue_scripts', [ $this, '_wp_enqueue_scripts' ], 100 );
        }

        if ( dt_is_rest() ) {
            add_action( 'rest_api_init', [ $this, 'add_endpoints' ] );
            add_filter( 'dt_allow_rest_access', [ $this, 'authorize_url' ], 10, 1 );
        }
    }

    public function dt_magic_url_base_allowed_js( $allowed_js ) {
        $allowed_js[] = 'jquery-touch-punch';
        $allowed_js[] = 'mapbox-gl';
        $allowed_js[] = 'jquery-cookie';
        $allowed_js[] = 'mapbox-cookie';
        $allowed_js[] = 'heatmap-js';
        return $allowed_js;
    }

    public function dt_magic_url_base_allowed_css( $allowed_css ) {
        $allowed_css[] = 'mapbox-gl-css';
        $allowed_css[] = 'introjs-css';
        $allowed_css[] = 'heatmap-css';
        $allowed_css[] = 'site-css';
        return $allowed_css;
    }

    public function header_javascript(){
        ?>
        <script>
            let jsObject = [<?php echo json_encode([
                'map_key' => DT_Mapbox_API::get_key(),
                'ipstack' => DT_Ipstack_API::get_key(),
                'mirror_url' => dt_get_location_grid_mirror( true ),
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'parts' => $this->parts,
                'translations' => [
                    'add' => __( 'Add Magic', 'gospel-ambition-impact-map' ),
                ],
            ]) ?>][0]
            jQuery(document).ready(function(){

                /* set vertical size the form column*/
                jQuery('#custom-style').append(`
                    <style>
                        #map-wrapper {
                            height: ${window.innerHeight}px !important;
                        }
                        #map {
                            height: ${window.innerHeight}px !important;
                        }
                    </style>`)

                mapboxgl.accessToken = jsObject.map_key;
                const map = new mapboxgl.Map({
                    container: 'map',
                    style: 'mapbox://styles/mapbox/streets-v9',
                    projection: 'globe', // Display the map as a globe, since satellite-v9 defaults to Mercator
                    zoom: 3,
                    center: [30, 15]
                });

                map.addControl(new mapboxgl.NavigationControl());
                map.scrollZoom.disable();

                map.on('style.load', () => {
                    map.setFog({}); // Set the default atmosphere style
                });

                // The following values can be changed to control rotation speed:

                // At low zooms, complete a revolution every two minutes.
                const secondsPerRevolution = 240;
                // Above zoom level 5, do not rotate.
                const maxSpinZoom = 5;
                // Rotate at intermediate speeds between zoom levels 3 and 5.
                const slowSpinZoom = 3;

                let userInteracting = false;
                const spinEnabled = true;

                function spinGlobe() {
                    const zoom = map.getZoom();
                    if (spinEnabled && !userInteracting && zoom < maxSpinZoom) {
                        let distancePerSecond = 360 / secondsPerRevolution;
                        if (zoom > slowSpinZoom) {
                            // Slow spinning at higher zooms
                            const zoomDif =
                                (maxSpinZoom - zoom) / (maxSpinZoom - slowSpinZoom);
                            distancePerSecond *= zoomDif;
                        }
                        const center = map.getCenter();
                        center.lng -= distancePerSecond;
                        // Smoothly animate the map over one second.
                        // When this animation is complete, it calls a 'moveend' event.
                        map.easeTo({ center, duration: 1000, easing: (n) => n });
                    }
                }

                // Pause spinning on interaction
                map.on('mousedown', () => {
                    userInteracting = true;
                });
                map.on('dragstart', () => {
                    userInteracting = true;
                });

                // When animation is complete, start spinning if there is no ongoing interaction
                map.on('moveend', () => {
                    spinGlobe();
                });

                spinGlobe();


                window.get_geojson().then(function(data){
                    map.addSource('layer-source', {
                            type: 'geojson',
                            data: data
                        });

                        map.addLayer({
                            id: 'circle-layer',
                            type: 'circle',
                            source: 'layer-source',
                            paint: {
                                'circle-color': '#00d9ff',
                                'circle-radius':12,
                                'circle-stroke-width': 1,
                                'circle-stroke-color': '#fff'
                            }
                        });

                })

            });

            window.get_geojson = () => {
                return jQuery.ajax({
                    type: "POST",
                    data: JSON.stringify({ action: 'geojson', parts: jsObject.parts }),
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    url: jsObject.root + jsObject.parts.root + '/v1/' + jsObject.parts.type,
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', jsObject.nonce )
                    }
                })
                    .fail(function(e) {
                        console.log(e)
                        jQuery('#error').html(e)
                    })
            }
        </script>
        <?php
    }



    public function header_style() {
        impact_map_css_map_site_css_php();
    }

    public function footer_javascript(){}

    public function body(){
        // DT_Mapbox_API::geocoder_scripts();
        ?>
        <link href="https://api.mapbox.com/mapbox-gl-js/v3.9.4/mapbox-gl.css" rel="stylesheet">
        <script src="https://api.mapbox.com/mapbox-gl-js/v3.9.4/mapbox-gl.js"></script>
        <?php
        impact_map_top();
        ?>
        <style id="custom-style"></style>
        <div id="map-wrapper">
            <div id='map'></div>
        </div>
        <?php
    }

    public static function _wp_enqueue_scripts(){
        DT_Mapbox_API::load_mapbox_header_scripts();
    }

    /**
     * Register REST Endpoints
     * @link https://github.com/DiscipleTools/disciple-tools-theme/wiki/Site-to-Site-Link for outside of wordpress authentication
     */
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
            case 'geojson':
                return $this->endpoint_geojson( $params['parts'] );
            default:
                return new WP_Error( __METHOD__, 'Missing valid action parameters', [ 'status' => 400 ] );
        }
    }

    public function endpoint_geojson( $parts ) {
        global $wpdb;

        $results = $wpdb->get_results(
        "SELECT * FROM $wpdb->dt_location_grid WHERE level = 0", ARRAY_A );

        if ( empty( $results ) ) {
            return $this->_empty_geojson();
        }

        $features = [];
        foreach ( $results as $result ) {
            $features[] = array(
                'type' => 'Feature',
                'properties' => array(
                    'grid_id' => $result['grid_id'],
                    'name' => $result['name'],
                    'value' => rand( 1, 10 ) // random value
                ),
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array(
                        (float) $result['longitude'],
                        (float) $result['latitude'],
                        1
                    ),
                ),
            );
        }

        $geojson = array(
            'type' => 'FeatureCollection',
            'features' => $features,
        );

        return $geojson;
    }

    private function _empty_geojson() {
        return array(
            'type' => 'FeatureCollection',
            'features' => array()
        );
    }
}
GO_Impact_Map_App_Template::instance();
