<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class GO_Impact_Map_Endpoints
{
    private static $_instance = null;
    public $namespace = 'gospel-ambition-impact-map/v1';
    public function add_api_routes() {
        $namespace = $this->namespace;

        register_rest_route(
            $namespace, '/endpoint', [
                'methods'  => 'POST',
                'callback' => [ $this, 'endpoint' ],
                'permission_callback' => '__return_true',
            ]
        );
    }
    public function endpoint( WP_REST_Request $request ) {
        $logs = dt_recursive_sanitize_array( $request->get_params() );

        if ( ! is_array( $logs ) ) {
            return false;
        }

        dt_write_log(__METHOD__);
        dt_write_log($logs);

        // modify time
        $current_time = time();

        foreach( $logs as $i => $v ) {
            $logs[$i]['time'] = $current_time + ( $current_time - (int) $v['time'] );
        }

        // complete location information
        $geocoder = new Location_Grid_Geocoder();
        foreach( $logs as $i => $v ) {
            if ( isset( $v['location']['grid_id'] ) && ! empty( $v['location']['grid_id'] ) ) {
                // convert grid id in to full lgm
                $row = Disciple_Tools_Mapping_Queries::get_full_name_by_grid_id( $v['location']['grid_id'] );
            }
            else if ( isset( $v['location']['lng'] ) && ! empty( $v['location']['lng'] ) ) {
                $row = $geocoder->get_grid_id_by_lnglat( $v['location']['lng'], $v['location']['lat'] );
            }
            else if ( isset( $v['location']['ip'] ) && ! empty( $v['location']['ip'] ) ) {
                $row = DT_Ipstack_API::convert_ip_result_to_location_grid_meta( DT_Ipstack_API::geocode_ip_address( $v['location']['ip'] ) );
            }
            else {
                $row = [];
            }
            dt_write_log($row);
            $logs[$i]['lng'] = $row['lng'] ?? null;
            $logs[$i]['lat'] = $row['lat'] ?? null;
            $logs[$i]['level'] = $row['level'] ?? null;
            $logs[$i]['label'] = $row['label'] ?? null;
            $logs[$i]['grid_id'] = $row['grid_id'] ?? null;
        }

        foreach( $logs as $i => $v ) {
            $logs[$i]['type'] = $this->_create_type( $v );
            $logs[$i]['payload'] = $this->_create_string( $v );
        }


        foreach( $logs as $i => $v ) {
            $args = [
                'post_type' => $v['post_type'],
                'type' => $v['type'],
                'subtype' => $v['subtype'],
                'payload' => $v['payload'],
                'value' => 1,
                'lng' => $v['lng'],
                'lat' => $v['lat'],
                'level' => $v['level'],
                'label' => $v['label'],
                'grid_id' => $v['grid_id'],
                'time_end' => $v['time'] ?? time(),
                'language_code' => $v['language_code'] ?? 'en',
            ];
            GO_Impact_Map_Insert::insert( $args );
        }

        return true;
    }
    public function _create_string( $log ) {
        $string = '';
        switch( $log['subtype'] ) {
            case 'prayer_for_location':
                $string = 'Someone is praying for '.$log['label'].'.';
                break;
            case 'prayer_person_location':
                $string = 'Someone in '.$log['label'].' is praying for global disciple making movement.';
                break;
            case 'created_custom_lap':
                $string = 'Someone created a custom prayer lap to mobilize others to pray.';
                break;
            case 'lap_completed':
                $string = 'One entire prayer lap around the world just completed.';
                break;
            case 'registered':
                $string = 'Someone is joining prayer global.';
                break;
            default:
                break;
        }
        return $string;
    }
    public function _create_type( $log ) {
        $string = '';
        switch( $log['subtype'] ) {
            case 'prayer_for_location':
            case 'prayer_person_location':
            case 'created_custom_lap':
            case 'lap_completed':
            case 'registered':
                $string = 'praying';
                break;
            default:
                break;
        }
        return $string;
    }

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
        add_filter( 'dt_allow_rest_access', [$this, 'authorize_url'], 10, 1 );
    }
    public function authorize_url( $authorized )
    {
        if ( isset( $_SERVER['REQUEST_URI'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), $this->namespace ) !== false ) {
            $authorized = true;
        }
        return $authorized;
    }
}
GO_Impact_Map_Endpoints::instance();

