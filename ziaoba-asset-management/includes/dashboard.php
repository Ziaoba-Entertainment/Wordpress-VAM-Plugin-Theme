<?php
/**
 * Analytics Dashboard
 */

namespace Ziaoba\VAM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dashboard {

    /**
     * Initialize Dashboard
     */
    public static function init() {
        $instance = new self();
        add_action( 'admin_menu', array( $instance, 'add_menu_page' ) );
        add_action( 'admin_init', array( $instance, 'handle_export' ) );
    }

    /**
     * Add Management Page
     */
    public function add_menu_page() {
        add_management_page(
            __( 'Ziaoba Analytics', 'ziaoba' ),
            __( 'Ziaoba Analytics', 'ziaoba' ),
            'manage_options',
            'ziaoba-analytics',
            array( $this, 'render_page' )
        );
    }

    /**
     * Handle CSV Export
     */
    public function handle_export() {
        if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'ziaoba-analytics' ) {
            return;
        }

        if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'ziaoba_export_csv' ) {
            return;
        }

        check_admin_referer( 'ziaoba_export_csv_nonce' );
        
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Unauthorized access', 'ziaoba' ) );
        }

        $from = isset( $_GET['from'] ) ? sanitize_text_field( $_GET['from'] ) : date( 'Y-m-d', strtotime( '-30 days' ) );
        $to   = isset( $_GET['to'] ) ? sanitize_text_field( $_GET['to'] ) : date( 'Y-m-d' );

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=ziaoba-analytics-' . $from . '-to-' . $to . '.csv' );
        
        $output = fopen( 'php://output', 'w' );
        fputcsv( $output, array( 'Video Title', 'Type', 'Genre', 'Views' ) );

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
            ) );
        }
        fclose( $output );
        exit;
    }

    /**
     * Render Analytics Page
     */
    public function render_page() {
        $from = isset( $_GET['from'] ) ? sanitize_text_field( $_GET['from'] ) : date( 'Y-m-d', strtotime( '-30 days' ) );
        $to   = isset( $_GET['to'] ) ? sanitize_text_field( $_GET['to'] ) : date( 'Y-m-d' );

        // Fetch Data for Charts
        $top_posts = get_posts( array(
            'post_type'      => array( 'entertainment', 'education' ),
            'posts_per_page' => 10,
            'meta_key'       => '_ziaoba_views',
            'orderby'        => 'meta_value_num',
            'order'          => 'DESC',
        ) );

        $labels = [];
        $view_data = [];
        foreach ( $top_posts as $post ) {
            $labels[] = $post->post_title;
            $view_data[] = (int) get_post_meta( $post->ID, '_ziaoba_views', true );
        }

        $analytics_data = array(
            'topViews' => array(
                'labels' => $labels,
                'values' => $view_data,
            )
        );

        // Enqueue Chart.js if needed, or use inline script for simplicity in this refactor
        // Ideally, this should be in a separate JS file
        ?>
        <div class="wrap">
            <h1><?php _e( 'Ziaoba Performance Dashboard', 'ziaoba' ); ?></h1>
            
            <div class="card" style="margin-top: 20px; padding: 20px; max-width: 100%;">
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
                        <button type="submit" class="button button-primary"><?php _e( 'Filter', 'ziaoba' ); ?></button>
                        <a href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'ziaoba_export_csv', 'from' => $from, 'to' => $to ) ), 'ziaoba_export_csv_nonce' ); ?>" class="button"><?php _e( 'Export CSV', 'ziaoba' ); ?></a>
                    </div>
                </form>
            </div>

            <div style="display: grid; grid-template-columns: 1fr; gap: 20px; margin-top: 20px;">
                <div class="card" style="padding: 20px;">
                    <h2><?php _e( 'Top Content by Views', 'ziaoba' ); ?></h2>
                    <div style="height: 400px;"><canvas id="viewsChart"></canvas></div>
                </div>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('viewsChart').getContext('2d');
                const data = <?php echo json_encode( $analytics_data['topViews'] ); ?>;
                
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: '<?php _e( 'Views', 'ziaoba' ); ?>',
                            data: data.values,
                            backgroundColor: 'rgba(229, 9, 20, 0.6)',
                            borderColor: 'rgba(229, 9, 20, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
        </script>
        <?php
    }
}
