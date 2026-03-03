# New Developer Quickstart

This repo contains a **WordPress theme + plugin** for Ziaoba’s streaming experience.

## 1) What matters most in this repo

Primary runtime components:

- `ziaoba-asset-management/` → WordPress plugin (CPTs, metadata, player shortcode, monetization settings, analytics/tracking).
- `ziaoba-stream/` → WordPress theme (homepage rails, single templates, styling, interactions).

Secondary/non-primary:

- Root `README.md` is not the main runtime for the WP site.

---

## 2) Local prerequisites

- PHP 8.0+
- WordPress 6.0+
- MySQL/MariaDB
- A local WP stack (LocalWP, Laragon, XAMPP, Docker, etc.)

Recommended WordPress plugins:

- **Ultimate Member** (many theme auth/login/profile hooks integrate with it)
- (Optional) Google Site Kit if you plan to extend social login placeholder logic

---

## 3) Install theme + plugin in WordPress

1. Start your local WordPress site.
2. Install the plugin from this folder:
   - `ziaoba-asset-management/`
   - (or use `ziaoba-asset-management/Plugin.zip`)
3. Install the theme:
   - `ziaoba-stream/`
   - (or use `ziaoba-stream/Theme.zip`)
4. Activate **Ziaoba Asset Management** plugin.
5. Activate **Ziaoba Stream** theme.
6. In **Settings → Permalinks**, click “Save Changes” once (safe rewrite refresh).

> Note: plugin activation already flushes rewrite rules and disables open registration (`users_can_register = 0`).

---

## 4) Core data model you should know first

Custom post types (plugin):

- `entertainment` (hierarchical; supports parent/child series + episodes)
- `education`

Shared taxonomy:

- `genre`

Important post meta keys:

- `_ziaoba_video_url`
- `_ziaoba_duration`
- `_ziaoba_age_rating`
- `_ziaoba_lesson_topic`
- `_ziaoba_season`
- `_ziaoba_episode_number`
- `_ziaoba_related_content`
- `_ziaoba_views`

Monetization option:

- `ziaoba_monetization_settings`

---

## 5) First content seeding checklist (do this right away)

Create at least:

- 1–2 `genre` terms (e.g., drama)
- 3+ `entertainment` posts with featured images and `_ziaoba_video_url`
- 2+ `education` posts with featured images and `_ziaoba_video_url`
- Add duration/rating/topic metadata
- Link related content (`_ziaoba_related_content`) in both directions where useful

For episode behavior:

- Create a parent `entertainment` post (series)
- Create child `entertainment` posts (episodes)
- Set episode order using page attributes (`menu_order`)

---

## 6) How front-end playback works (important flow)

1. Theme single templates call shortcode:
   - `[ziaoba_player id="<post_id>"]`
2. Shortcode reads `_ziaoba_video_url`
3. If user is not logged in, output is blocked with login prompt UI
4. If Flussonic is enabled, URL is rewritten to SSAI manifest endpoint
5. Video.js player renders HLS source
6. On `play`, client sends AJAX to increment `_ziaoba_views`

---

## 7) Admin screens and where to find them

- **Settings → Ziaoba Monetization**
  - Flussonic SSAI settings
  - GAM VAST settings
  - Sponsor branding settings
  - Metrics tab (currently static/demo values)

- **Tools → Ziaoba Analytics**
  - Date filter
  - Top content chart + trend chart
  - Detailed performance table
  - CSV export

---

## 8) Theme architecture at a glance

Key template files:

- `front-page.php` → hero + content rails
- `single-entertainment.php` → player + episodes + related education
- `single-education.php` → player + related entertainment
- `header.php` / `footer.php`

Assets:

- `style.css` → core design system and layout
- `css/custom.css` → additional theme styles
- `js/main.js` → menu behavior, Swiper setup, icon init

---

## 9) Common gotchas

- **No video appears**: check `_ziaoba_video_url` value for that post.
- **Login redirect behavior odd**: theme redirects `wp-login.php` to Ultimate Member login when UM functions exist.
- **Analytics charts empty**: ensure there are posts/views; some trend values are mock data by design.
- **Homepage rails look empty**: verify posts are published and have featured images.
- **Related sidebars missing**: check `_ziaoba_related_content` meta.

---

## 10) Recommended first tasks for a new contributor

1. Replace mocked trend metrics with real aggregates.
2. Add nonce/checks/rate-limiting to view tracking endpoint for stronger integrity.
3. Add WP-CLI seed script for demo CPT content.
4. Move inline styles/scripts toward enqueue’d files for maintainability.
5. Add a dedicated root README section for WordPress setup to avoid confusion with the Node scaffold files.

---

## 11) Quick validation routine after any change

- Activate plugin + theme without PHP errors
- Load homepage (`front-page.php`) and both single templates
- Confirm playback for logged-in users
- Confirm gate UI for logged-out users
- Confirm a `play` increments `_ziaoba_views`
- Confirm analytics page and CSV export load

---

## 12) Handy WP-CLI examples (optional)

```bash
# List active plugins
wp plugin list --status=active

# List themes and active one
wp theme list

# Flush rewrites
wp rewrite flush

# Inspect a meta value (example)
wp post meta get <POST_ID> _ziaoba_video_url
```

(Replace `<POST_ID>` with an actual post ID.)
