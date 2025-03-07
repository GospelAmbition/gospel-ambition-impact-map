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
            var isMobile = false; //initiate as false
            // device detection
            if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
                || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) {
                isMobile = true;
            }
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
            window.color_studying = '#FFBF00'
            window.color_training = '#98FB98'
            window.color_downloading = '#00BFFF'
            window.color_practicing = '#4CBB17'
            window.color_coaching = '#355E3B'

            window.praying_count = 0
            window.studying_count = 0
            window.training_count = 0
            window.downloading_count = 0
            window.practicing_count = 0
            window.coaching_count = 0

            jQuery(document).ready(function(){

                /* set vertical size the form column*/
                jQuery('#custom-style').append(`
                    <style>
                        #map-wrapper {
                            height: ${urlParams.has('no_top') ? window.innerHeight : window.innerHeight - 50}px !important;
                        }
                        #map {
                            height: ${urlParams.has('no_top') ? window.innerHeight : window.innerHeight - 50}px !important;
                        }
                        #legend {
                            opacity: 0.8;
                            z-index: 1;
                            position: absolute;
                            top: 70px;
                            left: 10px;
                            width: 230px;
                            background-color: white;
                            padding: 0 20px 10px;
                        }
                        @media (max-width: 639px) {
                            #legend {
                                opacity: 0.8;
                            }
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
                        .color-block.downloading {
                            background-color: ${window.color_downloading};
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
                        .click-hide {
                            display: none;
                        }
                        #donation {
                            display: none; /* Hidden by default, will be shown via JavaScript */
                            opacity: 0.8;
                            position: absolute;
                            top: 70px;
                            bottom: 40px;
                            right: 10px;
                            width: 200px;
                            background-color: white;
                            z-index: 1;
                        }
                         @media (min-width: 640px) {
                            #donation {
                                width: 350px;
                            }
                        }
                        @media (max-width: 639px) {
                            #donation {
                               display: none;
                            }
                        }
                        #qr {
                            display: none; /* Hidden by default, will be shown via JavaScript */
                            position: absolute;
                            bottom: 40px;
                            left: 10px;
                            width: 200px;
                            height: 200px;
                            background-color: white;
                            z-index: 1;
                        }
                        @media (min-width: 640px) {
                            #qr {
                                width: 300px;
                                height: 300px;
                            }
                        }
                        @media (max-width: 639px) {
                            #qr {
                                display: none;
                            }
                        }
                        #qr-donate {
                            display: none; /* Hidden by default, will be shown via JavaScript */
                            position: absolute;
                            bottom: 40px;
                            left: 10px;
                            width: 200px;
                            height: 200px;
                            background-color: white;
                            z-index: 1;
                        }
                        @media (min-width: 640px) {
                            #qr-donate {
                                width: 300px;
                                height: 300px;
                            }
                        }
                        @media (max-width: 639px) {
                            #qr-donate {
                                display: none;
                            }
                        }
                        #gear-menu {
                            position: absolute;
                            top: 10px;
                            right: 10px;
                            z-index: 1;
                            opacity: 0.8;
                        }
                    </style>`)
                let zoom = 2.5
                if ( isMobile && window.innerWidth < 640 ) {
                    zoom = 1
                } else if ( isMobile && window.innerWidth < 1350 ) {
                    zoom = 2
                } else if ( isMobile && window.innerWidth < 1350 ) {
                    zoom = 3
                }
                mapboxgl.accessToken = jsObject.map_key;
                const map = new mapboxgl.Map({
                    container: 'map',
                    style: 'mapbox://styles/mapbox/streets-v9',
                    projection: 'globe', // Display the map as a globe, since satellite-v9 defaults to Mercator
                    zoom: zoom,
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
                    if ( 'praying' === v.properties.type && 'prayer_for_location' !== v.properties.subtype ) {
                        window.activity_geojson_praying.features.push(v)
                        window.praying_count++
                    }
                    else if ( 'studying' === v.properties.type  ) {
                        v.geometry.coordinates[0] = v.geometry.coordinates[0] + 0.002 // layer shift so that they don't overlap
                        window.activity_geojson_studying.features.push(v)
                        window.studying_count++
                    }
                    else if ( 'training' === v.properties.type ) {
                        v.geometry.coordinates[0] = v.geometry.coordinates[0] - 0.002 // layer shift so that they don't overlap
                        window.activity_geojson_training.features.push(v)
                        window.training_count++
                    }
                    else if ( 'downloading' === v.properties.type ) {
                        v.geometry.coordinates[0] = v.geometry.coordinates[0] + 0.001 // layer shift so that they don't overlap
                        v.geometry.coordinates[1] = v.geometry.coordinates[1] + 0.002
                        window.activity_geojson_downloading.features.push(v)
                        window.downloading_count++
                    }
                    })

                    jQuery('#legend_praying').html(numberWithCommas(window.praying_count))
                    jQuery('#legend_studying').html(numberWithCommas(window.studying_count))
                    jQuery('#legend_training').html(numberWithCommas(window.training_count))
                    jQuery('#legend_downloading').html(numberWithCommas(window.downloading_count))
                    // jQuery('#legend_practicing').html(numberWithCommas(window.practicing_count))
                    // jQuery('#legend_coaching').html(numberWithCommas(window.coaching_count))

                    // full geojson source
                    map.addSource('layer-source-geojson', {
                    type: 'geojson',
                    data: window.activity_geojson,
                    cluster: true,
                    clusterMaxZoom: 20,
                    clusterRadius: 50
                    });

                    // praying
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


                    // studying
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


                    // training
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


                    // downloading
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
                })

                jQuery('#legend').on('click', function(){
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

                    let url = 'https://goimpactmap.com/app/globe';
                    if(params.length > 0) {
                        url += '?' + params.join('&');
                    }

                    window.open(url, '_blank');
                });
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
            function numberWithCommas(x) {
                return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }

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
            jQuery(document).ready(function() {
                checkURLParameters();
            });
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
                <i class="fi-widget" style="font-size: 24px; color: #666; cursor: pointer;"></i>
                <div id="gear-dropdown" style="display: none; background: white; padding: 10px; margin-top: 5px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
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
                    <button id="launch-btn" style="width: 100%; background: #b13634; color: white; border: none; padding: 5px; border-radius: 4px; cursor: pointer;">Launch</button>
                </div>
            </div>
        </div>
        <div id="legend">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div><strong>In the last 30 Days</strong></div>
                <i class="medium fi-info" style="color: #b13634; cursor:pointer;"></i>
            </div>
            <div><strong>someone has ...</strong></div>
            <div><div class="color-block praying"></div> Prayed: <span id="legend_praying"></span></div>
            <div><div class="color-block studying"></div> Studied: <span id="legend_studying"></span></div>
            <div><div class="color-block training"></div> Trained: <span id="legend_training"></span></div>
            <div><div class="color-block downloading"></div> Downloaded: <span id="legend_downloading"></span></div>
            <!-- <div><div class="color-block practicing"></div> Downloaded: <span id="legend_practicing"></span></div> -->
            <!-- <div><div class="color-block coaching"></div> Downloaded: <span id="legend_coaching"></span></div> -->
            <div class="click-hide"><hr></div>
            <div class="click-hide"><strong>Prayed</strong> - Someone has prayed in this location.</div>
            <div class="click-hide"><strong>Studied</strong> - Someone has studied disciple making ideas in this location.</div>
            <div class="click-hide"><strong>Trained</strong> - Someone has recieved or given training on disciple making skills in this location.</div>
            <div class="click-hide"><strong>Downloaded</strong> - Someone has downloaded training materials or tools.</div>
        </div>
        <div id="donation">
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
        $hours = 720;

        switch ( $action ) {
            case 'geojson':
                return GO_Queries::get_activity_geojson( $language_code, $hours );
            default:
                return new WP_Error( __METHOD__, 'Missing valid action', [ 'status' => 400 ] );
        }
    }
}
GO_Impact_Map_Globe::instance();
