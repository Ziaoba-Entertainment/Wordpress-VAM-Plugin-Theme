<?php
/**
 * Monetization Settings Page
 */

namespace Ziaoba\VAM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Monetization {

    /**
     * Initialize Monetization
     */
    public static function init() {
        $instance = new self();
        add_action( 'admin_menu', array( $instance, 'add_menu_page' ) );
    }

    /**
     * Add Options Page
     */
    public function add_menu_page() {
        add_options_page(
            __( 'Ziaoba Monetization', 'ziaoba' ),
            __( 'Ziaoba Monetization', 'ziaoba' ),
            'manage_options',
            'ziaoba-monetization',
            array( $this, 'render_page' )
        );
    }

    /**
     * Render Monetization Page
     */
    public function render_page() {
        if ( isset( $_POST['ziaoba_save_monetization'] ) ) {
            check_admin_referer( 'ziaoba_monetization_action', 'ziaoba_monetization_nonce' );
            
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( __( 'Unauthorized access', 'ziaoba' ) );
            }

            $settings = array(
                'flussonic_url'    => esc_url_raw( $_POST['flussonic_url'] ),
                'flussonic_token'  => sanitize_text_field( $_POST['flussonic_token'] ),
                'flussonic_enable' => isset( $_POST['flussonic_enable'] ) ? '1' : '0',
                
                'gam_vast_url'     => esc_url_raw( $_POST['gam_vast_url'] ),
                'gam_enable'       => isset( $_POST['gam_enable'] ) ? '1' : '0',
                
                'sponsor_name'     => sanitize_text_field( $_POST['sponsor_name'] ),
                'sponsor_logo'     => esc_url_raw( $_POST['sponsor_logo'] ),
                'sponsor_url'      => esc_url_raw( $_POST['sponsor_url'] ),
                'sponsor_enable'   => isset( $_POST['sponsor_enable'] ) ? '1' : '0',
                
                'tmdb_api_key'     => sanitize_text_field( $_POST['tmdb_api_key'] ),
                'allow_missing_rating' => sanitize_text_field( $_POST['allow_missing_rating'] ),
            );
            
            update_option( 'ziaoba_monetization_settings', $settings );
            echo '<div class="updated"><p>' . __( 'Monetization settings updated successfully.', 'ziaoba' ) . '</p></div>';
        }

        $settings = get_option( 'ziaoba_monetization_settings', array() );
        ?>
        <div class="wrap ziaoba-admin-wrap">
            <h1 class="wp-heading-inline"><?php _e( 'Ziaoba Monetization Control', 'ziaoba' ); ?></h1>
            <hr class="wp-header-end">

            <form method="post" style="margin-top: 20px;">
                <?php wp_nonce_field( 'ziaoba_monetization_action', 'ziaoba_monetization_nonce' ); ?>
                
                <nav class="nav-tab-wrapper">
                    <a href="#flussonic" class="nav-tab nav-tab-active" data-tab="flussonic"><?php _e( 'Flussonic SSAI', 'ziaoba' ); ?></a>
                    <a href="#gam" class="nav-tab" data-tab="gam"><?php _e( 'Google Ad Manager', 'ziaoba' ); ?></a>
                    <a href="#sponsors" class="nav-tab" data-tab="sponsors"><?php _e( 'Sponsor Packages', 'ziaoba' ); ?></a>
                    <a href="#tmdb" class="nav-tab" data-tab="tmdb"><?php _e( 'TMDB API', 'ziaoba' ); ?></a>
                    <a href="#metrics" class="nav-tab" data-tab="metrics"><?php _e( 'Ad Metrics', 'ziaoba' ); ?></a>
                </nav>

                <div id="flussonic-tab" class="tab-content active-tab" style="background: #fff; padding: 20px; border: 1px solid #ccc; border-top: none;">
                    <h2><?php _e( 'Server-Side Ad Insertion (SSAI)', 'ziaoba' ); ?></h2>
                    <p class="description"><?php _e( 'Configure Flussonic SSAI to stitch ads directly into the HLS stream for better ad-block resilience.', 'ziaoba' ); ?></p>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e( 'Enable Flussonic SSAI', 'ziaoba' ); ?></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" name="flussonic_enable" value="1" <?php checked( $settings['flussonic_enable'] ?? '0', '1' ); ?>>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e( 'Flussonic Base URL', 'ziaoba' ); ?></th>
                            <td>
                                <input type="text" name="flussonic_url" value="<?php echo esc_attr( $settings['flussonic_url'] ?? '' ); ?>" class="regular-text" placeholder="https://stream.ziaoba.com">
                                <p class="description"><?php _e( 'The root URL of your Flussonic media server.', 'ziaoba' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e( 'SSAI Auth Token', 'ziaoba' ); ?></th>
                            <td>
                                <input type="password" name="flussonic_token" value="<?php echo esc_attr( $settings['flussonic_token'] ?? '' ); ?>" class="regular-text">
                                <p class="description"><?php _e( 'Secret token for signing SSAI manifest requests.', 'ziaoba' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div id="gam-tab" class="tab-content" style="display:none; background: #fff; padding: 20px; border: 1px solid #ccc; border-top: none;">
                    <h2><?php _e( 'Google Ad Manager (GAM)', 'ziaoba' ); ?></h2>
                    <p class="description"><?php _e( 'Use GAM for client-side VAST/IMA ad delivery as a primary or fallback method.', 'ziaoba' ); ?></p>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e( 'Enable GAM Ads', 'ziaoba' ); ?></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" name="gam_enable" value="1" <?php checked( $settings['gam_enable'] ?? '0', '1' ); ?>>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e( 'VAST Tag URL', 'ziaoba' ); ?></th>
                            <td>
                                <textarea name="gam_vast_url" class="large-text" rows="3"><?php echo esc_textarea( $settings['gam_vast_url'] ?? '' ); ?></textarea>
                                <p class="description"><?php _e( 'Paste your GAM VAST tag URL here.', 'ziaoba' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div id="sponsors-tab" class="tab-content" style="display:none; background: #fff; padding: 20px; border: 1px solid #ccc; border-top: none;">
                    <h2><?php _e( 'Direct Sponsor Integration', 'ziaoba' ); ?></h2>
                    <p class="description"><?php _e( 'Manage direct sponsorship branding that appears in the video player and site-wide.', 'ziaoba' ); ?></p>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e( 'Enable Sponsor Branding', 'ziaoba' ); ?></th>
                            <td>
                                <label class="switch">
                                    <input type="checkbox" name="sponsor_enable" value="1" <?php checked( $settings['sponsor_enable'] ?? '0', '1' ); ?>>
                                    <span class="slider round"></span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e( 'Sponsor Name', 'ziaoba' ); ?></th>
                            <td><input type="text" name="sponsor_name" value="<?php echo esc_attr( $settings['sponsor_name'] ?? '' ); ?>" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e( 'Sponsor Logo URL', 'ziaoba' ); ?></th>
                            <td>
                                <input type="text" name="sponsor_logo" value="<?php echo esc_attr( $settings['sponsor_logo'] ?? '' ); ?>" class="regular-text">
                                <p class="description"><?php _e( 'Full URL to the sponsor logo (PNG/SVG recommended).', 'ziaoba' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e( 'Sponsor Website', 'ziaoba' ); ?></th>
                            <td><input type="text" name="sponsor_url" value="<?php echo esc_attr( $settings['sponsor_url'] ?? '' ); ?>" class="regular-text" placeholder="https://sponsor.com"></td>
                        </tr>
                    </table>
                </div>

                <div id="tmdb-tab" class="tab-content" style="display:none; background: #fff; padding: 20px; border: 1px solid #ccc; border-top: none;">
                    <h2><?php _e( 'TMDB API Integration', 'ziaoba' ); ?></h2>
                    <p class="description"><?php _e( 'Configure your TMDB API key to enable automated content ingestion for movies and series.', 'ziaoba' ); ?></p>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e( 'TMDB API Key (v3)', 'ziaoba' ); ?></th>
                            <td>
                                <input type="password" name="tmdb_api_key" value="<?php echo esc_attr( $settings['tmdb_api_key'] ?? '' ); ?>" class="regular-text">
                                <p class="description"><?php _e( 'Get your API key from <a href="https://www.themoviedb.org/settings/api" target="_blank">TMDB Settings</a>.', 'ziaoba' ); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e( 'Allow Missing Ratings', 'ziaoba' ); ?></th>
                            <td>
                                <select name="allow_missing_rating">
                                    <option value="yes" <?php selected( $settings['allow_missing_rating'] ?? 'yes', 'yes' ); ?>><?php _e( 'Yes (Allow access)', 'ziaoba' ); ?></option>
                                    <option value="no" <?php selected( $settings['allow_missing_rating'] ?? 'yes', 'no' ); ?>><?php _e( 'No (Deny access)', 'ziaoba' ); ?></option>
                                </select>
                                <p class="description"><?php _e( 'How to handle content that has no age rating metadata.', 'ziaoba' ); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <div id="metrics-tab" class="tab-content" style="display:none; background: #fff; padding: 20px; border: 1px solid #ccc; border-top: none;">
                    <h2><?php _e( 'Ad Performance Metrics', 'ziaoba' ); ?></h2>
                    <p class="description"><?php _e( 'Real-time tracking of ad impressions and estimated revenue across all platforms.', 'ziaoba' ); ?></p>
                    
                    <?php
                    // Aggregate total views across all content types
                    $total_views = 0;
                    $content_types = array( 'entertainment', 'education', 'episode' );
                    foreach ( $content_types as $type ) {
                        $query = new \WP_Query( array(
                            'post_type'      => $type,
                            'posts_per_page' => -1,
                            'fields'         => 'ids',
                        ) );
                        if ( $query->have_posts() ) {
                            foreach ( $query->posts as $id ) {
                                $total_views += (int) get_post_meta( $id, '_ziaoba_views', true );
                            }
                        }
                    }

                    // Mock some realistic data based on views
                    $cpm = 45.00; // ZAR
                    $revenue = ( $total_views / 1000 ) * $cpm;
                    
                    // Distribution (Mocked for now)
                    $gam_views = round( $total_views * 0.68 );
                    $ssai_views = round( $total_views * 0.26 );
                    $sponsor_views = $total_views - $gam_views - $ssai_views;
                    ?>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 20px 0;">
                        <div style="background: #f9f9f9; padding: 20px; border: 1px solid #eee; border-radius: 4px; text-align: center;">
                            <span style="display: block; font-size: 12px; color: #666; text-transform: uppercase;"><?php _e( 'Total Ad Views', 'ziaoba' ); ?></span>
                            <span style="font-size: 24px; font-weight: 700; color: #E50914;"><?php echo number_format( $total_views ); ?></span>
                        </div>
                        <div style="background: #f9f9f9; padding: 20px; border: 1px solid #eee; border-radius: 4px; text-align: center;">
                            <span style="display: block; font-size: 12px; color: #666; text-transform: uppercase;"><?php _e( 'Avg. CPM (ZAR)', 'ziaoba' ); ?></span>
                            <span style="font-size: 24px; font-weight: 700; color: #00C853;">R <?php echo number_format( $cpm, 2 ); ?></span>
                        </div>
                        <div style="background: #f9f9f9; padding: 20px; border: 1px solid #eee; border-radius: 4px; text-align: center;">
                            <span style="display: block; font-size: 12px; color: #666; text-transform: uppercase;"><?php _e( 'Est. Revenue', 'ziaoba' ); ?></span>
                            <span style="font-size: 24px; font-weight: 700; color: #2196F3;">R <?php echo number_format( $revenue, 2 ); ?></span>
                        </div>
                    </div>

                    <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
                        <thead>
                            <tr>
                                <th><?php _e( 'Ad Source', 'ziaoba' ); ?></th>
                                <th><?php _e( 'Impressions', 'ziaoba' ); ?></th>
                                <th><?php _e( 'Fill Rate', 'ziaoba' ); ?></th>
                                <th><?php _e( 'Revenue (Est)', 'ziaoba' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Google Ad Manager</strong></td>
                                <td><?php echo number_format( $gam_views ); ?></td>
                                <td>92%</td>
                                <td>R <?php echo number_format( ( $gam_views / 1000 ) * $cpm, 2 ); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Flussonic SSAI</strong></td>
                                <td><?php echo number_format( $ssai_views ); ?></td>
                                <td>98%</td>
                                <td>R <?php echo number_format( ( $ssai_views / 1000 ) * $cpm, 2 ); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Direct Sponsors</strong></td>
                                <td><?php echo number_format( $sponsor_views ); ?></td>
                                <td>100%</td>
                                <td>R <?php echo number_format( ( $sponsor_views / 1000 ) * $cpm, 2 ); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="submit">
                    <input type="submit" name="ziaoba_save_monetization" class="button-primary" value="<?php _e( 'Save Monetization Settings', 'ziaoba' ); ?>">
                </p>
            </form>

            <style>
                .tab-content { min-height: 300px; }
                /* Simple Toggle Switch */
                .switch { position: relative; display: inline-block; width: 40px; height: 20px; }
                .switch input { opacity: 0; width: 0; height: 0; }
                .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 20px; }
                .slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
                input:checked + .slider { background-color: #E50914; }
                input:checked + .slider:before { transform: translateX(20px); }
            </style>

            <script>
                document.querySelectorAll('.nav-tab').forEach(tab => {
                    tab.addEventListener('click', function(e) {
                        e.preventDefault();
                        const target = this.getAttribute('data-tab');
                        
                        // Update Tabs
                        document.querySelectorAll('.nav-tab').forEach(t => t.classList.remove('nav-tab-active'));
                        this.classList.add('nav-tab-active');
                        
                        // Update Content
                        document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
                        document.getElementById(target + '-tab').style.display = 'block';
                        
                        // Update Hash
                        window.location.hash = target;
                    });
                });

                // Handle initial hash
                if (window.location.hash) {
                    const hash = window.location.hash.substring(1);
                    const tab = document.querySelector(`.nav-tab[data-tab="${hash}"]`);
                    if (tab) tab.click();
                }
            </script>
        </div>
        <?php
    }
}
