/**
 * Ziaoba Stream Main JS
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Mobile Menu Toggle
    const mobileToggle = document.getElementById('mobileToggle');
    const navMenu = document.getElementById('navMenu');
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', function() {
            mobileToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
            document.body.classList.toggle('no-scroll');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navMenu.contains(e.target) && !mobileToggle.contains(e.target) && navMenu.classList.contains('active')) {
                mobileToggle.classList.remove('active');
                navMenu.classList.remove('active');
                document.body.classList.remove('no-scroll');
            }
        });
    }

    // Initialize Swiper Carousels
    const swiperOptions = {
        slidesPerView: 2,
        spaceBetween: 10,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            640: { slidesPerView: 3, spaceBetween: 15 },
            1024: { slidesPerView: 4, spaceBetween: 20 },
            1440: { slidesPerView: 6, spaceBetween: 20 }
        }
    };

    if (document.querySelector('.trending-swiper')) {
        new Swiper('.trending-swiper', swiperOptions);
    }
    if (document.querySelector('.edu-swiper')) {
        new Swiper('.edu-swiper', swiperOptions);
    }

    // Header Scroll Effect
    const header = document.getElementById('mainHeader');
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Season Selector Filtering
    const seasonSelector = document.getElementById('seasonSelector');
    const episodeCards = document.querySelectorAll('.episode-card');

    if (seasonSelector && episodeCards.length > 0) {
        const filterEpisodes = (selectedSeason) => {
            episodeCards.forEach(card => {
                const cardSeason = card.getAttribute('data-season');
                if (selectedSeason === 'all' || cardSeason === selectedSeason) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        };

        // Initial filter based on default selection (latest season)
        filterEpisodes(seasonSelector.value);

        seasonSelector.addEventListener('change', function() {
            filterEpisodes(this.value);
        });
    }
});
