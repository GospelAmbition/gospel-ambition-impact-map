<?php
/**
 * Plugin Name: Gospel Ambition - Impact Map
 * Plugin URI: https://github.com/GospelAmbition/gospel-ambition-impact-map
 * Description: Disciple.Tools - Gospel Ambition Impact Map is intended to help developers and integrator jumpstart their extension of the Disciple.Tools system.
 * Text Domain: gospel-ambition-impact-map
 * Domain Path: /languages
 * Version:  0.1
 * Author URI: https://github.com/GospelAmbition
 * GitHub Plugin URI: https://github.com/GospelAmbition/gospel-ambition-impact-map
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 6.2
 *
 * @package Disciple_Tools
 * @link    https://github.com/GospelAmbition
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 */

class GO_Impact_Context_Switcher {
    private static $instance = null;
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function __construct(){
        $site = get_bloginfo();

        if ( 'GO Impact Map' === $site ) {

            require_once( 'impact-map/loader.php' );

        } else {

            require_once( 'remote-trackers/logger-api.php' );
            require_once( 'remote-trackers/logger-script.php' );

            switch ( $site ) {

                case 'Prayer Global':
                    require_once( 'remote-trackers/prayer-global.php' );
                    break;

                default:
                    return false;
            }
        }
    }
}

add_action( 'after_setup_theme', [ 'GO_Impact_Context_Switcher', 'instance' ], 10 );
