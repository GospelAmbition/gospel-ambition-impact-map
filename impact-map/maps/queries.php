<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

class GO_Queries {

    // this is a reusable query that gets the user_id, post_id (contact_id), stage, and report id (rid) from the reports table.
    public static $query_for_user_stage = "SELECT r.user_id, r.post_id, r.post_id as contact_id, MAX(r.value) as stage, MAX(r.id) as rid FROM wp_dt_reports r
                                                  WHERE r.type = 'system' and r.subtype = 'current_level'
                                                  GROUP BY r.user_id, r.post_id";


    public static function stage_totals() {
        global $wpdb;
        $query_for_user_stage = self::$query_for_user_stage;

        // @phpcs:disable
        $results = $wpdb->get_results(
            "SELECT tb.stage, count(tb.user_id) as total
                FROM
                (
                   $query_for_user_stage
                ) as tb
                GROUP BY tb.stage;",
        ARRAY_A );
        // @phpcs:enable

        $stages = [];

        if ( empty( $results ) ) {
            return $stages;
        }

        foreach ( $results as $result ) {
            $stages[ $result['stage'] ] = $result;
        }

        return $stages;
    }

    public static function stage_by_location( array $range = [ 1 ] ) {
        global $wpdb;
        $query_for_user_stage = self::$query_for_user_stage;

        if ( count( $range ) > 1 ) {
            $range = '(' . implode( ',', $range ) . ')';
        } else {
            $range = '(' . $range[0] . ')';
        }

        // @phpcs:disable
        $results = $wpdb->get_results(
            "SELECT p.post_title as name, tb.user_id, tb.post_id, lgm.post_type, tb.stage, lgm.label, lgm.grid_id, lgm.lng, lgm.lat, lgm.level
            FROM
            (
              $query_for_user_stage
            ) as tb
            LEFT JOIN wp_posts p ON p.ID=tb.post_id
            LEFT JOIN wp_dt_location_grid_meta lgm ON lgm.post_id=tb.post_id AND lgm.post_type='contacts'
            WHERE tb.stage IN $range;", ARRAY_A );
        // @phpcs:enable

        if ( empty( $results ) ) {
            return [];
        }

        return $results;
    }

    public static function stage_by_boundary( array $range, float $north, float $south, float $east, float $west ) {
        global $wpdb;
        $query_for_user_stage = self::$query_for_user_stage;

        if ( count( $range ) > 1 ) {
            $range = '(' . implode( ',', $range ) . ')';
        } else {
            $range = '(' . $range[0] . ')';
        }

        // @phpcs:disable
        $results = $wpdb->get_results(
            "SELECT p.post_title as name, tb.user_id, tb.post_id, lgm.post_type, tb.stage, lgm.label, lgm.grid_id, lgm.lng, lgm.lat, lgm.level
            FROM
            (
              $query_for_user_stage
            ) as tb
            LEFT JOIN wp_posts p ON p.ID=tb.post_id
            LEFT JOIN wp_dt_location_grid_meta lgm ON lgm.post_id=tb.post_id AND lgm.post_type='contacts'
            WHERE tb.stage IN $range
            AND lgm.lat > $south
            AND lgm.lat < $north
            AND lgm.lng > $west
            AND lgm.lng < $east
            ;", ARRAY_A );
        // @phpcs:enable

        if ( empty( $results ) ) {
            return [];
        }

        return $results;
    }

    public static function churches_with_location() {
        global $wpdb;

        // @phpcs:disable
        $results = $wpdb->get_results(
            "SELECT p.ID as post_id, p.post_title as name, 'groups' as post_type, lgm.grid_id, lgm.lng, lgm.lat, lgm.level, lgm.source, lgm.label
            FROM wp_posts p
            LEFT JOIN wp_postmeta pm ON pm.post_id=p.ID AND pm.meta_key = 'location_grid_meta'
            LEFT JOIN wp_dt_location_grid_meta lgm ON lgm.grid_meta_id=pm.meta_value
            WHERE p.post_type = 'groups';", ARRAY_A );
        // @phpcs:enable

        if ( empty( $results ) ) {
            return [];
        }

        return $results;
    }

    public static function churches_by_boundary( float $north, float $south, float $east, float $west ) {
        global $wpdb;

        // @phpcs:disable
        $results = $wpdb->get_results(
            "SELECT p.ID, p.post_title as name, 'groups' as post_type, lgm.grid_id, lgm.lng, lgm.lat, lgm.level, lgm.source, lgm.label
            FROM wp_posts p
            LEFT JOIN wp_postmeta pm ON pm.post_id=p.ID AND pm.meta_key = 'location_grid_meta'
            LEFT JOIN wp_dt_location_grid_meta lgm ON lgm.grid_meta_id=pm.meta_value
            WHERE p.post_type = 'groups'
            AND lgm.lat > $south
            AND lgm.lat < $north
            AND lgm.lng > $west
            AND lgm.lng < $east
        ;", ARRAY_A );
        // @phpcs:enable

        if ( empty( $results ) ) {
            return [];
        }

        return $results;
    }

    /**
     * Training subtype counts for all *heard* reports.
     *
     * subtype
     * value count
     * @return array
     */
    public static function training_subtype_counts() {
        global $wpdb;

        // @phpcs:disable
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT subtype, COUNT(*) as value
            FROM wp_dt_reports
            WHERE type = 'training' AND subtype LIKE '%heard'
            GROUP BY subtype
            " ), ARRAY_A );
        // @phpcs:enable

        if ( empty( $results ) || is_wp_error( $results ) ) {
            return [];
        }

        return $results;
    }

    /**
     * Returns the total number of churches in the system.
     * @return int
     */
    public static function query_total_churches(): int {
        global $wpdb;
        $results = $wpdb->get_var(
            "SELECT count(*) as count
                    FROM wp_posts p
                    JOIN wp_postmeta pm ON pm.post_id=p.ID AND pm.meta_key = 'group_type' AND pm.meta_value = 'church'
                    JOIN wp_postmeta pm2 ON pm2.post_id=p.ID AND pm2.meta_key = 'group_status' AND pm2.meta_value = 'active'
                    WHERE post_type = 'groups';"
        );
        if ( $results ) {
            return (int) $results;
        } else {
            return 0;
        }
    }

    /**
     * Returns the total number of practitioners in the system.
     * @return int
     */
    public static function query_total_practitioners(): int {
        global $wpdb;
        $query_for_user_stage = self::$query_for_user_stage;

        // @phpcs:disable
        $results = $wpdb->get_var(
            "SELECT count(*) as practitioners
                FROM
                (
                    $query_for_user_stage
                ) as tb
            WHERE tb.stage >= 4;"
        );
        // @phpcs:enable

        if ( $results ) {
            return (int) $results;
        } else {
            return 0;
        }
    }

    public static function get_activity_geojson( $language_code = 'en', $hours = null ) {
        ini_set('memory_limit', -1);

        $languages_by_code = impact_map_languages();
        $list = self::query_activity_geojson( $language_code, $hours );

        if ( empty( $list ) ) {
            $list = [];
        }

        $features = [];
        foreach ( $list as $record ) {
            $features[] = array(
                'type' => 'Feature',
                'properties' => [
                    'type' => $record['type'],
                ],
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array(
                        (float) $record['lng'],
                        (float) $record['lat'],
                        1,
                    ),
                ),
            );

            $records++;

        } // end foreach loop

        $new_data = array(
            'type' => 'FeatureCollection',
            'features' => $features,
        );

        return $new_data;
    }

    public static function query_activity_geojson( $language_code = 'en', $hours = null ): array {
        global $wpdb;
        $time_string = '-100 hours';
        if ( $hours ) {
            $time_string = '-'.$hours.' hours';
        }
        $time_begin = strtotime( $time_string );
        $time_end = time();
        $results = $wpdb->get_results( $wpdb->prepare( "
                SELECT *
                FROM (
                SELECT r.type, r.lng, r.lat, r.time_end
                FROM wp_dt_reports r
                LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=r.grid_id
                LEFT JOIN wp_dt_location_grid lga0 ON lga0.grid_id=lg.admin0_grid_id
                WHERE r.time_end > %d AND r.time_end < %d AND r.type != 'system'
                ) as tb
                ORDER BY tb.time_end DESC
                ", $time_begin, $time_end ), ARRAY_A );

        return $results;
    }
}
