<?php
/**
 * Meta Boxes for City Coordinates
 *
 * Adds latitude and longitude input fields to the 'City' CPT edit screen
 * and handles saving the meta values securely.
 *
 * @package StorefrontChild
 */

/**
 * Register the city coordinates meta box.
 *
 * Hooks into 'add_meta_boxes' to add a custom meta box
 * for entering latitude and longitude on City posts.
 *
 * @return void
 */
function sc_add_city_coordinates_metabox() {
    add_meta_box(
        'sc_city_coords',                    // Unique ID for the meta box
        __( 'Координаты города', 'storefront-child' ), // Box title (Russian)
        'sc_render_city_coords_box',         // Callback to render fields
        'city',                              // Screen (CPT) to display
        'normal',                            // Context (normal, side, advanced)
        'high'                               // Priority
    );
}
add_action( 'add_meta_boxes', 'sc_add_city_coordinates_metabox' );

/**
 * Render the latitude and longitude fields inside the meta box.
 *
 * Outputs two text inputs prefilled with existing values, if any.
 * Adds a nonce field for security verification on save.
 *
 * @param WP_Post $post The current post object.
 * @return void
 */
function sc_render_city_coords_box( \WP_Post $post ) {
    // Add nonce for security and verification
    wp_nonce_field( basename( __FILE__ ), 'sc_city_coords_nonce' );

    // Retrieve existing meta values, if available
    $lat = get_post_meta( $post->ID, 'latitude',  true );
    $lng = get_post_meta( $post->ID, 'longitude', true );
    ?>
    <p>
        <label for="latitude"><?php _e( 'Широта', 'storefront-child' ); ?></label><br>
        <input
            type="text"
            name="latitude"
            id="latitude"
            value="<?php echo esc_attr( $lat ); ?>"
            style="width:100%;"
        />
    </p>
    <p>
        <label for="longitude"><?php _e( 'Долгота', 'storefront-child' ); ?></label><br>
        <input
            type="text"
            name="longitude"
            id="longitude"
            value="<?php echo esc_attr( $lng ); ?>"
            style="width:100%;"
        />
    </p>
    <?php
}

/**
 * Save the latitude and longitude meta values when the post is saved.
 *
 * Performs nonce verification, autosave check, post type check,
 * and user capability check before sanitizing and saving data.
 *
 * @param int $post_id The ID of the post being saved.
 * @return void
 */
function sc_save_city_coords_meta( $post_id ) {
    // Verify nonce, autosave, post type, and user permissions
    if (
        ! isset( $_POST['sc_city_coords_nonce'] ) ||
        ! wp_verify_nonce( $_POST['sc_city_coords_nonce'], basename( __FILE__ ) ) ||
        ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
        'city' !== get_post_type( $post_id ) ||
        ! current_user_can( 'edit_post', $post_id )
    ) {
        return;
    }

    // Sanitize and update latitude
    if ( isset( $_POST['latitude'] ) ) {
        $lat = sanitize_text_field( wp_unslash( $_POST['latitude'] ) );
        update_post_meta( $post_id, 'latitude', $lat );
    }

    // Sanitize and update longitude
    if ( isset( $_POST['longitude'] ) ) {
        $lng = sanitize_text_field( wp_unslash( $_POST['longitude'] ) );
        update_post_meta( $post_id, 'longitude', $lng );
    }
}
add_action( 'save_post', 'sc_save_city_coords_meta' );
