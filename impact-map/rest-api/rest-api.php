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
//        $current_time = time();
//
//        foreach( $logs as $i => $v ) {
//            $logs[$i]['time'] = $current_time + ( $current_time - (int) $v['time'] );
//        }

        // complete location information
        $ip_list = [];
        $geocoder = new Location_Grid_Geocoder();
        foreach( $logs as $i => $v ) {
            if ( isset( $v['location']['grid_id'] ) && ! empty( $v['location']['grid_id'] ) ) {
                // convert grid id in to full lgm
                $row = Disciple_Tools_Mapping_Queries::get_by_grid_id( $v['location']['grid_id'] );
                if ( ! empty( $row ) ) {
                    $row['label'] = $geocoder->_format_full_name( $row );
                    $row['lng'] = $row['longitude'];
                    $row['lat'] = $row['latitude'];
                    $row['level'] = $row['level_name'];
                }
            }
            else if ( isset( $v['location']['lng'] ) && ! empty( $v['location']['lng'] ) ) {
                $row = $geocoder->get_grid_id_by_lnglat( $v['location']['lng'], $v['location']['lat'] );
                if ( ! empty( $row ) ) {
                    $row['label'] = $geocoder->_format_full_name( $row );
                }
            }
            else if ( isset( $v['location']['ip'] ) && ! empty( $v['location']['ip'] ) ) {
                // test if ip address already been retrieved
                if ( isset( $ip_list[$v['location']['ip']] ) ) {
                    $row = $ip_list[$v['location']['ip']];
                }
                else {
                    $result = DT_Ipstack_API::geocode_ip_address( $v['location']['ip'] );
                    if ( isset( $result['longitude'] ) ) {
                        $row = DT_Ipstack_API::convert_ip_result_to_location_grid_meta( $result );
                        if ( ! empty( $row ) ) {
                            $row['label'] = $geocoder->_format_full_name( $row );
                        }
                        $ip_list[$v['location']['ip']] = $row;
                    }
                }

            }
            else {
                $row = [];
            }

            $logs[$i]['lng'] = $row['lng'] ?? null;
            $logs[$i]['lat'] = $row['lat'] ?? null;
            $logs[$i]['level'] = $row['level'] ?? null;
            $logs[$i]['label'] = $row['label'] ?? null;
            $logs[$i]['grid_id'] = $row['grid_id'] ?? null;
        }

        foreach( $logs as $i => $v ) {
            $logs[$i]['type'] = $v['type'] ?? $this->_create_type( $v );
            $logs[$i]['payload'] = $this->_create_string( $v );
            $logs[$i]['language_code'] = $this->_create_language_code( $v );
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
    public function _create_type( $log ) {
        $string = '';
        switch( $log['subtype'] ) {

            // PRAYER GLOBAL
            case 'prayer_for_location':
            case 'prayer_person_location':
            case 'created_custom_lap':
            case 'lap_completed':
            case 'pg_registered':
                $string = 'praying';
                break;

            // PRAYER GLOBAL
            case 'pt_registered':
                $string = 'praying';
                break;

            // ZUME - COACHING
            case '10_assisting':
            case '10_launching':
            case '10_modeling':
            case '10_watching':
            case '11_assisting':
            case '11_launching':
            case '11_modeling':
            case '11_watching':
            case '12_assisting':
            case '12_launching':
            case '12_modeling':
            case '12_watching':
            case '13_assisting':
            case '13_launching':
            case '13_modeling':
            case '13_watching':
            case '16_assisting':
            case '16_launching':
            case '16_modeling':
            case '16_watching':
            case '17_assisting':
            case '17_launching':
            case '17_modeling':
            case '17_watching':
            case '20_assisting':
            case '20_launching':
            case '20_modeling':
            case '20_watching':
            case '21_assisting':
            case '21_launching':
            case '21_modeling':
            case '21_watching':
            case '22_assisting':
            case '22_launching':
            case '22_modeling':
            case '22_watching':
            case '26_assisting':
            case '26_launching':
            case '26_modeling':
            case '26_watching':
            case '31_assisting':
            case '31_launching':
            case '31_modeling':
            case '31_watching':
            case '32_assisting':
            case '32_launching':
            case '32_modeling':
            case '32_watching':
            case '33_assisting':
            case '33_launching':
            case '33_modeling':
            case '33_watching':
            case '4_assisting':
            case '4_launching':
            case '4_modeling':
            case '4_watching':
            case '5_assisting':
            case '5_launching':
            case '5_modeling':
            case '5_watching':
            case '7_assisting':
            case '7_launching':
            case '7_modeling':
            case '7_watching':
            case '8_assisting':
            case '8_launching':
            case '8_modeling':
            case '8_watching':
            case 'mawl_completed':
            case 'requested_a_coach':
                $string = 'coaching';
                break;

            // ZUME - PRACTICING
            case 'join_community':
            case 'new_church':
            case 'seeing_generational_fruit':
                $string = 'practicing';
                break;

            // ZUME - TRAINING
            case '1_heard':
            case '1_obeyed':
            case '1_shared':
            case '1_trained':
            case '10_heard':
            case '10_obeyed':
            case '10_shared':
            case '10_trained':
            case '11_heard':
            case '11_obeyed':
            case '11_shared':
            case '11_trained':
            case '12_heard':
            case '12_obeyed':
            case '12_shared':
            case '12_trained':
            case '13_heard':
            case '13_obeyed':
            case '13_shared':
            case '13_trained':
            case '14_heard':
            case '14_obeyed':
            case '14_shared':
            case '14_trained':
            case '15_heard':
            case '15_obeyed':
            case '15_shared':
            case '15_trained':
            case '16_heard':
            case '16_obeyed':
            case '16_shared':
            case '16_trained':
            case '17_heard':
            case '17_obeyed':
            case '17_shared':
            case '17_trained':
            case '18_heard':
            case '18_obeyed':
            case '18_shared':
            case '18_trained':
            case '19_heard':
            case '19_obeyed':
            case '19_shared':
            case '19_trained':
            case '2_heard':
            case '2_obeyed':
            case '2_shared':
            case '2_trained':
            case '20_heard':
            case '20_obeyed':
            case '20_shared':
            case '20_trained':
            case '21_heard':
            case '21_obeyed':
            case '21_shared':
            case '21_trained':
            case '22_heard':
            case '22_obeyed':
            case '22_shared':
            case '22_trained':
            case '23_heard':
            case '23_obeyed':
            case '23_shared':
            case '23_trained':
            case '24_heard':
            case '24_obeyed':
            case '24_shared':
            case '24_trained':
            case '25_heard':
            case '25_obeyed':
            case '25_shared':
            case '25_trained':
            case '26_heard':
            case '26_obeyed':
            case '26_shared':
            case '26_trained':
            case '27_heard':
            case '27_obeyed':
            case '27_shared':
            case '27_trained':
            case '28_heard':
            case '28_obeyed':
            case '28_shared':
            case '28_trained':
            case '29_heard':
            case '29_obeyed':
            case '29_shared':
            case '29_trained':
            case '3_heard':
            case '3_obeyed':
            case '3_shared':
            case '3_trained':
            case '30_heard':
            case '30_obeyed':
            case '30_shared':
            case '30_trained':
            case '31_heard':
            case '31_obeyed':
            case '31_shared':
            case '31_trained':
            case '32_heard':
            case '32_obeyed':
            case '32_shared':
            case '32_trained':
            case '33_heard':
            case '33_obeyed':
            case '33_shared':
            case '33_trained':
            case '4_heard':
            case '4_obeyed':
            case '4_shared':
            case '4_trained':
            case '5_heard':
            case '5_obeyed':
            case '5_shared':
            case '5_trained':
            case '6_heard':
            case '6_obeyed':
            case '6_shared':
            case '6_trained':
            case '7_heard':
            case '7_obeyed':
            case '7_shared':
            case '7_trained':
            case '8_heard':
            case '8_obeyed':
            case '8_shared':
            case '8_trained':
            case '9_heard':
            case '9_obeyed':
            case '9_shared':
            case '9_trained':
            case 'host_completed':
            case 'joined_friends_training':
            case 'joined_online_training':
            case 'plan_created':
            case 'registered':
            case 'set_a_01':
            case 'set_a_02':
            case 'set_a_03':
            case 'set_a_04':
            case 'set_a_05':
            case 'set_a_06':
            case 'set_a_07':
            case 'set_a_08':
            case 'set_a_09':
            case 'set_a_10':
            case 'set_b_01':
            case 'set_b_02':
            case 'set_b_03':
            case 'set_b_04':
            case 'set_b_05':
            case 'set_b_06':
            case 'set_b_07':
            case 'set_b_09':
            case 'set_b_10':
            case 'set_b_11':
            case 'set_b_12':
            case 'set_b_13':
            case 'set_b_14':
            case 'set_b_15':
            case 'set_b_16':
            case 'set_b_17':
            case 'set_b_18':
            case 'set_b_19':
            case 'set_b_20':
            case 'set_c_1':
            case 'set_c_2':
            case 'set_c_3':
            case 'set_c_4':
            case 'set_c_5':
            case 'training_completed':
                $string = 'training';
                break;

            case 'guidebook_10':
            case 'guidebook_20':
            case 'guidebook_5':
            case 'guidebook_v4_10':
            case 'keynote_10':
            case 'keynote_20':
            case 'keynote_5':
            case 'order_print_copy':
            case 'powerpoint_10':
            case 'powerpoint_20':
            case 'powerpoint_5':
                $string = 'downloading';
                break;


            default:
                break;
        }
        return $string;
    }
    public function _create_string( $log ) {
        $string = '';
        switch( $log['subtype'] ) {

            // PRAYER GLOBAL
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
            case 'pg_registered':
                $string = 'Someone is joining prayer global.';
                break;


            case 'pt_registered':
                $string = 'Someone in '.$log['label'].' has joined a strategic prayer campaign.';
                break;
            case 'recurring_signup':
                $string = 'Someone in is praying for a strategic prayer campaign in '.$log['label'].'.';
                break;

                // ZUME - TRAINING
            case '1_heard':
            case '1_obeyed':
            case '1_shared':
            case '1_trained':
            case '10_heard':
            case '10_obeyed':
            case '10_shared':
            case '10_trained':
            case '11_heard':
            case '11_obeyed':
            case '11_shared':
            case '11_trained':
            case '12_heard':
            case '12_obeyed':
            case '12_shared':
            case '12_trained':
            case '13_heard':
            case '13_obeyed':
            case '13_shared':
            case '13_trained':
            case '14_heard':
            case '14_obeyed':
            case '14_shared':
            case '14_trained':
            case '15_heard':
            case '15_obeyed':
            case '15_shared':
            case '15_trained':
            case '16_heard':
            case '16_obeyed':
            case '16_shared':
            case '16_trained':
            case '17_heard':
            case '17_obeyed':
            case '17_shared':
            case '17_trained':
            case '18_heard':
            case '18_obeyed':
            case '18_shared':
            case '18_trained':
            case '19_heard':
            case '19_obeyed':
            case '19_shared':
            case '19_trained':
            case '2_heard':
            case '2_obeyed':
            case '2_shared':
            case '2_trained':
            case '20_heard':
            case '20_obeyed':
            case '20_shared':
            case '20_trained':
            case '21_heard':
            case '21_obeyed':
            case '21_shared':
            case '21_trained':
            case '22_heard':
            case '22_obeyed':
            case '22_shared':
            case '22_trained':
            case '23_heard':
            case '23_obeyed':
            case '23_shared':
            case '23_trained':
            case '24_heard':
            case '24_obeyed':
            case '24_shared':
            case '24_trained':
            case '25_heard':
            case '25_obeyed':
            case '25_shared':
            case '25_trained':
            case '26_heard':
            case '26_obeyed':
            case '26_shared':
            case '26_trained':
            case '27_heard':
            case '27_obeyed':
            case '27_shared':
            case '27_trained':
            case '28_heard':
            case '28_obeyed':
            case '28_shared':
            case '28_trained':
            case '29_heard':
            case '29_obeyed':
            case '29_shared':
            case '29_trained':
            case '3_heard':
            case '3_obeyed':
            case '3_shared':
            case '3_trained':
            case '30_heard':
            case '30_obeyed':
            case '30_shared':
            case '30_trained':
            case '31_heard':
            case '31_obeyed':
            case '31_shared':
            case '31_trained':
            case '32_heard':
            case '32_obeyed':
            case '32_shared':
            case '32_trained':
            case '33_heard':
            case '33_obeyed':
            case '33_shared':
            case '33_trained':
            case '4_heard':
            case '4_obeyed':
            case '4_shared':
            case '4_trained':
            case '5_heard':
            case '5_obeyed':
            case '5_shared':
            case '5_trained':
            case '6_heard':
            case '6_obeyed':
            case '6_shared':
            case '6_trained':
            case '7_heard':
            case '7_obeyed':
            case '7_shared':
            case '7_trained':
            case '8_heard':
            case '8_obeyed':
            case '8_shared':
            case '8_trained':
            case '9_heard':
            case '9_obeyed':
            case '9_shared':
            case '9_trained':
            case 'host_completed':
            case 'joined_friends_training':
            case 'joined_online_training':
            case 'plan_created':
            case 'registered':
            case 'set_a_01':
            case 'set_a_02':
            case 'set_a_03':
            case 'set_a_04':
            case 'set_a_05':
            case 'set_a_06':
            case 'set_a_07':
            case 'set_a_08':
            case 'set_a_09':
            case 'set_a_10':
            case 'set_b_01':
            case 'set_b_02':
            case 'set_b_03':
            case 'set_b_04':
            case 'set_b_05':
            case 'set_b_06':
            case 'set_b_07':
            case 'set_b_09':
            case 'set_b_10':
            case 'set_b_11':
            case 'set_b_12':
            case 'set_b_13':
            case 'set_b_14':
            case 'set_b_15':
            case 'set_b_16':
            case 'set_b_17':
            case 'set_b_18':
            case 'set_b_19':
            case 'set_b_20':
            case 'set_c_1':
            case 'set_c_2':
            case 'set_c_3':
            case 'set_c_4':
            case 'set_c_5':
            case 'training_completed':
                $string = 'Someone is training in Zume for '.$log['subtype'].'.';
                break;




            default:
                break;
        }
        return $string;
    }
    public function _create_language_code( $log ) {
        if ( empty( $log['language_code'] ) ) {
            return 'en';
        }

        $lang_array = explode( '_', $log['language_code'] );

        return $lang_array[0] ?? $log['language_code'];
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

