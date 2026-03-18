You are reviewing a WordPress codebase that contains a custom theme (`ziaoba-stream`) and plugin (`ziaoba-asset-management`) for a streaming platform.

Your task is to audit, debug, and improve the implementation without breaking anything that already works.

## Critical operating rules
1. Do not remove or regress any currently working user flow.
2. Preserve WordPress, Ultimate Member, and TMDB compatibility.
3. Prefer minimal, production-safe changes over risky rewrites.
4. Keep all URLs, redirects, auth flows, and template compatibility intact.
5. Validate every change against responsive behavior, mobile UX, and embedded WebView/browser behavior.
6. If a plugin dependency is optional, code defensively and keep graceful fallbacks.
7. Do not reintroduce redirects to the default `wp-login.php` page for normal login/signup journeys.
8. Ensure logout returns users to the homepage.
9. Preserve existing post data and meta when extending fields.
10. When changing import logic, ensure TMDB-derived metadata maps correctly for both movies and TV series.

## Areas to audit and fix thoroughly
### 1) Poster sizing and visual consistency
- Ensure homepage posters match TMDB poster proportions (portrait ratio similar to official TMDB posters, e.g. approximately 2:3).
- Apply the same fix to homepage hero support cards, “Trending Now”, “Education Shorts”, related cards, and episode cards where posters are shown.
- Use professional responsive behavior across mobile, tablet, and desktop.
- Avoid stretched or cropped poster rendering that distorts the source image.

### 2) Series metadata support in admin
- Extend plugin admin meta handling to properly support TV series.
- Add logical fields for:
  - content type
  - season number
  - episode number
  - total seasons
  - total episodes
- Ensure these fields can be imported from TMDB for TV content.
- Preserve support for standalone movies and education items.

### 3) Separate Age Rating and TMDB Vote
- Ensure “Age Rating” is not merged with TMDB vote data.
- Import age certification from the correct TMDB structures:
  - movies: release certifications
  - TV: content ratings
- Store TMDB vote average and vote count separately.
- Show them appropriately in admin and frontend.

### 4) Import and display other relevant TMDB fields
Audit and, where useful, support:
- original title / original name
- tagline
- status
- original language
- origin country
- networks / production companies
- trailer URL (YouTube when available)
- poster URL
- backdrop URL
- last TMDB sync timestamp
- any other high-value viewer-facing field that improves professionalism without clutter

Ensure these fields:
- import correctly
- save correctly
- appear in admin logically
- display to viewers where appropriate

### 5) Related Content auto-population
- Automatically populate Related Content IDs using relevant shared genres.
- Use randomized ordering.
- Exclude the current post.
- Keep it safe if no matching genres exist.
- Support both entertainment and education content.
- Allow manual override only if clearly necessary.

### 6) Professional responsive UX
- Improve spacing, hierarchy, card polish, and responsive layouts.
- Make the site feel production-grade.
- Ensure typography, padding, buttons, and cards behave well on mobile.
- Maintain accessibility and touch-friendly sizing.

### 7) Android hardware Back behavior / WebView safety
- Audit navigation and UI overlays for compatibility with browser history and hardware Back behavior.
- Ensure search overlays or modal-like states close correctly instead of trapping the user.
- Avoid broken history stacks.

### 8) Authentication sync in embedded browsers
- Keep login state persistent in embedded browser/WebView scenarios as much as WordPress safely allows.
- Review remember-me / auth cookie duration behavior.
- Make sure login sessions remain smooth across visits.

### 9) Login/logout/social auth routing
- Google login and other login entry points should stay on the branded theme/auth experience.
- Prevent unexpected jumps to the default WordPress login page where avoidable.
- Ensure logout redirects to home.
- Keep redirect targets stable and safe.

### 10) Minimal-effort login and signup UX
- Prefer email over username wherever feasible.
- Keep forms short and familiar.
- Support remember-me behavior.
- Avoid unnecessary fields.
- Do not require password confirmation where it is not essential.

### 11) Smart auth methods
- Support Google login CTA clearly.
- If passwordless or biometric integration is not implemented, identify the safest extension point rather than faking it.
- Do not claim unsupported auth methods work unless they are actually wired up.

### 12) User-centric auth design and feedback
- Add password show/hide toggles.
- Add inline validation where safe.
- Improve error messaging clarity.
- Ensure mobile-friendly inputs and button sizes.
- Make forgot-password easy to find.
- Preserve entered email when moving into recovery flows.

### 13) Clear separation of login vs sign-up
- Keep login and sign-up flows clearly distinct.
- Avoid confusing redirects or mixed forms.
- Use conventional wording and layout.

## Deliverables
1. A concise audit of what is broken or incomplete.
2. A safe implementation plan.
3. Exact code changes.
4. A regression checklist.
5. Notes on anything that still requires plugin/provider credentials or third-party configuration.

## Output style
- Be precise.
- Be conservative.
- Do not invent integration details you cannot verify in code.
- If a fix depends on Ultimate Member or Google Site Kit configuration, state that explicitly.
- Prefer patches that are realistic for a production WordPress site.
