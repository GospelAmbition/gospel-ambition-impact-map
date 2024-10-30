<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

/**
 * Adds a magic link home page to the Disciple Tools system.
 * @usage This could be used to add a microsite in front of the Disciple Tools system. Or used to hide the
 * Disciple Tools login behind a false store front. Or used to extend an entire application to the public out
 * in front of the Disciple Tools system.
 *
 * @example https://yoursite.com/(empty)
 *
 * @see https://disciple.tools/plugins/porch/
 * @see https://disciple.tools/plugins/disciple-tools-porch-template/
 */
class GA_Impact_Map_Magic_Home_App extends DT_Magic_Url_Base
{
    public $magic = false;
    public $parts = false;
    public $page_title = 'Gospel Ambition - Impact Map';
    public $root = 'go';
    public $type = 'home';
    public static $token = 'go_home';

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
        if ( empty( $url ) && ! dt_is_rest() ) { // this filter is looking for the root site url without params.

            // register url and access
            add_action( 'template_redirect', [ $this, 'theme_redirect' ] );
            add_filter( 'dt_blank_access', function (){ return true;
            }, 100, 1 ); // allows non-logged in visit
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
            add_action( 'dt_blank_body', [ $this, 'body' ] );

            add_filter( 'dt_magic_url_base_allowed_css', [ $this, 'dt_magic_url_base_allowed_css' ], 10, 1 );
            add_filter( 'dt_magic_url_base_allowed_js', [ $this, 'dt_magic_url_base_allowed_js' ], 10, 1 );
            add_filter( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        }
    }

    public function enqueue_scripts() {
        wp_register_script( 'foundation_js', 'https://cdnjs.cloudflare.com/ajax/libs/foundation/6.7.5/js/foundation.min.js', array( 'jquery' ), '6.7.5' );
        wp_enqueue_script( 'foundation_js' );
    }

    public function dt_magic_url_base_allowed_js( $allowed_js ) {
            $allowed_js[] = 'foundation_js';
            $allowed_js[] = 'foundation_reveal_js';

            $key = array_search('lodash', $allowed_js );
            if ( $key ) {
                unset($allowed_js[$key]);
            }
            $key = array_search('lodash-core', $allowed_js );
            if ( $key ) {
                unset($allowed_js[$key]);
            }
            $key = array_search('site-js', $allowed_js );
            if ( $key ) {
                unset($allowed_js[$key]);
            }
            $key = array_search('moment', $allowed_js );
            if ( $key ) {
                unset($allowed_js[$key]);
            }
            $key = array_search('datepicker', $allowed_js );
            if ( $key ) {
                unset($allowed_js[$key]);
            }

        return $allowed_js;
    }

    public function dt_magic_url_base_allowed_css( $allowed_css ) {
        return $allowed_css;
    }

    public function header_style(){
        ?>
        <style>
            body {
                background-color:white;
            }
            .top {

            }
            .right {
                text-align: end;
            }
        </style>
        <?php
    }

    public function body(){
        ?>
        <div class="body-wrapper">
            <div class="top">
                <div class="grid-x grid-padding-x grid-padding-y">
                    <div class="cell small-6 ">
                        <h1>Gospel Ambition - Impact Maps</h1>
                    </div>
                    <div class="cell small-6 right">

                    </div>
                </div>
            </div>
            <div class="content">
                <div class="grid-x grid-padding-x">
                    <div class="cell">
                        <iframe src="/map/100" style="width:100%; height: 800px; border: none;" frameborder="0"></iframe>
                    </div>
                    <div class="cell center">
                        <h2><a href="/map/100">Full Map</a></h2>
                    </div>
                </div>
            </div>
            <div class="footer">

            </div>
        </div>
        <?php
    }
}
GA_Impact_Map_Magic_Home_App::instance();