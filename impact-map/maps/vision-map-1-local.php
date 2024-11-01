<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

if ( strpos( dt_get_url_path(), 'zume_app' ) !== false || dt_is_rest() ){
    Zume_Map_Local_Vision::instance();
}

class Zume_Map_Local_Vision extends DT_Magic_Url_Base
{
    public $page_title = 'Zúme Activity';
    public $root = 'zume_app';
    public $type = 'local_vision';
    public $type_name = 'Local Vision';
    public $post_type = 'activity';
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
        return $allowed_css;
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
                'translation' => zume_map_translation_strings(),
                'grid_data' => [ 'data' => [], 'highest_value' => 1 ],
                'custom_marks' => [],
                'zoom' => 12,
            ]) ?>][0]
        </script>
        <?php
    }

    public static function _wp_enqueue_scripts(){
        Zume_Funnel_App_Heatmap::_wp_enqueue_scripts();
    }

    public function body() {
        // /?grid_id=34565432
        // /?location_select=true
        // /

        DT_Mapbox_API::geocoder_scripts();
            // @phpcs:disable
        if ( isset( $_GET['grid_id'] ) ) {
            // if grid_id given
                // then give local vision for the grid id
            $grid_id = sanitize_text_field( $_GET['grid_id'] );
            echo $grid_id;
        }
        else if ( isset( $_GET['location_select'] ) ) {
            // if no grid id, but user logged in, and location_select is not set to true
            // then give local vision for users location. Overrides logged in state.
            echo 'Location select';
        }
        else if ( is_user_logged_in() ) {
            // if no grid id, but user logged in, and location_select is not set to true
                // then give local vision for users location
            global $zume_user_profile;
            print_r( $zume_user_profile );
        }
        else {
            // if no grid id, and no login || location_select is set to true
                // then give location select tool
            echo 'Location select';
        }
        // @phpcs:enable
    }

    public function footer_javascript(){
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

        return $params;

//        switch ( $action ) {
//            case 'self':
//                return Zume_Funnel_App_Heatmap::get_self( $params['grid_id'], $this->global_div, $this->us_div );
//            case 'a3':
//            case 'a2':
//            case 'a1':
//            case 'a0':
//            case 'world':
//                $list = Zume_Funnel_App_Heatmap::query_activity_grid_totals( $action );
//                return Zume_Funnel_App_Heatmap::endpoint_get_activity_level( $params['grid_id'], $action, $list, $this->global_div, $this->us_div );
//            case 'activity_data':
//                $grid_id = sanitize_text_field( wp_unslash( $params['grid_id'] ) );
//                $offset = sanitize_text_field( wp_unslash( $params['offset'] ) );
//                return Zume_Funnel_App_Heatmap::get_activity_grid_id( $grid_id, $offset, $language_code );
//            case 'grid_data':
//                return $this->_initial_polygon_value_list();
//            default:
//                return new WP_Error( __METHOD__, 'Missing valid action', [ 'status' => 400 ] );
//        }
    }

    public function _initial_polygon_value_list(){
        $flat_grid = Zume_Funnel_App_Heatmap::query_saturation_list();
        $grid_totals = Zume_Funnel_App_Heatmap::query_activity_grid_totals();

        $data = [];
        $highest_value = 1;
        foreach ( $flat_grid as $i => $v ){
            $data[$i] = [
                'grid_id' => $i,
                'population' => number_format_i18n( $v['population'] ),
                'needed' => 1,
                'reported' => 0,
                'percent' => 0,
            ];

            $population_division = Zume_Funnel_App_Heatmap::_get_population_division( $v['country_code'], $this->global_div, $this->us_div );

            $needed = round( $v['population'] / $population_division );
            if ( $needed < 1 ){
                $needed = 1;
            }

            if ( isset( $grid_totals[$v['grid_id']] ) && ! empty( $grid_totals[$v['grid_id']] ) ){
                $reported = $grid_totals[$v['grid_id']];
                if ( ! empty( $reported ) && ! empty( $needed ) ){
                    $data[$v['grid_id']]['needed'] = $needed;

                    $data[$v['grid_id']]['reported'] = $reported;

                    $percent = round( $reported / $needed * 100 );
                    if ( 100 < $percent ) {
                        $percent = 100;
                    } else {
                        $percent = number_format_i18n( $percent, 2 );
                    }
                    $data[$v['grid_id']]['percent'] = $percent;
                }
            }
            else {
                $data[$v['grid_id']]['percent'] = 0;
                $data[$v['grid_id']]['reported'] = 0;
                $data[$v['grid_id']]['needed'] = $needed;
            }

            if ( $highest_value < $data[$v['grid_id']]['reported'] ){
                $highest_value = 200;
            }
        }

        return [
            'highest_value' => (int) $highest_value,
            'data' => $data,
            'count' => count( $data ),
        ];
    }
}
