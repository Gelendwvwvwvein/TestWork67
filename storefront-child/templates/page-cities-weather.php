<?php
/**
 * Custom Page Template: Cities Weather
 *
 * Displays a searchable table of countries, cities, and current temperatures.
 * Implements AJAX search and uses \$wpdb for data retrieval.
 * Includes action hooks before and after the table for extensibility.
 *
 * @package StorefrontChild
 */

/**
 * Template Name: Погода в городах
 */

get_header();

// Action hook: before rendering the cities table
// Enables injection of additional content before the table
do_action( 'sc_before_cities_table' );
?>

<div class="sc-cities-weather">
    <!-- Search input for live filtering via AJAX -->
    <input
        type="text"
        id="sc-city-search"
        placeholder="Поиск города…"
        style="margin-bottom:20px;padding:8px;width:100%;max-width:400px;"
    />

    <!-- Cities weather table -->
    <table id="sc-cities-table" style="width:100%;border-collapse:collapse;">
        <thead>
            <tr>
                <th style="border:1px solid #ddd;padding:8px;">Country</th>
                <th style="border:1px solid #ddd;padding:8px;">City</th>
                <th style="border:1px solid #ddd;padding:8px;">Temperature (°C)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            global $wpdb;
            $prefix = $wpdb->prefix;

            // Retrieve cities with associated country and coordinates
            $rows = $wpdb->get_results(
                "
                SELECT
                  p.ID             AS city_id,
                  p.post_title     AS city,
                  pm_lat.meta_value AS latitude,
                  pm_lng.meta_value AS longitude,
                  t.name           AS country
                FROM {$prefix}posts AS p
                LEFT JOIN {$prefix}postmeta AS pm_lat
                  ON pm_lat.post_id = p.ID AND pm_lat.meta_key = 'latitude'
                LEFT JOIN {$prefix}postmeta AS pm_lng
                  ON pm_lng.post_id = p.ID AND pm_lng.meta_key = 'longitude'
                LEFT JOIN {$prefix}term_relationships AS tr
                  ON tr.object_id = p.ID
                LEFT JOIN {$prefix}term_taxonomy AS tt
                  ON tt.term_taxonomy_id = tr.term_taxonomy_id
                     AND tt.taxonomy = 'country'
                LEFT JOIN {$prefix}terms AS t
                  ON t.term_id = tt.term_id
                WHERE p.post_type   = 'city'
                  AND p.post_status = 'publish'
                ORDER BY t.name, p.post_title
                "
            );

            // Loop through each city and render its row
            foreach ( $rows as $row ) :
                // Attempt to get cached temperature
                $temp_key = 'sc_weather_' . $row->city_id;
                $weather  = get_transient( $temp_key );

                // Fetch from API if no cached value and coordinates present
                if ( false === $weather && $row->latitude && $row->longitude ) {
                    $api_url = add_query_arg(
                        array(
                            'latitude'        => rawurlencode( $row->latitude ),
                            'longitude'       => rawurlencode( $row->longitude ),
                            'current_weather' => 'true',
                        ),
                        'https://api.open-meteo.com/v1/forecast'
                    );
                    $response = wp_remote_get( $api_url, array( 'timeout' => 5 ) );

                    if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
                        $data = json_decode( wp_remote_retrieve_body( $response ), true );
                        if ( isset( $data['current_weather']['temperature'] ) ) {
                            $weather = floatval( $data['current_weather']['temperature'] );
                            set_transient( $temp_key, $weather, HOUR_IN_SECONDS );
                        }
                    }
                }
                ?>
                <tr>
                    <td style="border:1px solid #ddd;padding:8px;">
                        <?php echo esc_html( $row->country ); ?>
                    </td>
                    <td style="border:1px solid #ddd;padding:8px;">
                        <?php echo esc_html( $row->city ); ?>
                    </td>
                    <td style="border:1px solid #ddd;padding:8px;">
                        <?php echo ( false !== $weather ) ? esc_html( number_format_i18n( $weather, 1 ) ) : '—'; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
// Action hook: after rendering the cities table
// Enables injection of additional content after the table
do_action( 'sc_after_cities_table' );

get_footer();
