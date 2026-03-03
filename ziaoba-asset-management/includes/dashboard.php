<?php
/**
 * Analytics Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'ziaoba_analytics_menu' );

function ziaoba_analytics_menu() {
    add_management_page(
        'Ziaoba Analytics',
        'Ziaoba Analytics',
        'manage_options',
        'ziaoba-analytics',
        'ziaoba_analytics_page_callback'
    );
}

function ziaoba_analytics_page_callback() {
    // Handle CSV Export
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'ziaoba_export_csv' ) {
        check_admin_referer( 'ziaoba_export_csv_nonce' );
        
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

    wp_localize_script( 'ziaoba-analytics-js', 'ziaobaAnalyticsData', $analytics_data );

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
    <?php
}
