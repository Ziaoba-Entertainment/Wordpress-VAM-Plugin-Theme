/**
 * main.js - Swiper and interactions.
 * ziaoba-stream/js/main.js
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide Icons
    try {
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    } catch (e) {
        console.error('Lucide Icons Error:', e);
    }

    // Hamburger Menu Toggle
    const menuToggle = document.getElementById('menuToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', function() {
            menuToggle.classList.toggle('active');
            navMenu.classList.toggle('active');
            document.body.classList.toggle('menu-open');
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navMenu.contains(e.target) && !menuToggle.contains(e.target) && navMenu.classList.contains('active')) {
                menuToggle.classList.remove('active');
                navMenu.classList.remove('active');
                document.body.classList.remove('menu-open');
            }
        });
    }

    // Header Scroll Effect
    const header = document.getElementById('mainHeader');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // Swiper Initializations
    const swiperOptions = {
        slidesPerView: 2.2,
        spaceBetween: 10,
        freeMode: true,
        grabCursor: true,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            640: { slidesPerView: 3.2, spaceBetween: 15, freeMode: false },
            1024: { slidesPerView: 5.2, spaceBetween: 20, freeMode: false },
            1440: { slidesPerView: 6.2, spaceBetween: 20, freeMode: false }
        }
    };

    try {
        if (document.querySelector('.trending-swiper')) {
            new Swiper('.trending-swiper', swiperOptions);
        }

        if (document.querySelector('.edu-swiper')) {
            new Swiper('.edu-swiper', {
                ...swiperOptions,
                slidesPerView: 2,
                breakpoints: {
                    640: { slidesPerView: 3 },
                    1024: { slidesPerView: 4 },
                    1440: { slidesPerView: 5 }
                }
            });
        }

        if (document.querySelector('.drama-swiper')) {
            new Swiper('.drama-swiper', swiperOptions);
        }
    } catch (e) {
        console.error('Swiper Initialization Error:', e);
    }
});
