/**
 * Ziaoba Stream Main JS
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide Icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
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
    if (document.querySelector('.drama-swiper')) {
        new Swiper('.drama-swiper', swiperOptions);
    }

    // Search Toggle
    const searchToggle = document.getElementById('searchToggle');
    const searchForm = document.getElementById('searchForm');
    
    if (searchToggle && searchForm) {
        searchToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            searchForm.classList.toggle('active');
            if (searchForm.classList.contains('active')) {
                searchForm.querySelector('.search-field').focus();
            }
        });
        
        document.addEventListener('click', function(e) {
            if (!searchForm.contains(e.target) && e.target !== searchToggle) {
                searchForm.classList.remove('active');
            }
        });
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
});
