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
        $allowed_js[] = 'globe-prayer-js';
        return $allowed_js;
    }

    public function dt_magic_url_base_allowed_css( $allowed_css ) {
        $allowed_css[] = 'mapbox-gl-css';
        $allowed_css[] = 'introjs-css';
        $allowed_css[] = 'heatmap-css';
        $allowed_css[] = 'site-css';
        $allowed_css[] = 'globe-prayer-css';
        return $allowed_css;
    }

    public function header_javascript(){
        ?>
        <script>
            // Global application configuration
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

            // Function to get geojson data from server
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
                    console.error("API Request Failed:", e);
                    return Promise.reject(e);
                });
            }
        </script>
        <?php
    }

    public function header_style() {
        // Base site styles
        impact_map_css_map_site_css_php();
        
        // Include external CSS
        wp_enqueue_style(
            'globe-prayer-css',
            plugin_dir_url(__FILE__) . 'globe-prayer.css',
            [],
            filemtime( plugin_dir_path(__FILE__) . 'globe-prayer.css' )
        );
    }

    public function footer_javascript(){
        // Include external JS
        wp_enqueue_script(
            'globe-prayer-js',
            plugin_dir_url(__FILE__) . 'globe-prayer.js',
            ['jquery', 'mapbox-gl'],
            filemtime( plugin_dir_path(__FILE__) . 'globe-prayer.js' ),
            true
        );
    }

    public function body(){
        ?>
        <link href="https://api.mapbox.com/mapbox-gl-js/v3.9.4/mapbox-gl.css" rel="stylesheet">
        <script src="https://api.mapbox.com/mapbox-gl-js/v3.9.4/mapbox-gl.js"></script>

        <?php impact_map_top(); ?>

        <div id="map-wrapper">
            <div id='map'></div>
            <div id="gear-menu">
                <i class="fi-widget" style="font-size: 24px; color: #666; cursor: pointer;"></i>
                <div id="gear-dropdown">
                    <div class="gear-menu-item">
                        <input type="checkbox" id="qr-donate-toggle"> Show Donate QR
                    </div>
                    <div class="gear-menu-item">
                        <input type="checkbox" id="qr-toggle"> Show Mobile Map QR
                    </div>
                    <div class="gear-menu-item">
                        <input type="checkbox" id="donation-toggle"> Hide Donation Panel
                    </div>
                    <div class="gear-menu-item">
                        <input type="checkbox" id="no-top-toggle"> Remove Top Bar
                    </div>
                    <button id="launch-btn">Launch</button>
                </div>
            </div>
        </div>
        
        <div id="legend">
            <div class="right"><i class="medium fi-info" style="color: #b13634; cursor:pointer;"></i></div>
            <div><strong>In the last 30 days...</strong></div>
            <div><div class="color-block praying"></div> Prayers: <span id="legend_praying"></span></div>
            <div><div class="color-block prayed_for"></div> Locations Covered: <span id="legend_prayed_for"></span></div>
            <div class="click-hide"><hr></div>
            <div class="click-hide"><strong>Prayers</strong> - These are times of prayer set aside to pray for the advance of the gospel.</div>
            <div class="click-hide"><strong>Locations Covered</strong> - These are locations that have been covered in prayer by name using strategic information about the spiritual state of this location.</div>
        </div>

        <div id="donation">
            <div id="donation-content"></div>
        </div>
        
        <div id="qr">
            <img src="<?php echo plugin_dir_url(__DIR__)  ?>images/qr-app-globe-prayer.png" alt="Prayer Map QR Code">
        </div>
        
        <div id="qr-donate">
            <img src="<?php echo plugin_dir_url(__DIR__)  ?>images/qr-donate.png" alt="Donate QR Code">
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

        try {
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
        } catch (Exception $e) {
            return new WP_Error( __METHOD__, 'Error processing request: ' . $e->getMessage(), [ 'status' => 500 ] );
        }
    }
}
GO_Impact_Map_Globe_Prayer::instance();
