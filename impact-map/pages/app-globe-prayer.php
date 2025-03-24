<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class GO_Impact_Map_Globe_Prayer extends DT_Magic_Url_Base
{
    public $magic = false;
    public $parts = false;
    public $page_title = 'Gospel Ambition Impact Map';
    public $root = 'app';
    public $type = 'prayerglobe';
    public $type_name = 'Gospel Ambition Impact Map - Prayer';
    public static $token = 'app_prayerglobe';

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
        if ( str_starts_with( $url, $this->root . '/' . $this->type ) ) {

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
        $allowed_css[] = 'app-globe-prayer-css';
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
            // Simpler mobile detection based on screen width
            const isMobile = window.innerWidth < 768;
            const urlParams = new URLSearchParams(window.location.search);

            window.activity_list = {}
            window.activity_geojson = {
                "type": "FeatureCollection",
                "features": []
            }
            window.activity_geojson_praying = {
                "type": "FeatureCollection",
                "features": []
            }
            window.activity_geojson_prayed_for = {
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
            window.activity_geojson_downloading = {
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
            window.color_prayed_for = '#FFBF00'
            window.color_studying = '#FFBF00'
            window.color_training = '#98FB98'
            window.color_downloading = '#00BFFF'
            window.color_practicing = '#4CBB17'
            window.color_coaching = '#355E3B'

            window.praying_count = 0
            window.prayed_for_count = 0
            window.studying_count = 0
            window.training_count = 0
            window.downloading_count = 0
            window.practicing_count = 0
            window.coaching_count = 0

            jQuery(document).ready(function(){

                /* Set only the dynamic height properties */
                jQuery('#custom-style').append(`
                    <style>
                        #map-wrapper {
                            height: ${urlParams.has('no_top') ? window.innerHeight : window.innerHeight - 50}px !important;
                        }
                        #map {
                            height: ${urlParams.has('no_top') ? window.innerHeight : window.innerHeight - 50}px !important;
                        }
                    </style>`)
                
                // Optimized zoom calculation based on screen width
                let zoom = 2.5;
                if (isMobile) {
                    if (window.innerWidth < 640) {
                        zoom = 1;
                    } else if (window.innerWidth < 1350) {
                        zoom = 2;
                    }
                }
                
                mapboxgl.accessToken = jsObject.map_key;
                const map = new mapboxgl.Map({
                    container: 'map',
                    style: 'mapbox://styles/mapbox/streets-v9',
                    projection: 'globe', // Display the map as a globe, since satellite-v9 defaults to Mercator
                    zoom: zoom,
                    center: [30, 15]
                });

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

                    let layer_toggle = { 'praying': true, 'prayed_for': true, 'studying': false, 'training': false, 'downloading': false, 'practicing': false, 'coaching': false }

                    window.activity_geojson = data
                    console.log('geojson')
                    console.log(window.activity_geojson)
                    data.features.forEach( (v) => {
                    if ( 'praying' === v.properties.type && 'prayer_for_location' !== v.properties.subtype ) {
                        v.geometry.coordinates[0] = v.geometry.coordinates[0] + 0.002 // layer shift so that they don't overlap
                        window.activity_geojson_praying.features.push(v)
                        window.praying_count++
                    }
                    else if ( 'praying' === v.properties.type && 'prayer_for_location' === v.properties.subtype ) {
                        v.geometry.coordinates[0] = v.geometry.coordinates[0] + 0.0012 // layer shift so that they don't overlap
                        v.geometry.coordinates[1] = v.geometry.coordinates[1] + 0.0012
                        window.activity_geojson_prayed_for.features.push(v)
                        window.prayed_for_count++
                    }
                    else if ( 'studying' === v.properties.type ) {
                        v.geometry.coordinates[1] = v.geometry.coordinates[1] - 0.002
                        window.activity_geojson_studying.features.push(v)
                        window.studying_count++
                    }
                    else if ( 'training' === v.properties.type ) {
                        v.geometry.coordinates[0] = v.geometry.coordinates[0] - 0.002
                        window.activity_geojson_training.features.push(v)
                        window.training_count++
                    }
                    else if ( 'downloading' === v.properties.type ) {
                        v.geometry.coordinates[0] = v.geometry.coordinates[0] + 0.0012
                        v.geometry.coordinates[1] = v.geometry.coordinates[1] + 0.0012
                        window.activity_geojson_downloading.features.push(v)
                        window.downloading_count++
                    }
                    else if ( 'practicing' === v.properties.type ) {
                        v.geometry.coordinates[1] = v.geometry.coordinates[1] - 0.002 // layer shift so that they don't overlap
                        window.activity_geojson_practicing.features.push(v)
                        window.practicing_count++
                    }
                    else if ( 'coaching' === v.properties.type ) {
                        v.geometry.coordinates[0] = v.geometry.coordinates[0] - 0.0012
                        v.geometry.coordinates[1] = v.geometry.coordinates[1] - 0.0012
                        window.activity_geojson_coaching.features.push(v)
                        window.coaching_count++
                    }
                    })

                    jQuery('#legend_praying').html(numberWithCommas(window.praying_count))
                    jQuery('#legend_prayed_for').html(numberWithCommas(window.prayed_for_count))
                    jQuery('#legend_studying').html(numberWithCommas(window.studying_count))
                    jQuery('#legend_training').html(numberWithCommas(window.training_count))
                    jQuery('#legend_downloading').html(numberWithCommas(window.downloading_count))
                    jQuery('#legend_practicing').html(numberWithCommas(window.practicing_count))
                    jQuery('#legend_coaching').html(numberWithCommas(window.coaching_count))


                    // full geojson source
                    map.addSource('layer-source-geojson', {
                    type: 'geojson',
                    data: window.activity_geojson,
                    cluster: true,
                    clusterMaxZoom: 20,
                    clusterRadius: 50
                    });


                    // prayed_for
                    if ( layer_toggle.prayed_for) {
                        map.addSource('layer-source-geojson-prayed_for', {
                        type: 'geojson',
                        data: window.activity_geojson_prayed_for,
                        cluster: true,
                        clusterMaxZoom: 20,
                        clusterRadius: 50
                        });
                        map.addLayer({
                        id: 'clusters-prayed_for',
                        type: 'circle',
                        source: 'layer-source-geojson-prayed_for',
                        filter: ['has', 'point_count'],
                        paint: {
                            'circle-color': [
                            'step',
                            ['get', 'point_count'],
                            window.color_prayed_for,
                            20,
                            window.color_prayed_for,
                            150,
                            window.color_prayed_for
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
                        id: 'cluster-count-prayed_for',
                        type: 'symbol',
                        source: 'layer-source-geojson-prayed_for',
                        filter: ['has', 'point_count'],
                        layout: {
                            'text-field': '{point_count_abbreviated}',
                            'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
                            'text-size': 12
                        }
                        });
                        map.addLayer({
                        id: 'unclustered-point-prayed_for',
                        type: 'circle',
                        source: 'layer-source-geojson-prayed_for',
                        filter: ['!', ['has', 'point_count'] ],
                        paint: {
                            'circle-color': window.color_prayed_for,
                            'circle-radius':12,
                            'circle-stroke-width': 1,
                            'circle-stroke-color': '#fff'
                        }
                        });
                    }


                    // praying
                    if ( layer_toggle.praying) {
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
                    }

                    // studying
                    if ( layer_toggle.studying) {
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
                    }


                    // training
                    if ( layer_toggle.training) {
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
                    }


                    // downloading
                    if ( layer_toggle.downloading) {
                        map.addSource('layer-source-geojson-downloading', {
                        type: 'geojson',
                        data: window.activity_geojson_downloading,
                        cluster: true,
                        clusterMaxZoom: 20,
                        clusterRadius: 50
                        });
                        map.addLayer({
                        id: 'clusters-downloading',
                        type: 'circle',
                        source: 'layer-source-geojson-downloading',
                        filter: ['has', 'point_count'],
                        paint: {
                            'circle-color': [
                            'step',
                            ['get', 'point_count'],
                            window.color_downloading,
                            20,
                            window.color_downloading,
                            150,
                            window.color_downloading
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
                        id: 'cluster-count-downloading',
                        type: 'symbol',
                        source: 'layer-source-geojson-downloading',
                        filter: ['has', 'point_count'],
                        layout: {
                            'text-field': '{point_count_abbreviated}',
                            'text-font': ['DIN Offc Pro Medium', 'Arial Unicode MS Bold'],
                            'text-size': 12
                        }
                        });
                        map.addLayer({
                        id: 'unclustered-point-downloading',
                        type: 'circle',
                        source: 'layer-source-geojson-downloading',
                        filter: ['!', ['has', 'point_count'] ],
                        paint: {
                            'circle-color': window.color_downloading,
                            'circle-radius':12,
                            'circle-stroke-width': 1,
                            'circle-stroke-color': '#fff'
                        }
                        });
                    }

                    // practicing
                    if ( layer_toggle.practicing) {
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
                    }


                    // coaching
                    if ( layer_toggle.coaching) {
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
                    }

                })

                jQuery('#legend-div').on('click', function(){
                    jQuery('.click-hide').toggle()
                })
                
                // Toggle gear dropdown
                jQuery('.fi-widget').click(function() {
                    jQuery('#gear-dropdown').toggle();
                });

                // Launch button click handler
                jQuery('#launch-btn').click(function() {
                    let params = [];

                    if(jQuery('#qr-donate-toggle').is(':checked')) {
                        params.push('qr-donate');
                    }
                    if(jQuery('#qr-toggle').is(':checked')) {
                        params.push('qr');
                    }
                    if(jQuery('#donation-toggle').is(':checked')) {
                        params.push('donation');
                    }
                    if(jQuery('#no-top-toggle').is(':checked')) {
                        params.push('no_top');
                    }

                    let url = 'https://goimpactmap.com/app/prayerglobe';
                    if(params.length > 0) {
                        url += '?' + params.join('&');
                    }

                    window.open(url, '_blank');
                });

                // Function to check URL parameters and show/hide elements
                function checkURLParameters() {
                    const urlParams = new URLSearchParams(window.location.search);

                    // Check for 'donation' parameter
                    if (urlParams.has('donation')) {
                        jQuery('#donation').show();
                    } else {
                        jQuery('#donation').hide();
                    }

                    // Check for 'qr' parameter
                    if (urlParams.has('qr')) {
                        jQuery('#qr').show();
                    } else {
                        jQuery('#qr').hide();
                    }

                    // Check for 'qr-donate' parameter
                    if (urlParams.has('qr-donate')) {
                        jQuery('#qr-donate').show();
                    } else {
                        jQuery('#qr-donate').hide();
                    }
                }

                // Run the check when the page loads
                checkURLParameters();
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
                .then(function(data) {
                    // Construct GeoJSON from raw data
                    const features = data.map(record => ({
                        type: 'Feature',
                        properties: {
                            type: record.type,
                            subtype: record.subtype
                        },
                        geometry: {
                            type: 'Point',
                            coordinates: [
                                parseFloat(record.lng),
                                parseFloat(record.lat),
                                1
                            ]
                        }
                    }));

                    return {
                        type: 'FeatureCollection',
                        features: features
                    };
                })
                .fail(function(e) {
                    console.error("Error loading map data:", e);
                    if (jQuery('#error').length) {
                        jQuery('#error').html(`<div class="error-message">Failed to load map data: ${e.statusText || 'Unknown error'}</div>`);
                    } else {
                        jQuery('#map').after(`<div id="error" class="error-message">Failed to load map data: ${e.statusText || 'Unknown error'}</div>`);
                    }
                    return Promise.reject(e);
                });
            }
            function numberWithCommas(x) {
                return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
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
            <div id="gear-menu">
                <i class="fi-widget"></i>
                <div id="gear-dropdown">
                    <div style="margin-bottom: 10px;">
                        <input type="checkbox" id="qr-donate-toggle"> Show Donate QR
                    </div>
                    <div style="margin-bottom: 10px;">
                        <input type="checkbox" id="qr-toggle"> Show Mobile Map QR
                    </div>
                    <div style="margin-bottom: 10px;">
                        <input type="checkbox" id="donation-toggle"> Hide Donation Panel
                    </div>
                    <div style="margin-bottom: 10px;">
                        <input type="checkbox" id="no-top-toggle"> Remove Top Bar
                    </div>
                    <button id="launch-btn">Launch</button>
                </div>
            </div>
        </div>
        <div id="legend-div">
            <div class="right"><i class="medium fi-info" style="color: #b13634; cursor:pointer;"></i></div>
            <div><strong>In the last 30 days...</strong></div>
            <div><div class="color-block praying"></div> Prayers: <span id="legend_praying"></span></div>
            <div><div class="color-block prayed_for"></div> Locations Covered: <span id="legend_prayed_for"></span></div>
            <div class="click-hide"><hr></div>
            <div class="click-hide"><strong>Prayers</strong> - These are times of prayer set aside to pray for the advance of the gospel.</div>
            <div class="click-hide"><strong>Locations Covered</strong> - These are locations that have been covered in prayer by name using strategic information about the spiritual state of this location.</div>
        </div>

        <div id="donation" style="z-index: 10;">
            <div id="donation-content"></div>
        </div>
        <div id="qr">
            <img src="<?php echo plugin_dir_url(__DIR__)  ?>images/qr-app-globe.png" alt="QR Code">
        </div>
        <div id="qr-donate">
            <img src="<?php echo plugin_dir_url(__DIR__)  ?>images/qr-donate.png" alt="QR Code">
        </div>

        <?php
    }

    public static function _wp_enqueue_scripts(){
        DT_Mapbox_API::load_mapbox_header_scripts();
        wp_enqueue_style( 'app-globe-prayer-css', plugin_dir_url(__DIR__) . 'css/app-globe-prayer.css', [], filemtime( plugin_dir_path(__DIR__) . 'css/app-globe-prayer.css' ) );
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
        $hours = 720; // 30 days

        switch ( $action ) {
            case 'geojson':
                return GO_Queries::get_activity_geojson( $language_code, $hours );
            default:
                return new WP_Error( __METHOD__, 'Missing valid action', [ 'status' => 400 ] );
        }
    }
}
GO_Impact_Map_Globe_Prayer::instance();
