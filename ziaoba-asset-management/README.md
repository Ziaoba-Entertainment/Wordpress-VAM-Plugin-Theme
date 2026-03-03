# Ziaoba Entertainment Platform Setup Guide

This document outlines the setup, dependencies, and architecture for the Ziaoba Entertainment AVOD platform.

## 🏗 Architecture Overview
The platform is built as a custom WordPress solution consisting of:
1.  **Ziaoba Stream Theme**: A dark, Netflix-style frontend optimized for video streaming.
2.  **Ziaoba Asset Management Plugin**: Handles core business logic, CPTs, tracking, and monetization.
3.  **Ultimate Member**: Manages user authentication and profiles.
4.  **Google Site Kit**: Handles Google Analytics and "Sign in with Google" integration.

## 📦 Dependency Matrix

| Dependency | Version | Purpose |
|------------|---------|---------|
| WordPress | 6.4+ | Core CMS |
| PHP | 8.2+ | Server-side runtime |
| Ultimate Member | Latest | User Auth & Profiles |
| Google Site Kit | Latest | Google Auth & Analytics |
| Chart.js | 4.x (CDN) | Admin Analytics Dashboard |
| Video.js | 8.x (CDN) | Video Player Engine |
| Swiper.js | 11.x (CDN) | Homepage Carousels |
| Lucide Icons | Latest | UI Iconography |

## 🚀 Setup Instructions

### 1. Plugin Installation
- Install and activate **Ultimate Member**.
- Install and activate **Google Site Kit**.
- Upload and activate the **Ziaoba Asset Management** plugin.

### 2. Theme Installation
- Upload and activate the **Ziaoba Stream** theme.

### 3. Page Setup
- **Login/Register**: Ultimate Member will create these pages automatically. Ensure they are styled correctly in the UM settings.
- **Homepage**: Set a static page as the homepage and use the `front-page.php` template.

### 4. Custom Post Types
- **Entertainment**: Used for movies and series. Supports hierarchical nesting (Series > Episodes).
- **Education**: Used for short-form educational content linked to entertainment assets.

### 5. Analytics & Tracking
- The platform tracks views automatically via an AJAX endpoint (`ziaoba_track_view`).
- **Hardening**: The endpoint is protected by nonces and simple rate-limiting to prevent view count spamming.
- **Logs**: Daily view aggregates are stored in post meta (`_ziaoba_views_log`) for the trend dashboard.

### 6. Monetization
- Configure **Flussonic SSAI** and **Google Ad Manager** tags in `Tools > Ziaoba Monetization`.
- Ad metrics can be viewed in the "Ad Metrics" tab within the monetization settings.

## 🛠 Developer Notes
- **CSS**: Uses a custom `@theme` block in `style.css` for Netflix-style variables.
- **JS**: Theme logic is in `js/main.js`. Analytics logic is in `js/analytics-dashboard.js`.
- **Hooks**: Use `ziaoba_google_auth_button` action to inject custom social login buttons.
