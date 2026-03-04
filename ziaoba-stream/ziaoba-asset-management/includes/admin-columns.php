<?php
/**
 * Custom Admin Columns
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Entertainment Columns
add_filter( 'manage_entertainment_posts_columns', 'ziaoba_set_ent_columns' );
add_action( 'manage_entertainment_posts_custom_column', 'ziaoba_ent_column_content', 10, 2 );

if ( ! function_exists( 'ziaoba_set_ent_columns' ) ) {
    function ziaoba_set_ent_columns( $columns ) {
        $columns['duration'] = __( 'Duration', 'ziaoba' );
        $columns['age_rating'] = __( 'Rating', 'ziaoba' );
        $columns['views'] = __( 'Views', 'ziaoba' );
        return $columns;
    }
}

if ( ! function_exists( 'ziaoba_ent_column_content' ) ) {
    function ziaoba_ent_column_content( $column, $post_id ) {
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
}

// Education Columns
add_filter( 'manage_education_posts_columns', 'ziaoba_set_edu_columns' );
add_action( 'manage_education_posts_custom_column', 'ziaoba_edu_column_content', 10, 2 );

if ( ! function_exists( 'ziaoba_set_edu_columns' ) ) {
    function ziaoba_set_edu_columns( $columns ) {
        $columns['topic'] = __( 'Topic', 'ziaoba' );
        $columns['views'] = __( 'Views', 'ziaoba' );
        return $columns;
    }
}

if ( ! function_exists( 'ziaoba_edu_column_content' ) ) {
    function ziaoba_edu_column_content( $column, $post_id ) {
        switch ( $column ) {
            case 'topic':
                echo esc_html( get_post_meta( $post_id, '_ziaoba_lesson_topic', true ) );
                break;
            case 'views':
                echo esc_html( get_post_meta( $post_id, '_ziaoba_views', true ) ?: '0' );
                break;
        }
    }
}
