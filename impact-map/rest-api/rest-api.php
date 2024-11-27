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
    
    public function _create_string( $log ) {

       $string = '';

       if ( !empty( $string = $this->_strings_for_prayer_global( $log ) ) ) {
            return $string;
        }
        else if ( !empty( $string = $this->_strings_for_prayer_tools( $log ) ) ) {
            return $string;
        }
        else if ( !empty( $string = $this->_strings_for_disciple_tools( $log ) ) ) {
            return $string;
        }
        else if ( !empty( $string = $this->_strings_for_kingdom_training( $log ) ) ) {
            return $string;
        }
        else if ( !empty( $string = $this->_strings_for_zume( $log ) ) ) {
            return $string;
        }

        return $string;

    }
    public function _strings_for_prayer_global( $log ) {
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
             default:
                $string = '';
        }

        return $string;   
    }
    public function _strings_for_prayer_tools( $log ) {
        $string = '';
        switch( $log['subtype'] ) {

            // PRAYER TOOLS
            case 'pt_registered':
                $string = 'Someone in '.$log['label'].' has joined a strategic prayer campaign.';
                break;
            case 'recurring_signup':
                $string = 'Someone in is praying for a strategic prayer campaign in '.$log['label'].'.';
                break;
            default:
                $string = '';
                break;
            }

        return $string;   
    }
    public function _strings_for_disciple_tools( $log ) {
        $string = '';
        switch( $log['subtype'] ) {
            case 'software_downloaded':
                $string = 'Someone in '.$log['label'].' has downloaded Disciple Tools.';
                break;
            case 'demo_site_launched':
                $string = 'Someone in '.$log['label'].' has launched a demo site.';
                break;
            case 'site_report':
                $string = 'Someone in '.$log['label'].' has submitted a site report.';
                break;
            default:
                $string = '';
        }

        return $string;   
    }
    public function _strings_for_kingdom_training( $log ) {
        $string = '';
        switch( $log['subtype'] ) {
            case 'kt_registered':
                $string = 'Someone is joining Kingdom Training.';
                break;
            case 'kt_completed':
                $string = 'Someone has completed Kingdom Training.';
                break;
            case 'kt_lesson_completed':
                $string = 'Someone has completed a lesson in Kingdom Training.';
                break;
            default:
                $string = '';
            }

        return $string;   
    }
    public function _strings_for_zume( $log ) {
        $string = '';
        [ $key, $value ] = explode( '_', $log['subtype'] );
        if ( is_numeric( $key ) ) {
            $key = (int) $key;
        }

        $training_items = $this->zume_training_items($key);
        $title = $training_items['title'] ?? '';
        $type = $log['type'];

        switch( $type .' '. $value ) {
            
            // ZUME - TRAINING
            case 'training heard':
            case 'coaching heard':
            case 'studying heard':
                $string = 'Someone is '.$type.' Zume Training: '.$title.'. ('.$log['label'].')';
                break;

            case 'training obeyed':
            case 'coaching obeyed':
                $string = 'Someone reported that they obeyed "'.$title.'" in Zume. ('.$log['label'].')';
                break;

            case 'training shared':
            case 'coaching shared':
                $string = 'Someone reported that they shared "'.$title.'" from Zume with someone else. ('.$log['label'].')';
                break;

            case 'training trained':
            case 'coaching trained':
                $string = 'Someone reported that they trained someone else to share "'.$title.'". ('.$log['label'].')';
                break;

            // ZUME - OTHER
            case 'host_completed':
                $string = 'A trainee has completed their full training and practice of Zume. ('.$log['label'].')';
                break;

            case 'joined_friends_training':
                $string = 'Someone joined someone elses training.';
                break;

            case 'joined_online_training':

                $string = 'A trainee has completed their full training and practice of Zume. ('.$log['label'].')';
                break;
            case 'plan_created':
                $string = 'A trainee has completed their full training and practice of Zume. ('.$log['label'].')';
                break;
            case 'registered':
                $string = 'A trainee has completed their full training and practice of Zume. ('.$log['label'].')';
                break;



            case 'training a':
            case 'training b':
            case 'training c':
                [ $set_pre, $set_type, $set_number ] = explode( '_', $log['subtype'] );
                $string = 'A trainee is starting session '.(int) $set_number.' of the Zume Training. ('.$log['label'].')';
                break;

            
            case 'training_completed':
                $string = 'Someone completed Zume Training. ('.$log['label'].')';
                break;

            default:
                break;
        }
        return $string;
    }
    // copied from global.php zume.training
    public function zume_training_items( $item_number ): array {
    
            $training_items = [
                '1' => [
                    'key' => 1,
                    'title' => __( 'God Uses Ordinary People', 'zume' ), // pieces title & SEO title
                    'description' => __( "You'll see how God uses ordinary people doing simple things to make a big impact.", 'zume' ),
                    'video_title' => __( 'God Uses Ordinary People', 'zume' ), // video title & training title. simple
                    'slug' => 'god-uses-ordinary-people',
                    'video' => 1,
                    'script' => 34,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '2' => [
                    'key'  => 2,
                    'title' => __( 'Simple Definition of Disciple and Church', 'zume' ),
                    'description' => __( 'Discover the essence of being a disciple, making a disciple, and what is the church.', 'zume' ),
                    'video_title' => __( 'Disciples and the Church', 'zume' ),
                    'slug' => 'definition-of-disciple-and-church',
                    'video' => 2,
                    'script' => 35,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '3' => [
                    'key' => 3,
                    'title' => __( 'Spiritual Breathing is Hearing and Obeying God', 'zume' ),
                    'description' => __( 'Being a disciple means we hear from God and we obey God.', 'zume' ),
                    'video_title' => __( 'Hearing and Obeying God', 'zume' ),
                    'slug' => 'spiritual-breathing-is-hearing-and-obeying-god',
                    'video' => 3,
                    'script' => 36,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '4' => [
                    'key' => 4,
                    'title' => __( 'S.O.A.P.S. Bible Study', 'zume' ),
                    'description' => __( 'A tool for daily Bible study that helps you understand, obey, and share God’s Word.', 'zume' ),
                    'video_title' => __( 'S.O.A.P.S. Bible Study', 'zume' ),
                    'slug' => 'soaps-bible-reading',
                    'video' => 4,
                    'script' => 37,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '5' => [
                    'key' => 5,
                    'title' => __( 'Accountability Groups', 'zume' ),
                    'description' => __( 'A tool for two or three people of the same gender to meet weekly and encourage each other in areas that are going well and reveal areas that need correction.', 'zume' ),
                    'video_title' => __( 'Accountability Groups', 'zume' ),
                    'slug' => 'accountability-groups',
                    'video' => 5,
                    'script' => 38,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '6' => [
                    'key' => 6,
                    'title' => __( 'Consumer vs Producer Lifestyle', 'zume' ),
                    'description' => __( "You'll discover the four main ways God makes everyday followers more like Jesus.", 'zume' ),
                    'video_title' => __( 'Producer not Consumer', 'zume' ),
                    'slug' => 'consumer-vs-producer-lifestyle',
                    'video' => 6,
                    'script' => 39,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '7' => [
                    'key' => 7,
                    'title' => __( 'How to Spend an Hour in Prayer', 'zume' ),
                    'description' => __( 'See how easy it is to spend an hour in prayer.', 'zume' ),
                    'video_title' => __( 'How to Spend an Hour in Prayer', 'zume' ),
                    'slug' => 'how-to-spend-an-hour-in-prayer',
                    'video' => 7,
                    'script' => 40,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '8' => [
                    'key' => 8,
                    'title' => __( 'Relational Stewardship – List of 100', 'zume' ),
                    'description' => __( 'A tool designed to help you be a good steward of your relationships.', 'zume' ),
                    'video_title' => __( 'List of 100', 'zume' ),
                    'slug' => 'relational-stewardship-list-of-100',
                    'video' => 8,
                    'script' => 41,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '9' => [
                    'key' => 9,
                    'title' => __( 'Spiritual Economy', 'zume' ),
                    'description' => __( "Learn how God's economy is different from the world's. God invests more in those who are faithful with what they've already been given.", 'zume' ),
                    'video_title' => __( 'Spiritual Economy', 'zume' ),
                    'slug' => 'the-kingdom-economy',
                    'video' => 9,
                    'script' => 42,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '10' => [
                    'key' => 10,
                    'title' => __( 'The Gospel and How to Share It', 'zume' ),
                    'description' => __( 'Learn a way to share God’s Good News from the beginning of humanity all the way to the end of this age.', 'zume' ),
                    'video_title' => __( 'Sharing God‘s Story', 'zume' ),
                    'slug' => 'the-gospel-and-how-to-share-it',
                    'video' => 10,
                    'script' => 43,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '11' => [
                    'key' => 11,
                    'title' => __( 'Baptism and How To Do It', 'zume' ),
                    'description' => __( 'Jesus said, “Go and make disciples of all nations, BAPTIZING them in the name of the Father and of the Son and of the Holy Spirit…” Learn how to put this into practice.', 'zume' ),
                    'video_title' => __( 'Baptism', 'zume' ),
                    'slug' => 'baptism-and-how-to-do-it',
                    'video' => 11,
                    'script' => 44,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '12' => [
                    'key' => 12,
                    'title' => __( 'Prepare Your 3-Minute Testimony', 'zume' ),
                    'description' => __( 'Learn how to share your testimony in three minutes by sharing how Jesus has impacted your life.', 'zume' ),
                    'video_title' => __( '3-Minute Testimony', 'zume' ),
                    'slug' => 'prepare-your-3-minute-testimony',
                    'video' => 12,
                    'script' => 45,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '13' => [
                    'key' => 13,
                    'title' => __( 'Vision Casting the Greatest Blessing', 'zume' ),
                    'description' => __( 'Learn a simple pattern of making not just one follower of Jesus but entire spiritual families who multiply for generations to come.', 'zume' ),
                    'video_title' => __( 'Great, Greater, and Greatest Blessing', 'zume' ),
                    'slug' => 'vision-casting-the-greatest-blessing',
                    'video' => 13,
                    'script' => 46,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '14' => [
                    'key' => 14,
                    'title' => __( 'Duckling Discipleship – Leading Immediately', 'zume' ),
                    'description' => __( 'Learn what ducklings have to do with disciple-making.', 'zume' ),
                    'video_title' => __( 'Duckling Discipleship', 'zume' ),
                    'slug' => 'duckling-discipleship-leading-sooner',
                    'video' => 14,
                    'script' => 47,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '15' => [
                    'key' => 15,
                    'title' => __( 'Eyes to See Where the Kingdom Isn’t', 'zume' ),
                    'description' => __( 'Begin to see where God’s Kingdom isn’t. These are usually the places where God wants to work the most.', 'zume' ),
                    'video_title' => __( 'Eyes to See Where the Kingdom Isn’t', 'zume' ),
                    'slug' => 'eyes-to-see-where-the-kingdom-isnt',
                    'video' => 15,
                    'script' => 48,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '16' => [
                    'key' => 16,
                    'title' => __( 'The Lord’s Supper and How To Lead It', 'zume' ),
                    'description' => __( 'It’s a simple way to celebrate our intimate connection and ongoing relationship with Jesus. Learn a simple way to celebrate.', 'zume' ),
                    'video_title' => __( 'The Lord’s Supper', 'zume' ),
                    'slug' => 'the-lords-supper-and-how-to-lead-it',
                    'video' => 16,
                    'script' => 49,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '17' => [
                    'key' => 17,
                    'title' => __( 'Prayer Walking and How To Do It', 'zume' ),
                    'description' => __( 'It‘s a simple way to obey God’s command to pray for others. And it‘s just what it sounds like — praying to God while walking around!', 'zume' ),
                    'video_title' => __( 'Prayer Walking', 'zume' ),
                    'slug' => 'prayer-walking',
                    'video' => 17,
                    'script' => 50,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '18' => [
                    'key' => 18,
                    'title' => __( 'A Person of Peace and How To Find One', 'zume' ),
                    'description' => __( 'Learn who a person of peace might be and how to know when you‘ve found one.', 'zume' ),
                    'video_title' => __( 'Person of Peace', 'zume' ),
                    'slug' => 'a-person-of-peace-and-how-to-find-one',
                    'video' => 18,
                    'script' => 51,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '19' => [
                    'key' => 19,
                    'title' => __( 'Faithfulness is Better Than Knowledge', 'zume' ),
                    'description' => __( 'It‘s important what disciples know — but it‘s much more important what they DO with what they know.', 'zume' ),
                    'video_title' => __( 'Faithfulness', 'zume' ),
                    'slug' => 'faithfulness-is-better-than-knowledge',
                    'video' => 19,
                    'script' => 52,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '20' => [
                    'key' => 20,
                    'title' => __( 'The BLESS Prayer Pattern', 'zume' ),
                    'description' => __( 'Practice a simple mnemonic to remind you of ways to pray for others.', 'zume' ),
                    'video_title' => __( 'The B.L.E.S.S. Prayer', 'zume' ),
                    'slug' => 'the-bless-prayer-pattern',
                    'video' => false,
                    'script' => false,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '21' => [
                    'key' => 21,
                    'title' => __( '3/3 Group Meeting Pattern', 'zume' ),
                    'description' => __( 'A 3/3 Group is a way for followers of Jesus to meet, pray, learn, grow, fellowship and practice obeying and sharing what they‘ve learned. In this way, a 3/3 Group is not just a small group but a Simple Church.', 'zume' ),
                    'video_title' => __( '3/3 Group', 'zume' ),
                    'slug' => '3-3-group-meeting-pattern',
                    'video' => 21,
                    'script' => 53,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '22' => [
                    'key' => 22,
                    'title' => __( 'Training Cycle for Maturing Disciples', 'zume' ),
                    'description' => __( 'Learn the training cycle and consider how it applies to disciple making.', 'zume' ),
                    'video_title' => __( 'Training Cycle', 'zume' ),
                    'slug' => 'training-cycle-for-maturing-disciples',
                    'video' => 22,
                    'script' => 54,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '23' => [
                    'key' => 23,
                    'title' => __( 'Leadership Cells', 'zume' ),
                    'description' => __( 'A Leadership Cell is a way someone who feels called to lead can develop their leadership by practicing serving.', 'zume' ),
                    'video_title' => __( 'Leadership Cells', 'zume' ),
                    'slug' => 'leadership-cells',
                    'video' => 23,
                    'script' => 55,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '24' => [
                    'key' => 24,
                    'title' => __( 'Expect Non-Sequential Growth', 'zume' ),
                    'description' => __( 'See how disciple making doesn‘t have to be linear. Multiple things can happen at the same time.', 'zume' ),
                    'video_title' => __( 'Expect Non-Sequential Growth', 'zume' ),
                    'slug' => 'expect-non-sequential-growth',
                    'video' => 24,
                    'script' => 56,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '25' => [
                    'key' => 25,
                    'title' => __( 'Pace of Multiplication Matters', 'zume' ),
                    'description' => __( 'Multiplying matters and multiplying quickly matters even more. See why pace matters.', 'zume' ),
                    'video_title' => __( 'Pace', 'zume' ),
                    'slug' => 'pace-of-multiplication-matters',
                    'video' => 25,
                    'script' => 57,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '26' => [
                    'key' => 26,
                    'title' => __( 'Always Part of Two Churches', 'zume' ),
                    'description' => __( 'Learn how to obey Jesus‘ commands by going AND staying.', 'zume' ),
                    'video_title' => __( 'Always Part of Two Churches', 'zume' ),
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
                    'title' => __( 'Three-Month Plan', 'zume' ),
                    'description' => __( 'Create and share your plan for how you will implement the Zúme tools over the next three months.', 'zume' ),
                    'video_title' => __( 'Three-Month Plan', 'zume' ),
                    'video' => false,
                    'script' => false,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => false,
                ],
                '28' => [
                    'key' => 28,
                    'title' => __( 'Coaching Checklist', 'zume' ),
                    'description' => __( 'A powerful tool you can use to quickly assess your own strengths and vulnerabilities when it comes to making disciples who multiply.', 'zume' ),
                    'video_title' => __( 'Coaching Checklist', 'zume' ),
                    'slug' => 'coaching-checklist',
                    'video' => 28,
                    'script' => 60,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => false,
                ],
                '29' => [
                    'key' => 29,
                    'title' => __( 'Leadership in Networks', 'zume' ),
                    'description' => __( 'Learn how multiplying churches stay connected and live life together as an extended, spiritual family.', 'zume' ),
                    'video_title' => __( 'Leadership in Networks', 'zume' ),
                    'slug' => 'leadership-in-networks',
                    'video' => 29,
                    'script' => 61,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '30' => [
                    'key' => 30,
                    'title' => __( 'Peer Mentoring Groups', 'zume' ),
                    'description' => __( 'This is a group that consists of people who are leading and starting 3/3 Groups. It also follows a 3/3 format and is a powerful way to assess the spiritual health of God’s work in your area.', 'zume' ),
                    'video_title' => __( 'Peer Mentoring', 'zume' ),
                    'slug' => 'peer-mentoring-groups',
                    'video' => 30,
                    'script' => 62,
                    'type' => 'concept',
                    'host' => true,
                    'mawl' => false,
                ],
                '31' => [
                    'key' => 31,
                    'title' => __( 'Four Fields Tool', 'zume' ),
                    'description' => __( 'The four fields diagnostic chart is a simple tool to be used by a leadership cell to reflect on the status of current efforts and the kingdom activity around them.', 'zume' ),
                    'video_title' => __( 'Four Fields Tool', 'zume' ),
                    'slug' => 'four-fields-tool',
                    'video' => false,
                    'script' => false,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '32' => [
                    'key' => 32,
                    'title' => __( 'Generational Mapping', 'zume' ),
                    'description' => __( 'Generation mapping is another simple tool to help leaders in a movement understand the growth around them.', 'zume' ),
                    'video_title' => __( 'Generational Mapping', 'zume' ),
                    'slug' => 'generational-mapping',
                    'video' => false,
                    'script' => false,
                    'type' => 'tool',
                    'host' => true,
                    'mawl' => true,
                ],
                '33' => [
                    'key' => 33,
                    'title' => __( '3-Circles Gospel Presentation', 'zume' ),
                    'description' => __( 'The 3-Circles gospel presentation is a way to tell the gospel using a simple illustration that can be drawn on a piece of paper.', 'zume' ),
                    'video_title' => __( '3-Circles', 'zume' ),
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

