<?php
/**
 * Frontend Display Logic (Grid, Series Browser)
 */

namespace Ziaoba\VAM;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Frontend {

    /**
     * Initialize Frontend
     */
    public static function init() {
        $instance = new self();
        add_shortcode( 'ziaoba_grid', array( $instance, 'render_grid' ) );
        add_shortcode( 'ziaoba_series_browser', array( $instance, 'render_series_browser' ) );
        add_shortcode( 'ziaoba_single_details', array( $instance, 'render_single_details' ) );
        add_shortcode( 'ziaoba_related_content', array( $instance, 'render_related_content' ) );
        add_action( 'wp_enqueue_scripts', array( $instance, 'enqueue_styles' ) );
        add_filter( 'the_content', array( $instance, 'enhance_single_content' ) );
        
        // AJAX Handlers
        add_action( 'wp_ajax_ziaoba_get_episodes_html', array( $instance, 'ajax_get_episodes_html' ) );
        add_action( 'wp_ajax_nopriv_ziaoba_get_episodes_html', array( $instance, 'ajax_get_episodes_html' ) );
    }

    /**
     * AJAX Get Episodes HTML
     */
    public function ajax_get_episodes_html() {
        check_ajax_referer( 'ziaoba_auth_nonce', 'nonce' );
        $season_id = isset( $_GET['season_id'] ) ? intval( $_GET['season_id'] ) : 0;
        
        if ( ! $season_id ) {
            wp_send_json_error( array( 'message' => 'Invalid season ID' ) );
        }

        $html = $this->render_episodes_html( $season_id );
        wp_send_json_success( array( 'html' => $html ) );
    }

    /**
     * Enqueue Styles
     */
    public function enqueue_styles() {
        // Styles moved to style.css
    }

    /**
     * Render Grid Shortcode
     */
    public function render_grid( $atts ) {
        $atts = shortcode_atts( array(
            'type' => 'entertainment',
            'count' => 12,
            'layout' => 'poster', // poster or backdrop
        ), $atts, 'ziaoba_grid' );

        $query = new \WP_Query( array(
            'post_type'      => $atts['type'],
            'posts_per_page' => $atts['count'],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );

        if ( ! $query->have_posts() ) {
            return '<p>' . __( 'No content found.', 'ziaoba-asset-management' ) . '</p>';
        }

        $is_backdrop = $atts['layout'] === 'backdrop';
        $grid_class = $is_backdrop ? 'ziaoba-grid-backdrop' : 'ziaoba-grid-poster';

        ob_start();
        echo '<div class="ziaoba-grid ' . $grid_class . '">';
        while ( $query->have_posts() ) {
            $query->the_post();
            $id = get_the_ID();
            
            if ( $is_backdrop ) {
                $backdrop = get_post_meta( $id, '_ziaoba_backdrop_url', true );
                $image = $backdrop ?: ( get_the_post_thumbnail_url( $id, 'large' ) ?: 'https://picsum.photos/seed/' . $id . '/500/281' );
                $img_class = 'ziaoba-backdrop';
            } else {
                $image = get_the_post_thumbnail_url( $id, 'large' ) ?: 'https://picsum.photos/seed/' . $id . '/300/450';
                $img_class = 'ziaoba-poster';
            }

            $duration = get_post_meta( $id, '_ziaoba_duration', true );
            $rating = get_post_meta( $id, '_ziaoba_age_rating', true );
            $topic = get_post_meta( $id, '_ziaoba_lesson_topic', true );
            
            $class = is_user_logged_in() ? '' : 'ziaoba-trigger-auth';
            $link = is_user_logged_in() ? get_permalink() : '#';
            ?>
            <div class="ziaoba-card <?php echo $class; ?>" onclick="<?php echo is_user_logged_in() ? "window.location.href='$link'" : ""; ?>">
                <img src="<?php echo esc_url( $image ); ?>" alt="<?php the_title(); ?>" class="<?php echo $img_class; ?>" referrerPolicy="no-referrer">
                <div class="ziaoba-card-content">
                    <h3 class="ziaoba-card-title"><?php the_title(); ?></h3>
                    <div class="ziaoba-card-meta">
                        <?php if ( $topic ) echo '<span style="color:#e50914; font-weight:600;">' . esc_html( $topic ) . '</span> &bull; '; ?>
                        <?php if ( $duration ) echo esc_html( $duration ) . ' &bull; '; ?>
                        <?php if ( $rating ) echo esc_html( $rating ); ?>
                    </div>
                </div>
            </div>
            <?php
        }
        echo '</div>';
        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * Render Series Browser Shortcode
     */
    public function render_series_browser( $atts ) {
        $atts = shortcode_atts( array(
            'id' => get_the_ID(),
        ), $atts, 'ziaoba_series_browser' );

        $series_id = $atts['id'];
        if ( get_post_type( $series_id ) !== 'series' ) {
            return '';
        }

        $seasons = SeriesHelper::get_seasons( $series_id );
        if ( empty( $seasons ) ) {
            return '<p>' . __( 'No seasons found for this series.', 'ziaoba-asset-management' ) . '</p>';
        }

        ob_start();
        ?>
        <div class="ziaoba-series-browser" id="ziaoba-series-browser-<?php echo $series_id; ?>">
            <div class="ziaoba-season-selector">
                <?php foreach ( $seasons as $index => $season ) : ?>
                    <div class="ziaoba-season-btn <?php echo $index === 0 ? 'active' : ''; ?>" 
                         data-season-id="<?php echo $season->ID; ?>">
                        <?php echo esc_html( $season->post_title ); ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div id="ziaoba-episode-container">
                <?php 
                // Render first season episodes by default
                echo $this->render_episodes_html( $seasons[0]->ID ); 
                ?>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const browser = document.getElementById('ziaoba-series-browser-<?php echo $series_id; ?>');
                if (!browser) return;

                const seasonBtns = browser.querySelectorAll('.ziaoba-season-btn');
                const episodeContainer = document.getElementById('ziaoba-episode-container');

                seasonBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const seasonId = this.dataset.seasonId;
                        
                        // Update UI
                        seasonBtns.forEach(b => b.classList.remove('active'));
                        this.classList.add('active');

                        // Fetch episodes via AJAX
                        if (typeof ziaobaAuth !== 'undefined') {
                            episodeContainer.innerHTML = '<div class="ziaoba-loading">Loading episodes...</div>';
                            fetch(ziaobaAuth.ajaxUrl + '?action=ziaoba_get_episodes_html&season_id=' + seasonId + '&nonce=' + ziaobaAuth.nonce)
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        episodeContainer.innerHTML = data.data.html;
                                    } else {
                                        episodeContainer.innerHTML = '<p>Error loading episodes.</p>';
                                    }
                                })
                                .catch(err => {
                                    console.error('Error fetching episodes:', err);
                                    episodeContainer.innerHTML = '<p>Error loading episodes.</p>';
                                });
                        }
                    });
                });
            });
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Enhance Single Content
     */
    public function enhance_single_content( $content ) {
        if ( ! is_single() || ! in_array( get_post_type(), array( 'entertainment', 'education' ) ) ) {
            return $content;
        }

        // Avoid recursion
        remove_filter( 'the_content', array( $this, 'enhance_single_content' ) );

        $id = get_the_ID();
        $details = '';
        $related = '';

        // Only add if not already present via shortcode
        if ( ! has_shortcode( $content, 'ziaoba_single_details' ) ) {
            $details = $this->render_single_details( array( 'id' => $id ) );
        }

        if ( ! has_shortcode( $content, 'ziaoba_related_content' ) ) {
            $related = $this->render_related_content( array( 'id' => $id ) );
        }

        // If the content doesn't have the player shortcode, we might want to add it too
        // but let's assume the user adds it manually for now to control placement.
        // Or we can prepend it if it's missing.
        $player = '';
        if ( ! has_shortcode( $content, 'ziaoba_player' ) ) {
            $player = do_shortcode( '[ziaoba_player id="' . $id . '"]' );
        }

        $enhanced_content = $player . $details . $content . $related;

        add_filter( 'the_content', array( $this, 'enhance_single_content' ) );

        return $enhanced_content;
    }

    /**
     * Render Single Details Shortcode
     */
    public function render_single_details( $atts ) {
        $atts = shortcode_atts( array(
            'id' => get_the_ID(),
        ), $atts, 'ziaoba_single_details' );

        $id = $atts['id'];
        $backdrop = get_post_meta( $id, '_ziaoba_backdrop_url', true );
        $poster = get_the_post_thumbnail_url( $id, 'large' ) ?: 'https://picsum.photos/seed/' . $id . '/300/450';
        $bg_image = $backdrop ?: $poster;
        
        $duration = get_post_meta( $id, '_ziaoba_duration', true );
        $rating = get_post_meta( $id, '_ziaoba_age_rating', true );
        $topic = get_post_meta( $id, '_ziaoba_lesson_topic', true );
        $genres = get_the_term_list( $id, 'genre', '', ', ', '' );
        $trailer_url = get_post_meta( $id, '_ziaoba_trailer_url', true );
        
        ob_start();
        ?>
        <div class="ziaoba-single-details">
            <div class="ziaoba-single-details-bg" style="background-image: url('<?php echo esc_url( $bg_image ); ?>');"></div>
            <div class="ziaoba-single-header">
                <img src="<?php echo esc_url( $poster ); ?>" alt="<?php the_title(); ?>" class="ziaoba-single-poster ziaoba-poster" referrerPolicy="no-referrer">
                <div class="ziaoba-single-info">
                    <h1 class="ziaoba-single-title"><?php echo get_the_title( $id ); ?></h1>
                    <div class="ziaoba-single-meta">
                        <?php if ( $rating ) : ?>
                            <span class="rating"><?php echo esc_html( $rating ); ?></span>
                        <?php endif; ?>
                        <?php if ( $duration ) : ?>
                            <span class="duration"><?php echo esc_html( $duration ); ?></span>
                        <?php endif; ?>
                        <?php if ( $topic ) : ?>
                            <span class="topic"><?php echo esc_html( $topic ); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ( $genres ) : ?>
                        <div class="ziaoba-single-genre" style="margin-bottom: 20px;"><?php echo $genres; ?></div>
                    <?php endif; ?>
                    
                    <div class="ziaoba-single-actions" style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <a href="#player" class="btn btn-primary">
                            <i data-lucide="play" style="width: 20px; height: 20px; fill: currentColor;"></i>
                            <?php _e( 'Watch Now', 'ziaoba-asset-management' ); ?>
                        </a>
                        <?php if ( $trailer_url ) : ?>
                            <a href="<?php echo esc_url( $trailer_url ); ?>" target="_blank" class="btn btn-outline">
                                <i data-lucide="film" style="width: 20px; height: 20px;"></i>
                                <?php _e( 'Watch Trailer', 'ziaoba-asset-management' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render Related Content Shortcode
     */
    public function render_related_content( $atts ) {
        $atts = shortcode_atts( array(
            'id' => get_the_ID(),
        ), $atts, 'ziaoba_related_content' );

        $id = $atts['id'];
        $related_id = get_post_meta( $id, '_ziaoba_related_content', true );
        
        if ( ! $related_id ) {
            return '';
        }

        ob_start();
        ?>
        <div class="ziaoba-related-section">
            <h2 class="ziaoba-related-title"><?php _e( 'Related Content', 'ziaoba-asset-management' ); ?></h2>
            <div class="ziaoba-grid">
                <?php
                $id = $related_id;
                $poster = get_the_post_thumbnail_url( $id, 'large' ) ?: 'https://picsum.photos/seed/' . $id . '/300/450';
                $duration = get_post_meta( $id, '_ziaoba_duration', true );
                $rating = get_post_meta( $id, '_ziaoba_age_rating', true );
                
                $class = is_user_logged_in() ? '' : 'ziaoba-trigger-auth';
                $link = is_user_logged_in() ? get_permalink( $id ) : '#';
                ?>
                <div class="ziaoba-card <?php echo $class; ?>" onclick="<?php echo is_user_logged_in() ? "window.location.href='$link'" : ""; ?>">
                    <img src="<?php echo esc_url( $poster ); ?>" alt="<?php echo get_the_title( $id ); ?>" class="ziaoba-poster" referrerPolicy="no-referrer">
                    <div class="ziaoba-card-content">
                        <h3 class="ziaoba-card-title"><?php echo get_the_title( $id ); ?></h3>
                        <div class="ziaoba-card-meta">
                            <?php if ( $duration ) echo esc_html( $duration ) . ' &bull; '; ?>
                            <?php if ( $rating ) echo esc_html( $rating ); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Helper to render episodes HTML
     */
    public function render_episodes_html( $season_id ) {
        $episodes = SeriesHelper::get_episodes( $season_id );
        if ( empty( $episodes ) ) {
            return '<p>' . __( 'No episodes found for this season.', 'ziaoba-asset-management' ) . '</p>';
        }

        ob_start();
        echo '<div class="ziaoba-episode-list">';
        foreach ( $episodes as $episode ) {
            $id = $episode->ID;
            $backdrop = get_post_meta( $id, '_ziaoba_backdrop_url', true );
            $poster = $backdrop ?: ( get_the_post_thumbnail_url( $id, 'medium' ) ?: 'https://picsum.photos/seed/' . $id . '/200/112' );
            $ep_num = get_post_meta( $id, '_ziaoba_episode_number', true );
            $link = is_user_logged_in() ? get_permalink( $id ) : '#';
            $class = is_user_logged_in() ? '' : 'ziaoba-trigger-auth';
            ?>
            <div class="ziaoba-episode-item <?php echo $class; ?>" onclick="<?php echo is_user_logged_in() ? "window.location.href='$link'" : ""; ?>">
                <img src="<?php echo esc_url( $poster ); ?>" alt="<?php echo esc_attr( $episode->post_title ); ?>" class="ziaoba-backdrop" referrerPolicy="no-referrer">
                <div class="ziaoba-episode-info">
                    <div class="ziaoba-episode-title"><?php echo $ep_num ? $ep_num . '. ' : ''; ?><?php echo esc_html( $episode->post_title ); ?></div>
                    <div class="ziaoba-episode-desc"><?php echo wp_trim_words( $episode->post_content, 20 ); ?></div>
                </div>
            </div>
            <?php
        }
        echo '</div>';
        return ob_get_clean();
    }
}
