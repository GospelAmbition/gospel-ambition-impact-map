<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class GO_Impact_Map_Globe extends DT_Magic_Url_Base
{
    public $magic = false;
    public $parts = false;
    public $page_title = 'Gospel Ambition Impact Map';
    public $root = 'app';
    public $type = 'globe';
    public $type_name = 'Gospel Ambition Impact Map';
    public static $token = 'app_globe';

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
            window.activity_list = {}
            window.activity_geojson = {
                "type": "FeatureCollection",
                "features": []
            }
            window.activity_geojson_praying = {
                "type": "FeatureCollection",
                "features": []
            }
            window.activity_geojson_studying = {
                "type": "FeatureCollection",
                "features": []
            }
            window.activity_geojson_training = {
                "type": "FeatureCollection",
                "features": []
            }
            window.activity_geojson_practicing = {
                "type": "FeatureCollection",
                "features": []
            }
            window.activity_geojson_coaching = {
                "type": "FeatureCollection",
                "features": []
            }
            window.color_praying = '#FF3131'
            window.color_studying = '#FFBF00'
            window.color_training = '#98FB98'
            window.color_practicing = '#4CBB17'
            window.color_coaching = '#355E3B'

            window.praying_count = 0
            window.studying_count = 0
            window.training_count = 0
            window.practicing_count = 0
            window.coaching_count = 0

            jQuery(document).ready(function(){

                /* set vertical size the form column*/
                jQuery('#custom-style').append(`
                    <style>
                        #map-wrapper {
                            height: ${window.innerHeight - 50}px !important;
                        }
                        #map {
                            height: ${window.innerHeight - 50}px !important;
                        }
                        #legend {
                            z-index: 1;
                            position: absolute;
                            top: 60px;
                            left: 10px;
                            width: 200px;
                            background-color: white;
                            padding: 20px;
                        }
                         .color-block.praying {
                            background-color: ${window.color_praying};
                            width: 20px;
                            height: 20px;
                            float: left;
                            margin-right: 5px;
                        }
                        .color-block.studying {
                            background-color: ${window.color_studying};
                            width: 20px;
                            height: 20px;
                            float: left;
                            margin-right: 5px;
                        }
                        .color-block.training {
                            background-color: ${window.color_training};
                            width: 20px;
                            height: 20px;
                            float: left;
                            margin-right: 5px;
                        }
                        .color-block.practicing {
                            background-color: ${window.color_practicing};
                            width: 20px;
                            height: 20px;
                            float: left;
                            margin-right: 5px;
                        }
                        .color-block.coaching {
                            background-color: ${window.color_coaching};
                            width: 20px;
                            height: 20px;
                            float: left;
                            margin-right: 5px;
                        }
                    </style>`)

                mapboxgl.accessToken = 'pk.eyJ1IjoiY2hyaXNjaGFzbSIsImEiOiJjajZyc2poNmEwZTdqMnFuenB0ODI5dWduIn0.6wKrDTf2exQJY-MY7Q1kRQ';
                const map = new mapboxgl.Map({
                    container: 'map',
                    style: 'mapbox://styles/mapbox/streets-v9',
                    projection: 'globe', // Display the map as a globe, since satellite-v9 defaults to Mercator
                    zoom: 3,
                    center: [30, 15]
                });

                // map.addControl(new mapboxgl.NavigationControl());
                // map.scrollZoom.disable();

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
                map.on('zoomend', () => {
                    spinGlobe();
                });

                spinGlobe();


                window.get_geojson().then(function(data){
                    window.activity_geojson = data
                    data.features.forEach( (v) => {
                    if ( 'praying' === v.properties.type ) {
                        window.activity_geojson_praying.features.push(v)
                        window.praying_count++
                    }
                    else if ( 'studying' === v.properties.type ) {
                        v.geometry.coordinates[0] = v.geometry.coordinates[0] + 0.002 // layer shift so that they don't overlap
                        window.activity_geojson_studying.features.push(v)
                        window.studying_count++
                    }
                    else if ( 'training' === v.properties.type ) {
                        v.geometry.coordinates[0] = v.geometry.coordinates[0] - 0.002 // layer shift so that they don't overlap
                        window.activity_geojson_training.features.push(v)
                        window.training_count++
                    }
                    else if ( 'practicing' === v.properties.type ) {
                        v.geometry.coordinates[1] = v.geometry.coordinates[1] + 0.002 // layer shift so that they don't overlap
                        window.activity_geojson_practicing.features.push(v)
                        window.practicing_count++
                    }
                    else if ( 'coaching' === v.properties.type ) {
                        v.geometry.coordinates[1] = v.geometry.coordinates[1] - 0.002 // layer shift so that they don't overlap
                        window.activity_geojson_coaching.features.push(v)
                        window.coaching_count++
                    }
                    })

                    jQuery('#legend_praying').html(window.praying_count)
                    jQuery('#legend_studying').html(window.studying_count)
                    jQuery('#legend_training').html(window.training_count)

                    // full geojson source
                    map.addSource('layer-source-geojson', {
                    type: 'geojson',
                    data: window.activity_geojson,
                    cluster: true,
                    clusterMaxZoom: 20,
                    clusterRadius: 50
                    });

                    // prayer geojson source and cluster
                    map.addSource('layer-source-geojson-praying', {
                    type: 'geojson',
                    data: window.activity_geojson_praying,
                    cluster: true,
                    clusterMaxZoom: 20,
                    clusterRadius: 50
                    });
                    map.addLayer({
                    id: 'clusters-praying',
                    type: 'circle',
                    source: 'layer-source-geojson-praying',
                    filter: ['has', 'point_count'],
                    paint: {
                        'circle-color': [
                        'step',
                        ['get', 'point_count'],
                        window.color_praying,
                        20,
                        window.color_praying,
                        150,
                        window.color_praying
                        ],
                        'circle-radius': [
                        'step',
                        ['get', 'point_count'],
                        20,
                        100,
                        30,
                        750,
                        40
                        ]
                    }
                    });
                    map.addLayer({
                    id: 'cluster-count-praying',
                    type: 'symbol',
                    source: 'layer-source-geojson-praying',
                    filter: ['has', 'point_count'],
                    layout: {
                        'text-field': '{point_count_abbreviated}',
                        'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
                        'text-size': 12
                    }
                    });
                    map.addLayer({
                    id: 'unclustered-point-prayer',
                    type: 'circle',
                    source: 'layer-source-geojson-praying',
                    filter: ['!', ['has', 'point_count'] ],
                    paint: {
                        'circle-color': window.color_praying,
                        'circle-radius':12,
                        'circle-stroke-width': 1,
                        'circle-stroke-color': '#fff'
                    }
                    });


                    // studying geojson source and cluster
                    map.addSource('layer-source-geojson-studying', {
                    type: 'geojson',
                    data: window.activity_geojson_studying,
                    cluster: true,
                    clusterMaxZoom: 20,
                    clusterRadius: 50
                    });
                    console.log('geojson_studying')
                    console.log(window.activity_geojson_studying)
                    map.addLayer({
                    id: 'clusters-studying',
                    type: 'circle',
                    source: 'layer-source-geojson-studying',
                    filter: ['has', 'point_count'],
                    paint: {
                        'circle-color': [
                        'step',
                        ['get', 'point_count'],
                        window.color_studying,
                        20,
                        window.color_studying,
                        150,
                        window.color_studying
                        ],
                        'circle-radius': [
                        'step',
                        ['get', 'point_count'],
                        20,
                        100,
                        30,
                        750,
                        40
                        ]
                    }
                    });
                    map.addLayer({
                    id: 'cluster-count-studying',
                    type: 'symbol',
                    source: 'layer-source-geojson-studying',
                    filter: ['has', 'point_count'],
                    layout: {
                        'text-field': '{point_count_abbreviated}',
                        'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
                        'text-size': 12
                    }
                    });
                    map.addLayer({
                    id: 'unclustered-point-studying',
                    type: 'circle',
                    source: 'layer-source-geojson-studying',
                    filter: ['!', ['has', 'point_count'] ],
                    paint: {
                        'circle-color': window.color_studying,
                        'circle-radius':12,
                        'circle-stroke-width': 1,
                        'circle-stroke-color': '#fff'
                    }
                    });


                    // practicing geojson source and cluster
                    map.addSource('layer-source-geojson-training', {
                    type: 'geojson',
                    data: window.activity_geojson_training,
                    cluster: true,
                    clusterMaxZoom: 20,
                    clusterRadius: 50
                    });
                    map.addLayer({
                    id: 'clusters-training',
                    type: 'circle',
                    source: 'layer-source-geojson-training',
                    filter: ['has', 'point_count'],
                    paint: {
                        'circle-color': [
                        'step',
                        ['get', 'point_count'],
                        window.color_training,
                        20,
                        window.color_training,
                        150,
                        window.color_training
                        ],
                        'circle-radius': [
                        'step',
                        ['get', 'point_count'],
                        20,
                        100,
                        30,
                        750,
                        40
                        ]
                    }
                    });
                    map.addLayer({
                    id: 'cluster-count-training',
                    type: 'symbol',
                    source: 'layer-source-geojson-training',
                    filter: ['has', 'point_count'],
                    layout: {
                        'text-field': '{point_count_abbreviated}',
                        'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
                        'text-size': 12
                    }
                    });
                    map.addLayer({
                    id: 'unclustered-point-training',
                    type: 'circle',
                    source: 'layer-source-geojson-training',
                    filter: ['!', ['has', 'point_count'] ],
                    paint: {
                        'circle-color': window.color_training,
                        'circle-radius':12,
                        'circle-stroke-width': 1,
                        'circle-stroke-color': '#fff'
                    }
                    });


                    map.addSource('layer-source-geojson-practicing', {
                    type: 'geojson',
                    data: window.activity_geojson_practicing,
                    cluster: true,
                    clusterMaxZoom: 20,
                    clusterRadius: 50
                    });
                    map.addLayer({
                    id: 'clusters-practicing',
                    type: 'circle',
                    source: 'layer-source-geojson-practicing',
                    filter: ['has', 'point_count'],
                    paint: {
                        'circle-color': [
                        'step',
                        ['get', 'point_count'],
                        window.color_practicing,
                        20,
                        window.color_practicing,
                        150,
                        window.color_practicing
                        ],
                        'circle-radius': [
                        'step',
                        ['get', 'point_count'],
                        20,
                        100,
                        30,
                        750,
                        40
                        ]
                    }
                    });
                    map.addLayer({
                    id: 'cluster-count-practicing',
                    type: 'symbol',
                    source: 'layer-source-geojson-practicing',
                    filter: ['has', 'point_count'],
                    layout: {
                        'text-field': '{point_count_abbreviated}',
                        'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
                        'text-size': 12
                    }
                    });
                    map.addLayer({
                    id: 'unclustered-point-practicing',
                    type: 'circle',
                    source: 'layer-source-geojson-practicing',
                    filter: ['!', ['has', 'point_count'] ],
                    paint: {
                        'circle-color': window.color_practicing,
                        'circle-radius':12,
                        'circle-stroke-width': 1,
                        'circle-stroke-color': '#fff'
                    }
                    });


                    map.addSource('layer-source-geojson-coaching', {
                    type: 'geojson',
                    data: window.activity_geojson_coaching,
                    cluster: true,
                    clusterMaxZoom: 20,
                    clusterRadius: 50
                    });
                    map.addLayer({
                    id: 'clusters-coaching',
                    type: 'circle',
                    source: 'layer-source-geojson-coaching',
                    filter: ['has', 'point_count'],
                    paint: {
                        'circle-color': [
                        'step',
                        ['get', 'point_count'],
                        window.color_coaching,
                        20,
                        window.color_coaching,
                        150,
                        window.color_coaching
                        ],
                        'circle-radius': [
                        'step',
                        ['get', 'point_count'],
                        20,
                        100,
                        30,
                        750,
                        40
                        ]
                    }
                    });
                    map.addLayer({
                    id: 'cluster-count-coaching',
                    type: 'symbol',
                    source: 'layer-source-geojson-coaching',
                    filter: ['has', 'point_count'],
                    layout: {
                    'text-field': '{point_count_abbreviated}',
                    'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
                    'text-size': 12,
                    },
                    paint: {
                        'text-color': '#FFF'
                    }
                    });
                    map.addLayer({
                    id: 'unclustered-point-coaching',
                    type: 'circle',
                    source: 'layer-source-geojson-coaching',
                    filter: ['!', ['has', 'point_count'] ],
                    paint: {
                        'circle-color': window.color_coaching,
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

        <?php impact_map_top(); ?>

        <style id="custom-style"></style>
        <div id="map-wrapper">
            <div id='map'></div>
        </div>
        <div id="legend">
            <div><div class="color-block praying"></div> Praying: <span id="legend_praying"></span></div>
            <div><div class="color-block studying"></div> Studying: <span id="legend_studying"></span></div>
            <div><div class="color-block training"></div> Training: <span id="legend_training"></span></div>
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
        $action = sanitize_text_field( wp_unslash( $params['action'] ) );
        $language_code = 'en';

        switch ( $action ) {
            case 'geojson':
                return GO_Funnel_App_Heatmap::get_activity_geojson( $language_code );
            default:
                return new WP_Error( __METHOD__, 'Missing valid action', [ 'status' => 400 ] );
        }
    }
}
GO_Impact_Map_Globe::instance();
