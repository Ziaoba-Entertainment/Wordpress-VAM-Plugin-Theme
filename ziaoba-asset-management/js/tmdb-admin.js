jQuery(document).ready(function($) {
    const searchBtn = $('#tmdb-search-btn');
    const searchInput = $('#tmdb-search-query');
    const resultsBox = $('#tmdb-search-results');
    const loader = $('#tmdb-loader');
    const postId = $('#post_ID').val();

    // Search TMDB
    searchBtn.on('click', function() {
        const query = searchInput.val();
        if (!query) return;

        loader.show();
        resultsBox.hide().empty();

        $.ajax({
            url: ziaobaTMDB.ajaxUrl,
            type: 'GET',
            data: {
                action: 'ziaoba_tmdb_search',
                query: query,
                post_id: postId,
                nonce: ziaobaTMDB.nonce
            },
            success: function(response) {
                loader.hide();
                if (response.success && response.data.length > 0) {
                    response.data.forEach(item => {
                        const title = item.title || item.name;
                        const date = item.release_date || item.first_air_date || '';
                        const year = date ? new Date(date).getFullYear() : 'N/A';
                        const poster = item.poster_path ? 'https://image.tmdb.org/t/p/w92' + item.poster_path : 'https://via.placeholder.com/92x138?text=No+Poster';

                        const html = `
                            <div class="tmdb-search-result" data-id="${item.id}">
                                <img src="${poster}" alt="${title}">
                                <div>
                                    <span class="title">${title}</span>
                                    <span class="year">${year}</span>
                                </div>
                            </div>
                        `;
                        resultsBox.append(html);
                    });
                    resultsBox.show();
                } else {
                    resultsBox.html('<p style="padding:10px;">No results found.</p>').show();
                }
            },
            error: function() {
                loader.hide();
                alert('Search failed.');
            }
        });
    });

    // Import Data
    resultsBox.on('click', '.tmdb-search-result', function() {
        const tmdbId = $(this).data('id');
        if (!confirm('Import this content? This will overwrite title and description.')) return;

        loader.show();
        resultsBox.hide();

        $.ajax({
            url: ziaobaTMDB.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ziaoba_tmdb_import',
                tmdb_id: tmdbId,
                post_id: postId,
                nonce: ziaobaTMDB.nonce
            },
            success: function(response) {
                loader.hide();
                if (response.success) {
                    alert('Import successful! Reloading page...');
                    window.location.reload();
                } else {
                    alert('Import failed: ' + response.data);
                }
            },
            error: function() {
                loader.hide();
                alert('Import failed.');
            }
        });
    });

    // Fetch Seasons
    $('#tmdb-fetch-seasons').on('click', function() {
        if (!confirm('Fetch all seasons for this series?')) return;

        loader.show();
        $.ajax({
            url: ziaobaTMDB.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ziaoba_tmdb_fetch_seasons',
                post_id: postId,
                nonce: ziaobaTMDB.nonce
            },
            success: function(response) {
                loader.hide();
                if (response.success) {
                    alert(response.data.message);
                    window.location.reload();
                } else {
                    alert('Fetch failed: ' + response.data);
                }
            },
            error: function() {
                loader.hide();
                alert('Fetch failed.');
            }
        });
    });

    // Fetch Episodes
    $('#tmdb-fetch-episodes').on('click', function() {
        if (!confirm('Fetch all episodes for this season?')) return;

        loader.show();
        $.ajax({
            url: ziaobaTMDB.ajaxUrl,
            type: 'POST',
            data: {
                action: 'ziaoba_tmdb_fetch_episodes',
                post_id: postId,
                nonce: ziaobaTMDB.nonce
            },
            success: function(response) {
                loader.hide();
                if (response.success) {
                    alert(response.data.message);
                    window.location.reload();
                } else {
                    alert('Fetch failed: ' + response.data);
                }
            },
            error: function() {
                loader.hide();
                alert('Fetch failed.');
            }
        });
    });
});
