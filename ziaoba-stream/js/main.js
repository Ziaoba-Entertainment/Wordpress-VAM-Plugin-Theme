/**
 * Ziaoba Stream Main JS
 */
document.addEventListener('DOMContentLoaded', function() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    const swiperOptions = {
        slidesPerView: 2.15,
        spaceBetween: 12,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            640: { slidesPerView: 3.1, spaceBetween: 16 },
            960: { slidesPerView: 4.1, spaceBetween: 18 },
            1280: { slidesPerView: 5.2, spaceBetween: 20 },
            1440: { slidesPerView: 6.2, spaceBetween: 22 }
        }
    };

    ['.trending-swiper', '.edu-swiper', '.drama-swiper'].forEach(function(selector) {
        if (document.querySelector(selector)) {
            new Swiper(selector, swiperOptions);
        }
    });

    const searchToggle = document.getElementById('searchToggle');
    const searchForm = document.getElementById('searchForm');
    let searchHistoryPushed = false;

    function closeSearch() {
        if (!searchForm || !searchToggle) return;
        searchForm.classList.remove('active');
        searchToggle.setAttribute('aria-expanded', 'false');
    }

    if (searchToggle && searchForm) {
        searchToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpening = !searchForm.classList.contains('active');
            searchForm.classList.toggle('active');
            searchToggle.setAttribute('aria-expanded', String(isOpening));
            if (isOpening) {
                if (!searchHistoryPushed) {
                    window.history.pushState({ ziaobaSearch: true }, document.title, window.location.href);
                    searchHistoryPushed = true;
                }
                const searchField = searchForm.querySelector('.search-field');
                if (searchField) searchField.focus();
            }
        });

        document.addEventListener('click', function(e) {
            if (!searchForm.contains(e.target) && e.target !== searchToggle) {
                closeSearch();
            }
        });

        window.addEventListener('popstate', function(event) {
            if (searchForm.classList.contains('active') || (event.state && event.state.ziaobaSearch)) {
                closeSearch();
                searchHistoryPushed = false;
            }
        });
    }

    const header = document.getElementById('mainHeader');
    window.addEventListener('scroll', function() {
        if (header) {
            header.classList.toggle('scrolled', window.scrollY > 50);
        }
    });

    const seasonSelector = document.getElementById('seasonSelector');
    const seasonPills = document.querySelectorAll('[data-season-target]');
    const seasonPanels = document.querySelectorAll('[data-season-panel]');

    function setActiveSeason(season) {
        if (!seasonPanels.length) return;
        seasonPanels.forEach(function(panel) {
            panel.classList.toggle('is-active', panel.dataset.seasonPanel === season);
        });
        seasonPills.forEach(function(button) {
            const active = button.dataset.seasonTarget === season;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-selected', String(active));
        });
        if (seasonSelector && seasonSelector.value !== season) {
            seasonSelector.value = season;
        }
    }

    if (seasonSelector && seasonPanels.length) {
        setActiveSeason(seasonSelector.value);
        seasonSelector.addEventListener('change', function() {
            setActiveSeason(this.value);
        });
        seasonPills.forEach(function(button) {
            button.addEventListener('click', function() {
                setActiveSeason(button.dataset.seasonTarget);
            });
        });
    }

    document.querySelectorAll('.auth-enhanced-form').forEach(function(container) {
        const emailInput = container.querySelector('input[type="email"], input[name="username"]');
        if (emailInput) {
            emailInput.setAttribute('inputmode', 'email');
            emailInput.setAttribute('autocomplete', 'email');
            emailInput.setAttribute('placeholder', 'Email address');
            emailInput.addEventListener('blur', function() {
                const value = emailInput.value.trim();
                if (!value) return;
                const valid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                emailInput.setCustomValidity(valid ? '' : 'Please enter a valid email address.');
                emailInput.reportValidity();
                const forgotLinks = container.querySelectorAll('a[href*="lostpassword"], a[href*="forgot"]');
                forgotLinks.forEach(function(link) {
                    try {
                        const url = new URL(link.href, window.location.origin);
                        url.searchParams.set('user_login', value);
                        link.href = url.toString();
                    } catch (err) {}
                });
            });
        }

        container.querySelectorAll('input[type="password"]').forEach(function(input, index) {
            const wrapper = document.createElement('div');
            wrapper.className = 'password-toggle-wrap';
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);

            const toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'password-toggle-btn';
            toggle.setAttribute('aria-label', 'Show password');
            toggle.textContent = 'Show';
            wrapper.appendChild(toggle);

            toggle.addEventListener('click', function() {
                const reveal = input.type === 'password';
                input.type = reveal ? 'text' : 'password';
                toggle.textContent = reveal ? 'Hide' : 'Show';
            });

        });

        container.querySelectorAll('input, textarea').forEach(function(input) {
            input.addEventListener('invalid', function() {
                input.classList.add('field-invalid');
            });
            input.addEventListener('input', function() {
                input.classList.remove('field-invalid');
                input.setCustomValidity('');
            });
        });
    });
});
