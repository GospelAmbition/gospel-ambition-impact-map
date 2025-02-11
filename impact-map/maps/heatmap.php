<?php
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

class GO_Funnel_App_Heatmap {


    /**
     * This query returns the 50k saturation list of locations with population and country code.
     *
     * Returns
     * grid_id, population, country_code
     *
     * @return array
     */
    public static function query_saturation_list(): array {

        if ( false !== ( $value = get_transient( __METHOD__ ) ) ) { // phpcs:ignore
            return $value;
        }

        // 44141 records

        global $wpdb;
        $results = $wpdb->get_results("

            SELECT
            lg1.grid_id, lg1.population, lg1.country_code
            FROM wp_dt_location_grid lg1
            WHERE lg1.level = 0
			AND lg1.grid_id NOT IN ( SELECT lg11.admin0_grid_id FROM wp_dt_location_grid lg11 WHERE lg11.level = 1 AND lg11.admin0_grid_id = lg1.grid_id )
 			#'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg1.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg1.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)
			# above admin 0 (22)

			UNION ALL
            --
            # admin 1 locations that have no level 2 (768)
            --
            SELECT
            lg2.grid_id, lg2.population, lg2.country_code
            FROM wp_dt_location_grid lg2
            WHERE lg2.level = 1
			AND lg2.grid_id NOT IN ( SELECT lg22.admin1_grid_id FROM wp_dt_location_grid lg22 WHERE lg22.level = 2 AND lg22.admin1_grid_id = lg2.grid_id )
             #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg2.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg2.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

			UNION ALL
			--
            # admin 2 all countries (37100)
            --
			SELECT
            lg3.grid_id, lg3.population,  lg3.country_code
            FROM wp_dt_location_grid lg3
            WHERE lg3.level = 2
            #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg3.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg3.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

			UNION ALL
            --
            # admin 1 for little highly divided countries (352)
            --
            SELECT
            lg4.grid_id, lg4.population,  lg4.country_code
            FROM wp_dt_location_grid lg4
            WHERE lg4.level = 1
            #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg4.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg4.admin0_grid_id IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

			UNION ALL

 			--
            # admin 3 for big countries (6153)
            --
            SELECT
            lg5.grid_id, lg5.population, lg5.country_code
            FROM wp_dt_location_grid as lg5
            WHERE
            lg5.level = 3
            #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
            AND lg5.admin0_grid_id IN (100050711,100219347, 100089589,100074576,100259978,100018514)
            #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
            AND lg5.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

			# Total Records (44395)

       ", ARRAY_A );

        $list = [];
        if ( is_array( $results ) ) {
            foreach ( $results as $result ) {
                $list[$result['grid_id']] = $result;
            }
        }

        set_transient( __METHOD__, $list, MONTH_IN_SECONDS );

        return $list;
    }

    public static function query_saturation_list_full(): array {

        if ( false !== ( $value = get_transient( __METHOD__ ) ) ) { // phpcs:ignore
            return $value;
        }

        // 44141 records

        global $wpdb;
        $results = $wpdb->get_results("

            SELECT
                lg1.grid_id, lg1.population, lg1.country_code, lg1.longitude, lg1.latitude,
                CONCAT_WS(', ',
                          IF(LENGTH(ga4.alt_name),ga4.alt_name,NULL),
                          IF(LENGTH(ga3.alt_name),ga3.alt_name,NULL),
                          IF(LENGTH(ga2.alt_name),ga2.alt_name,NULL),
                          IF(LENGTH(ga1.alt_name),ga1.alt_name,NULL),
                          IF(LENGTH(gc.alt_name),gc.alt_name,NULL)
                    ) as full_name
            FROM wp_dt_location_grid lg1
                     LEFT JOIN wp_dt_location_grid as gc ON lg1.admin0_grid_id=gc.grid_id
                     LEFT JOIN wp_dt_location_grid as ga1 ON lg1.admin1_grid_id=ga1.grid_id
                     LEFT JOIN wp_dt_location_grid as ga2 ON lg1.admin2_grid_id=ga2.grid_id
                     LEFT JOIN wp_dt_location_grid as ga3 ON lg1.admin3_grid_id=ga3.grid_id
                     LEFT JOIN wp_dt_location_grid as ga4 ON lg1.admin4_grid_id=ga4.grid_id
                     LEFT JOIN wp_dt_location_grid as ga5 ON lg1.admin5_grid_id=ga5.grid_id
            WHERE lg1.level = 0
              AND lg1.grid_id NOT IN ( SELECT lg11.admin0_grid_id FROM wp_dt_location_grid lg11 WHERE lg11.level = 1 AND lg11.admin0_grid_id = lg1.grid_id )
              #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
              AND lg1.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
              #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
              AND lg1.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

              # above admin 0 (22)

            UNION ALL
            --
            # admin 1 locations that have no level 2 (768)
            --
            SELECT
                lg2.grid_id, lg2.population, lg2.country_code, lg2.longitude, lg2.latitude,
                CONCAT_WS(', ',
                          IF(LENGTH(ga4.alt_name),ga4.alt_name,NULL),
                          IF(LENGTH(ga3.alt_name),ga3.alt_name,NULL),
                          IF(LENGTH(ga2.alt_name),ga2.alt_name,NULL),
                          IF(LENGTH(ga1.alt_name),ga1.alt_name,NULL),
                          IF(LENGTH(gc.alt_name),gc.alt_name,NULL)
                    ) as full_name
            FROM wp_dt_location_grid lg2
                     LEFT JOIN wp_dt_location_grid as gc ON lg2.admin0_grid_id=gc.grid_id
                     LEFT JOIN wp_dt_location_grid as ga1 ON lg2.admin1_grid_id=ga1.grid_id
                     LEFT JOIN wp_dt_location_grid as ga2 ON lg2.admin2_grid_id=ga2.grid_id
                     LEFT JOIN wp_dt_location_grid as ga3 ON lg2.admin3_grid_id=ga3.grid_id
                     LEFT JOIN wp_dt_location_grid as ga4 ON lg2.admin4_grid_id=ga4.grid_id
                     LEFT JOIN wp_dt_location_grid as ga5 ON lg2.admin5_grid_id=ga5.grid_id
            WHERE lg2.level = 1
              AND lg2.grid_id NOT IN ( SELECT lg22.admin1_grid_id FROM wp_dt_location_grid lg22 WHERE lg22.level = 2 AND lg22.admin1_grid_id = lg2.grid_id )
              #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
              AND lg2.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
              #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
              AND lg2.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)


            UNION ALL
            --
            # admin 2 all countries (37100)
            --
            SELECT
                lg3.grid_id, lg3.population,  lg3.country_code, lg3.longitude, lg3.latitude,
                CONCAT_WS(', ',
                          IF(LENGTH(ga4.alt_name),ga4.alt_name,NULL),
                          IF(LENGTH(ga3.alt_name),ga3.alt_name,NULL),
                          IF(LENGTH(ga2.alt_name),ga2.alt_name,NULL),
                          IF(LENGTH(ga1.alt_name),ga1.alt_name,NULL),
                          IF(LENGTH(gc.alt_name),gc.alt_name,NULL)
                    ) as full_name
            FROM wp_dt_location_grid lg3
                     LEFT JOIN wp_dt_location_grid as gc ON lg3.admin0_grid_id=gc.grid_id
                     LEFT JOIN wp_dt_location_grid as ga1 ON lg3.admin1_grid_id=ga1.grid_id
                     LEFT JOIN wp_dt_location_grid as ga2 ON lg3.admin2_grid_id=ga2.grid_id
                     LEFT JOIN wp_dt_location_grid as ga3 ON lg3.admin3_grid_id=ga3.grid_id
                     LEFT JOIN wp_dt_location_grid as ga4 ON lg3.admin4_grid_id=ga4.grid_id
                     LEFT JOIN wp_dt_location_grid as ga5 ON lg3.admin5_grid_id=ga5.grid_id
            WHERE lg3.level = 2
              #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
              AND lg3.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
              #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
              AND lg3.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

            UNION ALL
            --
            # admin 1 for little highly divided countries (352)
            --
            SELECT
                lg4.grid_id, lg4.population,  lg4.country_code, lg4.longitude, lg4.latitude,
                CONCAT_WS(', ',
                          IF(LENGTH(ga4.alt_name),ga4.alt_name,NULL),
                          IF(LENGTH(ga3.alt_name),ga3.alt_name,NULL),
                          IF(LENGTH(ga2.alt_name),ga2.alt_name,NULL),
                          IF(LENGTH(ga1.alt_name),ga1.alt_name,NULL),
                          IF(LENGTH(gc.alt_name),gc.alt_name,NULL)
                    ) as full_name
            FROM wp_dt_location_grid lg4
                     LEFT JOIN wp_dt_location_grid as gc ON lg4.admin0_grid_id=gc.grid_id
                     LEFT JOIN wp_dt_location_grid as ga1 ON lg4.admin1_grid_id=ga1.grid_id
                     LEFT JOIN wp_dt_location_grid as ga2 ON lg4.admin2_grid_id=ga2.grid_id
                     LEFT JOIN wp_dt_location_grid as ga3 ON lg4.admin3_grid_id=ga3.grid_id
                     LEFT JOIN wp_dt_location_grid as ga4 ON lg4.admin4_grid_id=ga4.grid_id
                     LEFT JOIN wp_dt_location_grid as ga5 ON lg4.admin5_grid_id=ga5.grid_id
            WHERE lg4.level = 1
              #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
              AND lg4.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
              #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
              AND lg4.admin0_grid_id IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

            UNION ALL

            --
            # admin 3 for big countries (6153)
            --
            SELECT
                lg5.grid_id, lg5.population, lg5.country_code, lg5.longitude, lg5.latitude,
                CONCAT_WS(', ',
                          IF(LENGTH(ga4.alt_name),ga4.alt_name,NULL),
                          IF(LENGTH(ga3.alt_name),ga3.alt_name,NULL),
                          IF(LENGTH(ga2.alt_name),ga2.alt_name,NULL),
                          IF(LENGTH(ga1.alt_name),ga1.alt_name,NULL),
                          IF(LENGTH(gc.alt_name),gc.alt_name,NULL)
                    ) as full_name
            FROM wp_dt_location_grid as lg5
                     LEFT JOIN wp_dt_location_grid as gc ON lg5.admin0_grid_id=gc.grid_id
                     LEFT JOIN wp_dt_location_grid as ga1 ON lg5.admin1_grid_id=ga1.grid_id
                     LEFT JOIN wp_dt_location_grid as ga2 ON lg5.admin2_grid_id=ga2.grid_id
                     LEFT JOIN wp_dt_location_grid as ga3 ON lg5.admin3_grid_id=ga3.grid_id
                     LEFT JOIN wp_dt_location_grid as ga4 ON lg5.admin4_grid_id=ga4.grid_id
                     LEFT JOIN wp_dt_location_grid as ga5 ON lg5.admin5_grid_id=ga5.grid_id
            WHERE
                    lg5.level = 3
              #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
              AND lg5.admin0_grid_id IN (100050711,100219347, 100089589,100074576,100259978,100018514)
              #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
              AND lg5.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

       ", ARRAY_A );

        $list = [];
        if ( is_array( $results ) ) {
            foreach ( $results as $result ) {
                $list[$result['grid_id']] = $result;
            }
        }

        set_transient( __METHOD__, $list, MONTH_IN_SECONDS );

        return $list;
    }

    public static function query_saturation_list_with_filters( $filters ): array {

        $additional_where = '';
        if ( ! empty( $filters['bounds'] ) && is_array( $filters['bounds'] ) && $filters['zoom'] > 1.5 ) {
            if ( isset( $filters['bounds']['n_lat'] )
                && isset( $filters['bounds']['s_lat'] )
                && isset( $filters['bounds']['e_lng'] )
                && isset( $filters['bounds']['w_lng'] )
            ) {
                $additional_where .= '
                WHERE tb.longitude < '.$filters['bounds']['e_lng'].'
                AND tb.longitude > '.$filters['bounds']['w_lng'].'
                AND tb.latitude > '.$filters['bounds']['s_lat'].'
                AND tb.latitude < '.$filters['bounds']['n_lat'].'
                ';
            }
        }


        // 44141 records
        // @phpcs:disable
        global $wpdb;
        $results = $wpdb->get_results("
            SELECT *
            FROM (
                SELECT
                    lg1.grid_id, lg1.population, lg1.country_code, lg1.longitude, lg1.latitude,
                    CONCAT_WS(', ',
                              IF(LENGTH(ga4.alt_name),ga4.alt_name,NULL),
                              IF(LENGTH(ga3.alt_name),ga3.alt_name,NULL),
                              IF(LENGTH(ga2.alt_name),ga2.alt_name,NULL),
                              IF(LENGTH(ga1.alt_name),ga1.alt_name,NULL),
                              IF(LENGTH(gc.alt_name),gc.alt_name,NULL)
                        ) as full_name
                FROM wp_dt_location_grid lg1
                         LEFT JOIN wp_dt_location_grid as gc ON lg1.admin0_grid_id=gc.grid_id
                         LEFT JOIN wp_dt_location_grid as ga1 ON lg1.admin1_grid_id=ga1.grid_id
                         LEFT JOIN wp_dt_location_grid as ga2 ON lg1.admin2_grid_id=ga2.grid_id
                         LEFT JOIN wp_dt_location_grid as ga3 ON lg1.admin3_grid_id=ga3.grid_id
                         LEFT JOIN wp_dt_location_grid as ga4 ON lg1.admin4_grid_id=ga4.grid_id
                         LEFT JOIN wp_dt_location_grid as ga5 ON lg1.admin5_grid_id=ga5.grid_id
                WHERE lg1.level = 0
                  AND lg1.grid_id NOT IN ( SELECT lg11.admin0_grid_id FROM wp_dt_location_grid lg11 WHERE lg11.level = 1 AND lg11.admin0_grid_id = lg1.grid_id )
                  #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
                  AND lg1.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
                  #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
                  AND lg1.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

                  # above admin 0 (22)

                UNION ALL
                --
                # admin 1 locations that have no level 2 (768)
                --
                SELECT
                    lg2.grid_id, lg2.population, lg2.country_code, lg2.longitude, lg2.latitude,
                    CONCAT_WS(', ',
                              IF(LENGTH(ga4.alt_name),ga4.alt_name,NULL),
                              IF(LENGTH(ga3.alt_name),ga3.alt_name,NULL),
                              IF(LENGTH(ga2.alt_name),ga2.alt_name,NULL),
                              IF(LENGTH(ga1.alt_name),ga1.alt_name,NULL),
                              IF(LENGTH(gc.alt_name),gc.alt_name,NULL)
                        ) as full_name
                FROM wp_dt_location_grid lg2
                         LEFT JOIN wp_dt_location_grid as gc ON lg2.admin0_grid_id=gc.grid_id
                         LEFT JOIN wp_dt_location_grid as ga1 ON lg2.admin1_grid_id=ga1.grid_id
                         LEFT JOIN wp_dt_location_grid as ga2 ON lg2.admin2_grid_id=ga2.grid_id
                         LEFT JOIN wp_dt_location_grid as ga3 ON lg2.admin3_grid_id=ga3.grid_id
                         LEFT JOIN wp_dt_location_grid as ga4 ON lg2.admin4_grid_id=ga4.grid_id
                         LEFT JOIN wp_dt_location_grid as ga5 ON lg2.admin5_grid_id=ga5.grid_id
                WHERE lg2.level = 1
                  AND lg2.grid_id NOT IN ( SELECT lg22.admin1_grid_id FROM wp_dt_location_grid lg22 WHERE lg22.level = 2 AND lg22.admin1_grid_id = lg2.grid_id )
                  #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
                  AND lg2.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
                  #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
                  AND lg2.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)


                UNION ALL
                --
                # admin 2 all countries (37100)
                --
                SELECT
                    lg3.grid_id, lg3.population,  lg3.country_code, lg3.longitude, lg3.latitude,
                    CONCAT_WS(', ',
                              IF(LENGTH(ga4.alt_name),ga4.alt_name,NULL),
                              IF(LENGTH(ga3.alt_name),ga3.alt_name,NULL),
                              IF(LENGTH(ga2.alt_name),ga2.alt_name,NULL),
                              IF(LENGTH(ga1.alt_name),ga1.alt_name,NULL),
                              IF(LENGTH(gc.alt_name),gc.alt_name,NULL)
                        ) as full_name
                FROM wp_dt_location_grid lg3
                         LEFT JOIN wp_dt_location_grid as gc ON lg3.admin0_grid_id=gc.grid_id
                         LEFT JOIN wp_dt_location_grid as ga1 ON lg3.admin1_grid_id=ga1.grid_id
                         LEFT JOIN wp_dt_location_grid as ga2 ON lg3.admin2_grid_id=ga2.grid_id
                         LEFT JOIN wp_dt_location_grid as ga3 ON lg3.admin3_grid_id=ga3.grid_id
                         LEFT JOIN wp_dt_location_grid as ga4 ON lg3.admin4_grid_id=ga4.grid_id
                         LEFT JOIN wp_dt_location_grid as ga5 ON lg3.admin5_grid_id=ga5.grid_id
                WHERE lg3.level = 2
                  #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
                  AND lg3.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
                  #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
                  AND lg3.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

                UNION ALL
                --
                # admin 1 for little highly divided countries (352)
                --
                SELECT
                    lg4.grid_id, lg4.population,  lg4.country_code, lg4.longitude, lg4.latitude,
                    CONCAT_WS(', ',
                              IF(LENGTH(ga4.alt_name),ga4.alt_name,NULL),
                              IF(LENGTH(ga3.alt_name),ga3.alt_name,NULL),
                              IF(LENGTH(ga2.alt_name),ga2.alt_name,NULL),
                              IF(LENGTH(ga1.alt_name),ga1.alt_name,NULL),
                              IF(LENGTH(gc.alt_name),gc.alt_name,NULL)
                        ) as full_name
                FROM wp_dt_location_grid lg4
                         LEFT JOIN wp_dt_location_grid as gc ON lg4.admin0_grid_id=gc.grid_id
                         LEFT JOIN wp_dt_location_grid as ga1 ON lg4.admin1_grid_id=ga1.grid_id
                         LEFT JOIN wp_dt_location_grid as ga2 ON lg4.admin2_grid_id=ga2.grid_id
                         LEFT JOIN wp_dt_location_grid as ga3 ON lg4.admin3_grid_id=ga3.grid_id
                         LEFT JOIN wp_dt_location_grid as ga4 ON lg4.admin4_grid_id=ga4.grid_id
                         LEFT JOIN wp_dt_location_grid as ga5 ON lg4.admin5_grid_id=ga5.grid_id
                WHERE lg4.level = 1
                  #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
                  AND lg4.admin0_grid_id NOT IN (100050711,100219347, 100089589,100074576,100259978,100018514)
                  #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
                  AND lg4.admin0_grid_id IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)

                UNION ALL

                --
                # admin 3 for big countries (6153)
                --
                SELECT
                    lg5.grid_id, lg5.population, lg5.country_code, lg5.longitude, lg5.latitude,
                    CONCAT_WS(', ',
                              IF(LENGTH(ga4.alt_name),ga4.alt_name,NULL),
                              IF(LENGTH(ga3.alt_name),ga3.alt_name,NULL),
                              IF(LENGTH(ga2.alt_name),ga2.alt_name,NULL),
                              IF(LENGTH(ga1.alt_name),ga1.alt_name,NULL),
                              IF(LENGTH(gc.alt_name),gc.alt_name,NULL)
                        ) as full_name
                FROM wp_dt_location_grid as lg5
                         LEFT JOIN wp_dt_location_grid as gc ON lg5.admin0_grid_id=gc.grid_id
                         LEFT JOIN wp_dt_location_grid as ga1 ON lg5.admin1_grid_id=ga1.grid_id
                         LEFT JOIN wp_dt_location_grid as ga2 ON lg5.admin2_grid_id=ga2.grid_id
                         LEFT JOIN wp_dt_location_grid as ga3 ON lg5.admin3_grid_id=ga3.grid_id
                         LEFT JOIN wp_dt_location_grid as ga4 ON lg5.admin4_grid_id=ga4.grid_id
                         LEFT JOIN wp_dt_location_grid as ga5 ON lg5.admin5_grid_id=ga5.grid_id
                WHERE
                        lg5.level = 3
                  #'China', 'India', 'France', 'Spain', 'Pakistan', 'Bangladesh'
                  AND lg5.admin0_grid_id IN (100050711,100219347, 100089589,100074576,100259978,100018514)
                  #'Romania', 'Estonia', 'Bhutan', 'Croatia', 'Solomon Islands', 'Guyana', 'Iceland', 'Vanuatu', 'Cape Verde', 'Samoa', 'Faroe Islands', 'Norway', 'Uruguay', 'Mongolia', 'United Arab Emirates', 'Slovenia', 'Bulgaria', 'Honduras', 'Columbia', 'Namibia', 'Switzerland', 'Western Sahara'
                  AND lg5.admin0_grid_id NOT IN (100314737,100083318,100041128,100133112,100341242,100132648,100222839,100379914,100055707,100379993,100130389,100255271,100363975,100248845,100001527,100342458,100024289,100132795,100054605,100253456,100342975,100074571)
              ) as tb
              $additional_where

              ORDER BY latitude ASC

       ", ARRAY_A );
        // @phpcs:enable

        $list = [];
        if ( is_array( $results ) ) {
            foreach ( $results as $result ) {
                $list[$result['grid_id']] = $result;
            }
        }

        set_transient( __METHOD__, $list, MONTH_IN_SECONDS );

        return $list;
    }

    public static function query_flat_grid_by_level( $administrative_level, $us_div = 5000, $global_div = 50000 ) {

        if ( false !== ( $value = get_transient( __METHOD__ . $administrative_level . $us_div . $global_div ) ) ) { // phpcs:ignore
            return $value;
        }

        global $wpdb;
        $wpdb->us_div = $us_div;
        $wpdb->global_div = $global_div;
        switch ( $administrative_level ) {
            case 'a0':
                $results = $wpdb->get_results("
                    # 'Needs' GROUPED BY country
                    SELECT tb0.admin0_grid_id as grid_id, loc.name,loc.country_code, SUM(tb0.population) as population, SUM(tb0.needed) as needed, (0) as reported, (0) as percent
                    FROM (
                             # 44395 Records
                             SELECT
                                 lg1.admin0_grid_id,
                                 lg1.admin1_grid_id,
                                 lg1.admin2_grid_id,
                                 lg1.admin3_grid_id,
                                 lg1.population,
                                 IF(ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg1
                             WHERE lg1.level = 0
                               AND lg1.grid_id NOT IN (SELECT lg11.admin0_grid_id
                                                       FROM wp_dt_location_grid lg11
                                                       WHERE lg11.level = 1
                                                         AND lg11.admin0_grid_id = lg1.grid_id)
                               AND lg1.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg1.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg2.admin0_grid_id,
                                 lg2.admin1_grid_id,
                                 lg2.admin2_grid_id,
                                 lg2.admin3_grid_id,
                                 lg2.population,
                                 IF(ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg2
                             WHERE lg2.level = 1
                               AND lg2.grid_id NOT IN (SELECT lg22.admin1_grid_id
                                                       FROM wp_dt_location_grid lg22
                                                       WHERE lg22.level = 2
                                                         AND lg22.admin1_grid_id = lg2.grid_id)
                               AND lg2.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg2.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg3.admin0_grid_id,
                                 lg3.admin1_grid_id,
                                 lg3.admin2_grid_id,
                                 lg3.admin3_grid_id,
                                 lg3.population,
                                 IF(ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg3
                             WHERE lg3.level = 2
                               AND lg3.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg3.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg4.admin0_grid_id,
                                 lg4.admin1_grid_id,
                                 lg4.admin2_grid_id,
                                 lg4.admin3_grid_id,
                                 lg4.population,
                                 IF(ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg4
                             WHERE lg4.level = 1
                               AND lg4.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg4.admin0_grid_id IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg5.admin0_grid_id,
                                 lg5.admin1_grid_id,
                                 lg5.admin2_grid_id,
                                 lg5.admin3_grid_id,
                                 lg5.population,
                                 IF(ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid as lg5
                             WHERE lg5.level = 3
                               AND lg5.admin0_grid_id IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg5.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                    ) as tb0
                    LEFT JOIN wp_dt_location_grid loc ON tb0.admin0_grid_id=loc.grid_id
                    GROUP BY tb0.admin0_grid_id
                ", ARRAY_A );
                break;
            case 'a1':
                $results = $wpdb->get_results("
                    # 'Needs' GROUPED BY state level
                    SELECT tb1.admin1_grid_id as grid_id, loc.name, loc.country_code, SUM(tb1.population) as population, SUM(tb1.needed) as needed, (0) as reported, (0) as percent
                    FROM (
                             SELECT
                                 lg1.admin0_grid_id,
                                 lg1.admin1_grid_id,
                                 lg1.admin2_grid_id,
                                 lg1.admin3_grid_id,
                                 lg1.population,
                                 IF(ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg1
                             WHERE lg1.level = 0
                               AND lg1.grid_id NOT IN (SELECT lg11.admin0_grid_id
                                                       FROM wp_dt_location_grid lg11
                                                       WHERE lg11.level = 1
                                                         AND lg11.admin0_grid_id = lg1.grid_id)
                               AND lg1.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg1.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg2.admin0_grid_id,
                                 lg2.admin1_grid_id,
                                 lg2.admin2_grid_id,
                                 lg2.admin3_grid_id,
                                 lg2.population,
                                 IF(ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg2
                             WHERE lg2.level = 1
                               AND lg2.grid_id NOT IN (SELECT lg22.admin1_grid_id
                                                       FROM wp_dt_location_grid lg22
                                                       WHERE lg22.level = 2
                                                         AND lg22.admin1_grid_id = lg2.grid_id)
                               AND lg2.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg2.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg3.admin0_grid_id,
                                 lg3.admin1_grid_id,
                                 lg3.admin2_grid_id,
                                 lg3.admin3_grid_id,
                                 lg3.population,
                                 IF(ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg3
                             WHERE lg3.level = 2
                               AND lg3.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg3.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg4.admin0_grid_id,
                                 lg4.admin1_grid_id,
                                 lg4.admin2_grid_id,
                                 lg4.admin3_grid_id,
                                 lg4.population,
                                 IF(ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg4
                             WHERE lg4.level = 1
                               AND lg4.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg4.admin0_grid_id IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg5.admin0_grid_id,
                                 lg5.admin1_grid_id,
                                 lg5.admin2_grid_id,
                                 lg5.admin3_grid_id,
                                 lg5.population,
                                 IF(ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid as lg5
                             WHERE lg5.level = 3
                               AND lg5.admin0_grid_id IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg5.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                    ) as tb1
                    LEFT JOIN wp_dt_location_grid loc ON tb1.admin1_grid_id=loc.grid_id
                    GROUP BY tb1.admin1_grid_id
                ", ARRAY_A );
                break;
            case 'a2':
                $results = $wpdb->get_results("
                    # 'Needs' GROUPED BY county level
                    SELECT tb2.admin2_grid_id as grid_id, loc.name, loc.country_code, SUM(tb2.population) as population, SUM(tb2.needed) as needed, (0) as reported, (0) as percent
                    FROM (
                             SELECT
                                 lg1.admin0_grid_id,
                                 lg1.admin1_grid_id,
                                 lg1.admin2_grid_id,
                                 lg1.admin3_grid_id,
                                 lg1.population,
                                 IF(ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg1
                             WHERE lg1.level = 0
                               AND lg1.grid_id NOT IN (SELECT lg11.admin0_grid_id
                                 FROM wp_dt_location_grid lg11
                                 WHERE lg11.level = 1
                               AND lg11.admin0_grid_id = lg1.grid_id)
                               AND lg1.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg1.admin0_grid_id NOT IN
                                 (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                 100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                 100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg2.admin0_grid_id,
                                 lg2.admin1_grid_id,
                                 lg2.admin2_grid_id,
                                 lg2.admin3_grid_id,
                                 lg2.population,
                                 IF(ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                 ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg2
                             WHERE lg2.level = 1
                               AND lg2.grid_id NOT IN (SELECT lg22.admin1_grid_id
                                 FROM wp_dt_location_grid lg22
                                 WHERE lg22.level = 2
                               AND lg22.admin1_grid_id = lg2.grid_id)
                               AND lg2.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg2.admin0_grid_id NOT IN
                                 (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                 100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                 100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg3.admin0_grid_id,
                                 lg3.admin1_grid_id,
                                 lg3.admin2_grid_id,
                                 lg3.admin3_grid_id,
                                 lg3.population,
                                 IF(ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                 ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg3
                             WHERE lg3.level = 2
                               AND lg3.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg3.admin0_grid_id NOT IN
                                 (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                 100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                 100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg4.admin0_grid_id,
                                 lg4.admin1_grid_id,
                                 lg4.admin2_grid_id,
                                 lg4.admin3_grid_id,
                                 lg4.population,
                                 IF(ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                 ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg4
                             WHERE lg4.level = 1
                               AND lg4.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg4.admin0_grid_id IN
                                 (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                 100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                 100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg5.admin0_grid_id,
                                 lg5.admin1_grid_id,
                                 lg5.admin2_grid_id,
                                 lg5.admin3_grid_id,
                                 lg5.population,
                                 IF(ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                 ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid as lg5
                             WHERE lg5.level = 3
                               AND lg5.admin0_grid_id IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg5.admin0_grid_id NOT IN
                                 (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                 100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                 100054605, 100253456, 100342975, 100074571)
                    ) as tb2
                    LEFT JOIN wp_dt_location_grid loc ON tb2.admin2_grid_id=loc.grid_id
                    GROUP BY tb2.admin2_grid_id
                ", ARRAY_A );
                break;
            case 'a3':
                $results = $wpdb->get_results("
                    # 'Needs' GROUPED BY sub-county level
                    SELECT tb3.admin3_grid_id as grid_id, loc.name, loc.country_code, SUM(tb3.population) as population, SUM(tb3.needed) as needed, (0) as reported, (0) as percent
                    FROM (
                             # 44395 Records
                             SELECT
                                 lg1.admin0_grid_id,
                                 lg1.admin1_grid_id,
                                 lg1.admin2_grid_id,
                                 lg1.admin3_grid_id,
                                 lg1.population,
                                 IF(ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg1
                             WHERE lg1.level = 0
                               AND lg1.grid_id NOT IN (SELECT lg11.admin0_grid_id
                                                       FROM wp_dt_location_grid lg11
                                                       WHERE lg11.level = 1
                                                         AND lg11.admin0_grid_id = lg1.grid_id)
                               AND lg1.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg1.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg2.admin0_grid_id,
                                 lg2.admin1_grid_id,
                                 lg2.admin2_grid_id,
                                 lg2.admin3_grid_id,
                                 lg2.population,
                                 IF(ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg2
                             WHERE lg2.level = 1
                               AND lg2.grid_id NOT IN (SELECT lg22.admin1_grid_id
                                                       FROM wp_dt_location_grid lg22
                                                       WHERE lg22.level = 2
                                                         AND lg22.admin1_grid_id = lg2.grid_id)
                               AND lg2.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg2.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg3.admin0_grid_id,
                                 lg3.admin1_grid_id,
                                 lg3.admin2_grid_id,
                                 lg3.admin3_grid_id,
                                 lg3.population,
                                 IF(ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg3
                             WHERE lg3.level = 2
                               AND lg3.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg3.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg4.admin0_grid_id,
                                 lg4.admin1_grid_id,
                                 lg4.admin2_grid_id,
                                 lg4.admin3_grid_id,
                                 lg4.population,
                                 IF(ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg4
                             WHERE lg4.level = 1
                               AND lg4.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg4.admin0_grid_id IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg5.admin0_grid_id,
                                 lg5.admin1_grid_id,
                                 lg5.admin2_grid_id,
                                 lg5.admin3_grid_id,
                                 lg5.population,
                                 IF(ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid as lg5
                             WHERE lg5.level = 3
                               AND lg5.admin0_grid_id IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg5.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                    ) as tb3
                    LEFT JOIN wp_dt_location_grid loc ON tb3.admin3_grid_id=loc.grid_id
                    WHERE tb3.admin3_grid_id IS NOT NULL
                    GROUP BY tb3.admin3_grid_id
                ", ARRAY_A );
                break;
            case 'world':
                $results = $wpdb->get_results("
                    # World
                    SELECT 1 as grid_id, 'World' as name,'' as country_code, SUM(tbw.population) as population, SUM(tbw.needed) as needed, (0) as reported, (0) as percent
                    FROM (
                             # 44395 Records
                             SELECT
                                 'World',
                                 lg1.admin0_grid_id,
                                 lg1.admin1_grid_id,
                                 lg1.admin2_grid_id,
                                 lg1.admin3_grid_id,
                                 lg1.population,
                                 IF(ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg1
                             WHERE lg1.level = 0
                               AND lg1.grid_id NOT IN (SELECT lg11.admin0_grid_id
                                                       FROM wp_dt_location_grid lg11
                                                       WHERE lg11.level = 1
                                                         AND lg11.admin0_grid_id = lg1.grid_id)
                               AND lg1.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg1.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 'World',
                                 lg2.admin0_grid_id,
                                 lg2.admin1_grid_id,
                                 lg2.admin2_grid_id,
                                 lg2.admin3_grid_id,
                                 lg2.population,
                                 IF(ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg2
                             WHERE lg2.level = 1
                               AND lg2.grid_id NOT IN (SELECT lg22.admin1_grid_id
                                                       FROM wp_dt_location_grid lg22
                                                       WHERE lg22.level = 2
                                                         AND lg22.admin1_grid_id = lg2.grid_id)
                               AND lg2.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg2.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 'World',
                                 lg3.admin0_grid_id,
                                 lg3.admin1_grid_id,
                                 lg3.admin2_grid_id,
                                 lg3.admin3_grid_id,
                                 lg3.population,
                                 IF(ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg3
                             WHERE lg3.level = 2
                               AND lg3.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg3.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 'World',
                                 lg4.admin0_grid_id,
                                 lg4.admin1_grid_id,
                                 lg4.admin2_grid_id,
                                 lg4.admin3_grid_id,
                                 lg4.population,
                                 IF(ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg4
                             WHERE lg4.level = 1
                               AND lg4.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg4.admin0_grid_id IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 'World',
                                 lg5.admin0_grid_id,
                                 lg5.admin1_grid_id,
                                 lg5.admin2_grid_id,
                                 lg5.admin3_grid_id,
                                 lg5.population,
                                 IF(ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid as lg5
                             WHERE lg5.level = 3
                               AND lg5.admin0_grid_id IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg5.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                    ) as tbw
                    LEFT JOIN wp_dt_location_grid loc ON 1=loc.grid_id
                    GROUP BY 'World';
                ", ARRAY_A );
                break;
            default:
                $results = $wpdb->get_results("
                    # 48367 Records
                    # 'Needs' GROUPED BY sub-county level
                    SELECT tb3.admin3_grid_id as grid_id, loc.name, loc.country_code, SUM(tb3.population) as population, SUM(tb3.needed) as needed, (0) as reported, (0) as percent
                    FROM (
                             # 44395 Records
                             SELECT
                                 lg1.admin0_grid_id,
                                 lg1.admin1_grid_id,
                                 lg1.admin2_grid_id,
                                 lg1.admin3_grid_id,
                                 lg1.population,
                                 IF(ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg1
                             WHERE lg1.level = 0
                               AND lg1.grid_id NOT IN (SELECT lg11.admin0_grid_id
                                                       FROM wp_dt_location_grid lg11
                                                       WHERE lg11.level = 1
                                                         AND lg11.admin0_grid_id = lg1.grid_id)
                               AND lg1.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg1.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg2.admin0_grid_id,
                                 lg2.admin1_grid_id,
                                 lg2.admin2_grid_id,
                                 lg2.admin3_grid_id,
                                 lg2.population,
                                 IF(ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg2
                             WHERE lg2.level = 1
                               AND lg2.grid_id NOT IN (SELECT lg22.admin1_grid_id
                                                       FROM wp_dt_location_grid lg22
                                                       WHERE lg22.level = 2
                                                         AND lg22.admin1_grid_id = lg2.grid_id)
                               AND lg2.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg2.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg3.admin0_grid_id,
                                 lg3.admin1_grid_id,
                                 lg3.admin2_grid_id,
                                 lg3.admin3_grid_id,
                                 lg3.population,
                                 IF(ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg3
                             WHERE lg3.level = 2
                               AND lg3.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg3.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg4.admin0_grid_id,
                                 lg4.admin1_grid_id,
                                 lg4.admin2_grid_id,
                                 lg4.admin3_grid_id,
                                 lg4.population,
                                 IF(ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg4
                             WHERE lg4.level = 1
                               AND lg4.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg4.admin0_grid_id IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg5.admin0_grid_id,
                                 lg5.admin1_grid_id,
                                 lg5.admin2_grid_id,
                                 lg5.admin3_grid_id,
                                 lg5.population,
                                 IF(ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid as lg5
                             WHERE lg5.level = 3
                               AND lg5.admin0_grid_id IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg5.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                    ) as tb3
                    LEFT JOIN wp_dt_location_grid loc ON tb3.admin3_grid_id=loc.grid_id
                    WHERE tb3.admin3_grid_id IS NOT NULL
                    GROUP BY tb3.admin3_grid_id

                    UNION ALL

                    # 'Needs' GROUPED BY county level
                    SELECT tb2.admin2_grid_id as grid_id, loc.name, loc.country_code, SUM(tb2.population) as population, SUM(tb2.needed) as needed, (0) as reported, (0) as percent
                    FROM (
                             SELECT
                                 lg1.admin0_grid_id,
                                 lg1.admin1_grid_id,
                                 lg1.admin2_grid_id,
                                 lg1.admin3_grid_id,
                                 lg1.population,
                                 IF(ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg1
                             WHERE lg1.level = 0
                               AND lg1.grid_id NOT IN (SELECT lg11.admin0_grid_id
                                 FROM wp_dt_location_grid lg11
                                 WHERE lg11.level = 1
                               AND lg11.admin0_grid_id = lg1.grid_id)
                               AND lg1.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg1.admin0_grid_id NOT IN
                                 (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                 100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                 100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg2.admin0_grid_id,
                                 lg2.admin1_grid_id,
                                 lg2.admin2_grid_id,
                                 lg2.admin3_grid_id,
                                 lg2.population,
                                 IF(ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                 ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg2
                             WHERE lg2.level = 1
                               AND lg2.grid_id NOT IN (SELECT lg22.admin1_grid_id
                                 FROM wp_dt_location_grid lg22
                                 WHERE lg22.level = 2
                               AND lg22.admin1_grid_id = lg2.grid_id)
                               AND lg2.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg2.admin0_grid_id NOT IN
                                 (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                 100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                 100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg3.admin0_grid_id,
                                 lg3.admin1_grid_id,
                                 lg3.admin2_grid_id,
                                 lg3.admin3_grid_id,
                                 lg3.population,
                                 IF(ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                 ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg3
                             WHERE lg3.level = 2
                               AND lg3.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg3.admin0_grid_id NOT IN
                                 (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                 100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                 100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg4.admin0_grid_id,
                                 lg4.admin1_grid_id,
                                 lg4.admin2_grid_id,
                                 lg4.admin3_grid_id,
                                 lg4.population,
                                 IF(ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                 ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg4
                             WHERE lg4.level = 1
                               AND lg4.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg4.admin0_grid_id IN
                                 (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                 100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                 100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg5.admin0_grid_id,
                                 lg5.admin1_grid_id,
                                 lg5.admin2_grid_id,
                                 lg5.admin3_grid_id,
                                 lg5.population,
                                 IF(ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                 ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid as lg5
                             WHERE lg5.level = 3
                               AND lg5.admin0_grid_id IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg5.admin0_grid_id NOT IN
                                 (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                 100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                 100054605, 100253456, 100342975, 100074571)
                    ) as tb2
                    LEFT JOIN wp_dt_location_grid loc ON tb2.admin2_grid_id=loc.grid_id
                    GROUP BY tb2.admin2_grid_id

                    UNION ALL

                    # 'Needs' GROUPED BY state level
                    SELECT tb1.admin1_grid_id as grid_id, loc.name, loc.country_code, SUM(tb1.population) as population, SUM(tb1.needed) as needed, (0) as reported, (0) as percent
                    FROM (
                             SELECT
                                 lg1.admin0_grid_id,
                                 lg1.admin1_grid_id,
                                 lg1.admin2_grid_id,
                                 lg1.admin3_grid_id,
                                 lg1.population,
                                 IF(ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg1
                             WHERE lg1.level = 0
                               AND lg1.grid_id NOT IN (SELECT lg11.admin0_grid_id
                                                       FROM wp_dt_location_grid lg11
                                                       WHERE lg11.level = 1
                                                         AND lg11.admin0_grid_id = lg1.grid_id)
                               AND lg1.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg1.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg2.admin0_grid_id,
                                 lg2.admin1_grid_id,
                                 lg2.admin2_grid_id,
                                 lg2.admin3_grid_id,
                                 lg2.population,
                                 IF(ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg2
                             WHERE lg2.level = 1
                               AND lg2.grid_id NOT IN (SELECT lg22.admin1_grid_id
                                                       FROM wp_dt_location_grid lg22
                                                       WHERE lg22.level = 2
                                                         AND lg22.admin1_grid_id = lg2.grid_id)
                               AND lg2.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg2.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg3.admin0_grid_id,
                                 lg3.admin1_grid_id,
                                 lg3.admin2_grid_id,
                                 lg3.admin3_grid_id,
                                 lg3.population,
                                 IF(ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg3
                             WHERE lg3.level = 2
                               AND lg3.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg3.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg4.admin0_grid_id,
                                 lg4.admin1_grid_id,
                                 lg4.admin2_grid_id,
                                 lg4.admin3_grid_id,
                                 lg4.population,
                                 IF(ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg4
                             WHERE lg4.level = 1
                               AND lg4.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg4.admin0_grid_id IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg5.admin0_grid_id,
                                 lg5.admin1_grid_id,
                                 lg5.admin2_grid_id,
                                 lg5.admin3_grid_id,
                                 lg5.population,
                                 IF(ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid as lg5
                             WHERE lg5.level = 3
                               AND lg5.admin0_grid_id IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg5.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                    ) as tb1
                    LEFT JOIN wp_dt_location_grid loc ON tb1.admin1_grid_id=loc.grid_id
                    GROUP BY tb1.admin1_grid_id

                    UNION ALL

                    # 'Needs' GROUPED BY country
                    SELECT tb0.admin0_grid_id as grid_id, loc.name,loc.country_code, SUM(tb0.population) as population, SUM(tb0.needed) as needed, (0) as reported, (0) as percent
                    FROM (
                             # 44395 Records
                             SELECT
                                 lg1.admin0_grid_id,
                                 lg1.admin1_grid_id,
                                 lg1.admin2_grid_id,
                                 lg1.admin3_grid_id,
                                 lg1.population,
                                 IF(ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg1
                             WHERE lg1.level = 0
                               AND lg1.grid_id NOT IN (SELECT lg11.admin0_grid_id
                                                       FROM wp_dt_location_grid lg11
                                                       WHERE lg11.level = 1
                                                         AND lg11.admin0_grid_id = lg1.grid_id)
                               AND lg1.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg1.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg2.admin0_grid_id,
                                 lg2.admin1_grid_id,
                                 lg2.admin2_grid_id,
                                 lg2.admin3_grid_id,
                                 lg2.population,
                                 IF(ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg2
                             WHERE lg2.level = 1
                               AND lg2.grid_id NOT IN (SELECT lg22.admin1_grid_id
                                                       FROM wp_dt_location_grid lg22
                                                       WHERE lg22.level = 2
                                                         AND lg22.admin1_grid_id = lg2.grid_id)
                               AND lg2.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg2.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg3.admin0_grid_id,
                                 lg3.admin1_grid_id,
                                 lg3.admin2_grid_id,
                                 lg3.admin3_grid_id,
                                 lg3.population,
                                 IF(ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg3
                             WHERE lg3.level = 2
                               AND lg3.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg3.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg4.admin0_grid_id,
                                 lg4.admin1_grid_id,
                                 lg4.admin2_grid_id,
                                 lg4.admin3_grid_id,
                                 lg4.population,
                                 IF(ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg4
                             WHERE lg4.level = 1
                               AND lg4.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg4.admin0_grid_id IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 lg5.admin0_grid_id,
                                 lg5.admin1_grid_id,
                                 lg5.admin2_grid_id,
                                 lg5.admin3_grid_id,
                                 lg5.population,
                                 IF(ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid as lg5
                             WHERE lg5.level = 3
                               AND lg5.admin0_grid_id IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg5.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                    ) as tb0
                    LEFT JOIN wp_dt_location_grid loc ON tb0.admin0_grid_id=loc.grid_id
                    GROUP BY tb0.admin0_grid_id

                    UNION ALL

                    # World
                    SELECT 1 as grid_id, 'World','' as country_code, SUM(tbw.population) as population, SUM(tbw.needed) as needed, (0) as reported, (0) as percent
                    FROM (
                             # 44395 Records
                             SELECT
                                 'World',
                                 lg1.admin0_grid_id,
                                 lg1.admin1_grid_id,
                                 lg1.admin2_grid_id,
                                 lg1.admin3_grid_id,
                                 lg1.population,
                                 IF(ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg1.population / IF(lg1.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg1
                             WHERE lg1.level = 0
                               AND lg1.grid_id NOT IN (SELECT lg11.admin0_grid_id
                                                       FROM wp_dt_location_grid lg11
                                                       WHERE lg11.level = 1
                                                         AND lg11.admin0_grid_id = lg1.grid_id)
                               AND lg1.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg1.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 'World',
                                 lg2.admin0_grid_id,
                                 lg2.admin1_grid_id,
                                 lg2.admin2_grid_id,
                                 lg2.admin3_grid_id,
                                 lg2.population,
                                 IF(ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg2.population / IF(lg2.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg2
                             WHERE lg2.level = 1
                               AND lg2.grid_id NOT IN (SELECT lg22.admin1_grid_id
                                                       FROM wp_dt_location_grid lg22
                                                       WHERE lg22.level = 2
                                                         AND lg22.admin1_grid_id = lg2.grid_id)
                               AND lg2.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg2.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 'World',
                                 lg3.admin0_grid_id,
                                 lg3.admin1_grid_id,
                                 lg3.admin2_grid_id,
                                 lg3.admin3_grid_id,
                                 lg3.population,
                                 IF(ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg3.population / IF(lg3.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg3
                             WHERE lg3.level = 2
                               AND lg3.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg3.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 'World',
                                 lg4.admin0_grid_id,
                                 lg4.admin1_grid_id,
                                 lg4.admin2_grid_id,
                                 lg4.admin3_grid_id,
                                 lg4.population,
                                 IF(ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg4.population / IF(lg4.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid lg4
                             WHERE lg4.level = 1
                               AND lg4.admin0_grid_id NOT IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg4.admin0_grid_id IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                             UNION ALL
                             SELECT
                                 'World',
                                 lg5.admin0_grid_id,
                                 lg5.admin1_grid_id,
                                 lg5.admin2_grid_id,
                                 lg5.admin3_grid_id,
                                 lg5.population,
                                 IF(ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div )) < 1, 1,
                                    ROUND(lg5.population / IF(lg5.country_code = 'US', $wpdb->us_div, $wpdb->global_div ))) as needed
                             FROM wp_dt_location_grid as lg5
                             WHERE lg5.level = 3
                               AND lg5.admin0_grid_id IN (100050711, 100219347, 100089589, 100074576, 100259978, 100018514)
                               AND lg5.admin0_grid_id NOT IN
                                   (100314737, 100083318, 100041128, 100133112, 100341242, 100132648, 100222839, 100379914, 100055707,
                                    100379993, 100130389, 100255271, 100363975, 100248845, 100001527, 100342458, 100024289, 100132795,
                                    100054605, 100253456, 100342975, 100074571)
                    ) as tbw
                    LEFT JOIN wp_dt_location_grid loc ON 1=loc.grid_id
                    GROUP BY 'World';
                ", ARRAY_A );
        }

        if ( empty( $results ) ) {
            return [];
        }

        set_transient( __METHOD__ . $administrative_level . $us_div . $global_div, $results, MONTH_IN_SECONDS );

        return $results;
    }

    public static function query_activity_grid_totals( $administrative_level = null ) {

        if ( false !== ( $value = get_transient( __METHOD__ . $administrative_level ) ) ) { // phpcs:ignore
            return $value;
        }

        global $wpdb;

        switch ( $administrative_level ) {
            case 'a0':
                $results = $wpdb->get_results( '
                    SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
                    FROM (
                     SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                        FROM wp_dt_reports ml
                        JOIN wp_dt_location_grid lg ON lg.grid_id=ml.grid_id
                        WHERE ml.grid_id > 0
                    ) as t0
                    GROUP BY t0.admin0_grid_id
                    ', ARRAY_A );
                break;
            case 'a1':
                $results = $wpdb->get_results( '
                    SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                        FROM wp_dt_reports ml
                        JOIN wp_dt_location_grid lg ON lg.grid_id=ml.grid_id
                        WHERE ml.grid_id > 0
                    ) as t1
                    GROUP BY t1.admin1_grid_id
                    ', ARRAY_A );
                break;
            case 'a2':
                $results = $wpdb->get_results( '
                    SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                        FROM wp_dt_reports ml
                        JOIN wp_dt_location_grid lg ON lg.grid_id=ml.grid_id
                        WHERE ml.grid_id > 0
                    ) as t2
                    GROUP BY t2.admin2_grid_id
                    ', ARRAY_A );
                break;
            case 'a3':
                $results = $wpdb->get_results( '
                    SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                        FROM wp_dt_reports ml
                        JOIN wp_dt_location_grid lg ON lg.grid_id=ml.grid_id
                        WHERE ml.grid_id > 0
                    ) as t3
                    GROUP BY t3.admin2_grid_id
                    ', ARRAY_A );
                break;
            case 'world':
                $results = $wpdb->get_results( "
                    SELECT 1 as grid_id, count('World') as count
                    FROM (
                        SELECT 'World'
                        FROM wp_dt_reports ml
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=ml.grid_id
                        WHERE ml.grid_id != 0
                    ) as tw
                    GROUP BY 'World'
                    ", ARRAY_A );
                break;
            case 'full':
                $results = $wpdb->get_results( "
                    SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
                    FROM (
                     SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                        FROM wp_dt_reports as ml
                        JOIN wp_dt_location_grid as lg ON ml.grid_id=lg.grid_id
                        WHERE ml.grid_id > 0
                    ) as t0
                    GROUP BY t0.admin0_grid_id
                    UNION
                    SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
                    FROM (
                     SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                        FROM wp_dt_reports as ml
                        JOIN wp_dt_location_grid as lg ON ml.grid_id=lg.grid_id
                        WHERE ml.grid_id > 0
                    ) as t1
                    GROUP BY t1.admin1_grid_id
                    UNION
                    SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                        FROM wp_dt_reports as ml
                        JOIN wp_dt_location_grid as lg ON ml.grid_id=lg.grid_id
                        WHERE ml.grid_id > 0
                    ) as t2
                    GROUP BY t2.admin2_grid_id
                    UNION
                    SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                        FROM wp_dt_reports as ml
                        JOIN wp_dt_location_grid as lg ON ml.grid_id=lg.grid_id
                        WHERE ml.grid_id > 0
                    ) as t3
                    GROUP BY t3.admin3_grid_id
                    UNION
                    SELECT t4.admin4_grid_id as grid_id, count(t4.admin4_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                        FROM wp_dt_reports as ml
                        JOIN wp_dt_location_grid as lg ON ml.grid_id=lg.grid_id
                        WHERE ml.grid_id > 0
                    ) as t4
                    GROUP BY t4.admin4_grid_id
                    UNION
                    SELECT t5.admin5_grid_id as grid_id, count(t5.admin5_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                        FROM wp_dt_reports as ml
                        JOIN wp_dt_location_grid as lg ON ml.grid_id=lg.grid_id
                        WHERE ml.grid_id > 0
                    ) as t5
                    GROUP BY t5.admin5_grid_id
                    UNION ALL
                    SELECT 1 as grid_id, count('World') as count
                    FROM (
                        SELECT 'World'
                        FROM wp_dt_reports ml
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=ml.grid_id
                        WHERE ml.grid_id != 0
                    ) as tw
                    GROUP BY 'World'
                    ", ARRAY_A );
                break;
            default:
                $results = $wpdb->get_results( '
                    SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
                    FROM (
                     SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                        FROM wp_dt_reports as ml
                        JOIN wp_dt_location_grid as lg ON ml.grid_id=lg.grid_id
                        WHERE ml.grid_id > 0
                    ) as t0
                    GROUP BY t0.admin0_grid_id
                    UNION
                    SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
                    FROM (
                     SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                        FROM wp_dt_reports as ml
                        JOIN wp_dt_location_grid as lg ON ml.grid_id=lg.grid_id
                        WHERE ml.grid_id > 0
                    ) as t1
                    GROUP BY t1.admin1_grid_id
                    UNION
                    SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                        FROM wp_dt_reports as ml
                        JOIN wp_dt_location_grid as lg ON ml.grid_id=lg.grid_id
                        WHERE ml.grid_id > 0
                    ) as t2
                    GROUP BY t2.admin2_grid_id
                    UNION
                    SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                        FROM wp_dt_reports as ml
                        JOIN wp_dt_location_grid as lg ON ml.grid_id=lg.grid_id
                        WHERE ml.grid_id > 0
                    ) as t3
                    GROUP BY t3.admin3_grid_id
                    UNION
                    SELECT t4.admin4_grid_id as grid_id, count(t4.admin4_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                        FROM wp_dt_reports as ml
                        JOIN wp_dt_location_grid as lg ON ml.grid_id=lg.grid_id
                        WHERE ml.grid_id > 0
                    ) as t4
                    GROUP BY t4.admin4_grid_id
                    UNION
                    SELECT t5.admin5_grid_id as grid_id, count(t5.admin5_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                        FROM wp_dt_reports as ml
                        JOIN wp_dt_location_grid as lg ON ml.grid_id=lg.grid_id
                        WHERE ml.grid_id > 0
                    ) as t5
                    GROUP BY t5.admin5_grid_id

                    ', ARRAY_A );
                break;
        }

        $list = [];
        if ( is_array( $results ) ) {
            foreach ( $results as $result ) {
                if ( empty( $result['grid_id'] ) ) {
                    continue;
                }
                if ( empty( $result['count'] ) ) {
                    continue;
                }
                $list[$result['grid_id']] = $result['count'];
            }
        }

        set_transient( __METHOD__ . $administrative_level, $list, HOUR_IN_SECONDS );

        return $list;
    }

    public static function query_activity_location_grid_totals() {

        global $wpdb;

        $results = $wpdb->get_results( '
        SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
            FROM (
             SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM wp_dt_reports ml
                LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=ml.grid_id
                WHERE ml.grid_id != 0
            ) as t0
            GROUP BY t0.admin0_grid_id
            UNION
            SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM wp_dt_reports ml
                LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=ml.grid_id
                WHERE ml.grid_id != 0
            ) as t1
            GROUP BY t1.admin1_grid_id
            UNION
            SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM wp_dt_reports ml
                LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=ml.grid_id
                WHERE ml.grid_id != 0
            ) as t2
            GROUP BY t2.admin2_grid_id
            UNION
            SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
            FROM (
                SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id, lg.admin4_grid_id, lg.admin5_grid_id
                FROM wp_dt_reports ml
                LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=ml.grid_id
                WHERE ml.grid_id != 0
            ) as t3
            GROUP BY t3.admin3_grid_id;
        ', ARRAY_A );

        $list = [];
        if ( is_array( $results ) ) {
            foreach ( $results as $result ) {
                $list[$result['grid_id']] = $result;
            }
        }

        return $list;
    }

    public static function query_funnel_grid_totals( $administrative_level = null, $stages = [ '1','2','3','4','5','6' ] ) {
        global $wpdb;

        $stage_list = '(' . implode( ',', $stages ) . ')';

        // @note temp removed caching
//        if ( false !== ( $value = get_transient( __METHOD__ . $administrative_level ) ) ) { // phpcs:ignore
//            return $value;
//        }

        // @phpcs:disable
        switch ( $administrative_level ) {
            case 'a0':

                $results = $wpdb->get_results( "
                    SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM
                        (
                           SELECT r.user_id, MAX(r.value) as stage, (
                                SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                ) as grid_id FROM wp_dt_reports r
                           WHERE r.type = 'system' AND r.subtype = 'current_level'
                           GROUP BY r.user_id
                        ) as tb
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                        WHERE tb.stage IN $stage_list
                    ) as t0
                    GROUP BY t0.admin0_grid_id
                    ", ARRAY_A );
                break;
            case 'a1':
                $results = $wpdb->get_results( "
                    SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
                    FROM (
                       SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM
                        (
                           SELECT r.user_id, MAX(r.value) as stage, (
                                SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                ) as grid_id FROM wp_dt_reports r
                           WHERE r.type = 'system' AND r.subtype = 'current_level'
                           GROUP BY r.user_id
                        ) as tb
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                        WHERE tb.stage IN $stage_list
                    ) as t1
                    GROUP BY t1.admin1_grid_id
                    ", ARRAY_A );
                break;
            case 'a2':
                $results = $wpdb->get_results( "
                    SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
                    FROM (
                       SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM
                        (
                           SELECT r.user_id, MAX(r.value) as stage, (
                                SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                ) as grid_id FROM wp_dt_reports r
                           WHERE r.type = 'system' AND r.subtype = 'current_level'
                           GROUP BY r.user_id
                        ) as tb
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                        WHERE tb.stage IN $stage_list
                    ) as t2
                    GROUP BY t2.admin2_grid_id
                    ", ARRAY_A );
                break;
            case 'a3':
                $results = $wpdb->get_results( "
                    SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM
                        (
                           SELECT r.user_id, MAX(r.value) as stage, (
                                SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                ) as grid_id FROM wp_dt_reports r
                           WHERE r.type = 'system' AND r.subtype = 'current_level'
                           GROUP BY r.user_id
                        ) as tb
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                        WHERE tb.stage IN $stage_list
                    ) as t3
                    GROUP BY t3.admin3_grid_id
                    ", ARRAY_A );
                break;
            case 'world':
                $results = $wpdb->get_results( "
                    SELECT 1 as grid_id, count('World') as count
                    FROM (
                        SELECT 'World'
                        FROM
                        (
                           SELECT r.user_id, MAX(r.value) as stage, (
                                SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                ) as grid_id FROM wp_dt_reports r
                           WHERE r.type = 'system' AND r.subtype = 'current_level'
                           GROUP BY r.user_id
                        ) as tb
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                        WHERE tb.stage IN $stage_list
                    ) as tw
                    GROUP BY 'World'
                    ", ARRAY_A );
                break;
            case 'full':
                $results = $wpdb->get_results( "
                    SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM
                        (
                           SELECT r.user_id, MAX(r.value) as stage, (
                                SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                ) as grid_id FROM wp_dt_reports r
                           WHERE r.type = 'system' AND r.subtype = 'current_level'
                           GROUP BY r.user_id
                        ) as tb
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                        WHERE tb.stage IN $stage_list
                    ) as t0
                    GROUP BY t0.admin0_grid_id
                    UNION ALL
                    SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM
                        (
                           SELECT r.user_id, MAX(r.value) as stage, (
                                SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                ) as grid_id FROM wp_dt_reports r
                           WHERE r.type = 'system' AND r.subtype = 'current_level'
                           GROUP BY r.user_id
                        ) as tb
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                        WHERE tb.stage IN $stage_list
                    ) as t1
                    GROUP BY t1.admin1_grid_id
                    UNION ALL
                    SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM
                        (
                           SELECT r.user_id, MAX(r.value) as stage, (
                                SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                ) as grid_id FROM wp_dt_reports r
                           WHERE r.type = 'system' AND r.subtype = 'current_level'
                           GROUP BY r.user_id
                        ) as tb
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                        WHERE tb.stage IN $stage_list
                    ) as t2
                    GROUP BY t2.admin2_grid_id
                    UNION ALL
                    SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM
                        (
                           SELECT r.user_id, MAX(r.value) as stage, (
                                SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                ) as grid_id FROM wp_dt_reports r
                           WHERE r.type = 'system' AND r.subtype = 'current_level'
                           GROUP BY r.user_id
                        ) as tb
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                        WHERE tb.stage IN $stage_list
                    ) as t3
                    GROUP BY t3.admin3_grid_id
                    UNION ALL
                    SELECT 1 as grid_id, count('World') as count
                    FROM (
                        SELECT 'World'
                        FROM
                        (
                           SELECT r.user_id, MAX(r.value) as stage, (
                                SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                ) as grid_id FROM wp_dt_reports r
                           WHERE r.type = 'system' AND r.subtype = 'current_level'
                           GROUP BY r.user_id
                        ) as tb
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                        WHERE tb.stage IN $stage_list
                    ) as tw
                    GROUP BY 'World'
                    ", ARRAY_A );
                break;
            default:
                $results = $wpdb->get_results( "
                    SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
                    FROM (
                       SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM
                        (
                           SELECT r.user_id, MAX(r.value) as stage, (
                                SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                ) as grid_id FROM wp_dt_reports r
                           WHERE r.type = 'system' AND r.subtype = 'current_level'
                           GROUP BY r.user_id
                        ) as tb
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                        WHERE tb.stage IN $stage_list
                    ) as t0
                    GROUP BY t0.admin0_grid_id
                    UNION ALL
                    SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM
                        (
                           SELECT r.user_id, MAX(r.value) as stage, (
                                SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                ) as grid_id FROM wp_dt_reports r
                           WHERE r.type = 'system' AND r.subtype = 'current_level'
                           GROUP BY r.user_id
                        ) as tb
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                        WHERE tb.stage IN $stage_list
                    ) as t1
                    GROUP BY t1.admin1_grid_id
                    UNION ALL
                    SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM
                        (
                           SELECT r.user_id, MAX(r.value) as stage, (
                                SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                ) as grid_id FROM wp_dt_reports r
                           WHERE r.type = 'system' AND r.subtype = 'current_level'
                           GROUP BY r.user_id
                        ) as tb
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                        WHERE tb.stage IN $stage_list
                    ) as t2
                    GROUP BY t2.admin2_grid_id
                    UNION ALL
                    SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM
                        (
                           SELECT r.user_id, MAX(r.value) as stage, (
                                SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                ) as grid_id FROM wp_dt_reports r
                           WHERE r.type = 'system' AND r.subtype = 'current_level'
                           GROUP BY r.user_id
                        ) as tb
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                        WHERE tb.stage IN $stage_list
                    ) as t3
                    GROUP BY t3.admin3_grid_id
                    ", ARRAY_A );
                break;
        }
        // @phpcs:enable

        $list = [];
        if ( is_array( $results ) ) {
            foreach ( $results as $result ) {
                if ( empty( $result['grid_id'] ) ) {
                    continue;
                }
                if ( empty( $result['count'] ) ) {
                    continue;
                }
                $list[$result['grid_id']] = $result['count'];
            }
        }

//        set_transient( __METHOD__ . $administrative_level, $list, HOUR_IN_SECONDS . 6 );

        return $list;
    }

    public static function clear_practitioner_grid_totals() {
        delete_transient( 'GO_Funnel_App_Heatmap::query_practitioner_grid_totals' );
        delete_transient( 'GO_Funnel_App_Heatmap::query_practitioner_grid_totalsa0' );
        delete_transient( 'GO_Funnel_App_Heatmap::query_practitioner_grid_totalsa1' );
        delete_transient( 'GO_Funnel_App_Heatmap::query_practitioner_grid_totalsa2' );
        delete_transient( 'GO_Funnel_App_Heatmap::query_practitioner_grid_totalsa3' );
        delete_transient( 'GO_Funnel_App_Heatmap::query_practitioner_grid_totalsa4' );
        delete_transient( 'GO_Funnel_App_Heatmap::query_practitioner_grid_totalsworld' );
        delete_transient( 'GO_Funnel_App_Heatmap::query_practitioner_grid_totalsfull' );
    }

    public static function query_practitioner_grid_totals( $administrative_level = null ) {

//        if ( false !== ( $value = get_transient( __METHOD__ . $administrative_level ) ) ) { // phpcs:ignore
//            return $value;
//        }

        global $wpdb;

        // @phpcs:disable
        switch ( $administrative_level ) {
            case 'a0':
                $results = $wpdb->get_results( "
                    SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
                    FROM (
                            SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                            FROM
                            (
                               SELECT r.user_id, MAX(r.value) as stage, (
                                    SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                    ) as grid_id FROM wp_dt_reports r
                               WHERE r.type = 'system' AND r.subtype = 'current_level'
                               GROUP BY r.user_id
                            ) as tb
                            LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                            WHERE tb.stage IN ('4', '5', '6')
                        ) as t0
                    GROUP BY t0.admin0_grid_id
                    ", ARRAY_A );
                break;
            case 'a1':
                $results = $wpdb->get_results( "
                    SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
                    FROM (
                            SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                            FROM
                            (
                               SELECT r.user_id, MAX(r.value) as stage, (
                                    SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                    ) as grid_id FROM wp_dt_reports r
                               WHERE r.type = 'system' AND r.subtype = 'current_level'
                               GROUP BY r.user_id
                            ) as tb
                            LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                            WHERE tb.stage IN ('4', '5', '6')
                        ) as t1
                    GROUP BY t1.admin1_grid_id
                    ", ARRAY_A );
                break;
            case 'a2':
                $results = $wpdb->get_results( "
                    SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
                    FROM (
                            SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                            FROM
                            (
                               SELECT r.user_id, MAX(r.value) as stage, (
                                    SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                    ) as grid_id FROM wp_dt_reports r
                               WHERE r.type = 'system' AND r.subtype = 'current_level'
                               GROUP BY r.user_id
                            ) as tb
                            LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                            WHERE tb.stage IN ('4', '5', '6')
                        ) as t2
                    GROUP BY t2.admin2_grid_id
                    ", ARRAY_A );
                break;
            case 'a3':
                $results = $wpdb->get_results( "
                    SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
                    FROM (
                            SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                            FROM
                            (
                               SELECT r.user_id, MAX(r.value) as stage, (
                                    SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                    ) as grid_id FROM wp_dt_reports r
                               WHERE r.type = 'system' AND r.subtype = 'current_level'
                               GROUP BY r.user_id
                            ) as tb
                            LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                            WHERE tb.stage IN ('4', '5', '6')
                        ) as t3
                    GROUP BY t3.admin3_grid_id

                    ", ARRAY_A );
                break;
            case 'world':
                $results = $wpdb->get_results( "
                    SELECT 1 as grid_id, count('World') as count
                    FROM (
                        SELECT 'World'
                        FROM
                        (
                           SELECT r.user_id, MAX(r.value) as stage, (
		                        SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                ) as grid_id FROM wp_dt_reports r
                           WHERE r.type = 'system' AND r.subtype = 'current_level'
                           GROUP BY r.user_id
                        ) as tb
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                        WHERE tb.stage IN ('4', '5', '6')
                        ) as tw
                    GROUP BY 'World'
                    ", ARRAY_A );
                break;
            case 'full': // full query including world
                $results = $wpdb->get_results( "
                    SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
                    FROM (
                            SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                            FROM
                            (
                               SELECT r.user_id, MAX(r.value) as stage, (
                                    SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                    ) as grid_id FROM wp_dt_reports r
                               WHERE r.type = 'system' AND r.subtype = 'current_level'
                               GROUP BY r.user_id
                            ) as tb
                            LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                            WHERE tb.stage IN ('4', '5', '6')
                        ) as t0
                    GROUP BY t0.admin0_grid_id
                    UNION ALL
                    SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
                    FROM (
                            SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                            FROM
                            (
                               SELECT r.user_id, MAX(r.value) as stage, (
                                    SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                    ) as grid_id FROM wp_dt_reports r
                               WHERE r.type = 'system' AND r.subtype = 'current_level'
                               GROUP BY r.user_id
                            ) as tb
                            LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                            WHERE tb.stage IN ('4', '5', '6')
                        ) as t1
                    GROUP BY t1.admin1_grid_id
                    UNION ALL
                    SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
                    FROM (
                            SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                            FROM
                            (
                               SELECT r.user_id, MAX(r.value) as stage, (
                                    SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                    ) as grid_id FROM wp_dt_reports r
                               WHERE r.type = 'system' AND r.subtype = 'current_level'
                               GROUP BY r.user_id
                            ) as tb
                            LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                            WHERE tb.stage IN ('4', '5', '6')
                        ) as t2
                    GROUP BY t2.admin2_grid_id
                    UNION ALL
                    SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
                    FROM (
                            SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                            FROM
                            (
                               SELECT r.user_id, MAX(r.value) as stage, (
                                    SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                    ) as grid_id FROM wp_dt_reports r
                               WHERE r.type = 'system' AND r.subtype = 'current_level'
                               GROUP BY r.user_id
                            ) as tb
                            LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                            WHERE tb.stage IN ('4', '5', '6')
                        ) as t3
                    GROUP BY t3.admin3_grid_id
                    UNION ALL
                    SELECT 1 as grid_id, count('World') as count
                    FROM (
                        SELECT 'World'
                        FROM
                        (
                           SELECT r.user_id, MAX(r.value) as stage, (
		                        SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                ) as grid_id FROM wp_dt_reports r
                           WHERE r.type = 'system' AND r.subtype = 'current_level'
                           GROUP BY r.user_id
                        ) as tb
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                        WHERE tb.stage IN ('4', '5', '6')
                        ) as tw
                    GROUP BY 'World'
                    ", ARRAY_A );
                break;
            default:
                $results = $wpdb->get_results( "
                        SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
                        FROM (
                            SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                            FROM
                            (
                               SELECT r.user_id, MAX(r.value) as stage, (
                                    SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                    ) as grid_id FROM wp_dt_reports r
                               WHERE r.type = 'system' AND r.subtype = 'current_level'
                               GROUP BY r.user_id
                            ) as tb
                            LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                            WHERE tb.stage IN ('4', '5', '6')
                        ) as t0
                        GROUP BY t0.admin0_grid_id
                        UNION ALL
                        SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
                        FROM (
                            SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                            FROM
                            (
                               SELECT r.user_id, MAX(r.value) as stage, (
                                    SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                    ) as grid_id FROM wp_dt_reports r
                               WHERE r.type = 'system' AND r.subtype = 'current_level'
                               GROUP BY r.user_id
                            ) as tb
                            LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                            WHERE tb.stage IN ('4', '5', '6')
                        ) as t1
                        GROUP BY t1.admin1_grid_id
                        UNION ALL
                        SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
                        FROM (
                            SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                            FROM
                            (
                               SELECT r.user_id, MAX(r.value) as stage, (
                                    SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                    ) as grid_id FROM wp_dt_reports r
                               WHERE r.type = 'system' AND r.subtype = 'current_level'
                               GROUP BY r.user_id
                            ) as tb
                            LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                            WHERE tb.stage IN ('4', '5', '6')
                        ) as t2
                        GROUP BY t2.admin2_grid_id
                        UNION ALL
                        SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
                        FROM (
                            SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                            FROM
                            (
                               SELECT r.user_id, MAX(r.value) as stage, (
                                    SELECT grid_id FROM wp_dt_reports WHERE user_id = r.user_id AND grid_id IS NOT NULL ORDER BY id DESC LIMIT 1
                                    ) as grid_id FROM wp_dt_reports r
                               WHERE r.type = 'system' AND r.subtype = 'current_level'
                               GROUP BY r.user_id
                            ) as tb
                            LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=tb.grid_id
                            WHERE tb.stage IN ('4', '5', '6')
                        ) as t3
                        GROUP BY t3.admin3_grid_id
                        ", ARRAY_A );
                break;
        }
        // @phpcs:enable

        $list = [];
        if ( is_array( $results ) ) {
            foreach ( $results as $result ) {
                if ( empty( $result['grid_id'] ) ) {
                    continue;
                }
                if ( empty( $result['count'] ) ) {
                    continue;
                }
                $list[$result['grid_id']] = $result['count'];
            }
        }

//        set_transient( __METHOD__ . $administrative_level, $list, HOUR_IN_SECONDS . 6 );

        return $list;
    }

    public static function clear_church_grid_totals() {
        delete_transient( 'GO_Funnel_App_Heatmap::query_church_grid_totals' );
        delete_transient( 'GO_Funnel_App_Heatmap::query_church_grid_totalsa0' );
        delete_transient( 'GO_Funnel_App_Heatmap::query_church_grid_totalsa1' );
        delete_transient( 'GO_Funnel_App_Heatmap::query_church_grid_totalsa2' );
        delete_transient( 'GO_Funnel_App_Heatmap::query_church_grid_totalsa3' );
        delete_transient( 'GO_Funnel_App_Heatmap::query_church_grid_totalsa4' );
        delete_transient( 'GO_Funnel_App_Heatmap::query_church_grid_totalsworld' );
        delete_transient( 'GO_Funnel_App_Heatmap::query_church_grid_totalsfull' );
    }

    public static function query_church_grid_totals( $administrative_level = null ) {
//        dt_write_log( __METHOD__ );
//        if ( false !== ( $value = get_transient( __METHOD__ . $administrative_level ) ) ) { // phpcs:ignore
//            return $value;
//        }

        global $wpdb;

        // @phpcs:disable
        switch ( $administrative_level ) {
            case 'a0':
                $results = $wpdb->get_results( "
                    SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM wp_dt_location_grid_meta lgm
                        LEFT JOIN wp_postmeta pm ON pm.post_id=lgm.post_id AND pm.meta_key = 'group_type' AND pm.meta_value = 'church'
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=lgm.grid_id
                        WHERE lgm.post_type = 'groups'
                    ) as t0
                    GROUP BY t0.admin0_grid_id
                    ", ARRAY_A );
                break;
            case 'a1':
                $results = $wpdb->get_results( "
                    SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM wp_dt_location_grid_meta lgm
                        LEFT JOIN wp_postmeta pm ON pm.post_id=lgm.post_id AND pm.meta_key = 'group_type' AND pm.meta_value = 'church'
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=lgm.grid_id
                        WHERE lgm.post_type = 'groups'
                    ) as t1
                    GROUP BY t1.admin1_grid_id
                    ", ARRAY_A );
                break;
            case 'a2':
                $results = $wpdb->get_results( "
                    SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM wp_dt_location_grid_meta lgm
                        LEFT JOIN wp_postmeta pm ON pm.post_id=lgm.post_id AND pm.meta_key = 'group_type' AND pm.meta_value = 'church'
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=lgm.grid_id
                        WHERE lgm.post_type = 'groups'
                    ) as t2
                    GROUP BY t2.admin2_grid_id
                    ", ARRAY_A );
                break;
            case 'a3':
                $results = $wpdb->get_results( "
                    SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM wp_dt_location_grid_meta lgm
                        LEFT JOIN wp_postmeta pm ON pm.post_id=lgm.post_id AND pm.meta_key = 'group_type' AND pm.meta_value = 'church'
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=lgm.grid_id
                        WHERE lgm.post_type = 'groups'
                    ) as t3
                    GROUP BY t3.admin3_grid_id

                    ", ARRAY_A );
                break;
            case 'world':
                $results = $wpdb->get_results( "
                    SELECT 1 as grid_id, count('World') as count
                    FROM (
                        SELECT 'World'
                        FROM wp_dt_location_grid_meta lgm
                        LEFT JOIN wp_postmeta pm ON pm.post_id=lgm.post_id AND pm.meta_key = 'group_type' AND pm.meta_value = 'church'
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=lgm.grid_id
                        WHERE lgm.post_type = 'groups'
                         ) as tw
                    GROUP BY 'World'
                    ", ARRAY_A );
                break;
            case 'full': // full query including world
            default:
                $results = $wpdb->get_results( "
                    SELECT t0.admin0_grid_id as grid_id, count(t0.admin0_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM wp_dt_location_grid_meta lgm
                        LEFT JOIN wp_postmeta pm ON pm.post_id=lgm.post_id AND pm.meta_key = 'group_type' AND pm.meta_value = 'church'
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=lgm.grid_id
                        WHERE lgm.post_type = 'groups'
                    ) as t0
                    GROUP BY t0.admin0_grid_id
                    UNION ALL
                    SELECT t1.admin1_grid_id as grid_id, count(t1.admin1_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM wp_dt_location_grid_meta lgm
                        LEFT JOIN wp_postmeta pm ON pm.post_id=lgm.post_id AND pm.meta_key = 'group_type' AND pm.meta_value = 'church'
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=lgm.grid_id
                        WHERE lgm.post_type = 'groups'
                    ) as t1
                    GROUP BY t1.admin1_grid_id
                    UNION ALL
                    SELECT t2.admin2_grid_id as grid_id, count(t2.admin2_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM wp_dt_location_grid_meta lgm
                        LEFT JOIN wp_postmeta pm ON pm.post_id=lgm.post_id AND pm.meta_key = 'group_type' AND pm.meta_value = 'church'
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=lgm.grid_id
                        WHERE lgm.post_type = 'groups'
                    ) as t2
                    GROUP BY t2.admin2_grid_id
                    UNION ALL
                    SELECT t3.admin3_grid_id as grid_id, count(t3.admin3_grid_id) as count
                    FROM (
                        SELECT lg.admin0_grid_id, lg.admin1_grid_id, lg.admin2_grid_id, lg.admin3_grid_id
                        FROM wp_dt_location_grid_meta lgm
                        LEFT JOIN wp_postmeta pm ON pm.post_id=lgm.post_id AND pm.meta_key = 'group_type' AND pm.meta_value = 'church'
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=lgm.grid_id
                        WHERE lgm.post_type = 'groups'
                    ) as t3
                    GROUP BY t3.admin3_grid_id
                    UNION ALL
                    SELECT 1 as grid_id, count('World') as count
                    FROM (
                        SELECT 'World'
                        FROM wp_dt_location_grid_meta lgm
                        LEFT JOIN wp_postmeta pm ON pm.post_id=lgm.post_id AND pm.meta_key = 'group_type' AND pm.meta_value = 'church'
                        LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=lgm.grid_id
                        WHERE lgm.post_type = 'groups'
                         ) as tw
                    GROUP BY 'World'
                    ", ARRAY_A );
                break;
        }
        // @phpcs:enable

            $list = [];
        if ( is_array( $results ) ) {
            foreach ( $results as $result ) {
                $result['count'] = (int) $result['count'];
                if ( empty( $result['count'] ) ) {
                    continue;
                }
                if ( isset( $result['world'] ) ) {
                    if ( ! isset( $list[$result['world']] ) ) {
                        $list[$result['world']] = 0;
                    }
                    $list[$result['world']] = $list[$result['world']] + $result['count'];
                }
                if ( isset( $result['grid_id'] ) ) {
                    if ( ! isset( $list[$result['grid_id']] ) ) {
                        $list[$result['grid_id']] = 0;
                    }
                    $list[$result['grid_id']] = $list[$result['grid_id']] + $result['count'];
                }
                if ( ! empty( $result['admin0_grid_id'] ) ) {
                    if ( ! isset( $list[$result['admin0_grid_id']] ) ) {
                        $list[$result['admin0_grid_id']] = 0;
                    }
                    $list[$result['admin0_grid_id']] = $list[$result['admin0_grid_id']] + $result['count'];
                }
                if ( ! empty( $result['admin1_grid_id'] ) ) {
                    if ( ! isset( $list[$result['admin1_grid_id']] ) ) {
                        $list[$result['admin1_grid_id']] = 0;
                    }
                    $list[$result['admin1_grid_id']] = $list[$result['admin1_grid_id']] + $result['count'];
                }
                if ( ! empty( $result['admin2_grid_id'] ) ) {
                    if ( ! isset( $list[$result['admin2_grid_id']] ) ) {
                        $list[$result['admin2_grid_id']] = 0;
                    }
                    $list[$result['admin2_grid_id']] = $list[$result['admin2_grid_id']] + $result['count'];
                }
                if ( ! empty( $result['admin3_grid_id'] ) ) {
                    if ( ! isset( $list[$result['admin3_grid_id']] ) ) {
                        $list[$result['admin3_grid_id']] = 0;
                    }
                    $list[$result['admin3_grid_id']] = $list[$result['admin3_grid_id']] + $result['count'];
                }
            }
        }

//        set_transient( __METHOD__ . $administrative_level, $list, HOUR_IN_SECONDS . 6 );

//        dt_write_log(__METHOD__);
//        dt_write_log($list);

            return $list;
    }

    public static function query_grid_elements( $grid_id ) {
        global $wpdb;

        $result = $wpdb->get_row($wpdb->prepare( '
            SELECT
                   lg.admin3_grid_id as a3,
                   lg.admin2_grid_id as a2,
                   lg.admin1_grid_id as a1,
                   lg.admin0_grid_id as a0,
                   1 as world,
                   lg3.population as a3_population,
                   lg2.population as a2_population,
                   lg1.population as a1_population,
                   lg0.population as a0_population,
                   lgw.population as world_population,
                   lg.country_code
            FROM wp_dt_location_grid lg
            LEFT JOIN wp_dt_location_grid lg0 ON lg.admin0_grid_id=lg0.grid_id
            LEFT JOIN wp_dt_location_grid lg1 ON lg.admin1_grid_id=lg1.grid_id
            LEFT JOIN wp_dt_location_grid lg2 ON lg.admin2_grid_id=lg2.grid_id
            LEFT JOIN wp_dt_location_grid lg3 ON lg.admin3_grid_id=lg3.grid_id
            LEFT JOIN wp_dt_location_grid lgw ON 1=lgw.grid_id
            WHERE lg.grid_id = %s
        ', $grid_id ), ARRAY_A );

        return $result;
    }


    public static function get_self( $grid_id, $global_div, $us_div ) {
        global $wpdb;

        // get grid elements for design
        $grid = $wpdb->get_row( $wpdb->prepare( "
            SELECT
              g.grid_id,
              g.level,
              g.alt_name as name,
              gn.alt_name as parent_name,
              g.country_code,
              g.population,
              IF(ROUND(g.population / IF(g.country_code = 'US', %d, %d)) < 1, 1,
                 ROUND(g.population / IF(g.country_code = 'US', %d, %d))) as needed,
              (SELECT COUNT(prs.grid_id) FROM wp_dt_location_grid as prs WHERE prs.parent_id = g.parent_id ) as peers
            FROM wp_dt_location_grid as g
            LEFT JOIN wp_dt_location_grid as gn ON g.parent_id=gn.grid_id
            WHERE g.grid_id = %s
        ", $us_div, $global_div, $us_div, $global_div, $grid_id ), ARRAY_A );

        // set array
        $population_division = self::_get_population_division( $grid['country_code'], $global_div, $us_div );
        $data = [
            'level' => $grid['level'],
            'parent_level' => $grid['level'] - 1, // one level higher than current
            'population_division' => number_format_i18n( $population_division ), // label for content not calculation
            'population_division_int' => $population_division, // label for content not calculation
            'name' => $grid['name'],
            'parent_name' => $grid['parent_name'],
            'peers' => number_format_i18n( $grid['peers'] ),
            'population' => number_format_i18n( $grid['population'] ),
            'needed' => number_format_i18n( $grid['needed'] ),
        ];

        return $data;
    }

    public static function endpoint_get_level( $grid_id, $administrative_level, $list, $global_div, $us_div ) {
        // add levels
        $flat_grid = self::query_flat_grid_by_level( $administrative_level, $us_div, $global_div );
        $flat_grid_limited = self::_limit_counts( $flat_grid, $list ); // limit counts to no larger than needed per location.

        $grid = self::query_grid_elements( $grid_id ); // get level ids for grid_id

        if ( isset( $flat_grid_limited[$grid[$administrative_level]] ) && ! empty( $flat_grid_limited[$grid[$administrative_level]] ) ) {
            $level = $flat_grid_limited[$grid[$administrative_level]];
        }
        else {
            return false;
        }

        $percent = $level['reported'] / $level['needed'] * 100;
        if ( 100 < $percent ) {
            $percent = 100;
        } else {
            $percent = number_format_i18n( $percent, 2 );
        }

        if ( isset( $flat_grid[$grid[$administrative_level]] ) && ! empty( $flat_grid[$grid[$administrative_level]] ) ) {
            $raw_level = $flat_grid[$grid[$administrative_level]];
            $raw_reported = $raw_level['reported'];
        } else {
            $raw_reported = $level['reported'];
        }

        /**
         * @todo temp cover for populations
         */
        if ( isset( $grid[$administrative_level . '_population'] )
            && ! empty( $grid[$administrative_level . '_population'] )
            && in_array( $administrative_level, [ 'a0', 'world' ] ) ) {
            $level['population'] = $grid[$administrative_level . '_population'];

            $population_division = self::_get_population_division( $grid['country_code'], $global_div, $us_div );
            $needed = round( $level['population'] / $population_division );
            if ( $needed < 1 ){
                $needed = 1;
            }
            $level['needed'] = $needed;
            if ( $administrative_level === 'world' ) {
                $world_population = 8174493405;
                $us_population = 335701430;
                $global_pop_block = $global_div;
                $us_pop_block = $us_div;
                $world_population_without_us = $world_population - $us_population;
                $needed_without_us = $world_population_without_us / $global_pop_block;
                $needed_in_the_us = $us_population / $us_pop_block;
                $level['needed'] = $needed_without_us + $needed_in_the_us;
                $percent = $level['reported'] / $level['needed'] * 100;
            }
        }
        // @todo end temp cover for populations

        if ( empty( $level['name'] ) ) {
            return false;
        }

        $data = [
            'name' => $level['name'],
            'grid_id' => (int) $level['grid_id'],
            'population' => number_format_i18n( $level['population'] ),
            'needed' => number_format_i18n( $level['needed'] ),
            'reported' => number_format_i18n( $raw_reported ),
            'percent' => number_format_i18n( $percent, 2 ),
        ];

        return $data;
    }

    public static function endpoint_get_activity_level( $grid_id, $administrative_level, $list, $global_div, $us_div ) {

        $flat_grid = self::query_flat_grid_by_level( $administrative_level, $us_div, $global_div );
//        $flat_grid_limited = self::_limit_counts( $flat_grid, $list ); // limit counts to no larger than needed per location.
        $flat_grid_limited = [];
        foreach ( $flat_grid as $value ) {
            $flat_grid_limited[$value['grid_id']] = $value;

            if ( isset( $list[$value['grid_id']] ) && ! empty( $list[$value['grid_id']] ) ) {
                $flat_grid_limited[$value['grid_id']]['reported'] = $list[$value['grid_id']];
//                if ( $list[$value['grid_id']] <= $value['needed'] ) {
//                    $flat_grid_limited[$value['grid_id']]['reported'] = $list[$value['grid_id']];
//                } else {
//                    $flat_grid_limited[$value['grid_id']]['reported'] = $value['needed'];
//                }
            }
        }

        $grid = self::query_grid_elements( $grid_id ); // get level ids for grid_id

        if ( isset( $flat_grid_limited[$grid[$administrative_level]] ) && ! empty( $flat_grid_limited[$grid[$administrative_level]] ) ) {
            $level = $flat_grid_limited[$grid[$administrative_level]];
        }
        else {
            return false;
        }

//        $percent = $level['reported'] / $level['needed'] * 100;
//        if ( 100 < $percent ) {
//            $percent = 100;
//        } else {
//            $percent = number_format_i18n( $percent, 2 );
//        }

        if ( isset( $flat_grid[$grid[$administrative_level]] ) && ! empty( $flat_grid[$grid[$administrative_level]] ) ) {
            $raw_level = $flat_grid[$grid[$administrative_level]];
            $raw_reported = $raw_level['reported'];
        } else {
            $raw_reported = $level['reported'];
        }

        /**
         * @todo temp cover for populations
         */
        if ( isset( $grid[$administrative_level . '_population'] )
            && ! empty( $grid[$administrative_level . '_population'] )
            && in_array( $administrative_level, [ 'a0', 'world' ] ) ) {
            $level['population'] = $grid[$administrative_level . '_population'];

            $population_division = self::_get_population_division( $grid['country_code'], $global_div, $us_div );
            $needed = round( $level['population'] / $population_division );
            if ( $needed < 1 ){
                $needed = 1;
            }
            $level['needed'] = $needed;
            if ( $administrative_level === 'world' ) {
                $world_population = 7974493405;
                $us_population = 335701430;
                $global_pop_block = $global_div;
                $us_pop_block = $us_div;
                $world_population_without_us = $world_population - $us_population;
                $needed_without_us = $world_population_without_us / $global_pop_block;
                $needed_in_the_us = $us_population / $us_pop_block;
                $level['needed'] = $needed_without_us + $needed_in_the_us;
                $percent = $level['reported'] / $level['needed'] * 100;
            }
        }
        // @todo end temp cover for populations

        if ( empty( $level['name'] ) ) {
            return false;
        }

        $data = [
            'name' => $level['name'],
            'grid_id' => (int) $level['grid_id'],
            'population' => number_format_i18n( $level['population'] ),
//            'needed' => number_format_i18n( $level['needed'] ),
            'reported' => number_format_i18n( $raw_reported ),
//            'percent' => number_format_i18n( $percent, 2 ),
        ];

        return $data;
    }


    public static function get_activity_list( $filters, $limit = false, $language_code = 'en', $hours = null ) {

        // dt_write_log( __METHOD__ );
        // dt_write_log( $filters );

        $zume_languages_by_code = impact_map_languages();
        $languages = [];
        $countries = [];
        $projects = [];
        $types = [];
        $utc_time = new DateTime( 'now', new DateTimeZone( $filters['timezone'] ?? '' ) );
        $timezone_offset = $utc_time->format( 'Z' );

        $training_items = [];
        $records = 0;
        // $praying_count = 0;
        // $studying_count = 0;
        // $training_count = 0;
        // $practicing_count = 0;
        // $coaching_count = 0;

        $activity_list = self::query_activity_list( $filters, $language_code, $hours );
        $list = [];

        foreach ( $activity_list as $record ) {

            // time string
            $time_string = self::_time_ago( $record['time_end'] );

            $prepared_array = array(
                'note' => $record['note'],
                'time' => $time_string,
                'type' => $record['type'],
                'language' => $record['language_code'],
                'project' => $record['project'],
                'country' => $record['country_code'],
            );

            // COUNTERS FOR TOTALS
            // count types
            if ( isset( $record['type'] ) && ! empty( $record['type'] ) )
            {
                if ( ! isset( $types[$record['type']] ) ) {
                    $types[$record['type']] = [
                        'code' => $record['type'],
                        'name' => ucwords( $record['type'] ),
                        'count' => 0,
                    ];
                }
                $types[$record['type']]['count']++;
            }
            // count projects
            if ( isset( $record['project'] ) && ! empty( $record['project'] ) )
            {
                if ( ! isset( $projects[$record['project']] ) ) {
                    $projects[$record['project']] = [
                        'code' => $record['project'],
                        'name' => ucwords( $record['project'] ),
                        'count' => 0,
                    ];
                }
                $projects[$record['project']]['count']++;
            }
            // count country
            if ( isset( $record['country_code'] ) && !empty( $record['country_code'] ) && ! isset( $countries[$record['country_name']] ) ) {
                $countries[$record['country_name']] = [
                    'code' => $record['country_code'],
                    'name' => $record['country_name'],
                    'count' => 0,
                ];
            }
            if ( isset( $record['country_code'] ) ) {
                $countries[$record['country_name']]['count']++;
            }

            // count language
            $language_code = $record['language_code'];
            if ( ! isset( $zume_languages_by_code[$language_code] ) ) {
                continue;
            }
            $language_name = $zume_languages_by_code[$language_code];
            // $language_name = $language['name'];
            if ( isset( $language_code )
                && ! isset( $languages[$language_name] )
            ) {
                $languages[$language_name] = [
                    'code' => $language_code,
                    'name' => $language_name,
                    'count' => 0,
                ];
            }
            if ( isset( $language_name ) ) {
                $languages[$language_name]['count']++;
            }

            // APPLY FILTER
            // filter out non selected country
            // no filter set
            if ( 'none' === $filters['project'] && 'none' === $filters['country'] && 'none' === $filters['language'] && 'none' === $filters['type'] ) {
                $list[] = $prepared_array;
            }

            // country set
            else if ( 'none' === $filters['project'] && $prepared_array['country'] === $filters['country'] && 'none' === $filters['language'] && 'none' === $filters['type'] ) {
                $list[] = $prepared_array;
            }
            // project set
            else if ( $prepared_array['project'] === $filters['project'] && 'none' === $filters['country'] && 'none' === $filters['language'] && 'none' === $filters['type'] ) {
                $list[] = $prepared_array;
            }
            // language set
            else if ( 'none' === $filters['project'] && 'none' === $filters['country'] && $prepared_array['language'] === $filters['language'] && 'none' === $filters['type'] ) {
                $list[] = $prepared_array;
            }
            // type set
            else if ( 'none' === $filters['project'] && 'none' === $filters['country'] && 'none' === $filters['language'] && $prepared_array['type'] === $filters['type'] ) {
                $list[] = $prepared_array;
            }

            // language & type set
            else if ( 'none' === $filters['project'] && 'none' === $filters['country'] && $prepared_array['language'] === $filters['language'] && $prepared_array['type'] === $filters['type'] ) {
                $list[] = $prepared_array;
            }
            // country & type set
            else if ( 'none' === $filters['project'] && $prepared_array['country'] === $filters['country'] && 'none' === $filters['language'] && $prepared_array['type'] === $filters['type'] ) {
                $list[] = $prepared_array;
            }
            // project & type set
            else if ( $prepared_array['project'] === $filters['project'] && 'none' === $filters['country'] && 'none' === $filters['language'] && $prepared_array['type'] === $filters['type'] ) {
                $list[] = $prepared_array;
            }

            // country & language set
            else if ( 'none' === $filters['project'] && $prepared_array['country'] === $filters['country'] && $prepared_array['language'] === $filters['language'] && 'none' === $filters['type'] ) {
                $list[] = $prepared_array;
            }
            // project & language set
            else if ( $prepared_array['project'] === $filters['project'] && 'none' === $filters['country'] && $prepared_array['language'] === $filters['language'] && 'none' === $filters['type'] ) {
                $list[] = $prepared_array;
            }
            // type & language set
            else if ( 'none' === $filters['project'] && 'none' === $filters['country'] && $prepared_array['language'] === $filters['language'] && $prepared_array['type'] === $filters['type'] ) {
                $list[] = $prepared_array;
            }

            // language & country set
            else if ( 'none' === $filters['project'] && $prepared_array['country'] === $filters['country'] && $prepared_array['language'] === $filters['language'] && 'none' === $filters['type'] ) {
                $list[] = $prepared_array;
            }
            // project & country set
            else if ( $prepared_array['project'] === $filters['project'] && $prepared_array['country'] === $filters['country'] && 'none' === $filters['language'] && 'none' === $filters['type'] ) {
                $list[] = $prepared_array;
            }
            // type & country set
            else if ( 'none' === $filters['project'] && $prepared_array['country'] === $filters['country'] && 'none' === $filters['language'] && $prepared_array['type'] === $filters['type'] ) {
                $list[] = $prepared_array;
            }

            // project & language & type set
            else if ( $prepared_array['project'] === $filters['project'] && 'none' === $filters['country'] && $prepared_array['language'] === $filters['language'] && $prepared_array['type'] === $filters['type'] ) {
                $list[] = $prepared_array;
            }
            // country & language & type set
            else if ( 'none' === $filters['project'] && $prepared_array['country'] === $filters['country'] && $prepared_array['language'] === $filters['language'] && $prepared_array['type'] === $filters['type'] ) {
                $list[] = $prepared_array;
            }
            // country & project & language set
            else if ( $prepared_array['project'] === $filters['project'] && $prepared_array['country'] === $filters['country'] && $prepared_array['language'] === $filters['language'] && 'none' === $filters['type'] ) {
                $list[] = $prepared_array;
            }
            // country & project & type set
            else if ( $prepared_array['project'] === $filters['project'] && $prepared_array['country'] === $filters['country'] && 'none' === $filters['language'] && $prepared_array['type'] === $filters['type'] ) {
                $list[] = $prepared_array;
            }

            $records++;

        } // end foreach loop

        $c = array_chunk( $list, 250 );
        $array = [
            'list' => $c[0] ?? $list,
            'countries_count' => count( $countries ),
            'languages_count' => count( $languages ),
            'types_count' => count( $types ),
            'projects_count' => count( $projects ),
            'projects' => $projects,
            'types' => $types,
        ];


        // dt_write_log($array);
        return $array;
    }

    public static function get_activity_grid_id( $grid_id, $timezone_offset, $language_code ) {
        global $zume_languages_by_code;
        $list = [];
        switch_to_locale( 'en_US' );
        $training_items = [];

        $activity_list = self::query_activity_grid_id( $grid_id, $language_code );
        // @phpcs:enable

        foreach ( $activity_list as $record ) {
            $time_string = self::_time_ago( $record['time_end'] );

            // location string
            $location_name = $record['label'];

            $note = $record['note'];
            if ( ! $note ) {
                continue;
            }

            $prepared_array = array(
                'note' => $record['note'],
                'time' => $time_string,
                'type' => $record['type'],
                'language' => $record['language_code'],
                'country' => $record['country_code'],
            );

            $list[] = $prepared_array;

        } // end foreach loop

        if ( empty( $list ) ) {
            return [
                'list' => [],
                'count' => 0,
            ];
        }

        return [
            'list' => $list,
            'count' => count( $list ),
        ];
    }

    public static function get_activity_geojson( $language_code = 'en', $hours = null ) {
        $languages_by_code = impact_map_languages();
        $list = self::query_activity_geojson( $language_code, $hours );

        if ( empty( $list ) ) {
            $list = [];
        }

        $countries = [];
        $languages = [];
        $types = [];
        $projects = [];
        $records = 0;

        $features = [];
        foreach ( $list as $record ) {

            // count country
            if ( empty( $record['country_name'] ) ) {
                $record['country_name'] = 'No Location Info';
            }
            if ( ! isset( $countries[$record['country_name']] ) ) {
                $countries[$record['country_name']] = [
                    'code' => $record['country_code'] ?? 'no_code',
                    'name' => $record['country_name'],
                    'count' => 0,
                ];
            }
            $countries[$record['country_name']]['count']++;


            // count language
            if ( isset( $languages_by_code[$record['language_code'] ] ) ) {
                $language_name = $languages_by_code[$record['language_code']];
                if ( ! isset( $languages[$record['language_code']] ) ) {
                    $languages[$record['language_code']] = [
                        'code' => $record['language_code'],
                        'name' => $language_name,
                        'count' => 0,
                    ];
                }

                $languages[$record['language_code']]['count']++;
            }

            // count types
            if ( isset( $record['type'] ) && ! empty( $record['type'] ) )
            {
                if ( ! isset( $types[$record['type']] ) ) {
                    $types[$record['type']] = [
                        'code' => $record['type'],
                        'name' => ucwords( $record['type'] ),
                        'count' => 0,
                    ];
                }
                $types[$record['type']]['count']++;
            }

            // count projects
            if ( isset( $record['project'] ) && ! empty( $record['project'] ) )
            {
                if ( ! isset( $projects[$record['project']] ) ) {
                    $projects[$record['project']] = [
                        'code' => $record['project'],
                        'name' => ucwords( str_replace( '_', ' ', $record['project'] ) ),
                        'count' => 0,
                    ];
                }
                $projects[$record['project']]['count']++;
            }


            $location = self::_create_location_precision( $record['lng'], $record['lat'], $record['label'], $record['country_code'] );

            $features[] = array(
                'type' => 'Feature',
                'properties' => [
                    'project' => $record['project'],
                    'type' => $record['type'],
                    'language' => $record['language_code'],
                    'country' => $record['country_code'],
                ],
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array(
                        $location['lng'],
                        $location['lat'],
                        1,
                    ),
                ),
            );

            $records++;

        } // end foreach loop

        ksort( $countries );
        ksort( $languages );

        $new_data = array(
            'type' => 'FeatureCollection',
            'features' => $features,
            'countries' => $countries,
            'countries_count' => count( $countries ),
            'languages' => $languages,
            'languages_count' => count( $languages ),
            'types' => $types,
            'projects' => $projects,
            'total' => $records,
        );

        return $new_data;
    }



    public static function query_activity_grid_id( $grid_id, $language_code = 'en' ) {
        global $wpdb;
        $ids = [];
        $ids[] = $grid_id;
        $children = Disciple_Tools_Mapping_Queries::get_children_by_grid_id( $grid_id );
        if ( ! empty( $children ) ) {
            foreach ( $children as $child ){
                $ids[] = $child['grid_id'];
            }
        }
        $prepared_list = dt_array_to_sql( $ids );
        // phpcs:disable
        $sql = "
                SELECT *
                FROM (
                SELECT r.type, r.subtype, r.payload as note, r.value, r.lng, r.lat, r.grid_id, r.label, r.time_end, lga0.name as country_name, lga0.country_code, r.language_code, lgn.full_name
                FROM wp_dt_reports r
                LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=r.grid_id
                LEFT JOIN wp_dt_location_grid lga0 ON lga0.grid_id=lg.admin0_grid_id
                LEFT JOIN location_grid_names lgn ON lgn.grid_id=lg.grid_id AND lgn.language_code = '$language_code'
                WHERE r.type != 'system' AND r.grid_id IN ($prepared_list)
                ) as tb
                ORDER BY tb.time_end DESC
        ";

        $list = $wpdb->get_results( $sql, ARRAY_A );

//        dt_write_log( __METHOD__ );
//        dt_write_log($list);

        // phpcs:enable
        if ( empty( $list ) ){
            return [];
        }

        return $list;
    }

    public static function query_activity_list( $filters, $language_code = 'en', $time = null ): array {
        global $wpdb;
        $additional_where = '';
        if ( ! empty( $filters['bounds'] ) && is_array( $filters['bounds'] ) && $filters['zoom'] > 1.5 ) {
            if ( isset( $filters['bounds']['n_lat'] )
                && isset( $filters['bounds']['s_lat'] )
                && isset( $filters['bounds']['e_lng'] )
                && isset( $filters['bounds']['w_lng'] )
            ) {
                $additional_where .= '
                AND lng < '.$filters['bounds']['e_lng'].'
                AND lng > '.$filters['bounds']['w_lng'].'
                AND lat > '.$filters['bounds']['s_lat'].'
                AND lat < '.$filters['bounds']['n_lat'].'
                ';
            }
        }

        if ( 'none' !== $filters['country'] ) {
            $additional_where .= " AND tb.country_code = '" .$filters['country']. "'";
        }

        $time_string = '-100 hours';
        if ( $hours ) {
            $time_string = '-'.$hours.' hours';
        }
        $time_begin = strtotime( $time_string );
        $time_end = time();
        // @phpcs:disable
        $sql = "
                SELECT *
                FROM (
                SELECT r.post_type as project, r.type, r.subtype, r.payload as note, r.value, r.lng, r.lat, r.label, r.time_end, lga0.name as country_name, lga0.country_code, r.language_code, lgn.full_name
                FROM wp_dt_reports r
                LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=r.grid_id
                LEFT JOIN wp_dt_location_grid lga0 ON lga0.grid_id=lg.admin0_grid_id
                LEFT JOIN location_grid_names lgn ON lgn.grid_id=lg.grid_id AND lgn.language_code = 'en'
                WHERE r.time_end > $time_begin AND r.type != 'system'
                ) as tb
                WHERE tb.time_end > $time_begin AND tb.time_end < $time_end
                $additional_where
                ORDER BY tb.time_end DESC
        ";

        $results = $wpdb->get_results( $sql, ARRAY_A );
        // @phpcs:enable

        if ( is_wp_error( $results ) || empty( $results ) ) {
            return [];
        }
        return $results;
    }

    public static function query_activity_list_simple( $hours = null ): array {
        global $wpdb;
        $time_string = '-100 hours';
        if ( $hours ) {
            $time_string = '-'.$hours.' hours';
        }
        $time_begin = strtotime( $time_string );
        $time_end = time();

        // @phpcs:disable
        $sql = "
                SELECT *
                FROM (
                SELECT r.post_type as project, r.type, r.subtype, r.payload as note, r.value, r.lng, r.lat, r.label, r.time_end, lga0.name as country_name, lga0.country_code, r.language_code, lgn.full_name
                FROM wp_dt_reports r
                LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=r.grid_id
                LEFT JOIN wp_dt_location_grid lga0 ON lga0.grid_id=lg.admin0_grid_id
                LEFT JOIN location_grid_names lgn ON lgn.grid_id=lg.grid_id AND lgn.language_code = 'en'
                WHERE r.time_end > $time_begin AND r.type != 'system'
                ) as tb
                WHERE tb.time_end > $time_begin AND tb.time_end < $time_end
                ORDER BY tb.time_end DESC
        ";

        $results = $wpdb->get_results( $sql, ARRAY_A );
        // @phpcs:enable

        foreach( $results as $key => $record ) {
            if ( ! $record['note'] ) {
                unset( $results[$key] );
            }
            $results[$key]['time'] = self::_time_ago( $record['time_end'] );
        }

        if ( is_wp_error( $results ) || empty( $results ) ) {
            return [];
        }
        return $results;
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
                SELECT r.post_type as project, r.type, r.subtype, r.payload as note, r.value, r.lng, r.lat, r.label, r.time_end, lga0.name as country_name, lga0.country_code, r.language_code, lgn.full_name
                FROM wp_dt_reports r
                LEFT JOIN wp_dt_location_grid lg ON lg.grid_id=r.grid_id
                LEFT JOIN wp_dt_location_grid lga0 ON lga0.grid_id=lg.admin0_grid_id
                LEFT JOIN location_grid_names lgn ON lgn.grid_id=lg.grid_id AND lgn.language_code = 'en'
                WHERE r.time_end > %d AND r.time_end < %d AND r.type != 'system'
                ) as tb
                ORDER BY tb.time_end DESC
                ", $time_begin, $time_end ), ARRAY_A );
                dt_write_log( count( $results ) );
        return $results;
    }

    /**
     * Function limits counts to no higher than the location need. This keeps from inflating the counts up the levels.
     * @return array
     */
    public static function _limit_counts( $flat_grid, $list ) {
        $flat_grid_limited = [];
        foreach ( $flat_grid as $value ) {
            $flat_grid_limited[$value['grid_id']] = $value;

            if ( isset( $list[$value['grid_id']] ) && ! empty( $list[$value['grid_id']] ) ) {
                if ( $list[$value['grid_id']] <= $value['needed'] ) {
                    $flat_grid_limited[$value['grid_id']]['reported'] = $list[$value['grid_id']];
                } else {
                    $flat_grid_limited[$value['grid_id']]['reported'] = $value['needed'];
                }
            }
        }
        return $flat_grid_limited;
    }

    public static function _get_population_division( $country_code, $global_div, $us_div ){
        $population_division = $global_div;
        if ( $country_code === 'US' ){
            $population_division = $us_div;
        }
        return $population_division;
    }

    public static function _create_time_string( $time_end, $timezone_offset, $year = false ): string {

        $time_string = self::_time_ago( $time_end );
        return $time_string;

        $adjusted_time = $time_end + $timezone_offset;
        if ( $time_end > strtotime( '-1 hour' ) ) {
            $time_string = self::_time_elapsed_string( '@'.$time_end );
            return $time_string;
        }
        if ( $time_end > strtotime( 'today+00:00' ) + $timezone_offset ) {
            $time_string = date( 'g:i a', $adjusted_time ); // @phpcs:ignore
            return $time_string;
        }
        if ( $year ) {
            $time_string = date( 'M d, Y', $adjusted_time ); // @phpcs:ignore
        } else {
            $time_string = date( 'D g:i a', $adjusted_time ); // @phpcs:ignore
        }
        return $time_string;
    }

    public static function _time_ago( $time ) {

        // Calculate difference between current
        // time and given timestamp in seconds
        $diff     = time() - $time;

        // Time difference in seconds
        $sec     = $diff;

        // Convert time difference in minutes
        $min     = round( $diff / 60 );

        // Convert time difference in hours
        $hrs     = round( $diff / 3600 );

        // Convert time difference in days
        $days     = round( $diff / 86400 );

        // Convert time difference in weeks
        $weeks     = round( $diff / 604800 );

        // Convert time difference in months
        $mnths     = round( $diff / 2600640 );

        // Convert time difference in years
        $yrs     = round( $diff / 31207680 );

        // Check for seconds
        if ( $sec <= 60 ) {
            return "$sec seconds ago";
        }

        // Check for minutes
        else if ( $min <= 60 ) {
            if ( $min ==1 ) {
                return 'one minute ago';
            }
            else {
                return "$min minutes ago";
            }
        }

        // Check for hours
        else if ( $hrs <= 24 ) {
            if ( $hrs == 1 ) {
                return 'an hour ago';
            }
            else {
                return "$hrs hours ago";
            }
        }

        // Check for days
        else if ( $days <= 7 ) {
            if ( $days == 1 ) {
                return 'Yesterday';
            }
            else {
                return "$days days ago";
            }
        }

        // Check for weeks
        else if ( $weeks <= 4.3 ) {
            if ( $weeks == 1 ) {
                return 'a week ago';
            }
            else {
                return "$weeks weeks ago";
            }
        }

        // Check for months
        else if ( $mnths <= 12 ) {
            if ( $mnths == 1 ) {
                return 'a month ago';
            }
            else {
                return "$mnths months ago";
            }
        }

        // Check for years
        else {
            if ( $yrs == 1 ) {
                return 'one year ago';
            }
            else {
                return "$yrs years ago";
            }
        }
    }

    public static function _create_in_language_string( $data, $languages ): string {
        $language = $languages[$data['language_code']] ?? $languages['en'] ?? 'en';
        return 'English';
//        return $language['name'] ?? 'English';
    }

    public static function _create_location_precision( $lng, $lat, $label, $country_code ) : array {
        $location = [
            'lng' => (float) $lng,
            'lat' => (float) $lat,
            'label' => $label,
        ];

        return $location;

        // $restricted = self::_persecuted_countries();

        // if ( in_array( $country_code, $restricted ) ) { // if persecuted country, reduce precision to 111km
        //     $location['lng'] = round( $location['lng'], 1 );
        //     $location['lat'] = round( $location['lat'], 1 );
        // } else {
        //     // reduce lng to 1.1 km
        //     $location['lng'] = round( $location['lng'], 2 );
        //     $location['lat'] = round( $location['lat'], 2 );
        // }

        // return $location;
    }

    public static function _persecuted_countries(): array {

        // Top 50 most persecuted countries
        // @link https://www.opendoorsusa.org/christian-persecution/world-watch-list/

        return [
            'KP', // 'North Korea',
            'AF', // 'Afghanistan',
            'SO', // 'Somalia',
            'LY', // 'Libya',
            'PK', // 'Pakistan',
            'ER', // 'Eritrea',
            'SD', // 'Sudan',
            'YE', // 'Yemen',
            'IR', // 'Iran',
            'IN', // 'India',
            'SY', // 'Syria',
            'NG', // 'Nigeria',
            'SA', // 'Saudi Arabia',
            'MV', // 'Maldives',
            'IQ', // 'Iraq',
            'EG', // 'Egypt',
            'DZ', // 'Algeria',
            'UZ', // 'Uzbekistan',
            'MM', // 'Myanmar',
            'LA', // 'Laos',
            'VN', // 'Vietnam',
            'TM', // 'Turkmenistan',
            'CN', // 'China',
            'MR', // 'Mauritania',
            'CF', // 'Central African Republic',
            'MA', // 'Morocco',
            'QA', // 'Qatar',
            'BF', // 'Burkina Faso',
            'ML', // 'Mali',
            'SL', // 'Sri Lanka',
            'TJ', // 'Tajikistan',
            'NP', // 'Nepal',
            'JO', // 'Jordan',
            'TN', // 'Tunisia',
            'KZ', // 'Kazakhstan',
            'TR', // 'Turkey',
            'BN', // 'Brunei',
            'BD', // 'Bangladesh',
            'ET', // 'Ethiopia',
            'MY', // 'Malaysia',
            'CO', // 'Colombia',
            'OM', // 'Oman',
            'KW', // 'Kuwait',
            'BT', // 'Bhutan',
            'RU', // 'Russian Federation',
            'AE', // 'United Arab Emirates',
            'CM', // 'Cameroon',
            'ID', // 'Indonesia',
            'NE', // 'Niger',
        ];
    }



    public static function _time_elapsed_string( $datetime, $full = false ) {
        $now = new DateTime();
        $then = new DateTime( $datetime );
        $diff = (array) $now->diff( $then );

        $diff['w']  = floor( $diff['d'] / 7 );
        $diff['d'] -= $diff['w'] * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );

        foreach ( $string as $k => & $v )
        {
            if ( $diff[$k] )
            {
                $v = $diff[$k] . ' ' . $v .( $diff[$k] > 1 ? 's' : '' );
            }
            else {
                unset( $string[$k] );
            }
        }

        if ( ! $full ) { $string = array_slice( $string, 0, 1 );
        }
        return $string ? implode( ', ', $string ) . ' ago' : 'just now';
    }

    /**
     * Grid list build initial map list of elements and drives sidebar
     * @return array
     */
    public static function _initial_polygon_value_list( $grid_totals, $global_div, $us_div ){
        $flat_grid = self::query_saturation_list();

        $data = [];
        $highest_value = 1;
        foreach ( $flat_grid as $i => $v ){
            $data[$i] = [
                'grid_id' => $i,
                'population' => number_format_i18n( $v['population'] ),
                'needed' => 1,
                'reported' => 0,
                'percent' => 0,
            ];

            $population_division = self::_get_population_division( $v['country_code'], $global_div, $us_div );

            $needed = round( $v['population'] / $population_division );
            if ( $needed < 1 ){
                $needed = 1;
            }

            if ( isset( $grid_totals[$v['grid_id']] ) && ! empty( $grid_totals[$v['grid_id']] ) ){
                $reported = $grid_totals[$v['grid_id']];

                if ( ! empty( $reported ) && ! empty( $needed ) ){
                    $data[$v['grid_id']]['needed'] = $needed;

                    $data[$v['grid_id']]['reported'] = $reported;
                    $percent = ceil( $reported / $needed * 100 );
                    if ( 100 < $percent ) {
                        $percent = 100;
                    } else {
                        $percent = number_format_i18n( $percent, 2 );
                    }

                    $data[$v['grid_id']]['percent'] = $percent;
                }
            }
            else {
                $data[$v['grid_id']]['percent'] = 0;
                $data[$v['grid_id']]['reported'] = 0;
                $data[$v['grid_id']]['needed'] = $needed;
            }

            if ( $highest_value < $data[$v['grid_id']]['reported'] ){
                $highest_value = $data[$v['grid_id']]['reported'];
            }
        }

        return [
            'highest_value' => (int) $highest_value,
            'data' => $data,
        ];
    }

    public static function _wp_enqueue_scripts(){
//        wp_enqueue_script( 'lodash' );
        wp_enqueue_script( 'jquery-ui' );
        wp_enqueue_script( 'jquery-touch-punch' );

        wp_enqueue_script( 'heatmap-js', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'heatmap.js', [
            'jquery',
            'mapbox-cookie',
            'jquery-cookie',
        ], filemtime( plugin_dir_path( __FILE__ ) .'heatmap.js' ), true );

        wp_enqueue_style( 'heatmap-css', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'heatmap.css', [], filemtime( plugin_dir_path( __FILE__ ) .'heatmap.css' ) );

        wp_register_style( 'vite_bundle_css', plugin_dir_url( __DIR__ ) . 'site/assets/dist/assets/main.css', [], filemtime( plugin_dir_path( __DIR__ ) . 'site/assets/dist/assets/main.css' ) );
        wp_enqueue_style( 'vite_bundle_css' );

        wp_enqueue_script( 'jquery-cookie', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'js.cookie.min.js', [ 'jquery' ],
        filemtime( trailingslashit( plugin_dir_path( __FILE__ ) ) .'js.cookie.min.js' ), true );

        wp_enqueue_script( 'mapbox-cookie', trailingslashit( get_stylesheet_directory_uri() ) . 'dt-mapping/geocode-api/mapbox-cookie.js', [ 'jquery', 'jquery-cookie' ], '3.0.0' );
    }

    /**
     * Shared heatmap functions
     */
    public static function _header(){
        ?>
        <link rel="dns-prefetch" href="https://storage.googleapis.com/" >
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/1.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/2.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/3.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/4.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/5.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/6.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/7.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/8.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/9.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/10.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/11.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/12.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/13.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/14.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/15.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/16.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/17.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/18.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/19.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/20.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/21.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/22.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/23.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/24.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/25.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/26.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/27.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/28.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/29.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/30.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/31.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/32.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/33.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/34.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/35.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/36.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/37.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/38.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/39.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/40.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/41.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/42.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/43.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/44.geojson">
        <link rel="prefetch" href="https://storage.googleapis.com/location-grid-mirror-v2/tiles/world/saturation/45.geojson">
        <style>
            #initialize-screen {
                background-image: url("<?php echo esc_url( plugin_dir_url( __FILE__ ) ) ?>/images/initialize-background.jpg");
                background-size:cover;
            }
        </style>
        <?php
        wp_head();
    }
}
