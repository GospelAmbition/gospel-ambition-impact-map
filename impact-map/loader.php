<?php


if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Gets the instance of the `GA_Impact_Map` class.
 *
 * @since  0.1
 * @access public
 * @return object|bool
 */
function ga_impact_map() {
    $ga_impact_map_required_dt_theme_version = '1.19';
    $wp_theme = wp_get_theme();
    $version = $wp_theme->version;

    /*
     * Check if the Disciple.Tools theme is loaded and is the latest required version
     */
    $is_theme_dt = class_exists( 'Disciple_Tools' );
    if ( $is_theme_dt && version_compare( $version, $ga_impact_map_required_dt_theme_version, '<' ) ) {
        add_action( 'admin_notices', 'ga_impact_map_hook_admin_notice' );
        add_action( 'wp_ajax_dismissed_notice_handler', 'dt_hook_ajax_notice_handler' );
        return false;
    }
    if ( !$is_theme_dt ){
        return false;
    }
    /**
     * Load useful function from the theme
     */
    if ( !defined( 'DT_FUNCTIONS_READY' ) ){
        require_once get_template_directory() . '/dt-core/global-functions.php';
    }

    return GA_Impact_Map::instance();
}
add_action( 'after_setup_theme', 'ga_impact_map', 20 );

//register the D.T Plugin
add_filter( 'dt_plugins', function ( $plugins ){
    $plugin_data = get_file_data( __FILE__, [ 'Version' => 'Version', 'Plugin Name' => 'Plugin Name' ], false );
    $plugins['gospel-ambition-impact-map'] = [
        'plugin_url' => trailingslashit( plugin_dir_url( __FILE__ ) ),
        'version' => $plugin_data['Version'] ?? null,
        'name' => $plugin_data['Plugin Name'] ?? null,
    ];
    return $plugins;
});

/**
 * Singleton class for setting up the plugin.
 *
 * @since  0.1
 * @access public
 */
class GA_Impact_Map {

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        $is_rest = dt_is_rest();

        if ( $is_rest && strpos( dt_get_url_path(), 'gospel-ambition-impact-map' ) !== false ) {
            require_once( 'rest-api/insert.php' ); // adds starter rest api class
            require_once( 'rest-api/rest-api.php' ); // adds starter rest api class
        }

        require_once( 'pages/magic-link-map.php' );
        require_once( 'pages/magic-link-home.php' );
        require_once( 'maps/loader.php' );

        if ( is_admin() ) { // adds links to the plugin description area in the plugin admin list.
            add_filter( 'plugin_row_meta', [ $this, 'plugin_description_links' ], 10, 4 );
        }
    }

    /**
     * Filters the array of row meta for each/specific plugin in the Plugins list table.
     * Appends additional links below each/specific plugin on the plugins page.
     */
    public function plugin_description_links( $links_array, $plugin_file_name, $plugin_data, $status ) {
        if ( strpos( $plugin_file_name, basename( __FILE__ ) ) ) {
            $links_array[] = '<a href="https://disciple.tools">Disciple.Tools Community</a>';
            $links_array[] = '<a href="https://gospelambition.org">Gospel Ambition</a>';
            $links_array[] = '<a href="https://zume.training">Zume</a>';
        }

        return $links_array;
    }

    /**
     * Method that runs only when the plugin is activated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function activation() {
        // add elements here that need to fire on activation

    }

    /**
     * Method that runs only when the plugin is deactivated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function deactivation() {
        // add functions here that need to happen on deactivation
        delete_option( 'dismissed-gospel-ambition-impact-map' );
    }

    /**
     * Loads the translation files.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function i18n() {
        $domain = 'gospel-ambition-impact-map';
        load_plugin_textdomain( $domain, false, trailingslashit( dirname( plugin_basename( __FILE__ ) ) ). 'languages' );
    }

    /**
     * Magic method to output a string if trying to use the object as a string.
     *
     * @since  0.1
     * @access public
     * @return string
     */
    public function __toString() {
        return 'gospel-ambition-impact-map';
    }

    /**
     * Magic method to keep the object from being cloned.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, 'Whoah, partner!', '0.1' );
    }

    /**
     * Magic method to keep the object from being unserialized.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, 'Whoah, partner!', '0.1' );
    }

    /**
     * Magic method to prevent a fatal error when calling a method that doesn't exist.
     *
     * @param string $method
     * @param array $args
     * @return null
     * @since  0.1
     * @access public
     */
    public function __call( $method = '', $args = array() ) {
        _doing_it_wrong( 'ga_impact_map::' . esc_html( $method ), 'Method does not exist.', '0.1' );
        unset( $method, $args );
        return null;
    }
}


// Register activation hook.
register_activation_hook( __FILE__, [ 'GA_Impact_Map', 'activation' ] );
register_deactivation_hook( __FILE__, [ 'GA_Impact_Map', 'deactivation' ] );


if ( ! function_exists( 'ga_impact_map_hook_admin_notice' ) ) {
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

add_action( 'plugins_loaded', function (){
    if ( is_admin() && !( is_multisite() && class_exists( "DT_Multisite" ) ) || wp_doing_cron() ){
        // Check for plugin updates
        if ( ! class_exists( 'Puc_v4_Factory' ) ) {
            if ( file_exists( get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php' )){
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

if ( ! function_exists( 'zume_languages' ) ) {
    /**
     * @param string $type 'code' or 'locale' or 'full'
     * @return array
     */
    function zume_languages( $type = 'code' ) {
        global $zume_languages_by_code, $zume_languages_by_locale, $zume_languages_full_list, $zume_languages_v5_ready;
        $list = array(
            'en' => array(
                'name' => 'English',
                'enDisplayName' => 'English',
                'code' => 'en',
                'displayCode' => 'en',
                'locale' => 'en',
                'weblate' => 'en',
                'nativeName' => 'English',
                'rtl' => false,
                'flag' => '🇺🇸',
                'population' => 500000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    // has a published version in zume 4.0
                    // if version 5 not ready, then language will be listed and redirect to legacy.zume.training
                    'translator_enabled' => true,
                    // enables the translator app to begin translation
                    'version_5_ready' => true,
                    // publishes publicly the version 5.0 system with minimum support
                    // has translated (weblate, scripts, activities, videos, files)
                    // allows the language to show up in the selection  list, and disables redirect to 4.0
                    'pieces_pages' => true,
                    'course_slides_download' => true,
                ],
            ),
            'am' => array(
                'name' => 'Amharic',
                'enDisplayName' => 'Amharic',
                'code' => 'am',
                'displayCode' => 'am',
                'locale' => 'amh',
                'weblate' => 'am',
                'nativeName' => 'አማርኛ',
                'rtl' => false,
                'flag' => '🇪🇹',
                'population' => 22000000,
                'enable_flags' => [
                    'version_4_available' => false,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'ar' => array(
                'name' => 'Arabic',
                'enDisplayName' => 'Arabic',
                'code' => 'ar',
                'displayCode' => 'ar',
                'locale' => 'ar',
                'weblate' => 'ar',
                'nativeName' => 'العربية',
                'rtl' => true,
                'flag' => '🇸🇦',
                'population' => 230000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'ar_jo' => array(
                'name' => 'Arabic (Jordanian)',
                'enDisplayName' => 'Arabic (Jordanian)',
                'code' => 'ar_jo',
                'displayCode' => 'ar_jo',
                'locale' => 'ar_JO',
                'weblate' => 'ar_JO',
                'nativeName' => 'العربية - الأردن',
                'rtl' => true,
                'flag' => '🇯🇴',
                'population' => 0,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'ar_tn' => array(
                'name' => 'Arabic (Tunisian)',
                'enDisplayName' => 'Arabic (Tunisian)',
                'code' => 'ar_tn',
                'displayCode' => 'ar_tn',
                'locale' => 'ar_TN',
                'weblate' => 'ar_TN',
                'nativeName' => ' العربية التونسية',
                'rtl' => true,
                'flag' => '🇹🇳',
                'population' => 0,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => true,
                ],
            ),
            'ar_ma' => array(
                'name' => 'Arabic (Moroccan)',
                'enDisplayName' => 'Arabic (Moroccan)',
                'code' => 'ar_ma',
                'displayCode' => 'ar_ma',
                'locale' => 'ar_MA',
                'weblate' => 'ar_MA',
                'nativeName' => 'العربية - الأردن',
                'rtl' => true,
                'flag' => '🇲🇦',
                'population' => 0,
                'enable_flags' => [
                    'version_4_available' => false,
                    'translator_enabled' => false,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'hy' => array(
                'name' => 'Armenian',
                'enDisplayName' => 'Armenian',
                'code' => 'hy',
                'displayCode' => 'hy',
                'locale' => 'hy',
                'weblate' => 'hy',
                'nativeName' => 'Armenian',
                'rtl' => false,
                'flag' => '🇦🇲',
                'population' => 5300000,
                'enable_flags' => [
                    'version_4_available' => false,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'az' => array(
                'name' => 'Azerbaijani',
                'enDisplayName' => 'Azerbaijani',
                'code' => 'az',
                'displayCode' => 'az',
                'locale' => 'az',
                'weblate' => 'az',
                'nativeName' => 'Azerbaijani',
                'rtl' => false,
                'flag' => '🇦🇿',
                'population' => 24000000,
                'enable_flags' => [
                    'version_4_available' => false,
                    'translator_enabled' => false,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'asl' => array(
                'name' => 'American Sign Language',
                'enDisplayName' => 'American Sign Language',
                'code' => 'asl',
                'displayCode' => 'asl',
                'locale' => 'asl',
                'weblate' => 'asl',
                'nativeName' => 'Sign Language',
                'rtl' => false,
                'flag' => '🤟',
                'population' => 15360000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'bn' => array(
                'name' => 'Bengali (India)',
                'enDisplayName' => 'Bengali (India)',
                'code' => 'bn',
                'displayCode' => 'bn',
                'locale' => 'bn_IN',
                'weblate' => 'bn_IN',
                'nativeName' => 'বাংলা',
                'rtl' => false,
                'flag' => '🇮🇳',
                'population' => 215000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'bho' => array(
                'name' => 'Bhojpuri',
                'enDisplayName' => 'Bhojpuri',
                'code' => 'bho',
                'displayCode' => 'bho',
                'locale' => 'bho',
                'weblate' => 'bho',
                'nativeName' => 'भोजपुरी',
                'rtl' => false,
                'flag' => '🇮🇳',
                'population' => 40000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'bs' => array(
                'name' => 'Bosnian',
                'enDisplayName' => 'Bosnian',
                'code' => 'bs',
                'displayCode' => 'bs',
                'locale' => 'bs_BA',
                'weblate' => 'bs_BA',
                'nativeName' => 'Bosanski',
                'rtl' => false,
                'flag' => '🇧🇦',
                'population' => 2600000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'bg' => array(
                'name' => 'Bulgarian',
                'enDisplayName' => 'Bulgarian',
                'code' => 'bg',
                'displayCode' => 'bg',
                'locale' => 'bg_BG',
                'weblate' => 'bg_BG',
                'nativeName' => 'български език',
                'rtl' => false,
                'flag' => '🇧🇬',
                'population' => 15000000,
                'enable_flags' => [
                    'version_4_available' => false,
                    'translator_enabled' => false,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'my' => array(
                'name' => 'Burmese',
                'enDisplayName' => 'Burmese',
                'code' => 'my',
                'displayCode' => 'my',
                'locale' => 'my',
                'weblate' => 'my',
                'nativeName' => 'မြန်မာဘာသာ',
                'rtl' => false,
                'flag' => '🇲🇲',
                'population' => 42000000,
                'enable_flags' => [
                    'version_4_available' => false,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'zhhk' => array(
                'name' => 'Cantonese (Traditional)',
                'enDisplayName' => 'Cantonese (Traditional)',
                'code' => 'zhhk',
                'displayCode' => 'zhhk',
                'locale' => 'zh_HK',
                'weblate' => 'zh_Hant_HK',
                'nativeName' => '中文（繁體,香港）',
                'rtl' => false,
                'flag' => '🇭🇰',
                'population' => 72000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'zhcn' => array(
                'name' => 'Chinese (Simplified)',
                'enDisplayName' => 'Chinese (Simplified)',
                'code' => 'zhcn',
                'displayCode' => 'zhcn',
                'locale' => 'zh_CN',
                'weblate' => 'zh_Hans',
                'nativeName' => '中文（简体）',
                'rtl' => false,
                'flag' => '🇨🇳',
                'population' => 1300000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => true,
                ],
            ),
            'zhtw' => array(
                'name' => 'Chinese (Traditional)',
                'enDisplayName' => 'Chinese (Traditional)',
                'code' => 'zhtw',
                'displayCode' => 'zhtw',
                'locale' => 'zh_TW',
                'weblate' => 'zh_Hant',
                'nativeName' => '中文（繁體）',
                'rtl' => false,
                'flag' => '🇹🇼',
                'population' => 0,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => true,
                ],
            ),
            'hr' => array(
                'name' => 'Croatian',
                'enDisplayName' => 'Croatian',
                'code' => 'hr',
                'displayCode' => 'hr',
                'locale' => 'hr',
                'weblate' => 'hr',
                'nativeName' => 'Hrvatski',
                'rtl' => false,
                'flag' => '🇭🇷',
                'population' => 6000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'fo' => array(
                'name' => 'Faroese',
                'enDisplayName' => 'Faroese',
                'code' => 'fo',
                'displayCode' => 'fo',
                'locale' => 'fo',
                'weblate' => 'fo',
                'nativeName' => 'Faroese',
                'rtl' => false,
                'flag' => '🇫🇴',
                'population' => 69000,
                'enable_flags' => [
                    'version_4_available' => false,
                    'translator_enabled' => false,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'fr' => array(
                'name' => 'French',
                'enDisplayName' => 'French',
                'code' => 'fr',
                'displayCode' => 'fr',
                'locale' => 'fr_FR',
                'weblate' => 'fr_FR',
                'nativeName' => 'Français',
                'rtl' => false,
                'flag' => '🇫🇷',
                'population' => 321000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'de' => array(
                'name' => 'German',
                'enDisplayName' => 'German',
                'code' => 'de',
                'displayCode' => 'de',
                'locale' => 'de_DE',
                'weblate' => 'de_DE',
                'nativeName' => 'Deutsch',
                'rtl' => false,
                'flag' => '🇩🇪',
                'population' => 229000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => true,
                ],
            ),
            'gu' => array(
                'name' => 'Gujarati',
                'enDisplayName' => 'Gujarati',
                'code' => 'gu',
                'displayCode' => 'gu',
                'locale' => 'gu',
                'weblate' => 'gu',
                'nativeName' => 'ગુજરાતી',
                'rtl' => false,
                'flag' => '🇮🇳',
                'population' => 210000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'ha' => array(
                'name' => 'Hausa',
                'enDisplayName' => 'Hausa',
                'code' => 'ha',
                'displayCode' => 'ha',
                'locale' => 'ha_NG',
                'weblate' => 'ha_NG',
                'nativeName' => 'Hausa',
                'rtl' => false,
                'flag' => '🇳🇬',
                'population' => 88000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'hi' => array(
                'name' => 'Hindi',
                'enDisplayName' => 'Hindi',
                'code' => 'hi',
                'displayCode' => 'hi',
                'locale' => 'hi_IN',
                'weblate' => 'hi_IN',
                'nativeName' => 'हिन्दी',
                'rtl' => false,
                'flag' => '🇮🇳',
                'population' => 615000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'id' => array(
                'name' => 'Indonesian',
                'enDisplayName' => 'Indonesian',
                'code' => 'id',
                'displayCode' => 'id',
                'locale' => 'id_ID',
                'weblate' => 'id_ID',
                'nativeName' => 'Bahasa Indonesia',
                'rtl' => false,
                'flag' => '🇮🇩',
                'population' => 200000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'it' => array(
                'name' => 'Italian',
                'enDisplayName' => 'Italian',
                'code' => 'it',
                'displayCode' => 'it',
                'locale' => 'it_IT',
                'weblate' => 'it_IT',
                'nativeName' => 'Italiano',
                'rtl' => false,
                'flag' => '🇮🇹',
                'population' => 64600000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'ja' => array(
                'name' => 'Japanese',
                'enDisplayName' => 'Japanese',
                'code' => 'ja',
                'displayCode' => 'ja',
                'locale' => 'ja',
                'weblate' => 'ja',
                'nativeName' => '日本語',
                'rtl' => false,
                'flag' => '🇯🇵',
                'population' => 126000000,
                'enable_flags' => [
                    'version_4_available' => false,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'kn' => array(
                'name' => 'Kannada',
                'enDisplayName' => 'Kannada',
                'code' => 'kn',
                'displayCode' => 'kn',
                'locale' => 'kn',
                'weblate' => 'kn',
                'nativeName' => 'ಕನ್ನಡ',
                'rtl' => false,
                'flag' => '🇮🇳',
                'population' => 47000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'ko' => array(
                'name' => 'Korean',
                'enDisplayName' => 'Korean',
                'code' => 'ko',
                'displayCode' => 'ko',
                'locale' => 'ko_KR',
                'weblate' => 'ko_KR',
                'nativeName' => '한국어',
                'rtl' => false,
                'flag' => '🇰🇷',
                'population' => 75000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'ku' => array(
                'name' => 'Kurdish',
                'enDisplayName' => 'Kurdish',
                'code' => 'ku',
                'displayCode' => 'ku',
                'locale' => 'ku',
                'weblate' => 'ku',
                'nativeName' => 'کوردی',
                'rtl' => true,
                'flag' => '🇮🇶',
                'population' => 26000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'lo' => array(
                'name' => 'Lao',
                'enDisplayName' => 'Lao',
                'code' => 'lo',
                'displayCode' => 'lo',
                'locale' => 'lo',
                'weblate' => 'lo',
                'nativeName' => 'ພາສາລາວ',
                'rtl' => false,
                'flag' => '🇱🇦',
                'population' => 3000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'lv' => array(
                'name' => 'Latvian',
                'enDisplayName' => 'Latvian',
                'code' => 'lv',
                'displayCode' => 'lv',
                'locale' => 'lv',
                'weblate' => 'lv',
                'nativeName' => 'Latviešu',
                'rtl' => false,
                'flag' => '🇱🇻',
                'population' => 1200000,
                'enable_flags' => [
                    'version_4_available' => false,
                    'translator_enabled' => false,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'mai' => array(
                'name' => 'Maithili',
                'enDisplayName' => 'Maithili',
                'code' => 'mai',
                'displayCode' => 'mai',
                'locale' => 'mai',
                'weblate' => 'mai',
                'nativeName' => '𑒧𑒻𑒟𑒱𑒪𑒲',
                'rtl' => false,
                'flag' => '🇮🇳',
                'population' => 50000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'ml' => array(
                'name' => 'Malayalam',
                'enDisplayName' => 'Malayalam',
                'code' => 'ml',
                'displayCode' => 'ml',
                'locale' => 'ml_IN',
                'weblate' => 'ml',
                'nativeName' => 'മലയാളം',
                'rtl' => false,
                'flag' => '🇮🇳',
                'population' => 35000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'mr' => array(
                'name' => 'Marathi',
                'enDisplayName' => 'Marathi',
                'code' => 'mr',
                'displayCode' => 'mr',
                'locale' => 'mr',
                'weblate' => 'mr',
                'nativeName' => 'मराठी',
                'rtl' => false,
                'flag' => '🇮🇳',
                'population' => 83000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'mn' => array(
                'name' => 'Mongolian',
                'enDisplayName' => 'Mongolian',
                'code' => 'mn',
                'displayCode' => 'mn',
                'locale' => 'mn',
                'weblate' => 'mn',
                'nativeName' => 'Монгол',
                'rtl' => false,
                'flag' => '🇲🇳',
                'population' => 9000000,
                'enable_flags' => [
                    'version_4_available' => false,
                    'translator_enabled' => false,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'ne' => array(
                'name' => 'Nepali',
                'enDisplayName' => 'Nepali',
                'code' => 'ne',
                'displayCode' => 'ne',
                'locale' => 'ne_NP',
                'weblate' => 'ne_NP',
                'nativeName' => 'नेपाली',
                'rtl' => false,
                'flag' => '🇳🇵',
                'population' => 32000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'or' => array(
                'name' => 'Oriya',
                'enDisplayName' => 'Oriya',
                'code' => 'or',
                'displayCode' => 'or',
                'locale' => 'or_IN',
                'weblate' => 'or_IN',
                'nativeName' => 'ଓଡ଼ିଆ',
                'rtl' => false,
                'flag' => '🇮🇳',
                'population' => 50000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'fa' => array(
                'name' => 'Persian/Farsi',
                'enDisplayName' => 'Persian/Farsi',
                'code' => 'fa',
                'displayCode' => 'fa',
                'locale' => 'fa_IR',
                'weblate' => 'fa_IR',
                'nativeName' => 'فارسی',
                'rtl' => true,
                'flag' => '🇮🇷',
                'population' => 62000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'pl ' => array(
                'name' => 'Polish',
                'enDisplayName' => 'Polish',
                'code' => 'pl',
                'displayCode' => 'pl',
                'locale' => 'pl_PL',
                'weblate' => 'pl_PL',
                'nativeName' => 'Polski',
                'rtl' => false,
                'flag' => '🇵🇱',
                'population' => 43000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'pt' => array(
                'name' => 'Portuguese',
                'enDisplayName' => 'Portuguese',
                'code' => 'pt',
                'displayCode' => 'pt',
                'locale' => 'pt_PT',
                'weblate' => 'pt_PT',
                'nativeName' => 'Português',
                'rtl' => false,
                'flag' => '🇵🇹',
                'population' => 300000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => true,
                ],
            ),
            'pa' => array(
                'name' => 'Punjabi',
                'enDisplayName' => 'Punjabi',
                'code' => 'pa',
                'displayCode' => 'pa',
                'locale' => 'pa_IN',
                'weblate' => 'pa_IN',
                'nativeName' => 'ਪੰਜਾਬੀ',
                'rtl' => false,
                'flag' => '🇮🇳',
                'population' => 210000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'pa_pk' => array(
                'name' => 'Punjabi (Western)',
                'enDisplayName' => 'Punjabi (Western)',
                'code' => 'pa_pk',
                'displayCode' => 'pa_pk',
                'locale' => 'pa_PK',
                'weblate' => 'pa_PK',
                'nativeName' => 'ਪੰਜਾਬੀ (ਪੱਛਮੀ)',
                'rtl' => false,
                'flag' => '🇵🇰',
                'population' => 80000000,
                'enable_flags' => [
                    'version_4_available' => false,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'ru' => array(
                'name' => 'Russian',
                'enDisplayName' => 'Russian',
                'code' => 'ru',
                'displayCode' => 'ru',
                'locale' => 'ru_RU',
                'weblate' => 'ru_RU',
                'nativeName' => 'Русский',
                'rtl' => false,
                'flag' => '🇷🇺',
                'population' => 258000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'ro' => array(
                'name' => 'Romanian',
                'enDisplayName' => 'Romanian',
                'code' => 'ro',
                'displayCode' => 'ro',
                'locale' => 'ro_RO',
                'weblate' => 'ro_RO',
                'nativeName' => 'Română',
                'rtl' => false,
                'flag' => '🇷🇴',
                'population' => 25000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'sl' => array(
                'name' => 'Slovenian',
                'enDisplayName' => 'Slovenian',
                'code' => 'sl',
                'displayCode' => 'sl',
                'locale' => 'sl_SI',
                'weblate' => 'sl_SI',
                'nativeName' => 'Slovenščina',
                'rtl' => false,
                'flag' => '🇸🇮',
                'population' => 2500000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => true,
                ],
            ),
            'so' => array(
                'name' => 'Somali',
                'enDisplayName' => 'Somali',
                'code' => 'so',
                'displayCode' => 'so',
                'locale' => 'so',
                'weblate' => 'so',
                'nativeName' => 'Soomaali',
                'rtl' => false,
                'flag' => '🇸🇴',
                'population' => 24000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'es' => array(
                'name' => 'Spanish',
                'enDisplayName' => 'Spanish',
                'code' => 'es',
                'displayCode' => 'es',
                'locale' => 'es',
                'weblate' => 'es',
                'nativeName' => 'Español',
                'rtl' => false,
                'flag' => '🇪🇸',
                'population' => 500000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => true,
                ],
            ),
            'es_es' => array(
                'name' => 'Spanish (Spain)',
                'enDisplayName' => 'Spanish (Spain)',
                'code' => 'es_es',
                'displayCode' => 'es_es',
                'locale' => 'es_ES',
                'weblate' => 'es_ES',
                'nativeName' => 'Español (España)',
                'rtl' => false,
                'flag' => '🇪🇸',
                'population' => 0,
                'enable_flags' => [
                    'version_4_available' => false,
                    'translator_enabled' => false,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'swa' => array(
                'name' => 'Swahili',
                'enDisplayName' => 'Swahili',
                'code' => 'swa',
                'displayCode' => 'swa',
                'locale' => 'swa',
                'weblate' => 'sw',
                'nativeName' => 'Kiswahili',
                'rtl' => false,
                'flag' => '🇹🇿',
                'population' => 200000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'ta' => array(
                'name' => 'Tamil',
                'enDisplayName' => 'Tamil',
                'code' => 'ta',
                'displayCode' => 'ta',
                'locale' => 'ta_IN',
                'weblate' => 'ta_IN',
                'nativeName' => 'தமிழ்',
                'rtl' => false,
                'flag' => '🇮🇳',
                'population' => 89000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'te' => array(
                'name' => 'Telugu',
                'enDisplayName' => 'Telugu',
                'code' => 'te',
                'displayCode' => 'te',
                'locale' => 'te',
                'weblate' => 'te',
                'nativeName' => 'తెలుగు',
                'rtl' => false,
                'flag' => '🇮🇳',
                'population' => 96000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'th' => array(
                'name' => 'Thai',
                'enDisplayName' => 'Thai',
                'code' => 'th',
                'displayCode' => 'th',
                'locale' => 'th',
                'weblate' => 'th',
                'nativeName' => 'ไทย',
                'rtl' => false,
                'flag' => '🇹🇭',
                'population' => 69000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'tr' => array(
                'name' => 'Turkish',
                'enDisplayName' => 'Turkish',
                'code' => 'tr',
                'displayCode' => 'tr',
                'locale' => 'tr_TR',
                'weblate' => 'tr_TR',
                'nativeName' => 'Türkçe',
                'rtl' => false,
                'flag' => '🇹🇷',
                'population' => 80000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'uk' => array(
                'name' => 'Ukrainian',
                'enDisplayName' => 'Ukrainian',
                'code' => 'uk',
                'displayCode' => 'uk',
                'locale' => 'uk',
                'weblate' => 'uk',
                'nativeName' => 'Україна',
                'rtl' => true,
                'flag' => '🇺🇦',
                'population' => 45000000,
                'enable_flags' => [
                    'version_4_available' => false,
                    'translator_enabled' => false,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'ur' => array(
                'name' => 'Urdu',
                'enDisplayName' => 'Urdu',
                'code' => 'ur',
                'displayCode' => 'ur',
                'locale' => 'ur',
                'weblate' => 'ur',
                'nativeName' => 'اردو',
                'rtl' => true,
                'flag' => '🇵🇰',
                'population' => 230000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => false,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
            'vi' => array(
                'name' => 'Vietnamese',
                'enDisplayName' => 'Vietnamese',
                'code' => 'vi',
                'displayCode' => 'vi',
                'locale' => 'vi',
                'weblate' => 'vi',
                'nativeName' => 'Tiếng Việt',
                'rtl' => false,
                'flag' => '🇻🇳',
                'population' => 85000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => true,
                    'course_slides_download' => false,
                ],
            ),
            'yo' => array(
                'name' => 'Yoruba',
                'enDisplayName' => 'Yoruba',
                'code' => 'yo',
                'displayCode' => 'yo',
                'locale' => 'yo',
                'weblate' => 'yo',
                'nativeName' => 'Yorùbá',
                'rtl' => false,
                'flag' => '🇳🇬',
                'population' => 47000000,
                'enable_flags' => [
                    'version_4_available' => true,
                    'translator_enabled' => true,
                    'version_5_ready' => true,
                    'pieces_pages' => false,
                    'course_slides_download' => false,
                ],
            ),
        );
        foreach ( $list as $lang ) {
            if ( $lang['enable_flags']['version_5_ready'] || $lang['enable_flags']['version_4_available'] ) {
                $zume_languages_by_code[$lang['code']] = $lang;
                $zume_languages_by_locale[$lang['locale']] = $lang;
            }
            if ( $lang['enable_flags']['version_5_ready'] ) {
                $zume_languages_v5_ready[$lang['code']] = $lang;
            }
            if ( $lang['enable_flags']['translator_enabled'] ) {
                $zume_languages_full_list[$lang['code']] = $lang;
            }
        }

        if ( $type === 'full' ) {
            return $zume_languages_full_list;
        }
        else if ( $type === 'v5_only' ) {
            return $zume_languages_v5_ready;
        }
        else if ( $type === 'locale' ) {
            return $zume_languages_by_locale;
        } else {
            return $zume_languages_by_code;
        }
    }
    zume_languages();
}
