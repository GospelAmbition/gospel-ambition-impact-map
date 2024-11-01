<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

if ( strpos( dt_get_url_path(), 'zume_app' ) !== false || dt_is_rest() ){
    Zume_Funnel_Public_Heatmap_Churches::instance();
}

class Zume_Funnel_Public_Heatmap_Churches extends DT_Magic_Url_Base
{
    public $page_title = 'Zúme Churches Map';
    public $root = 'zume_app';
    public $type = 'heatmap_churches';
    public $type_name = 'Churches';
    public $post_type = 'contacts';
    private $meta_key = '';
    public $us_div = 5000; // this is 2 for every 5000
    public $global_div = 50000; // this equals 2 for every 50000

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

        add_action( 'rest_api_init', [ $this, 'add_endpoints' ] );

        // fail if not valid url
        $url = dt_get_url_path();
        if ( strpos( $url, $this->root . '/' . $this->type ) === false ) {
            return;
        }

        if ( !$this->check_parts_match( false ) ){
            return;
        }

        add_action( 'dt_blank_body', [ $this, 'body' ] );
        add_filter( 'dt_magic_url_base_allowed_css', [ $this, 'dt_magic_url_base_allowed_css' ], 10, 1 );
        add_filter( 'dt_magic_url_base_allowed_js', [ $this, 'dt_magic_url_base_allowed_js' ], 10, 1 );
        add_action( 'wp_enqueue_scripts', [ $this, '_wp_enqueue_scripts' ], 99 );
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
        $allowed_css[] = 'vite_bundle_css';
        return $allowed_css;
    }

    public static function _wp_enqueue_scripts(){
        Zume_Funnel_App_Heatmap::_wp_enqueue_scripts();
    }

    public function _header(){
        Zume_Funnel_App_Heatmap::_header();
        ?>
        <script>
            let mapObject = [<?php echo json_encode([
                'map_key' => DT_Mapbox_API::get_key(),
                'mirror_url' => dt_get_location_grid_mirror( true ),
                'theme_uri' => trailingslashit( get_stylesheet_directory_uri() ),
                'root' => esc_url_raw( rest_url() ),
                'nonce' => wp_create_nonce( 'wp_rest' ),
                'parts' => $this->parts,
                'post_type' => $this->post_type,
                'image_url' => trailingslashit( plugin_dir_url( __FILE__ ) ) . 'images/',
                'translation' => zume_map_translation_strings(),
                'grid_data' => [ 'data' => [], 'highest_value' => 1 ],
                'custom_marks' => [],
                'zoom' => 8,
            ]) ?>][0]

            /* custom content */
            function load_self_content(data) {
                jQuery('#custom-paragraph').html(`

                    `)
            }

            /* custom level content */
            function load_level_content(data, level) {
                let gl = jQuery('#' + level + '-list-item')
                gl.empty()
                if (false !== data) {
                    gl.append(`
                            <div class="progress-list-item">
                                <div class="cell">
                                  <strong>${data.name}</strong><br>
                                  ${mapObject.translation.population}: <span>${data.population}</span><br>
                                  ${mapObject.translation.churches_needed}: <span>${data.needed}</span><br>
                                  ${mapObject.translation.churches_reported}: <span class="reported_number">${data.reported}</span><br>
                                  ${mapObject.translation.goal_reached}: <span>${data.percent}</span>%
                                  <meter class="meter" value="${data.percent}" min="0" low="33" high="66" optimum="100" max="100"></meter>
                                </div>
                            </div>
                        `)
                }
                else {
                    jQuery('.' + level + '-list-wrapper')
                }
            }

            jQuery(document).ready(function($) {

                jQuery('#welcome-modal').foundation('open');
                jQuery('#panel-type-title').html('<?php echo esc_html__( 'Churches', 'zume' ) ?>');
                jQuery('#map-header-title').html('<?php echo esc_html__( 'Map of Zúme Churches', 'zume' ) ?>');
                jQuery('#map-header-description').html('<p style="max-width: 400px;"><?php echo esc_html__( 'Saturation Goal: 2 simple churches per 5,000 people in the United States, and 2 simple churches per 50,000 people globally', 'zume' ) ?></p>');

            })
        </script>
        <?php
    }

    public function body(){
        DT_Mapbox_API::geocoder_scripts(); // load mapping
        include( 'html/heatmap-html.php' ); // load saturation map template
        $this->customized_elements(); // load map specific elements
    }

    public function customized_elements(){
        ?>
        <div class="reveal" id="welcome-modal" data-reveal>
            <h1><?php echo esc_html__( 'Purpose of the Map', 'zume' ) ?></h1>
            <p><?php echo esc_html__( 'We want to saturate the world with multiplying disciples and churches. The "Map of Zúme Churches" shows what is needed and what is reported for global saturation of multiplying simple churches.', 'zume' ) ?></p>
            <p><strong style="text-transform: uppercase;"><?php echo esc_html__( 'Saturation Goal', 'zume' ) ?></strong></p>
            <ul>
                <li><?php echo esc_html__( '2 simple churches per 5,000 people in the United States', 'zume' ) ?></li>
                <li><?php echo esc_html__( '2 simple churches per 50,000 people globally', 'zume' ) ?></li>
            </ul>
            <p><strong style="text-transform: uppercase;"><?php echo esc_html__( 'Measurements Used', 'zume' ) ?></strong></p>
            <ul>
                <li><?php echo esc_html__( '"Needed" refers to what is missing in the location listed according to population.', 'zume' ) ?></li>
                <li><?php echo esc_html__( '"Reported" refers to churches who have registered themselves to the Zúme community.', 'zume' ) ?></li>
            </ul>
            <p><strong style="text-transform: uppercase;"><?php echo esc_html__( 'How to Get on The Map', 'zume' ) ?></strong></p>
            <ol>
                <li><a href="<?php echo esc_url( zume_wizard_url( 'join_the_community' ) ) ?>" target="_parent"><?php echo esc_html__( 'Join the Zúme community.', 'zume' ) ?></a></li>
                <li><?php echo esc_html__( 'Add your churches in the "My Churches" section of the dashboard.', 'zume' ) ?></li>
            </ol>
            <button class="close-button" data-close aria-label="Close modal" type="button">
                <span aria-hidden="true">&times;</span>
            </button>
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
        $action = sanitize_text_field( wp_unslash( $params['action'] ) );
        $language_code = 'en'; // @todo get parameter

        switch ( $action ) {
            case 'self':
                return Zume_Funnel_App_Heatmap::get_self( $params['grid_id'], $this->global_div, $this->us_div );
            case 'a3':
            case 'a2':
            case 'a1':
            case 'a0':
            case 'world':
                $list = Zume_Funnel_App_Heatmap::query_church_grid_totals( $action );
                return Zume_Funnel_App_Heatmap::endpoint_get_level( $params['grid_id'], $action, $list, $this->global_div, $this->us_div );
            case 'activity_data':
                $grid_id = sanitize_text_field( wp_unslash( $params['grid_id'] ) );
                $offset = sanitize_text_field( wp_unslash( $params['offset'] ) );
                return Zume_Funnel_App_Heatmap::get_activity_grid_id( $grid_id, $offset, $language_code );
            case 'grid_data':
                $grid_totals = Zume_Funnel_App_Heatmap::query_church_grid_totals();
                return Zume_Funnel_App_Heatmap::_initial_polygon_value_list( $grid_totals, $this->global_div, $this->us_div );
            default:
                return new WP_Error( __METHOD__, 'Missing valid action', [ 'status' => 400 ] );
        }
    }
}
