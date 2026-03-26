<?php
/**
 * Helper functions for Series, Seasons, and Episodes
 */

namespace Ziaoba\VAM;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SeriesHelper {

    /**
     * Get all seasons for a specific series
     * 
     * @param int $series_id
     * @return \WP_Post[]
     */
    public static function get_seasons( $series_id ) {
        return get_posts( array(
            'post_type'      => 'season',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'   => '_ziaoba_series_id',
                    'value' => $series_id,
                ),
            ),
            'meta_key'       => '_ziaoba_season_number',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
        ) );
    }

    /**
     * Get all episodes for a specific season
     * 
     * @param int $season_id
     * @return \WP_Post[]
     */
    public static function get_episodes( $season_id ) {
        return get_posts( array(
            'post_type'      => 'episode',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'   => '_ziaoba_season_id',
                    'value' => $season_id,
                ),
            ),
            'meta_key'       => '_ziaoba_episode_number',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
        ) );
    }

    /**
     * Get series info for a season or episode
     * 
     * @param int $post_id
     * @return \WP_Post|null
     */
    public static function get_parent_series( $post_id ) {
        $post_type = get_post_type( $post_id );
        
        if ( $post_type === 'series' ) {
            return get_post( $post_id );
        }
        
        if ( $post_type === 'season' ) {
            $series_id = get_post_meta( $post_id, '_ziaoba_series_id', true );
            return $series_id ? get_post( $series_id ) : null;
        }
        
        if ( $post_type === 'episode' ) {
            $season_id = get_post_meta( $post_id, '_ziaoba_season_id', true );
            if ( $season_id ) {
                $series_id = get_post_meta( $season_id, '_ziaoba_series_id', true );
                return $series_id ? get_post( $series_id ) : null;
            }
        }
        
        return null;
    }

    /**
     * Get season info for an episode
     * 
     * @param int $episode_id
     * @return \WP_Post|null
     */
    public static function get_parent_season( $episode_id ) {
        $season_id = get_post_meta( $episode_id, '_ziaoba_season_id', true );
        return $season_id ? get_post( $season_id ) : null;
    }
}
