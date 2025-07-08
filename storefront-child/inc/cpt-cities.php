<?php
/**
 * Register the "Cities" Custom Post Type.
 *
 * Sets up CPT with English name "Cities" and Russian interface strings for actions.
 * Hooks into 'init'.
 *
 * @package StorefrontChild
 */

/**
 * Registers the 'city' post type.
 *
 * @return void
 */
function sc_register_cities_cpt() {
    // Labels
    $labels = array(
        'name'                  => __( 'Cities',               'storefront-child' ),
        'singular_name'         => __( 'City',                 'storefront-child' ),
        'menu_name'             => __( 'Cities',               'storefront-child' ),
        'name_admin_bar'        => __( 'City',                 'storefront-child' ),
        'add_new'               => __( 'Добавить новый',       'storefront-child' ),
        'add_new_item'          => __( 'Добавить новый City',  'storefront-child' ),
        'new_item'              => __( 'Новый City',           'storefront-child' ),
        'edit_item'             => __( 'Редактировать City',   'storefront-child' ),
        'view_item'             => __( 'Просмотреть City',     'storefront-child' ),
        'all_items'             => __( 'Все Cities',           'storefront-child' ),
        'search_items'          => __( 'Поиск Cities',         'storefront-child' ),
        'parent_item_colon'     => __( 'Родительский Cities:', 'storefront-child' ),
        'not_found'             => __( 'Cities не найдены.',    'storefront-child' ),
        'not_found_in_trash'    => __( 'В корзине Cities нет.', 'storefront-child' ),
    );

    // General settings for CPT behavior
    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'show_in_menu'       => true,
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-location-alt',
        'supports'           => array( 'title', 'editor', 'thumbnail' ),
        'show_in_rest'       => true,
        'rewrite'            => array(
            'slug'       => 'cities',
            'with_front' => false,
        ),
        'capability_type'    => 'post',
        'hierarchical'       => false,
    );

    register_post_type( 'city', $args );
}
add_action( 'init', 'sc_register_cities_cpt' );
