<?php
namespace Ziaoba\VAM;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin Columns Class
 */
class AdminColumns {
    public static function init() {
        $instance = new self();
        // Entertainment
        add_filter( 'manage_entertainment_posts_columns', array( $instance, 'set_ent_columns' ) );
        add_action( 'manage_entertainment_posts_custom_column', array( $instance, 'ent_column_content' ), 10, 2 );

        // Education
        add_filter( 'manage_education_posts_columns', array( $instance, 'set_edu_columns' ) );
        add_action( 'manage_education_posts_custom_column', array( $instance, 'edu_column_content' ), 10, 2 );

        // Series
        add_filter( 'manage_series_posts_columns', array( $instance, 'set_series_columns' ) );
        add_action( 'manage_series_posts_custom_column', array( $instance, 'series_column_content' ), 10, 2 );

        // Season
        add_filter( 'manage_season_posts_columns', array( $instance, 'set_season_columns' ) );
        add_action( 'manage_season_posts_custom_column', array( $instance, 'season_column_content' ), 10, 2 );

        // Episode
        add_filter( 'manage_episode_posts_columns', array( $instance, 'set_episode_columns' ) );
        add_action( 'manage_episode_posts_custom_column', array( $instance, 'episode_column_content' ), 10, 2 );
    }

    public function set_ent_columns( $columns ) {
        $columns['duration'] = __( 'Duration', 'ziaoba-asset-management' );
        $columns['age_rating'] = __( 'Rating', 'ziaoba-asset-management' );
        $columns['views'] = __( 'Views', 'ziaoba-asset-management' );
        return $columns;
    }

    public function ent_column_content( $column, $post_id ) {
        switch ( $column ) {
            case 'duration':
                echo esc_html( get_post_meta( $post_id, '_ziaoba_duration', true ) );
                break;
            case 'age_rating':
                echo esc_html( get_post_meta( $post_id, '_ziaoba_age_rating', true ) );
                break;
            case 'views':
                echo esc_html( get_post_meta( $post_id, '_ziaoba_views', true ) ?: '0' );
                break;
        }
    }

    public function set_edu_columns( $columns ) {
        $columns['topic'] = __( 'Topic', 'ziaoba-asset-management' );
        $columns['views'] = __( 'Views', 'ziaoba-asset-management' );
        return $columns;
    }

    public function edu_column_content( $column, $post_id ) {
        switch ( $column ) {
            case 'topic':
                echo esc_html( get_post_meta( $post_id, '_ziaoba_lesson_topic', true ) );
                break;
            case 'views':
                echo esc_html( get_post_meta( $post_id, '_ziaoba_views', true ) ?: '0' );
                break;
        }
    }

    public function set_series_columns( $columns ) {
        $columns['tmdb_id'] = __( 'TMDB ID', 'ziaoba-asset-management' );
        return $columns;
    }

    public function series_column_content( $column, $post_id ) {
        if ( $column === 'tmdb_id' ) {
            echo esc_html( get_post_meta( $post_id, '_ziaoba_tmdb_id', true ) );
        }
    }

    public function set_season_columns( $columns ) {
        $columns['series'] = __( 'Series', 'ziaoba-asset-management' );
        $columns['season_num'] = __( 'Season #', 'ziaoba-asset-management' );
        return $columns;
    }

    public function season_column_content( $column, $post_id ) {
        switch ( $column ) {
            case 'series':
                $parent_id = wp_get_post_parent_id( $post_id );
                if ( $parent_id ) {
                    echo '<a href="' . get_edit_post_link( $parent_id ) . '">' . get_the_title( $parent_id ) . '</a>';
                }
                break;
            case 'season_num':
                echo esc_html( get_post_meta( $post_id, '_ziaoba_season_number', true ) );
                break;
        }
    }

    public function set_episode_columns( $columns ) {
        $columns['season'] = __( 'Season', 'ziaoba-asset-management' );
        $columns['ep_num'] = __( 'Ep #', 'ziaoba-asset-management' );
        $columns['views'] = __( 'Views', 'ziaoba-asset-management' );
        return $columns;
    }

    public function episode_column_content( $column, $post_id ) {
        switch ( $column ) {
            case 'season':
                $parent_id = wp_get_post_parent_id( $post_id );
                if ( $parent_id ) {
                    echo '<a href="' . get_edit_post_link( $parent_id ) . '">' . get_the_title( $parent_id ) . '</a>';
                }
                break;
            case 'ep_num':
                echo esc_html( get_post_meta( $post_id, '_ziaoba_episode_number', true ) );
                break;
            case 'views':
                echo esc_html( get_post_meta( $post_id, '_ziaoba_views', true ) ?: '0' );
                break;
        }
    }
}
