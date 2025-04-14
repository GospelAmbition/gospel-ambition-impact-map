<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

/**
 * Class GO_Impact_Map_Endpoints
 * 
 * Handles the REST API endpoints for the Impact Map.
 * Processes incoming data, geocodes locations, and stores records in the database.
 *
 * @since 0.1
 */
class GO_Impact_Map_Endpoints
{
    private static $_instance = null;
    public $namespace = 'gospel-ambition-impact-map/v1';
    
    /**
     * Registers the REST API routes.
     *
     * @since  0.1
     * @access public
     * @return void
     */
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
    
    /**
     * Handles the main endpoint requests.
     * 
     * Processes incoming log data, enhances it with location information,
     * and stores it in the database.
     *
     * @since  0.1
     * @access public
     * @param  WP_REST_Request $request The request object.
     * @return bool True on success, false on failure.
     */
    public function endpoint( WP_REST_Request $request ) {
        $logs = dt_recursive_sanitize_array( $request->get_params() );

        if ( ! is_array( $logs ) ) {
            return false;
        }

        // complete location information
        $ip_list = [];
        $ip_other_list = [];
        $geocoder = new Location_Grid_Geocoder();
        foreach ( $logs as $i => $v ) {

            // PRIMARY LOCATION
            $row = [];
            if ( isset( $v['location']['grid_id'] ) && ! empty( $v['location']['grid_id'] ) ) {
                // convert grid id in to full lgm
                $row = Disciple_Tools_Mapping_Queries::get_by_grid_id( $v['location']['grid_id'] );
                if ( ! empty( $row ) ) {
                    $row['label'] = $geocoder->_format_full_name( $row );
                    $row['lng'] = $row['longitude'];
                    $row['lat'] = $row['latitude'];
                    $row['level'] = $row['level_name'];
                    $row['grid_id'] = $row['grid_id'];
                }
            }
            else if ( isset( $v['location']['lng'] ) && ! empty( $v['location']['lng'] ) ) {
                $row = $geocoder->get_grid_id_by_lnglat( $v['location']['lng'], $v['location']['lat'] );
                $row = Disciple_Tools_Mapping_Queries::get_by_grid_id( $v['location']['grid_id'] );
                if ( ! empty( $row ) ) {
                    $row['label'] = $geocoder->_format_full_name( $row );
                    $row['lng'] = $row['longitude'];
                    $row['lat'] = $row['latitude'];
                    $row['level'] = $row['level_name'];
                    $row['grid_id'] = $row['grid_id'];
                }
            }
            else if ( isset( $v['location']['ip'] ) && ! empty( $v['location']['ip'] ) ) {
                // test if ip address already been retrieved
                if ( isset( $ip_list[$v['location']['ip']] ) ) {
                    // already queried ip address
                    // dt_write_log( 'ip address already been retrieved' );
                    $row = $ip_list[$v['location']['ip']];
                }
                else {
                    // lookup ip address
                    // dt_write_log( 'lookup ip address' );
                    $result = DT_Ipstack_API::geocode_ip_address( $v['location']['ip'] );
                    $lgm = DT_Ipstack_API::convert_ip_result_to_location_grid_meta( $result );
                    $row = Disciple_Tools_Mapping_Queries::get_by_grid_id( $lgm['grid_id'] );
                    if ( ! empty( $row ) ) {
                        $row['label'] = $geocoder->_format_full_name( $row );
                        $row['lng'] = $row['longitude'];
                        $row['lat'] = $row['latitude'];
                        $row['level'] = $row['level_name'];
                        $row['grid_id'] = $row['grid_id'];

                        $ip_list[$v['location']['ip']] = $row;
                    }
                }
            }

            $logs[$i]['lng'] = $row['lng'] ?? null;
            $logs[$i]['lat'] = $row['lat'] ?? null;
            $logs[$i]['level'] = $row['level'] ?? null;
            $logs[$i]['label'] = $row['label'] ?? null;
            $logs[$i]['grid_id'] = $row['grid_id'] ?? null;
            // END PRIMARY LOCATION


            // ADDITIONAL LOCATION
            if ( isset( $v['data']['location'] ) && ! empty( $v['data']['location'] ) ) {
                $other_string_location = [];
                $data_location = $v['data']['location'];
                if ( isset( $data_location['grid_id'] ) && ! empty( $data_location['grid_id'] ) ) {
                    // convert grid id in to full lgm
                    $string_row = Disciple_Tools_Mapping_Queries::get_by_grid_id( $data_location['grid_id'] );
                    if ( ! empty( $string_row ) ) {
                        $other_string_location['name'] = $string_row['name'];
                        $other_string_location['full_name'] = $geocoder->_format_full_name( $string_row );
                        $elements = explode( ',', $other_string_location['full_name'] );
                        $other_string_location['country'] = $elements[5] ?? $elements[4] ?? $elements[3] ?? $elements[2] ?? $elements[1] ?? $elements[0];
                    }
                }
                else if ( isset( $data_location['lng'] ) && ! empty( $data_location['lng'] ) ) {
                    $string_row = $geocoder->get_grid_id_by_lnglat( $data_location['lng'], $data_location['lat'] );
                    if ( ! empty( $row ) ) {
                        $other_string_location['name'] = $string_row['name'];
                        $other_string_location['full_name'] = $geocoder->_format_full_name( $string_row );
                        $elements = explode( ',', $other_string_location['full_name'] );
                        $other_string_location['country'] = $elements[5] ?? $elements[4] ?? $elements[3] ?? $elements[2] ?? $elements[1] ?? $elements[0];
                    }
                }
                else if ( isset( $data_location['ip'] ) && ! empty( $data_location['ip'] ) ) {
                    // test if ip address already been retrieved
                    if ( isset( $ip_other_list[$data_location['ip']] ) ) {
                        $other_string_location = $ip_other_list[$data_location['ip']];
                    }
                    else {
                        $result = DT_Ipstack_API::geocode_ip_address( $data_location['ip'] );
                        if ( isset( $result['longitude'] ) ) {
                            $lgm = DT_Ipstack_API::convert_ip_result_to_location_grid_meta( $result );
                            $string_row = Disciple_Tools_Mapping_Queries::get_by_grid_id( $lgm['grid_id'] );

                            if ( ! empty( $string_row ) ) {
                                $other_string_location['name'] = $string_row['name'];
                                $other_string_location['full_name'] = $geocoder->_format_full_name( $string_row );
                                $elements = explode( ',', $other_string_location['full_name'] );
                                $other_string_location['country'] = $elements[5] ?? $elements[4] ?? $elements[3] ?? $elements[2] ?? $elements[1] ?? $elements[0];

                                $ip_other_list[$data_location['ip']] = $other_string_location;
                            }
                        }
                    }
                }
                $logs[$i]['data']['location'] = $other_string_location;
            }
            // END ADDITIONAL LOCATION
        }

        foreach ( $logs as $i => $v ) {
            $logs[$i]['language_code'] = $this->_create_language_code( $v );
            $logs[$i]['payload'] = $this->_create_string( $v );
        }

        // dt_write_log( $logs );

        foreach ( $logs as $i => $v ) {
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

        // dt_write_log( __METHOD__ . ' END' );
        return true;
    }

    /**
     * Creates a string representation of the log for storage.
     * 
     * Determines the source of the log and calls the appropriate string formatter.
     *
     * @since  0.1
     * @access public
     * @param  array $log The log data.
     * @return string The formatted string representation.
     */
    public function _create_string( $log ) {

        $string = '';

        $string = $this->_strings_for_prayer_global( $log );
        if ( !empty( $string ) ) {
            return $string;
        }

        $string = $this->_strings_for_prayer_tools( $log );
        if ( !empty( $string ) ) {
            return $string;
        }

        $string = $this->_strings_for_disciple_tools( $log );
        if ( !empty( $string ) ) {
            return $string;
        }

        $string = $this->_strings_for_kingdom_training( $log );
        if ( !empty( $string ) ) {
            return $string;
        }

        $string = $this->_strings_for_zume( $log );
        if ( !empty( $string ) ) {
            return $string;
        }

        return $string;
    }
    /**
     * Creates string representations for different log types.
     * 
     * The following methods (_strings_for_*) handle formatting logs from different
     * sources into human-readable strings for display and storage.
     * 
     * Each method processes a specific application's logs based on the subtype
     * and returns an appropriate string description of the activity.
     * 
     * The language string helper adds localization context when applicable.
     */
    public function _add_language_string( $log ) {
        $langauges = impact_map_languages();
        $string = '';
        if ( isset( $log['language_code'] ) && ! empty( $log['language_code'] ) && ! in_array( $log['language_code'], [ 'en', 'en_US', '' ] ) ) {
            $string = ' in '.$langauges[$log['language_code']];
        }
        return $string;
    }
    /*
    *  PRAYER GLOBAL
    *  PRAYER TOOLS
    *  DISCIPLE TOOLS
    *  KINGDOM TRAINING
    *  ZUME
    */

    // PRAYER GLOBAL
    /**
     * Creates string representations for Prayer Global logs.
     * 
     * Processes Prayer Global application logs based on the subtype
     * and returns an appropriate string description of the activity.
     * 
     * Handles various prayer activities such as location-based prayers,
     * prayer laps, and user registrations.
     *
     * @since  0.1
     * @access public
     * @param  array $log The log data to process.
     * @return string The formatted string representation of the log.
     */
    public function _strings_for_prayer_global( $log ) {
        $string = '';
        switch ( $log['subtype'] ) {

            // PRAYER GLOBAL
            case 'prayer_for_location':
                $string = $log['label'].' is being covered in prayer.';
                break;
            case 'prayer_person_location':
                $string = 'An intercessor is praying for global disciple making movements'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'created_custom_lap':
                $string = 'Someone created a custom prayer lap to mobilize others to pray'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'lap_completed':
                $string = 'One entire prayer lap around the world just completed'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'pg_registered':
                $string = 'Someone joined Prayer Global'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            default:
                $string = '';
        }

        return $string;
    }
    // PRAYER TOOLS
    public function _strings_for_prayer_tools( $log ) {
        $string = '';
        switch ( $log['subtype'] ) {

            // PRAYER TOOLS
            case 'pt_registered':
                $string = 'Someone has joined a strategic prayer campaign'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'actively_praying':
            case 'recurring_signup':
                $title = ( isset( $log['data']['title'] ) ) ? ': "'.$log['data']['title'] . '"' : '';
                $string = 'An intercessor is praying for a strategic prayer campaign'. $title .$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;

            default:
                $string = '';
                break;
        }

        return $string;
    }
    // DISCIPLE TOOLS
    public function _strings_for_disciple_tools( $log ) {
        $string = '';
        switch ( $log['subtype'] ) {
            case 'software_downloaded':
                $string = 'Someone has downloaded Disciple Tools software. ('.$log['label'].')';
                break;
            case 'demo_site_launched':
                $string = 'Someone has launched a Disciple.Tools demo site. ('.$log['label'].')';
                break;
            case 'site_report':
                $string = 'Someone has reported using Disciple Tools for disciple making. ('.$log['label'].')';
                break;
            default:
                $string = '';
        }

        return $string;
    }
    // KINGDOM TRAINING
    public function _strings_for_kingdom_training( $log ) {
        $string = '';
        switch ( $log['subtype'] ) {
            case 'kt_registered':
                $string = 'Someone is joining Kingdom Training'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'course_completed':
                $string = 'Someone completed the Kingdom Training course: "'.$log['data']['title'].'"'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'lesson_completed':
                $string = 'Someone completed the Kingdom Training lesson "'.$log['data']['title'].'" in the course "'.$log['data']['course'].'"'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            default:
                $string = '';
        }

        return $string;
    }
    // ZUME
    public function _strings_for_zume( $log ) {
        $string = '';
        [ $key, $value ] = explode( '_', $log['subtype'] );
        if ( is_numeric( $key ) ) {
            $key = (int) $key;
        }

        $training_items = $this->zume_training_items( $key );
        $title = $training_items['title'] ?? '';
        $type = $log['type'];

        switch ( $type .' '. $value ) {

            // ZUME - TRAINING
            case 'training heard':
            case 'coaching heard':
            case 'studying heard':
                $string = 'Someone is '.$type.' the Zume concept "'.$title.'"'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;

            case 'training obeyed':
            case 'coaching obeyed':
                $string = 'Someone reported that they obeyed "'.$title.'" in Zume'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;

            case 'training shared':
            case 'coaching shared':
                $string = 'Someone reported that they shared "'.$title.'" with someone else'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;

            case 'training trained':
            case 'coaching trained':
                $string = 'Someone reported that they trained someone else to share "'.$title.'"'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;

            case 'coaching modeling':
                $string = 'A coach is modeling "'.$title.'"'.$this->_add_language_string( $log ).' for a trainee. ('.$log['label'].')';
                break;
            case 'coaching assisting':
                $string = 'A coach has entered the assisting phase with "'.$title.'"'.$this->_add_language_string( $log ).' for a trainee. ('.$log['label'].')';
                break;
            case 'coaching watching':
                $string = 'A coach has entered the watching phase with a trainee for "'.$title.'"'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'coaching launching':
                $string = 'A coach is lauching a trainee for "'.$title.'"'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;

            case 'training a':
            case 'training b':
            case 'training c':
                [ $set_pre, $set_type, $set_number ] = explode( '_', $log['subtype'] );
                $string = 'A trainee is starting session '. (int) $set_number.' of the Zume Training'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;

            default:
                break;
        }

        switch ( $log['subtype'] ) {

            case 'guidebook_10':
                $string = 'Someone downloaded the 10 session Zume Training guidebook'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'guidebook_20':
                $string = 'Someone downloaded the 20 session Zume Training guidebook'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'guidebook_5':
                $string = 'Someone downloaded the Zume Training guidebook'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;

            case 'powerpoint_10':
                $string = 'Someone downloaded the 10 session Zume Training powerpoint'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'powerpoint_20':
                $string = 'Someone downloaded the 20 session Zume Training powerpoint'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'powerpoint_5':
                $string = 'Someone downloaded the Zume Training powerpoint'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;

            case 'order_print_copy':
                $string = 'Someone has ordered a printed version of Zume Training'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;

            case 'join_community':
                $string = 'Someone has joined the Zume Community'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;

                // ZUME - OTHER
            case 'host_completed':
                $string = 'A trainee has completed their full training and practice of Zume'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'joined_friends_training':
                $string = 'Someone joined someone elses training'.$this->_add_language_string( $log ).'.';
                break;
            case 'joined_online_training':
                $string = 'A trainee has joined an online Zume training'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'made_post_training_plan':
            case 'plan_created':
                $string = 'A trainee has has created a training plan for Zume'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'registered':
                $string = 'A trainee has registered to begin Zume Training'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'completed_3_month_plan':
                $string = 'Someone completed their disciple making obedience plan with Zume'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'requested_a_coach':
                $string = 'A Zume trainee requested a Zume coach for mentoring'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'connected_to_coach':
                $string = 'A Zume trainee connected with a Zume coach for mentoring'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;
            case 'training_completed':
            case 'host_completed':
                $string = 'Someone completed Zume Training'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;

            case 'new_church':
                $string = 'Someone has reported a new church planted'.$this->_add_language_string( $log ).'. ('.$log['label'].')';
                break;

            default:
                break;
        }

        return $string;
    }
    // built from zume_languages
    public function _convert_locale_to_language_name( $locale ) {
        $locale_name_array = impact_map_languages();
        $string = '';
        if ( isset( $locale_name_array[$locale] ) ) {
            $string = ' in ' . $locale_name_array[$locale];
        }
        return $string;
    }
    // copied from global.php zume.training
    public function zume_training_items( $item_number ): array {

            $training_items = [
                '1' => [
                    'key' => 1,
                    'title' => __( 'God Uses Ordinary People', 'impact_map' ), // pieces title & SEO title
                    'description' => __( "You'll see how God uses ordinary people doing simple things to make a big impact.", 'impact_map' ),
                    'video_title' => __( 'God Uses Ordinary People', 'impact_map' ), // video title & training title. simple
                    'slug' => 'god-uses-ordinary-people',
                    'video' => 1,
                    'script' => 34,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '2' => [
                    'key'  => 2,
                    'title' => __( 'Simple Definition of Disciple and Church', 'impact_map' ),
                    'description' => __( 'Discover the essence of being a disciple, making a disciple, and what is the church.', 'impact_map' ),
                    'video_title' => __( 'Disciples and the Church', 'impact_map' ),
                    'slug' => 'definition-of-disciple-and-church',
                    'video' => 2,
                    'script' => 35,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '3' => [
                    'key' => 3,
                    'title' => __( 'Spiritual Breathing is Hearing and Obeying God', 'impact_map' ),
                    'description' => __( 'Being a disciple means we hear from God and we obey God.', 'impact_map' ),
                    'video_title' => __( 'Hearing and Obeying God', 'impact_map' ),
                    'slug' => 'spiritual-breathing-is-hearing-and-obeying-god',
                    'video' => 3,
                    'script' => 36,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '4' => [
                    'key' => 4,
                    'title' => __( 'S.O.A.P.S. Bible Study', 'impact_map' ),
                    'description' => __( 'A tool for daily Bible study that helps you understand, obey, and share God's Word.', 'impact_map' ),
                    'video_title' => __( 'S.O.A.P.S. Bible Study', 'impact_map' ),
                    'slug' => 'soaps-bible-reading',
                    'video' => 4,
                    'script' => 37,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '5' => [
                    'key' => 5,
                    'title' => __( 'Accountability Groups', 'impact_map' ),
                    'description' => __( 'A tool for two or three people of the same gender to meet weekly and encourage each other in areas that are going well and reveal areas that need correction.', 'impact_map' ),
                    'video_title' => __( 'Accountability Groups', 'impact_map' ),
                    'slug' => 'accountability-groups',
                    'video' => 5,
                    'script' => 38,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '6' => [
                    'key' => 6,
                    'title' => __( 'Consumer vs Producer Lifestyle', 'impact_map' ),
                    'description' => __( "You'll discover the four main ways God makes everyday followers more like Jesus.", 'impact_map' ),
                    'video_title' => __( 'Producer not Consumer', 'impact_map' ),
                    'slug' => 'consumer-vs-producer-lifestyle',
                    'video' => 6,
                    'script' => 39,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '7' => [
                    'key' => 7,
                    'title' => __( 'How to Spend an Hour in Prayer', 'impact_map' ),
                    'description' => __( 'See how easy it is to spend an hour in prayer.', 'impact_map' ),
                    'video_title' => __( 'How to Spend an Hour in Prayer', 'impact_map' ),
                    'slug' => 'how-to-spend-an-hour-in-prayer',
                    'video' => 7,
                    'script' => 40,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '8' => [
                    'key' => 8,
                    'title' => __( 'Relational Stewardship – List of 100', 'impact_map' ),
                    'description' => __( 'A tool designed to help you be a good steward of your relationships.', 'impact_map' ),
                    'video_title' => __( 'List of 100', 'impact_map' ),
                    'slug' => 'relational-stewardship-list-of-100',
                    'video' => 8,
                    'script' => 41,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '9' => [
                    'key' => 9,
                    'title' => __( 'Spiritual Economy', 'impact_map' ),
                    'description' => __( "Learn how God's economy is different from the world's. God invests more in those who are faithful with what they've already been given.", 'impact_map' ),
                    'video_title' => __( 'Spiritual Economy', 'impact_map' ),
                    'slug' => 'the-kingdom-economy',
                    'video' => 9,
                    'script' => 42,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '10' => [
                    'key' => 10,
                    'title' => __( 'The Gospel and How to Share It', 'impact_map' ),
                    'description' => __( 'Learn a way to share God's Good News from the beginning of humanity all the way to the end of this age.', 'impact_map' ),
                    'video_title' => __( 'Sharing God's Story', 'impact_map' ),
                    'slug' => 'the-gospel-and-how-to-share-it',
                    'video' => 10,
                    'script' => 43,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '11' => [
                    'key' => 11,
                    'title' => __( 'Baptism and How To Do It', 'impact_map' ),
                    'description' => __( 'Jesus said, "Go and make disciples of all nations, BAPTIZING them in the name of the Father and of the Son and of the Holy Spirit…" Learn how to put this into practice.', 'impact_map' ),
                    'video_title' => __( 'Baptism', 'impact_map' ),
                    'slug' => 'baptism-and-how-to-do-it',
                    'video' => 11,
                    'script' => 44,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '12' => [
                    'key' => 12,
                    'title' => __( 'Prepare Your 3-Minute Testimony', 'impact_map' ),
                    'description' => __( 'Learn how to share your testimony in three minutes by sharing how Jesus has impacted your life.', 'impact_map' ),
                    'video_title' => __( '3-Minute Testimony', 'impact_map' ),
                    'slug' => 'prepare-your-3-minute-testimony',
                    'video' => 12,
                    'script' => 45,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '13' => [
                    'key' => 13,
                    'title' => __( 'Vision Casting the Greatest Blessing', 'impact_map' ),
                    'description' => __( 'Learn a simple pattern of making not just one follower of Jesus but entire spiritual families who multiply for generations to come.', 'impact_map' ),
                    'video_title' => __( 'Great, Greater, and Greatest Blessing', 'impact_map' ),
                    'slug' => 'vision-casting-the-greatest-blessing',
                    'video' => 13,
                    'script' => 46,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '14' => [
                    'key' => 14,
                    'title' => __( 'Duckling Discipleship – Leading Immediately', 'impact_map' ),
                    'description' => __( 'Learn what ducklings have to do with disciple-making.', 'impact_map' ),
                    'video_title' => __( 'Duckling Discipleship', 'impact_map' ),
                    'slug' => 'duckling-discipleship-leading-sooner',
                    'video' => 14,
                    'script' => 47,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '15' => [
                    'key' => 15,
                    'title' => __( 'Eyes to See Where the Kingdom Isn't', 'impact_map' ),
                    'description' => __( 'Begin to see where God's Kingdom isn't. These are usually the places where God wants to work the most.', 'impact_map' ),
                    'video_title' => __( 'Eyes to See Where the Kingdom Isn't', 'impact_map' ),
                    'slug' => 'eyes-to-see-where-the-kingdom-isnt',
                    'video' => 15,
                    'script' => 48,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '16' => [
                    'key' => 16,
                    'title' => __( 'The Lord's Supper and How To Lead It', 'impact_map' ),
                    'description' => __( 'It's a simple way to celebrate our intimate connection and ongoing relationship with Jesus. Learn a simple way to celebrate.', 'impact_map' ),
                    'video_title' => __( 'The Lord's Supper', 'impact_map' ),
                    'slug' => 'the-lords-supper-and-how-to-lead-it',
                    'video' => 16,
                    'script' => 49,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '17' => [
                    'key' => 17,
                    'title' => __( 'Prayer Walking and How To Do It', 'impact_map' ),
                    'description' => __( 'It's a simple way to obey God's command to pray for others. And it's just what it sounds like — praying to God while walking around!', 'impact_map' ),
                    'video_title' => __( 'Prayer Walking', 'impact_map' ),
                    'slug' => 'prayer-walking',
                    'video' => 17,
                    'script' => 50,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '18' => [
                    'key' => 18,
                    'title' => __( 'A Person of Peace and How To Find One', 'impact_map' ),
                    'description' => __( 'Learn who a person of peace might be and how to know when you've found one.', 'impact_map' ),
                    'video_title' => __( 'Person of Peace', 'impact_map' ),
                    'slug' => 'a-person-of-peace-and-how-to-find-one',
                    'video' => 18,
                    'script' => 51,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '19' => [
                    'key' => 19,
                    'title' => __( 'Faithfulness is Better Than Knowledge', 'impact_map' ),
                    'description' => __( 'It's important what disciples know — but it's much more important what they DO with what they know.', 'impact_map' ),
                    'video_title' => __( 'Faithfulness', 'impact_map' ),
                    'slug' => 'faithfulness-is-better-than-knowledge',
                    'video' => 19,
                    'script' => 52,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '20' => [
                    'key' => 20,
                    'title' => __( 'The BLESS Prayer Pattern', 'impact_map' ),
                    'description' => __( 'Practice a simple mnemonic to remind you of ways to pray for others.', 'impact_map' ),
                    'video_title' => __( 'The B.L.E.S.S. Prayer', 'impact_map' ),
                    'slug' => 'the-bless-prayer-pattern',
                    'video' => false,
                    'script' => false,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '21' => [
                    'key' => 21,
                    'title' => __( '3/3 Group Meeting Pattern', 'impact_map' ),
                    'description' => __( 'A 3/3 Group is a way for followers of Jesus to meet, pray, learn, grow, fellowship and practice obeying and sharing what they've learned. In this way, a 3/3 Group is not just a small group but a Simple Church.', 'impact_map' ),
                    'video_title' => __( '3/3 Group', 'impact_map' ),
                    'slug' => '3-3-group-meeting-pattern',
                    'video' => 21,
                    'script' => 53,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '22' => [
                    'key' => 22,
                    'title' => __( 'Training Cycle for Maturing Disciples', 'impact_map' ),
                    'description' => __( 'Learn the training cycle and consider how it applies to disciple making.', 'impact_map' ),
                    'video_title' => __( 'Training Cycle', 'impact_map' ),
                    'slug' => 'training-cycle-for-maturing-disciples',
                    'video' => 22,
                    'script' => 54,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '23' => [
                    'key' => 23,
                    'title' => __( 'Leadership Cells', 'impact_map' ),
                    'description' => __( 'A Leadership Cell is a way someone who feels called to lead can develop their leadership by practicing serving.', 'impact_map' ),
                    'video_title' => __( 'Leadership Cells', 'impact_map' ),
                    'slug' => 'leadership-cells',
                    'video' => 23,
                    'script' => 55,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '24' => [
                    'key' => 24,
                    'title' => __( 'Expect Non-Sequential Growth', 'impact_map' ),
                    'description' => __( 'See how disciple making doesn't have to be linear. Multiple things can happen at the same time.', 'impact_map' ),
                    'video_title' => __( 'Expect Non-Sequential Growth', 'impact_map' ),
                    'slug' => 'expect-non-sequential-growth',
                    'video' => 24,
                    'script' => 56,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '25' => [
                    'key' => 25,
                    'title' => __( 'Pace of Multiplication Matters', 'impact_map' ),
                    'description' => __( 'Multiplying matters and multiplying quickly matters even more. See why pace matters.', 'impact_map' ),
                    'video_title' => __( 'Pace', 'impact_map' ),
                    'slug' => 'pace-of-multiplication-matters',
                    'video' => 25,
                    'script' => 57,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '26' => [
                    'key' => 26,
                    'title' => __( 'Always Part of Two Churches', 'impact_map' ),
                    'description' => __( 'Learn how to obey Jesus' commands by going AND staying.', 'impact_map' ),
                    'video_title' => __( 'Always Part of Two Churches', 'impact_map' ),
                    'slug' => 'always-part-of-two-churches',
                    'video' => 26,
                    'script' => 58,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => true,
                ],
                '27' => [
                    'key' => 27,
                    'slug' => 'three-month-plan',
                    'title' => __( 'Three-Month Plan', 'impact_map' ),
                    'description' => __( 'Create and share your plan for how you will implement the Zúme tools over the next three months.', 'impact_map' ),
                    'video_title' => __( 'Three-Month Plan', 'impact_map' ),
                    'video' => false,
                    'script' => false,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => false,
                ],
                '28' => [
                    'key' => 28,
                    'title' => __( 'Coaching Checklist', 'impact_map' ),
                    'description' => __( 'A powerful tool you can use to quickly assess your own strengths and vulnerabilities when it comes to making disciples who multiply.', 'impact_map' ),
                    'video_title' => __( 'Coaching Checklist', 'impact_map' ),
                    'slug' => 'coaching-checklist',
                    'video' => 28,
                    'script' => 60,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => false,
                ],
                '29' => [
                    'key' => 29,
                    'title' => __( 'Leadership in Networks', 'impact_map' ),
                    'description' => __( 'Learn how multiplying churches stay connected and live life together as an extended, spiritual family.', 'impact_map' ),
                    'video_title' => __( 'Leadership in Networks', 'impact_map' ),
                    'slug' => 'leadership-in-networks',
                    'video' => 29,
                    'script' => 61,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '30' => [
                    'key' => 30,
                    'title' => __( 'Peer Mentoring Groups', 'impact_map' ),
                    'description' => __( 'This is a group that consists of people who are leading and starting 3/3 Groups. It also follows a 3/3 format and is a powerful way to assess the spiritual health of God's work in your area.', 'impact_map' ),
                    'video_title' => __( 'Peer Mentoring', 'impact_map' ),
                    'slug' => 'peer-mentoring-groups',
                    'video' => 30,
                    'script' => 62,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '31' => [
                    'key' => 31,
                    'title' => __( 'Four Fields Tool', 'impact_map' ),
                    'description' => __( 'The four fields diagnostic chart is a simple tool to be used by a leadership cell to reflect on the status of current efforts and the kingdom activity around them.', 'impact_map' ),
                    'video_title' => __( 'Four Fields Tool', 'impact_map' ),
                    'slug' => 'four-fields-tool',
                    'video' => false,
                    'script' => false,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '32' => [
                    'key' => 32,
                    'title' => __( 'Generational Mapping', 'impact_map' ),
                    'description' => __( 'Generation mapping is another simple tool to help leaders in a movement understand the growth around them.', 'impact_map' ),
                    'video_title' => __( 'Generational Mapping', 'impact_map' ),
                    'slug' => 'generational-mapping',
                    'video' => false,
                    'script' => false,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '33' => [
                    'key' => 33,
                    'title' => __( '3-Circles Gospel Presentation', 'impact_map' ),
                    'description' => __( 'The 3-Circles gospel presentation is a way to tell the gospel using a simple illustration that can be drawn on a piece of paper.', 'impact_map' ),
                    'video_title' => __( '3-Circles', 'impact_map' ),
                    'slug' => '3-circles-gospel-presentation',
                    'video' => 33,
                    'script' => 63,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
            ];

            $list = [];
            foreach ( $training_items as $training_item ) {
                $index = $training_item['key'];
                $list[$training_item['key']] = [
                    'key' => $training_item['key'],
                    'type' => $training_item['type'],
                    'title' => $training_item['title'],
                    'video_title' => $training_item['video_title'],
                    'video' => $training_item['video'],
                    'slug' => $training_item['slug'],
                    'script' => $training_item['script'],
                    'description' => $training_item['description'],
                    'host' => $training_item['host'] ? [
                        [
                            'label' => 'Heard',
                            'short_label' => 'H',
                            'type' => 'training',
                            'subtype' => $index.'_heard',
                            'key' => 'training_'.$index.'_heard',
                        ],
                        [
                            'label' => 'Obeyed',
                            'short_label' => 'O',
                            'type' => 'training',
                            'subtype' => $index.'_obeyed',
                            'key' => 'training_'.$index.'_obeyed',
                        ],
                        [
                            'label' => 'Shared',
                            'short_label' => 'S',
                            'type' => 'training',
                            'subtype' => $index.'_shared',
                            'key' => 'training_'.$index.'_shared',
                        ],
                        [
                            'label' => 'Trained',
                            'short_label' => 'T',
                            'type' => 'training',
                            'subtype' => $index.'_trained',
                            'key' => 'training_'.$index.'_trained',
                        ],
                    ] : [],
                    'mawl' => $training_item['mawl'] ? [
                        [
                            'label' => 'Modeling',
                            'short_label' => 'M',
                            'type' => 'coaching',
                            'subtype' => $index.'_modeling',
                            'key' => 'coaching_'.$index.'_modeling',
                        ],
                        [
                            'label' => 'Assisting',
                            'short_label' => 'A',
                            'type' => 'coaching',
                            'subtype' => $index.'_assisting',
                            'key' => 'coaching_'.$index.'_assisting',
                        ],
                        [
                            'label' => 'Watching',
                            'short_label' => 'W',
                            'type' => 'coaching',
                            'subtype' => $index.'_watching',
                            'key' => 'coaching_'.$index.'_watching',
                        ],
                        [
                            'label' => 'Launching',
                            'short_label' => 'L',
                            'type' => 'coaching',
                            'subtype' => $index.'_launching',
                            'key' => 'coaching_'.$index.'_launching',
                        ],
                    ] : [],
                ];
            }

            return $list[$item_number] ?? [];
    }

    public function _create_language_code( $log ) {
        if ( empty( $log['language_code'] ) ) {
            return 'en';
        }

        return $log['language_code'];
    }

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'add_api_routes' ] );
        add_filter( 'dt_allow_rest_access', [ $this, 'authorize_url' ], 10, 1 );
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
