<?php
/**
 * Player Scripts, Styles, and Stream/Progress endpoints.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'ziaoba_enqueue_player_assets' );
add_action( 'wp_ajax_ziaoba_stream_video', 'ziaoba_stream_video_callback' );
add_action( 'wp_ajax_ziaoba_get_progress', 'ziaoba_get_progress_callback' );
add_action( 'wp_ajax_ziaoba_save_progress', 'ziaoba_save_progress_callback' );

function ziaoba_enqueue_player_assets() {
    wp_enqueue_style( 'videojs-css', 'https://vjs.zencdn.net/8.10.0/video-js.css', array(), '8.10.0' );
    wp_enqueue_script( 'videojs-js', 'https://vjs.zencdn.net/8.10.0/video.min.js', array(), '8.10.0', true );

    wp_add_inline_style( 'videojs-css', '
        .video-js .vjs-big-play-button { border-radius: 50%; width: 2em; height: 2em; line-height: 2em; margin-top: -1em; margin-left: -1em; background-color: rgba(229, 9, 20, 0.8); border-color: transparent; }
        .video-js:hover .vjs-big-play-button { background-color: #E50914; }
        .vjs-control-bar { background-color: rgba(10, 10, 10, 0.9); }
        .video-js.vjs-fluid { display: block; width: 100%; min-height: 200px; background: #000; }
        .ziaoba-player-wrapper { position: relative; width: 100%; display: block; }
        .video-js .vjs-poster { background-size: cover; background-position: center; }
    ' );

    $script = <<<'JS'
(function() {
    function postForm(url, data) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: new URLSearchParams(data).toString()
        }).then(function(response) { return response.json(); });
    }

    function initPlayer(wrapper) {
        var configRaw = wrapper.getAttribute('data-player-config');
        if (!configRaw || typeof videojs !== 'function') {
            return;
        }

        var config = JSON.parse(configRaw);
        var video = wrapper.querySelector('video');
        if (!video) {
            return;
        }

        var player = videojs(video.id);
        var furthestWatched = 0;
        var resumeApplied = false;
        var trackingSent = false;
        var lastSavedTime = 0;

        player.ready(function() {
            player.poster(video.getAttribute('poster') || '');
        });

        postForm(config.ajaxUrl, {
            action: 'ziaoba_get_progress',
            post_id: config.postId,
            nonce: config.progressNonce
        }).then(function(result) {
            if (!result || !result.success || !result.data) {
                return;
            }

            furthestWatched = Number(result.data.furthest || 0);
            var resumeTime = Number(result.data.resume_at || 0);

            if (resumeTime >= Number(config.resumeThreshold || 15)) {
                player.one('loadedmetadata', function() {
                    if (resumeApplied) {
                        return;
                    }
                    var duration = Number(player.duration() || 0);
                    if (duration && resumeTime < duration - Number(config.rewatchThreshold || 8)) {
                        player.currentTime(resumeTime);
                        furthestWatched = Math.max(furthestWatched, resumeTime);
                        resumeApplied = true;
                    }
                });
            }
        }).catch(function() {});

        player.on('play', function() {
            if (trackingSent) {
                return;
            }
            trackingSent = true;
            postForm(config.ajaxUrl, {
                action: 'ziaoba_track_view',
                post_id: config.postId,
                nonce: config.trackNonce
            }).catch(function() {});
        });

        player.on('timeupdate', function() {
            var current = Number(player.currentTime() || 0);
            if (current > furthestWatched) {
                furthestWatched = current;
            }

            if (Math.abs(current - lastSavedTime) >= 10 || player.ended()) {
                lastSavedTime = current;
                postForm(config.ajaxUrl, {
                    action: 'ziaoba_save_progress',
                    post_id: config.postId,
                    nonce: config.progressNonce,
                    current_time: current,
                    furthest_time: furthestWatched,
                    duration: Number(player.duration() || 0),
                    completed: player.ended() ? 1 : 0
                }).catch(function() {});
            }
        });

        player.on('seeking', function() {
            var current = Number(player.currentTime() || 0);
            var allowed = Number(furthestWatched || 0) + Number(config.seekGrace || 5);
            if (current > allowed) {
                player.currentTime(Math.max(0, furthestWatched));
            }
        });

        player.on('ended', function() {
            furthestWatched = Math.max(furthestWatched, Number(player.duration() || 0));
            postForm(config.ajaxUrl, {
                action: 'ziaoba_save_progress',
                post_id: config.postId,
                nonce: config.progressNonce,
                current_time: 0,
                furthest_time: furthestWatched,
                duration: Number(player.duration() || 0),
                completed: 1
            }).catch(function() {});
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.ziaoba-player-wrapper[data-player-config]').forEach(initPlayer);
    });
})();
JS;

    wp_add_inline_script( 'videojs-js', $script );
}

function ziaoba_generate_stream_token( $post_id, $user_id, $target = '', $expires = 0 ) {
    $post_id = (int) $post_id;
    $user_id = (int) $user_id;
    $expires = $expires ? (int) $expires : ( time() + MINUTE_IN_SECONDS * 10 );
    $payload = $post_id . '|' . $user_id . '|' . $expires . '|' . $target;
    $sig     = hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );

    return base64_encode( wp_json_encode( array(
        'post_id' => $post_id,
        'user_id' => $user_id,
        'expires' => $expires,
        'target'  => $target,
        'sig'     => $sig,
    ) ) );
}

function ziaoba_validate_stream_token( $token, $post_id, $user_id, $target = '' ) {
    $decoded = json_decode( base64_decode( (string) $token ), true );
    if ( ! is_array( $decoded ) ) {
        return false;
    }

    $expires = isset( $decoded['expires'] ) ? (int) $decoded['expires'] : 0;
    $payload = (int) $post_id . '|' . (int) $user_id . '|' . $expires . '|' . $target;
    $sig     = hash_hmac( 'sha256', $payload, wp_salt( 'auth' ) );

    if ( empty( $decoded['sig'] ) || ! hash_equals( $sig, $decoded['sig'] ) ) {
        return false;
    }

    if ( $expires < time() ) {
        return false;
    }

    if ( (int) $decoded['post_id'] !== (int) $post_id || (int) $decoded['user_id'] !== (int) $user_id ) {
        return false;
    }

    return true;
}

function ziaoba_is_allowed_stream_target( $post_id, $target ) {
    $source_url  = get_post_meta( $post_id, '_ziaoba_video_url', true );
    $source_host = wp_parse_url( $source_url, PHP_URL_HOST );
    $target_host = wp_parse_url( $target, PHP_URL_HOST );

    return $source_host && $target_host && strtolower( $source_host ) === strtolower( $target_host );
}

function ziaoba_make_absolute_stream_url( $reference_url, $base_url ) {
    if ( preg_match( '#^https?://#i', $reference_url ) ) {
        return $reference_url;
    }

    $base_parts = wp_parse_url( $base_url );
    if ( empty( $base_parts['scheme'] ) || empty( $base_parts['host'] ) ) {
        return '';
    }

    $base_root = $base_parts['scheme'] . '://' . $base_parts['host'];
    if ( ! empty( $base_parts['port'] ) ) {
        $base_root .= ':' . $base_parts['port'];
    }

    if ( 0 === strpos( $reference_url, '/' ) ) {
        return $base_root . $reference_url;
    }

    $base_path = isset( $base_parts['path'] ) ? $base_parts['path'] : '/';
    $base_dir  = trailingslashit( preg_replace( '#/[^/]*$#', '', $base_path ) );

    return $base_root . $base_dir . ltrim( $reference_url, '/' );
}

function ziaoba_rewrite_manifest_contents( $manifest, $post_id, $base_url ) {
    $lines    = preg_split( '/\r\n|\r|\n/', (string) $manifest );
    $rewritten = array();

    foreach ( $lines as $line ) {
        $trimmed = trim( $line );

        if ( '' === $trimmed ) {
            $rewritten[] = $line;
            continue;
        }

        if ( 0 === strpos( $trimmed, '#EXT-X-KEY:' ) ) {
            $rewritten[] = preg_replace_callback(
                '/URI="([^"]+)"/',
                function( $matches ) use ( $post_id, $base_url ) {
                    $absolute = ziaoba_make_absolute_stream_url( $matches[1], $base_url );
                    if ( ! $absolute || ! ziaoba_is_allowed_stream_target( $post_id, $absolute ) ) {
                        return $matches[0];
                    }
                    $token = ziaoba_generate_stream_token( $post_id, get_current_user_id(), $absolute );
                    $proxy = add_query_arg(
                        array(
                            'action'  => 'ziaoba_stream_video',
                            'post_id' => $post_id,
                            'token'   => rawurlencode( $token ),
                            'target'  => rawurlencode( $absolute ),
                        ),
                        admin_url( 'admin-ajax.php' )
                    );
                    return 'URI="' . esc_url_raw( $proxy ) . '"';
                },
                $line
            );
            continue;
        }

        if ( '#' === $trimmed[0] ) {
            $rewritten[] = $line;
            continue;
        }

        $absolute = ziaoba_make_absolute_stream_url( $trimmed, $base_url );
        if ( ! $absolute || ! ziaoba_is_allowed_stream_target( $post_id, $absolute ) ) {
            $rewritten[] = $line;
            continue;
        }

        $token = ziaoba_generate_stream_token( $post_id, get_current_user_id(), $absolute );
        $rewritten[] = add_query_arg(
            array(
                'action'  => 'ziaoba_stream_video',
                'post_id' => $post_id,
                'token'   => rawurlencode( $token ),
                'target'  => rawurlencode( $absolute ),
            ),
            admin_url( 'admin-ajax.php' )
        );
    }

    return implode( "\n", $rewritten );
}

function ziaoba_stream_video_callback() {
    if ( ! is_user_logged_in() ) {
        wp_die( esc_html__( 'Unauthorized', 'ziaoba' ), 401 );
    }

    $post_id = isset( $_GET['post_id'] ) ? (int) $_GET['post_id'] : 0;
    $token   = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';
    $target  = isset( $_GET['target'] ) ? esc_url_raw( wp_unslash( $_GET['target'] ) ) : '';

    if ( ! $post_id || ! $token ) {
        wp_die( esc_html__( 'Missing stream token.', 'ziaoba' ), 400 );
    }

    if ( ! $target ) {
        $target = get_post_meta( $post_id, '_ziaoba_video_url', true );
    }

    if ( ! $target || ! ziaoba_validate_stream_token( $token, $post_id, get_current_user_id(), $target ) ) {
        wp_die( esc_html__( 'Invalid stream token.', 'ziaoba' ), 403 );
    }

    if ( ! ziaoba_is_allowed_stream_target( $post_id, $target ) ) {
        wp_die( esc_html__( 'Disallowed stream target.', 'ziaoba' ), 403 );
    }

    $response = wp_remote_get(
        $target,
        array(
            'timeout' => 20,
            'headers' => array(
                'Accept' => '*/*',
            ),
        )
    );

    if ( is_wp_error( $response ) ) {
        wp_die( esc_html( $response->get_error_message() ), 502 );
    }

    $code = (int) wp_remote_retrieve_response_code( $response );
    if ( $code < 200 || $code >= 300 ) {
        wp_die( esc_html__( 'Unable to fetch stream.', 'ziaoba' ), $code ?: 502 );
    }

    $content_type = wp_remote_retrieve_header( $response, 'content-type' );
    $body         = wp_remote_retrieve_body( $response );

    if ( false !== stripos( (string) $content_type, 'mpegurl' ) || preg_match( '/\.m3u8($|\?)/i', $target ) ) {
        $body         = ziaoba_rewrite_manifest_contents( $body, $post_id, $target );
        $content_type = 'application/vnd.apple.mpegurl';
    }

    nocache_headers();
    header( 'Content-Type: ' . ( $content_type ? $content_type : 'application/octet-stream' ) );
    header( 'Content-Length: ' . strlen( $body ) );
    header( 'Accept-Ranges: none' );
    echo $body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    exit;
}

function ziaoba_get_progress_callback() {
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'ziaoba' ) ), 401 );
    }

    $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
    check_ajax_referer( 'ziaoba_progress_nonce_' . $post_id, 'nonce' );

    $progress = get_user_meta( get_current_user_id(), 'ziaoba_progress_' . $post_id, true );
    if ( ! is_array( $progress ) ) {
        $progress = array(
            'resume_at' => 0,
            'furthest'  => 0,
            'completed' => false,
        );
    }

    wp_send_json_success( $progress );
}

function ziaoba_save_progress_callback() {
    if ( ! is_user_logged_in() ) {
        wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'ziaoba' ) ), 401 );
    }

    $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
    check_ajax_referer( 'ziaoba_progress_nonce_' . $post_id, 'nonce' );

    $current_time  = isset( $_POST['current_time'] ) ? (float) $_POST['current_time'] : 0;
    $furthest_time = isset( $_POST['furthest_time'] ) ? (float) $_POST['furthest_time'] : 0;
    $duration      = isset( $_POST['duration'] ) ? (float) $_POST['duration'] : 0;
    $completed     = ! empty( $_POST['completed'] );

    if ( $duration > 0 ) {
        $current_time  = min( $current_time, $duration );
        $furthest_time = min( max( $furthest_time, $current_time ), $duration );
    }

    $remaining = $duration > 0 ? max( 0, $duration - $current_time ) : 0;
    if ( $completed || ( $duration > 0 && $remaining <= 8 ) ) {
        $current_time = 0;
        $completed    = true;
    }

    $payload = array(
        'resume_at'  => round( max( 0, $current_time ), 2 ),
        'furthest'   => round( max( 0, $furthest_time ), 2 ),
        'duration'   => round( max( 0, $duration ), 2 ),
        'completed'  => (bool) $completed,
        'updated_at' => current_time( 'mysql', true ),
    );

    update_user_meta( get_current_user_id(), 'ziaoba_progress_' . $post_id, $payload );
    wp_send_json_success( $payload );
}
