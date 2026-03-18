<?php
/**
 * Register Custom Post Types and Taxonomies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'ziaoba_register_cpts', 0 );

function ziaoba_register_cpts() {
    register_taxonomy( 'genre', array( 'entertainment', 'education' ), array(
        'hierarchical'      => true,
        'labels'            => array(
            'name'              => _x( 'Genres', 'taxonomy general name' ),
            'singular_name'     => _x( 'Genre', 'taxonomy singular name' ),
            'search_items'      => __( 'Search Genres', 'ziaoba' ),
            'all_items'         => __( 'All Genres', 'ziaoba' ),
            'edit_item'         => __( 'Edit Genre', 'ziaoba' ),
            'update_item'       => __( 'Update Genre', 'ziaoba' ),
            'add_new_item'      => __( 'Add New Genre', 'ziaoba' ),
            'new_item_name'     => __( 'New Genre Name', 'ziaoba' ),
            'menu_name'         => __( 'Genres', 'ziaoba' ),
        ),
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'genre' ),
        'show_in_rest'      => true,
    ) );

    register_post_type( 'entertainment', array(
        'labels'             => array(
            'name'               => _x( 'Entertainment', 'post type general name', 'ziaoba' ),
            'singular_name'      => _x( 'Entertainment', 'post type singular name', 'ziaoba' ),
            'menu_name'          => _x( 'Entertainment', 'admin menu', 'ziaoba' ),
            'add_new'            => _x( 'Add New', 'entertainment', 'ziaoba' ),
            'add_new_item'       => __( 'Add New Entertainment', 'ziaoba' ),
            'edit_item'          => __( 'Edit Entertainment', 'ziaoba' ),
            'view_item'          => __( 'View Entertainment', 'ziaoba' ),
            'all_items'          => __( 'All Entertainment', 'ziaoba' ),
            'search_items'       => __( 'Search Entertainment', 'ziaoba' ),
            'not_found'          => __( 'No entertainment found.', 'ziaoba' ),
        ),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'entertainment' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => true,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-video-alt3',
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
        'taxonomies'         => array( 'genre' ),
        'show_in_rest'       => true,
    ) );

    register_post_type( 'education', array(
        'labels'             => array(
            'name'               => _x( 'Education', 'post type general name', 'ziaoba' ),
            'singular_name'      => _x( 'Education', 'post type singular name', 'ziaoba' ),
            'menu_name'          => _x( 'Education', 'admin menu', 'ziaoba' ),
            'add_new'            => _x( 'Add New', 'education', 'ziaoba' ),
            'add_new_item'       => __( 'Add New Education', 'ziaoba' ),
            'edit_item'          => __( 'Edit Education', 'ziaoba' ),
            'view_item'          => __( 'View Education', 'ziaoba' ),
            'all_items'          => __( 'All Education', 'ziaoba' ),
            'search_items'       => __( 'Search Education', 'ziaoba' ),
            'not_found'          => __( 'No education found.', 'ziaoba' ),
        ),
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'education' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 6,
        'menu_icon'          => 'dashicons-welcome-learn-more',
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'taxonomies'         => array( 'genre' ),
        'show_in_rest'       => true,
    ) );

    $meta_fields = array(
        '_ziaoba_season'            => 'string',
        '_ziaoba_episode_number'    => 'integer',
        '_ziaoba_total_seasons'     => 'integer',
        '_ziaoba_total_episodes'    => 'integer',
        '_ziaoba_content_type'      => 'string',
        '_ziaoba_tmdb_vote_average' => 'number',
        '_ziaoba_tmdb_vote_count'   => 'integer',
        '_ziaoba_original_title'    => 'string',
        '_ziaoba_tagline'           => 'string',
        '_ziaoba_status'            => 'string',
        '_ziaoba_original_language' => 'string',
        '_ziaoba_networks'          => 'string',
        '_ziaoba_origin_countries'  => 'string',
        '_ziaoba_trailer_url'       => 'string',
        '_ziaoba_poster_url'        => 'string',
        '_ziaoba_tmdb_last_sync'    => 'string',
        '_ziaoba_related_content'   => 'string',
    );

    foreach ( $meta_fields as $meta_key => $type ) {
        register_post_meta( 'entertainment', $meta_key, array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => $type,
        ) );

        register_post_meta( 'education', $meta_key, array(
            'show_in_rest' => true,
            'single'       => true,
            'type'         => $type,
        ) );
    }
}
