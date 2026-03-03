<?php
/**
 * Analytics Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'ziaoba_analytics_menu' );

if ( ! function_exists( 'ziaoba_analytics_menu' ) ) {
    function ziaoba_analytics_menu() {
        add_management_page(
            'Ziaoba Analytics',
            'Ziaoba Analytics',
            'manage_options',
            'ziaoba-analytics',
            'ziaoba_analytics_page_callback'
        );
    }
}

if ( ! function_exists( 'ziaoba_analytics_page_callback' ) ) {
    function ziaoba_analytics_page_callback() {
        // Handle CSV Export
        if ( isset( $_GET['action'] ) && $_GET['action'] === 'ziaoba_export_csv' ) {
            check_admin_referer( 'ziaoba_export_csv_nonce' );
            
            $from = isset( $_GET['from'] ) ? sanitize_text_field( $_GET['from'] ) : date( 'Y-m-d', strtotime( '-30 days' ) );
            $to   = isset( $_GET['to'] ) ? sanitize_text_field( $_GET['to'] ) : date( 'Y-m-d' );

            header( 'Content-Type: text/csv; charset=utf-8' );
            header( 'Content-Disposition: attachment; filename=ziaoba-analytics-' . $from . '-to-' . $to . '.csv' );
            
            $output = fopen( 'php://output', 'w' );
            fputcsv( $output, array( 'Video Title', 'Type', 'Genre', 'Views', 'Avg Dwell (s)', 'Ad Impressions' ) );

            $posts = get_posts( array(
                'post_type'      => array( 'entertainment', 'education' ),
                'posts_per_page' => -1,
                'date_query'     => array(
                    array(
                        'after'     => $from,
                        'before'    => $to,
                        'inclusive' => true,
                    ),
                ),
            ) );

            foreach ( $posts as $post ) {
                $genres = get_the_terms( $post->ID, 'genre' );
                $genre_list = $genres ? implode( ', ', wp_list_pluck( $genres, 'name' ) ) : 'N/A';
                
                fputcsv( $output, array(
                    $post->post_title,
                    $post->post_type,
                    $genre_list,
                    get_post_meta( $post->ID, '_ziaoba_views', true ) ?: 0,
                    get_post_meta( $post->ID, '_ziaoba_avg_dwell', true ) ?: 0,
                    get_post_meta( $post->ID, '_ziaoba_ad_impressions', true ) ?: 0,
                ) );
            }
            fclose( $output );
            exit;
        }

        $from = isset( $_GET['from'] ) ? sanitize_text_field( $_GET['from'] ) : date( 'Y-m-d', strtotime( '-30 days' ) );
        $to   = isset( $_GET['to'] ) ? sanitize_text_field( $_GET['to'] ) : date( 'Y-m-d' );

        // Fetch Data for Charts
        $top_posts = get_posts( array(
            'post_type'      => array( 'entertainment', 'education' ),
            'posts_per_page' => 10,
            'meta_key'       => '_ziaoba_views',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
            'date_query'     => array(
                array(
                    'after'     => $from,
                    'before'    => $to,
                    'inclusive' => true,
                ),
            ),
        ) );

        $labels = [];
        $view_data = [];
        foreach ( $top_posts as $post ) {
            $labels[] = $post->post_title;
            $view_data[] = (int) get_post_meta( $post->ID, '_ziaoba_views', true );
        }

        // Weekly Trend Data (Mocked for now but structured for Chart.js)
        $trend_labels = array( 'Week 1', 'Week 2', 'Week 3', 'Week 4' );
        $impression_trend = array( 1200, 1900, 3000, 5000 );
        $dwell_trend = array( 45, 52, 48, 60 );

        $analytics_data = array(
            'topViews' => array(
                'labels' => $labels,
                'values' => $view_data,
            ),
            'trends' => array(
                'labels'      => $trend_labels,
                'impressions' => $impression_trend,
                'dwell'       => $dwell_trend,
            )
        );

        wp_localize_script( 'ziaoba-analytics-js', 'ziaobaAnalyticsData', $analytics_data );

        ?>
        <div class="wrap ziaoba-analytics-wrap">
            <h1 class="wp-heading-inline"><?php _e( 'Ziaoba Performance Dashboard', 'ziaoba' ); ?></h1>
            <hr class="wp-header-end">

            <div class="card" style="margin-top: 20px; padding: 20px; background: #fff; border: 1px solid #ccd0d4;">
                <form method="get" action="">
                    <input type="hidden" name="page" value="ziaoba-analytics">
                    <div style="display: flex; gap: 20px; align-items: flex-end;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php _e( 'From Date', 'ziaoba' ); ?></label>
                            <input type="date" name="from" value="<?php echo esc_attr( $from ); ?>">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;"><?php _e( 'To Date', 'ziaoba' ); ?></label>
                            <input type="date" name="to" value="<?php echo esc_attr( $to ); ?>">
                        </div>
                        <button type="submit" class="button button-primary"><?php _e( 'Filter Data', 'ziaoba' ); ?></button>
                        <a href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'ziaoba_export_csv', 'from' => $from, 'to' => $to ) ), 'ziaoba_export_csv_nonce' ); ?>" class="button"><?php _e( 'Export CSV', 'ziaoba' ); ?></a>
                    </div>
                </form>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <div class="card" style="padding: 20px; background: #fff; border: 1px solid #ccd0d4;">
                    <h2><?php _e( 'Top 10 Content by Views', 'ziaoba' ); ?></h2>
                    <div style="height: 300px;"><canvas id="viewsChart"></canvas></div>
                </div>
                <div class="card" style="padding: 20px; background: #fff; border: 1px solid #ccd0d4;">
                    <h2><?php _e( 'Weekly Impressions Trend', 'ziaoba' ); ?></h2>
                    <div style="height: 300px;"><canvas id="trendChart"></canvas></div>
                </div>
            </div>

            <div class="card" style="margin-top: 20px; padding: 20px; background: #fff; border: 1px solid #ccd0d4;">
                <h2><?php _e( 'Detailed Content Performance', 'ziaoba' ); ?></h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e( 'Video Title', 'ziaoba' ); ?></th>
                            <th><?php _e( 'Type', 'ziaoba' ); ?></th>
                            <th><?php _e( 'Genre', 'ziaoba' ); ?></th>
                            <th><?php _e( 'Views', 'ziaoba' ); ?></th>
                            <th><?php _e( 'Avg Dwell (s)', 'ziaoba' ); ?></th>
                            <th><?php _e( 'Ad Impressions', 'ziaoba' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $table_posts = get_posts( array(
                            'post_type'      => array( 'entertainment', 'education' ),
                            'posts_per_page' => 20,
                            'date_query'     => array(
                                array(
                                    'after'     => $from,
                                    'before'    => $to,
                                    'inclusive' => true,
                                ),
                            ),
                        ) );

                        if ( $table_posts ) :
                            foreach ( $table_posts as $post ) :
                                $genres = get_the_terms( $post->ID, 'genre' );
                                $genre_list = $genres ? implode( ', ', wp_list_pluck( $genres, 'name' ) ) : '—';
                                ?>
                                <tr>
                                    <td><strong><?php echo esc_html( $post->post_title ); ?></strong></td>
                                    <td><?php echo ucfirst( $post->post_type ); ?></td>
                                    <td><?php echo esc_html( $genre_list ); ?></td>
                                    <td><?php echo (int) get_post_meta( $post->ID, '_ziaoba_views', true ); ?></td>
                                    <td><?php echo (int) get_post_meta( $post->ID, '_ziaoba_avg_dwell', true ); ?>s</td>
                                    <td><?php echo (int) get_post_meta( $post->ID, '_ziaoba_ad_impressions', true ); ?></td>
                                </tr>
                                <?php
                            endforeach;
                        else :
                            echo '<tr><td colspan="6">' . __( 'No data found for the selected period.', 'ziaoba' ) . '</td></tr>';
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
}
