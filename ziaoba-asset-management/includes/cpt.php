<?php
/**
 * Register Custom Post Types and Taxonomies
 */

namespace Ziaoba\VAM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPT {

    /**
     * Initialize CPT registration
     */
    public static function init() {
        $instance = new self();
        add_action( 'init', array( $instance, 'register' ), 0 );
        
        // Admin columns
        add_filter( 'manage_season_posts_columns', array( $instance, 'add_season_columns' ) );
        add_action( 'manage_season_posts_custom_column', array( $instance, 'render_season_columns' ), 10, 2 );
        
        add_filter( 'manage_episode_posts_columns', array( $instance, 'add_episode_columns' ) );
        add_action( 'manage_episode_posts_custom_column', array( $instance, 'render_episode_columns' ), 10, 2 );
    }

    /**
     * Add Season Columns
     */
    public function add_season_columns( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $value ) {
            if ( $key === 'date' ) {
                $new_columns['series'] = __( 'Series', 'ziaoba-asset-management' );
                $new_columns['season_num'] = __( 'Season #', 'ziaoba-asset-management' );
            }
            $new_columns[$key] = $value;
        }
        return $new_columns;
    }

    /**
     * Render Season Columns
     */
    public function render_season_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'series':
                $series_id = get_post_meta( $post_id, '_ziaoba_series_id', true );
                if ( $series_id ) {
                    echo '<a href="' . get_edit_post_link( $series_id ) . '">' . get_the_title( $series_id ) . '</a>';
                } else {
                    echo '<span class="na">&mdash;</span>';
                }
                break;
            case 'season_num':
                echo get_post_meta( $post_id, '_ziaoba_season_number', true ) ?: '0';
                break;
        }
    }

    /**
     * Add Episode Columns
     */
    public function add_episode_columns( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $value ) {
            if ( $key === 'date' ) {
                $new_columns['series'] = __( 'Series', 'ziaoba-asset-management' );
                $new_columns['season'] = __( 'Season', 'ziaoba-asset-management' );
                $new_columns['episode_num'] = __( 'Episode #', 'ziaoba-asset-management' );
            }
            $new_columns[$key] = $value;
        }
        return $new_columns;
    }

    /**
     * Render Episode Columns
     */
    public function render_episode_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'series':
                $season_id = get_post_meta( $post_id, '_ziaoba_season_id', true );
                if ( $season_id ) {
                    $series_id = get_post_meta( $season_id, '_ziaoba_series_id', true );
                    if ( $series_id ) {
                        echo '<a href="' . get_edit_post_link( $series_id ) . '">' . get_the_title( $series_id ) . '</a>';
                    } else {
                        echo '<span class="na">&mdash;</span>';
                    }
                } else {
                    echo '<span class="na">&mdash;</span>';
                }
                break;
            case 'season':
                $season_id = get_post_meta( $post_id, '_ziaoba_season_id', true );
                if ( $season_id ) {
                    echo '<a href="' . get_edit_post_link( $season_id ) . '">' . get_the_title( $season_id ) . '</a>';
                } else {
                    echo '<span class="na">&mdash;</span>';
                }
                break;
            case 'episode_num':
                echo get_post_meta( $post_id, '_ziaoba_episode_number', true ) ?: '0';
                break;
        }
    }

    /**
     * Register CPTs and Taxonomies
     */
    public function register() {
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
            'show_in_rest'       => false,
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
            'show_in_rest'       => false,
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

        // Series CPT
        register_post_type( 'series', array(
            'labels'             => array(
                'name'               => _x( 'Series', 'post type general name' ),
                'singular_name'      => _x( 'Series', 'post type singular name' ),
                'menu_name'          => _x( 'Series', 'admin menu' ),
                'add_new'            => _x( 'Add New', 'series' ),
                'add_new_item'       => __( 'Add New Series' ),
                'edit_item'          => __( 'Edit Series' ),
                'view_item'          => __( 'View Series' ),
                'all_items'          => __( 'All Series' ),
                'search_items'       => __( 'Search Series' ),
                'not_found'          => __( 'No series found.' ),
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'series' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => true,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-video-alt2',
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
            'taxonomies'         => array( 'genre' ),
            'show_in_rest'       => false,
        ) );

        // Season CPT
        register_post_type( 'season', array(
            'labels'             => array(
                'name'               => _x( 'Seasons', 'post type general name' ),
                'singular_name'      => _x( 'Season', 'post type singular name' ),
                'menu_name'          => _x( 'Seasons', 'admin menu' ),
                'add_new'            => _x( 'Add New', 'season' ),
                'add_new_item'       => __( 'Add New Season' ),
                'edit_item'          => __( 'Edit Season' ),
                'view_item'          => __( 'View Season' ),
                'all_items'          => __( 'All Seasons' ),
                'search_items'       => __( 'Search Seasons' ),
                'not_found'          => __( 'No seasons found.' ),
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=series',
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'season' ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => true,
            'supports'           => array( 'title', 'thumbnail', 'page-attributes' ),
            'show_in_rest'       => false,
        ) );

        // Episode CPT
        register_post_type( 'episode', array(
            'labels'             => array(
                'name'               => _x( 'Episodes', 'post type general name' ),
                'singular_name'      => _x( 'Episode', 'post type singular name' ),
                'menu_name'          => _x( 'Episodes', 'admin menu' ),
                'add_new'            => _x( 'Add New', 'episode' ),
                'add_new_item'       => __( 'Add New Episode' ),
                'edit_item'          => __( 'Edit Episode' ),
                'view_item'          => __( 'View Episode' ),
                'all_items'          => __( 'All Episodes' ),
                'search_items'       => __( 'Search Episodes' ),
                'not_found'          => __( 'No episodes found.' ),
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=series',
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'episode' ),
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => true,
            'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
            'show_in_rest'       => false,
        ) );
    }
}
