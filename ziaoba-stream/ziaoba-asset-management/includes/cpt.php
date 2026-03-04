<?php
/**
 * Register Custom Post Types and Taxonomies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'ziaoba_register_cpts', 0 );

function ziaoba_register_cpts() {
    // Genre Taxonomy
    register_taxonomy( 'genre', array( 'entertainment', 'education' ), array(
        'hierarchical'      => true,
        'labels'            => array(
            'name'              => _x( 'Genres', 'taxonomy general name' ),
            'singular_name'     => _x( 'Genre', 'taxonomy singular name' ),
            'search_items'      => __( 'Search Genres' ),
            'all_items'         => __( 'All Genres' ),
            'edit_item'         => __( 'Edit Genre' ),
            'update_item'       => __( 'Update Genre' ),
            'add_new_item'      => __( 'Add New Genre' ),
            'new_item_name'     => __( 'New Genre Name' ),
            'menu_name'         => __( 'Genres' ),
        ),
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'genre' ),
        'show_in_rest'      => true,
    ) );

    // Entertainment CPT
    register_post_type( 'entertainment', array(
        'labels'             => array(
            'name'               => _x( 'Entertainment', 'post type general name' ),
            'singular_name'      => _x( 'Entertainment', 'post type singular name' ),
            'menu_name'          => _x( 'Entertainment', 'admin menu' ),
            'add_new'            => _x( 'Add New', 'entertainment' ),
            'add_new_item'       => __( 'Add New Entertainment' ),
            'edit_item'          => __( 'Edit Entertainment' ),
            'view_item'          => __( 'View Entertainment' ),
            'all_items'          => __( 'All Entertainment' ),
            'search_items'       => __( 'Search Entertainment' ),
            'not_found'          => __( 'No entertainment found.' ),
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

    // Education CPT
    register_post_type( 'education', array(
        'labels'             => array(
            'name'               => _x( 'Education', 'post type general name' ),
            'singular_name'      => _x( 'Education', 'post type singular name' ),
            'menu_name'          => _x( 'Education', 'admin menu' ),
            'add_new'            => _x( 'Add New', 'education' ),
            'add_new_item'       => __( 'Add New Education' ),
            'edit_item'          => __( 'Edit Education' ),
            'view_item'          => __( 'View Education' ),
            'all_items'          => __( 'All Education' ),
            'search_items'       => __( 'Search Education' ),
            'not_found'          => __( 'No education found.' ),
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

    // Register Meta for Entertainment
    register_post_meta( 'entertainment', '_ziaoba_season', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'string',
    ) );

    register_post_meta( 'entertainment', '_ziaoba_episode_number', array(
        'show_in_rest' => true,
        'single'       => true,
        'type'         => 'integer',
    ) );
}
