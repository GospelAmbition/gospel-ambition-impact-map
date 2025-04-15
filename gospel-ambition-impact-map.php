<?php
/**
 * Plugin Name: Gospel Ambition - Impact Map
 * Plugin URI: https://github.com/GospelAmbition/gospel-ambition-impact-map
 * Description: Gospel Ambition - Impact Map shows some of the global activity of the GO digital disciple making activity.
 * Text Domain: gospel-ambition-impact-map
 * Domain Path: /languages
 * Version:  0.8.0
 * Author URI: https://github.com/GospelAmbition
 * GitHub Plugin URI: https://github.com/GospelAmbition/gospel-ambition-impact-map
 * Requires at least: 4.7.0
 * (Requires 4.7+ because of the integration of the REST API at 4.7 and the security requirements of this milestone version.)
 * Tested up to: 6.7.1
 *
 * @package Disciple_Tools
 * @link    https://github.com/GospelAmbition
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * Class GO_Impact_Context_Switcher
 * 
 * Main controller class that determines the plugin's behavior based on the site context.
 */
class GO_Impact_Context_Switcher {
    private static $instance = null;
    
    /**
     * Returns the singleton instance of this class.
     *
     * @since  0.1
     * @access public
     * @return GO_Impact_Context_Switcher|null The instance of this class.
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor for the GO_Impact_Context_Switcher class.
     * 
     * Determines the site context and loads the appropriate functionality based on that context.
     * If the site is 'GO Impact Map', it loads the impact map functionality.
     * Otherwise, it loads various remote trackers based on active plugins.
     *
     * @since  0.1
     * @access public
     */
    public function __construct(){

        $site = get_bloginfo();
        if ( 'GO Impact Map' === $site ) {
            require_once( 'impact-map/loader.php' );
        } else {

            $active_plugins = get_option( 'active_plugins' );
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
            if ( 'Kingdom Training' === $site ) {
                require_once( 'remote-trackers/kingdom-training.php' );
            }
            // disciple tools
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

if ( ! function_exists( 'ga_impact_map_hook_admin_notice' ) ) {
    /**
     * Displays an admin notice when the Disciple.Tools theme is not active or is outdated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    function ga_impact_map_hook_admin_notice() {
        global $ga_impact_map_required_dt_theme_version;
        $wp_theme = wp_get_theme();
        $current_version = $wp_theme->version;
        $message = "'Disciple.Tools - Gospel Ambition Impact Map' plugin requires 'Disciple.Tools' theme to work. Please activate 'Disciple.Tools' theme or make sure it is latest version.";
        if ( $wp_theme->get_template() === 'disciple-tools-theme' ){
            $message .= ' ' . sprintf( esc_html( 'Current Disciple.Tools version: %1$s, required version: %2$s' ), esc_html( $current_version ), esc_html( $ga_impact_map_required_dt_theme_version ) );
        }
        // Check if it's been dismissed...
        if ( ! get_option( 'dismissed-gospel-ambition-impact-map', false ) ) { ?>
            <div class="notice notice-error notice-gospel-ambition-impact-map is-dismissible" data-notice="gospel-ambition-impact-map">
                <p><?php echo esc_html( $message );?></p>
            </div>
            <script>
                jQuery(function($) {
                    $( document ).on( 'click', '.notice-gospel-ambition-impact-map .notice-dismiss', function () {
                        $.ajax( ajaxurl, {
                            type: 'POST',
                            data: {
                                action: 'dismissed_notice_handler',
                                type: 'gospel-ambition-impact-map',
                                security: '<?php echo esc_html( wp_create_nonce( 'wp_rest_dismiss' ) ) ?>'
                            }
                        })
                    });
                });
            </script>
        <?php }
    }
}

/**
 * AJAX handler to store the state of dismissible notices.
 *
 * @since  0.1
 * @access public
 * @return void
 */
if ( !function_exists( 'dt_hook_ajax_notice_handler' ) ){
    function dt_hook_ajax_notice_handler(){
        check_ajax_referer( 'wp_rest_dismiss', 'security' );
        if ( isset( $_POST['type'] ) ){
            $type = sanitize_text_field( wp_unslash( $_POST['type'] ) );
            update_option( 'dismissed-' . $type, true );
        }
    }
}

/**
 * Sets up the plugin update checker to keep the plugin up to date.
 *
 * @since  0.1
 * @access public
 * @return void
 */
add_action( 'plugins_loaded', function (){
    if ( ( is_admin() && !( is_multisite() && class_exists( 'DT_Multisite' ) ) ) || ( wp_doing_cron() ) ) {
        // Check for plugin updates
        if ( ! class_exists( 'Puc_v4_Factory' ) ) {
            if ( file_exists( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' ) ) {
                require( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' );
            }
        }
        if ( class_exists( 'Puc_v4_Factory' ) ){
            Puc_v4_Factory::buildUpdateChecker(
                'https://raw.githubusercontent.com/GospelAmbition/gospel-ambition-impact-map/master/version-control.json',
                __FILE__,
                'gospel-ambition-impact-map'
            );
        }
    }
} );
