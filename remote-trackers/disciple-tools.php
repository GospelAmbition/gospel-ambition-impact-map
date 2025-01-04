<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

// convert the site report log event
add_action( 'dt_usage_telemetry_insert', function( $args, $result ) {

    $languages = array_keys( $user_languages );

    $site_url = $args['site_url'];
    $ip = gethostbyname( $site_url );

    add_log_to_queue( [
        'post_type' => 'disciple_tools',
        'type' => 'practicing',
        'subtype' => 'site_report',
        'time' => strtotime( $args['timestamp'] ), // time
        'language_code' => $languages[0] ?? 'en', // language
        'location' => [
            'ip' => $ip,
        ],
    ] );
}, 10, 2 );

// record download
// @todo: add download log event

// record demos launched
// @todo: add demo log event




 /**
*  do_action( 'dt_usage_telemetry_insert', $args, $result );
 * Array
(
    [site_id] => b82bc0e411673622385bd79a552a0c15784e96ebc0f9fe7c9e5cb39a95d1659d
    [usage_version] => 7
    [php_version] => 8.1.30
    [wp_version] => 6.6.2
    [wp_db_version] => 57155
    [site_url] => https://demos.disciple.tools/itpastor
    [server_url] => http://demos.disciple.tools/
    [theme_version] => 1.68.1
    [in_debug] => 0
    [active_contacts] => 0
    [total_contacts] => 1
    [active_groups] => 0
    [total_groups] => 0
    [active_churches] => 0
    [total_churches] => 0
    [active_users] => 0
    [total_users] => 1
    [user_languages] => Array
        (
            [en_US] => 1
        )

    [has_demo_data] => 0
    [regions] => 0
    [timestamp] => 2024-11-25
    [active_plugins] => Array
        (
            [0] => wppusher.php
            [1] => disciple-tools-import.php
            [2] => disciple-tools-webform.php
            [3] => coder-invitation.php
            [4] => unconfirmed.php
            [5] => disciple-tools-demo-content.php
            [6] => disciple-tools-multisite.php
            [7] => wordfence.php
            [8] => disciple-tools-dashboard.php
            [9] => crm-link.php
            [10] => mailgun.php
            [11] => disciple-tools-facebook.php
            [12] => disciple-tools-share-app.php
            [13] => disciple-tools-prayer-campaigns.php
            [14] => org-multisite-config.php
            [15] => disciple-tools-bulk-magic-link-sender.php
        )

    [using_mapbox] => 1
    [using_google_geocode] => 0
    [is_multisite] => 1
)
 */
