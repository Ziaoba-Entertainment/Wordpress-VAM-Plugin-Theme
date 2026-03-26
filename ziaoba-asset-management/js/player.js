/**
 * Ziaoba Player JS
 */
document.addEventListener('DOMContentLoaded', function() {
    const playerElement = document.getElementById('ziaoba-video-player');
    if (!playerElement) return;

    const player = videojs('ziaoba-video-player');
    const config = typeof ziaobaPlayer !== 'undefined' ? ziaobaPlayer : null;

    if (!config) return;

    let maxTimeWatched = 0;
    let lastSavedTime = 0;
    const SAVE_INTERVAL = 10000; // Save every 10 seconds

    player.ready(function() {
        // Resume Playback
        if (config.saved_time > 0) {
            player.currentTime(config.saved_time);
        }

        // Track View on first play
        player.one('play', function() {
            const data = new URLSearchParams();
            data.append('action', 'ziaoba_track_view');
            data.append('nonce', config.nonce);
            data.append('post_id', config.post_id);

            fetch(config.ajax_url, {
                method: 'POST',
                body: data,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            }).catch(err => console.error('Error tracking view:', err));
        });

        // Forced Viewing Logic
        if (config.forced_view) {
            player.on('seeking', function() {
                const currentTime = player.currentTime();
                if (currentTime > maxTimeWatched) {
                    player.currentTime(maxTimeWatched);
                }
            });

            player.on('timeupdate', function() {
                const currentTime = player.currentTime();
                if (currentTime > maxTimeWatched) {
                    maxTimeWatched = currentTime;
                }
            });
        }

        // Tracking Progress
        player.on('timeupdate', function() {
            const currentTime = player.currentTime();
            const duration = player.duration();

            if (config.is_logged_in && duration > 0) {
                const now = Date.now();
                if (now - lastSavedTime > SAVE_INTERVAL) {
                    saveProgress(currentTime, duration);
                    lastSavedTime = now;
                }
            }
        });

        // Save on pause and end
        player.on('pause', function() {
            if (config.is_logged_in) {
                saveProgress(player.currentTime(), player.duration());
            }
        });

        player.on('ended', function() {
            if (config.is_logged_in) {
                saveProgress(player.currentTime(), player.duration());
            }
        });
    });

    function saveProgress(timecode, duration) {
        if (!config.is_logged_in) return;

        const data = new URLSearchParams();
        data.append('action', 'ziaoba_save_playback_progress');
        data.append('nonce', config.nonce);
        data.append('post_id', config.post_id);
        data.append('timecode', timecode);
        data.append('duration', duration);

        fetch(config.ajax_url, {
            method: 'POST',
            body: data,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        }).catch(err => console.error('Error saving playback progress:', err));
    }
});

/**
 * Global playTrailer function for TMDB integration
 */
window.playTrailer = function(key) {
    const modal = document.getElementById('trailer-modal');
    const container = document.getElementById('trailer-container');
    if (modal && container) {
        container.innerHTML = `<iframe src="https://www.youtube.com/embed/${key}?autoplay=1" frameborder="0" allowfullscreen style="width: 100%; aspect-ratio: 16/9;"></iframe>`;
        modal.style.display = 'flex';
    }
};

window.closeTrailer = function() {
    const modal = document.getElementById('trailer-modal');
    const container = document.getElementById('trailer-container');
    if (modal && container) {
        container.innerHTML = '';
        modal.style.display = 'none';
    }
};
