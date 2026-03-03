<?php
/**
 * Register Custom Post Types and Taxonomies
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'ziaoba_register_cpts', 0 );

if ( ! function_exists( 'ziaoba_register_cpts' ) ) {
    function ziaoba_register_cpts() {
        // Genre Taxonomy
        $genre_labels = array(
            'name'              => _x( 'Genres', 'taxonomy general name' ),
            'singular_name'     => _x( 'Genre', 'taxonomy singular name' ),
            'search_items'      => __( 'Search Genres' ),
            'all_items'         => __( 'All Genres' ),
            'parent_item'       => __( 'Parent Genre' ),
            'parent_item_colon' => __( 'Parent Genre:' ),
            'edit_item'         => __( 'Edit Genre' ),
            'update_item'       => __( 'Update Genre' ),
            'add_new_item'      => __( 'Add New Genre' ),
            'new_item_name'     => __( 'New Genre Name' ),
            'menu_name'         => __( 'Genres' ),
        );

        $genre_args = array(
            'hierarchical'      => true,
            'labels'            => $genre_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'genre' ),
            'show_in_rest'      => true,
        );

        register_taxonomy( 'genre', array( 'entertainment', 'education' ), $genre_args );

        // Entertainment CPT
        $ent_labels = array(
            'name'               => _x( 'Entertainment', 'post type general name' ),
            'singular_name'      => _x( 'Entertainment', 'post type singular name' ),
            'menu_name'          => _x( 'Entertainment', 'admin menu' ),
            'name_admin_bar'     => _x( 'Entertainment', 'add new on admin bar' ),
            'add_new'            => _x( 'Add New', 'entertainment' ),
            'add_new_item'       => __( 'Add New Entertainment' ),
            'new_item'           => __( 'New Entertainment' ),
            'edit_item'          => __( 'Edit Entertainment' ),
            'view_item'          => __( 'View Entertainment' ),
            'all_items'          => __( 'All Entertainment' ),
            'search_items'       => __( 'Search Entertainment' ),
            'parent_item_colon'  => __( 'Parent Entertainment:' ),
            'not_found'          => __( 'No entertainment found.' ),
            'not_found_in_trash' => __( 'No entertainment found in Trash.' )
        );

        $ent_args = array(
            'labels'             => $ent_labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'entertainment' ),
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
            'has_archive'        => true,
            'hierarchical'       => true, // For Series/Episodes
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-video-alt3',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
            'taxonomies'         => array( 'genre' ),
            'show_in_rest'       => true,
        );

        register_post_type( 'entertainment', $ent_args );

        // Education CPT
        $edu_labels = array(
            'name'               => _x( 'Education', 'post type general name' ),
            'singular_name'      => _x( 'Education', 'post type singular name' ),
            'menu_name'          => _x( 'Education', 'admin menu' ),
            'name_admin_bar'     => _x( 'Education', 'add new on admin bar' ),
            'add_new'            => _x( 'Add New', 'education' ),
            'add_new_item'       => __( 'Add New Education' ),
            'new_item'           => __( 'New Education' ),
            'edit_item'          => __( 'Edit Education' ),
            'view_item'          => __( 'View Education' ),
            'all_items'          => __( 'All Education' ),
            'search_items'       => __( 'Search Education' ),
            'parent_item_colon'  => __( 'Parent Education:' ),
            'not_found'          => __( 'No education found.' ),
            'not_found_in_trash' => __( 'No education found in Trash.' )
        );

        $edu_args = array(
            'labels'             => $edu_labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'education' ),
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 6,
            'menu_icon'          => 'dashicons-welcome-learn-more',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'taxonomies'         => array( 'genre' ),
            'show_in_rest'       => true,
        );

        register_post_type( 'education', $edu_args );
    }
}
