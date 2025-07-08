<?php
/**
 * Widget: City Weather
 *
 * Allows selecting a City CPT and displays the city’s name and current temperature
 * using the Open-Meteo API.
 *
 * @package StorefrontChild
 */

if ( ! class_exists( 'SC_City_Weather_Widget' ) ) {

    /**
     * City Weather Widget Class
     *
     * Outputs a widget that shows the current temperature for a selected city.
     */
    class SC_City_Weather_Widget extends WP_Widget {

        /**
         * Register widget with WordPress.
         *
         * @return void
         */
        public function __construct() {
            parent::__construct(
                'sc_city_weather',
                __( 'Погода города', 'storefront-child' ),
                array(
                    'description' => __( 'Показывает текущую температуру для выбранного города.', 'storefront-child' ),
                )
            );
        }

        /**
         * Front-end display of widget.
         *
         * @param array $args     Widget display arguments.
         * @param array $instance Saved widget settings.
         * @return void
         */
        public function widget( $args, $instance ) {
            echo $args['before_widget'];

            // Display title if provided.
            if ( ! empty( $instance['title'] ) ) {
                echo $args['before_title']
                    . apply_filters( 'widget_title', $instance['title'] )
                    . $args['after_title'];
            }

            // Retrieve selected city ID.
            $city_id = ! empty( $instance['city_id'] ) ? absint( $instance['city_id'] ) : 0;
            if ( $city_id ) {
                $city_name = get_the_title( $city_id );
                $lat       = get_post_meta( $city_id, 'latitude', true );
                $lng       = get_post_meta( $city_id, 'longitude', true );

                // Only fetch weather if coordinates exist.
                if ( $lat && $lng ) {
                    $transient_key = 'sc_weather_' . $city_id;
                    $weather       = get_transient( $transient_key );

                    // Fetch fresh data if not cached.
                    if ( false === $weather ) {
                        $url = add_query_arg(
                            array(
                                'latitude'        => rawurlencode( $lat ),
                                'longitude'       => rawurlencode( $lng ),
                                'current_weather' => 'true',
                            ),
                            'https://api.open-meteo.com/v1/forecast'
                        );

                        $response = wp_remote_get( $url, array( 'timeout' => 5 ) );

                        if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
                            $data = json_decode( wp_remote_retrieve_body( $response ), true );
                            if ( isset( $data['current_weather']['temperature'] ) ) {
                                $weather = $data['current_weather']['temperature'];
                                set_transient( $transient_key, $weather, HOUR_IN_SECONDS );
                            }
                        }
                    }

                    // Display temperature or error.
                    if ( null !== $weather && false !== $weather ) {
                        printf(
                            '<p>%1$s: <strong>%2$.1f °C</strong></p>',
                            esc_html( $city_name ),
                            floatval( $weather )
                        );
                    } else {
                        echo '<p>' . sprintf(
                            __( 'Не удалось получить погоду для %s.', 'storefront-child' ),
                            esc_html( $city_name )
                        ) . '</p>';
                    }
                } else {
                    // Coordinates missing.
                    echo '<p>' . __( 'Координаты не заданы.', 'storefront-child' ) . '</p>';
                }
            }

            echo $args['after_widget'];
        }

        /**
         * Back-end widget form.
         *
         * @param array $instance Previously saved values from database.
         * @return void
         */
        public function form( $instance ) {
            $title   = isset( $instance['title'] ) ? $instance['title'] : __( 'Погода города', 'storefront-child' );
            $city_id = isset( $instance['city_id'] ) ? absint( $instance['city_id'] ) : 0;

            // Fetch all city CPT entries for the dropdown.
            $cities = get_posts(
                array(
                    'post_type'      => 'city',
                    'posts_per_page' => -1,
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                )
            );
            ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                    <?php _e( 'Заголовок:', 'storefront-child' ); ?>
                </label>
                <input
                    class="widefat"
                    id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                    name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                    type="text"
                    value="<?php echo esc_attr( $title ); ?>"
                />
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'city_id' ) ); ?>">
                    <?php _e( 'Город:', 'storefront-child' ); ?>
                </label>
                <select
                    class="widefat"
                    id="<?php echo esc_attr( $this->get_field_id( 'city_id' ) ); ?>"
                    name="<?php echo esc_attr( $this->get_field_name( 'city_id' ) ); ?>"
                >
                    <option value="0"><?php _e( '&mdash; Выберите город &mdash;', 'storefront-child' ); ?></option>
                    <?php foreach ( $cities as $city ) : ?>
                        <option
                            value="<?php echo esc_attr( $city->ID ); ?>"
                            <?php selected( $city_id, $city->ID ); ?>
                        ><?php echo esc_html( $city->post_title ); ?></option>
                    <?php endforeach; ?>
                    <?php wp_reset_postdata(); ?>
                </select>
            </p>
            <?php
        }

        /**
         * Sanitize and save widget form values.
         *
         * @param array $new_instance Values just sent to be saved.
         * @param array $old_instance Previously saved values.
         * @return array Updated safe values to be saved.
         */
        public function update( $new_instance, $old_instance ) {
            $instance = array();
            $instance['title']   = sanitize_text_field( $new_instance['title'] );
            $instance['city_id'] = absint( $new_instance['city_id'] );
            return $instance;
        }
    }

} // end if class_exists

/**
 * Register SC_City_Weather_Widget widget.
 *
 * @return void
 */
add_action( 'widgets_init', function() {
    register_widget( 'SC_City_Weather_Widget' );
} );
