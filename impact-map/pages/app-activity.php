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

    public function scripts() {}

    public function header_javascript(){
        impact_map_css_map_site_css_php();
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

            var isMobile = false; //initiate as false
            // device detection
            if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent)
                || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))) {
                isMobile = true;
            }

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
                    // console.log('loaded_map_activity')
                    // console.log(data)
                    "use strict";
                    window.activity_list = data

                    jQuery.each( window.activity_list, function(i,v){
                        if ( '' === v.note ) {
                            return
                        }
                        jQuery('#activity-list').append(`<li class="${v.type} ${v.country_code} ${v.language_code}"><strong class="time">(${v.time})</strong> ${v.note} </li>`)
                    })

                    spinner.removeClass('active')
                })
            }
            load_map_activity()

            let time_width = '150px'
            if ( isMobile ) {
                time_width = 'auto'
            }

            jQuery( document ).ready( function() {
                jQuery('#custom-css').append(
                        `body {
                            background: white !important;
                        }
                        #activity-list li {
                            padding-bottom: 2px;
                            list-style-type: none;
                        }
                        #activity-list {
                            margin: 0 .2em !important;
                        }
                        #activity-list h2 {
                            font-size:1.2em;
                            font-weight:bold;
                        }
                        #activity-wrapper {
                            max-width: 1200px;
                            margin: 0 auto;
                        }
                        .time {
                            width: ${time_width};
                            display: inline-block;
                        }
                        .center {
                            text-align: center;
                        }`
                )
            })
        </script>
        <?php
    }

    public function header_style() {}

    public function footer_javascript(){}

    public function body(){
        impact_map_top();
        ?>
        <style id="custom-css"></style>
        <div class="grid-x grid-padding-x grid-padding-y align-center">
            <div class="cell center">
                <h1 style="padding-top: 1em;">Impact Activity (Last 100 Hours)</h1>
                <hr>
                <span class="loading-spinner active"></span>
            </div>
            <div class="cell">
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
                return GO_Funnel_App_Heatmap::query_activity_list_simple();
            default:
                return new WP_Error( __METHOD__, 'Missing valid action parameters', [ 'status' => 400 ] );
        }
    }


}
GO_Impact_Map_Magic_Map_App::instance();
