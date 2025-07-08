<?php
/**
 * Register the "Countries" custom taxonomy for the City CPT.
 *
 * Adds a hierarchical taxonomy to group City posts by country.
 * Hooks into 'init' to register early in the load process.
 *
 * @package StorefrontChild
 */

/**
 * Registers the 'country' taxonomy and assigns it to the 'city' post type.
 *
 * Labels use English taxonomy names with Russian interface strings
 * for administrative actions and messages.
 *
 * @return void
 */
function sc_register_countries_taxonomy() {
    // Taxonomy labels for the admin UI
    $labels = array(
        'name'                       => __( 'Countries',               'storefront-child' ), // General name
        'singular_name'              => __( 'Country',                 'storefront-child' ), // Singular name
        'menu_name'                  => __( 'Countries',               'storefront-child' ), // Admin menu

        // Action and display strings
        'all_items'                  => __( 'Все Countries',          'storefront-child' ),
        'edit_item'                  => __( 'Редактировать Country',   'storefront-child' ),
        'view_item'                  => __( 'Просмотреть Country',     'storefront-child' ),
        'update_item'                => __( 'Обновить Country',        'storefront-child' ),
        'add_new_item'               => __( 'Добавить новый Country',  'storefront-child' ),
        'new_item_name'              => __( 'Новое имя Country',       'storefront-child' ),
        'search_items'               => __( 'Поиск Countries',         'storefront-child' ),
        'popular_items'              => __( 'Популярные Countries',    'storefront-child' ),
        'not_found'                  => __( 'Countries не найдены',     'storefront-child' ),
        'no_terms'                   => __( 'Нет Countries',           'storefront-child' ),
        'parent_item'                => __( 'Родительский Country',    'storefront-child' ),
        'parent_item_colon'          => __( 'Родительский Country:',   'storefront-child' ),
        'separate_items_with_commas' => __( 'Разделяйте Countries запятыми', 'storefront-child' ),
        'add_or_remove_items'        => __( 'Добавить или удалить Countries', 'storefront-child' ),
    );

    // Taxonomy settings for behavior and URL structure
    $args = array(
        'labels'            => $labels,
        'public'            => true,              // Visible on front-end and in admin
        'hierarchical'      => true,              // Behaves like categories
        'show_in_rest'      => true,              // Enables Gutenberg editor support
        'rewrite'           => array(
            'slug'         => 'countries',        // URL slug base
            'with_front'   => false,              // No front base prefix
        ),
    );

    // Register the taxonomy to the 'city' CPT
    register_taxonomy( 'country', array( 'city' ), $args );
}

// Hook into 'init' to register taxonomy at initialization
add_action( 'init', 'sc_register_countries_taxonomy' );
