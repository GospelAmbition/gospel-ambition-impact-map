<?php

class GO_Impact_Map_Insert
{
    public static function insert( array $args, bool $save_hash = true, bool $duplicate_check = true )
    {
        // dt_write_log(get_bloginfo( 'name' ) . ' ' . __METHOD__);

        global $wpdb;
        if ( !isset( $args['type'] ) ) {
            return false;
        }

        $args = wp_parse_args(
            $args,
            [
                'user_id' => null,
                'parent_id' => null,
                'post_id' => null,
                'post_type' => null,
                'type' => null, // required
                'subtype' => null,
                'payload' => null,
                'value' => 1,
                'lng' => null,
                'lat' => null,
                'level' => null,
                'label' => null,
                'grid_id' => null,
                'time_begin' => null,
                'time_end' => null,
                'hash' => null,
                'language_code' => null,
            ]
        );

        if ( $save_hash ) {
            if ( empty( $args['hash'] ) ) {
                $args['hash'] = hash( 'sha256', maybe_serialize( $args ) );
            }

            if ( $duplicate_check ) {
                // Make sure no duplicate is found.
                $duplicate_found = $wpdb->get_row(
                    $wpdb->prepare(
                        'SELECT
                                `id`
                            FROM
                                wp_dt_reports
                            WHERE hash = %s AND hash IS NOT NULL;',
                        $args['hash']
                    )
                );
                if ( $duplicate_found ) {
                    return false;
                }
            }
        }

        $args['timestamp'] = time();

        if ( is_array( $args['payload'] ) || is_object( $args['payload'] ) ) {
            $args['payload'] = serialize( $args['payload'] );
        }

        // dt_write_log(__METHOD__);
        // dt_write_log($args);


        $wpdb->insert(
            'wp_dt_reports',
            [
                'user_id' => $args['user_id'],
                'parent_id' => $args['parent_id'],
                'post_id' => $args['post_id'],
                'post_type' => $args['post_type'],
                'type' => $args['type'],
                'subtype' => $args['subtype'],
                'payload' => $args['payload'],
                'value' => $args['value'],
                'lng' => $args['lng'],
                'lat' => $args['lat'],
                'level' => $args['level'],
                'label' => $args['label'],
                'grid_id' => $args['grid_id'],
                'time_begin' => $args['time_begin'],
                'time_end' => $args['time_end'],
                'timestamp' => time(),
                'hash' => $args['hash'],
                'language_code' => $args['language_code'],
            ],
            [
                '%d', // user_id
                '%d', // parent_id
                '%d', // post_id
                '%s', // post_type
                '%s', // type
                '%s', // subtype
                '%s', // payload
                '%d', // value
                '%f', // lng
                '%f', // lat
                '%s', // level
                '%s', // label
                '%d', // grid_id
                '%d', // time_begin
                '%d', // time_end
                '%d', // timestamp
                '%s', // hash
                '%s', // language code
            ]
        );

        $report_id = $wpdb->insert_id;
        if ( !$report_id ) {
            return $report_id;
        } else {
            $args['id'] = $report_id;
        }

        // dt_write_log(__METHOD__ . ' END');
        return $report_id;
    }
}
