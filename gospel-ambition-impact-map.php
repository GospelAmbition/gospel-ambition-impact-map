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
        $active_plugins = get_option( 'active_plugins' );

        if ( 'GO Impact Map' === $site ) {
            require_once( 'impact-map/loader.php' );
        } else {

            require_once( 'remote-trackers/logger-api.php' );
            require_once( 'remote-trackers/logger-queue.php' );
            require_once( 'remote-trackers/logger-script.php' );

            // prayer campaign
            if ( in_array( 'disciple-tools-prayer-campaigns/disciple-tools-prayer-campaigns.php', $active_plugins ) ) {
                require_once( 'remote-trackers/prayer-campaigns.php' );
            }
            // prayer tools
            if ( in_array( 'prayer-global-porch/prayer-global-porch.php', $active_plugins ) ) {
                require_once( 'remote-trackers/prayer-global.php' );
            }
            // kingdom training
            if ( in_array( 'prayer-global-porch/prayer-global-porch.php', $active_plugins ) ) {
                require_once( 'remote-trackers/prayer-global.php' );
            }
            // // disciple tools
            if ( in_array( 'dt_usage/dt-usage.php', $active_plugins ) ) {
                require_once( 'remote-trackers/disciple-tools.php' );
            }
            // zume training
            if ( in_array( 'zume-training-system/zume-training-system.php', $active_plugins ) ) {
                require_once( 'remote-trackers/zume-training.php' );
            }
            // zume training coaching
            if ( in_array( 'zume-coaching-system/zume-coaching-system.php', $active_plugins ) ) {
                require_once( 'remote-trackers/zume-training-coaching.php' );
            }

        }
    }
}

add_action( 'after_setup_theme', [ 'GO_Impact_Context_Switcher', 'instance' ], 10 );
