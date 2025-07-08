<?php
/**
 * Storefront Child Theme Functions and Hooks
 *
 * Includes registration for the "Cities" CPT, meta boxes for latitude/longitude,
 * the "Countries" taxonomy, the city weather widget, and AJAX search functionality.
 *
 * @package StorefrontChild
 */

// Load component files
require_once get_stylesheet_directory() . '/inc/cpt-cities.php';           // Register "Cities" CPT
require_once get_stylesheet_directory() . '/inc/meta-boxes.php';            // Add latitude and longitude meta boxes
require_once get_stylesheet_directory() . '/inc/taxonomies.php';            // Register "Countries" taxonomy
require_once get_stylesheet_directory() . '/inc/widgets/widget-city-weather.php'; // City weather widget

/**
 * Enqueue parent and child theme stylesheets.
 *
 * Hooks into 'wp_enqueue_scripts'.
 *
 * @return void
 */
function sc_enqueue_parent_child_styles() {
    // Parent theme stylesheet
    wp_enqueue_style(
        'storefront-style',
        get_template_directory_uri() . '/style.css'
    );

    // Child theme stylesheet
    wp_enqueue_style(
        'storefront-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('storefront-style'),
        wp_get_theme()->get('Version')
    );
}
add_action('wp_enqueue_scripts', 'sc_enqueue_parent_child_styles');

/**
 * Enqueue the AJAX search script for the Cities weather page template.
 *
 * Only loads on the custom page template 'page-cities-weather.php'.
 * Localizes script with admin-ajax URL and security nonce.
 *
 * @return void
 */
function sc_enqueue_cities_search_script() {
    if ( is_page_template('templates/page-cities-weather.php') ) {
        wp_enqueue_script(
            'sc-cities-search',
            get_stylesheet_directory_uri() . '/assets/js/cities-search.js',
            array('jquery'),
            null,
            true
        );

        // Provide AJAX URL and nonce to the script
        wp_localize_script(
            'sc-cities-search',
            'scCitiesAjax',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('sc_cities_search'),
            )
        );
    }
}
add_action('wp_enqueue_scripts', 'sc_enqueue_cities_search_script');

/**
 * Handle AJAX requests for live city search by name prefix.
 *
 * Verifies the nonce, queries the database directly via $wpdb,
 * and returns a JSON response with country, city, and temperature.
 * Registered for both logged-in and guest users.
 *
 * @return void
 */
function sc_ajax_search_cities() {
    // Security check: verify nonce
    check_ajax_referer('sc_cities_search', 'nonce');

    global $wpdb;
    $prefix = $wpdb->prefix;

    // Retrieve and sanitize the search term
    $term = isset($_POST['term'])
        ? sanitize_text_field(wp_unslash($_POST['term']))
        : '';
    $like = $wpdb->esc_like($term) . '%';

    // Prepare and execute the SQL query
    $query = $wpdb->prepare(
        "
        SELECT
            p.ID AS city_id,
            p.post_title AS city,
            pm_lat.meta_value AS latitude,
            pm_lng.meta_value AS longitude,
            t.name AS country
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
        WHERE p.post_type = 'city'
          AND p.post_status = 'publish'
          AND p.post_title LIKE %s
        ORDER BY t.name, p.post_title
        ",
        $like
    );

    $rows = $wpdb->get_results($query);
    $data = array();

    // Build response data, using cached temperature if available
    foreach ($rows as $row) {
        $temp_key = 'sc_weather_' . $row->city_id;
        $weather  = get_transient($temp_key);
        if (false === $weather) {
            $weather = '—';
        }

        $data[] = array(
            'country'     => $row->country,
            'city'        => $row->city,
            'temperature' => is_numeric($weather)
                ? number_format_i18n($weather, 1)
                : '—',
        );
    }

    // Send the JSON response
    wp_send_json_success($data);
}
add_action('wp_ajax_sc_search_cities', 'sc_ajax_search_cities');
add_action('wp_ajax_nopriv_sc_search_cities', 'sc_ajax_search_cities');
