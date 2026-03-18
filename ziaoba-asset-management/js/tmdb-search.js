/**
 * TMDB Search JS
 */
jQuery(document).ready(function($) {
    const $searchInput = $('#tmdb-search-input');
    const $searchType = $('#tmdb-search-type');
    const $searchButton = $('#tmdb-search-button');
    const $resultsContainer = $('#tmdb-search-results');

    $searchButton.on('click', function() {
        const query = $searchInput.val();
        const type = $searchType.val();

        if (!query) {
            alert('Please enter a title to search.');
            return;
        }

        $resultsContainer.html('<p>' + ziaobaTMDB.i18n.searching + '</p>');
        $searchButton.prop('disabled', true);

        $.ajax({
            url: ziaobaTMDB.ajaxUrl,
            type: 'POST',
            data: {
                action: 'vam_tmdb_search',
                query: query,
                type: type,
                nonce: ziaobaTMDB.nonce
            },
            success: function(response) {
                if (response.success) {
                    renderResults(response.data);
                } else {
                    $resultsContainer.html('<p class="error">' + response.data + '</p>');
                }
            },
            error: function() {
                $resultsContainer.html('<p class="error">' + ziaobaTMDB.i18n.error + '</p>');
            },
            complete: function() {
                $searchButton.prop('disabled', false);
            }
        });
    });

    function renderResults(results) {
        if (!results || results.length === 0) {
            $resultsContainer.html('<p>No results found.</p>');
            return;
        }

        let html = '';
        results.forEach(function(item) {
            const title = item.title || item.name;
            const releaseDate = item.release_date || item.first_air_date || 'N/A';
            const year = releaseDate.split('-')[0];
            const poster = item.poster_path ? 'https://image.tmdb.org/t/p/w185' + item.poster_path : 'https://via.placeholder.com/185x278?text=No+Poster';
            const type = item.media_type || (item.title ? 'movie' : 'tv');

            html += `
                <div class="tmdb-result-card card" style="display: flex; flex-direction: column; padding: 15px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                    <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                        <img src="${poster}" alt="${title}" style="width: 100px; height: 150px; object-fit: cover; border-radius: 4px;">
                        <div style="flex-grow: 1;">
                            <h3 style="margin: 0 0 5px 0;">${title} (${year})</h3>
                            <p style="margin: 0 0 10px 0; font-size: 12px; color: #666; text-transform: uppercase;">${type}</p>
                            <p style="margin: 0; font-size: 13px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden;">${item.overview}</p>
                        </div>
                    </div>
                    <div style="margin-top: auto;">
                        <button type="button" class="button button-secondary tmdb-import-btn" data-id="${item.id}" data-type="${type}">
                            ${ziaobaTMDB.i18n.importing.replace('...', '')}
                        </button>
                    </div>
                </div>
            `;
        });

        $resultsContainer.html(html);
    }

    $(document).on('click', '.tmdb-import-btn', function() {
        const $btn = $(this);
        const id = $btn.data('id');
        const type = $btn.data('type');

        $btn.prop('disabled', true).text(ziaobaTMDB.i18n.importing);

        $.ajax({
            url: ziaobaTMDB.ajaxUrl,
            type: 'POST',
            data: {
                action: 'vam_tmdb_import',
                id: id,
                type: type,
                nonce: ziaobaTMDB.nonce
            },
            success: function(response) {
                if (response.success) {
                    $btn.text(ziaobaTMDB.i18n.imported).addClass('button-primary').removeClass('button-secondary');
                    setTimeout(function() {
                        window.location.href = response.data.edit_url;
                    }, 1000);
                } else {
                    alert(response.data);
                    $btn.prop('disabled', false).text('Import Title');
                }
            },
            error: function() {
                alert(ziaobaTMDB.i18n.error);
                $btn.prop('disabled', false).text('Import Title');
            }
        });
    });
});
