# Games4Cash — Technical Documentation

**Framework:** Laravel 11 (PHP)  
**Database:** SQLite  
**External APIs:** IGDB (game data), Steam (pricing), CheapShark (pricing), postcodes.io (address lookup), ip-api.com (geolocation)

---

## Table of Contents

1. [Application Overview](#1-application-overview)
2. [Directory Structure](#2-directory-structure)
3. [Authentication & User System](#3-authentication--user-system)
4. [Middleware](#4-middleware)
5. [Routing](#5-routing)
6. [Controllers](#6-controllers)
7. [Models](#7-models)
8. [Services](#8-services)
9. [Pricing Engine](#9-pricing-engine)
10. [Admin Panel](#10-admin-panel)
11. [Email System](#11-email-system)
12. [Database Schema](#12-database-schema)
13. [Configuration](#13-configuration)
14. [External API Integrations](#14-external-api-integrations)
15. [SEO & Sitemap](#15-seo--sitemap)
16. [Content Pages](#16-content-pages)

---

## 1. Application Overview

Games4Cash is a UK-focused web application that allows users to sell their physical video games for cash. The core user journey is:

1. User browses games by title, platform, or genre (powered by the IGDB API)
2. Each game page shows a cash offer price (computed from live Steam/CheapShark data)
3. Users add games to a **Cash Basket** and select the condition of each game
4. They submit a **Cash Quote** with a pickup address
5. The admin receives an email notification, reviews the order, and updates the status

The application also includes a blog, FAQ, contact form, wishlist, recently viewed games, and several content/engagement pages (gaming timeline, gaming legends, snake game).

---

## 2. Directory Structure

```
app/
  Http/
    Controllers/     — All request handlers (see §6)
    Middleware/      — Custom middleware (see §4)
  Mail/              — Mailable classes (see §11)
  Models/            — Eloquent models (see §7)
  Rules/             — Custom validation rules
  Services/          — Business logic services (see §8)
  helpers.php        — Global helper functions (igdb_img())

config/
  igdb.php           — Platform/genre config, IGDB credentials (see §13)
  mail.php           — Mail driver configuration

database/
  migrations/        — ~45 migration files
  database.sqlite    — SQLite database file

resources/
  views/
    layouts/         — app.blade.php (main layout)
    admin/           — All admin panel views
    auth/            — Login, register, password reset views
    blog/            — Blog index and post views
    emails/          — HTML email templates
    pages/           — Static pages (about, faq, contact, timeline, etc.)
    components/      — Reusable Blade components (game-card)

routes/
  web.php            — All application routes

public/
  css/style.css      — Single compiled stylesheet
  js/main.js         — Single compiled JS file
```

---

## 3. Authentication & User System

### Registration

Route: `POST /register` → `AuthController::register()`

Validation rules:
- `first_name`, `surname` — required strings
- `email` — unique, valid email format
- `contact_number` — 7–20 digits, allows spaces/dashes/brackets
- `username` — 12–30 chars, alphanumeric/dashes/underscores, must contain at least one number, must be unique
- `password` — min 12 characters, must contain numbers and symbols, must not be on the blacklisted passwords list (checked via `NotCommonPassword` rule)

On success: user is logged in immediately, a **Welcome Email** is sent to the user, and an **Admin New User alert** is sent to the configured admin notification email.

### Login

Route: `POST /login` → `AuthController::login()`

Requires both `username` and `email` to match the same user record (dual-field authentication). Failed attempts are logged to `login_attempts` and the `activity_logs` table. Successful logins are also logged with IP address and geolocation.

If the user has `force_password_reset = true`, they are redirected to `/password/reset-required` before accessing any other page.

### Password Reset

Standard Laravel token-based flow:
- `POST /forgot-password` — sends a reset link email (throttled)
- `GET /reset-password/{token}` — shows reset form
- `POST /reset-password` — validates token and updates password

### Force Password Reset

Admins can flag individual users (or all users) to reset their password on next login. This is enforced by the `CheckForcePasswordReset` middleware which intercepts all authenticated requests and redirects to `/password/reset-required`.

### User Roles

The `users` table has a `role` column with two values:
- `user` — standard customer account
- `admin` — full access to the admin panel

The `User::isAdmin()` method checks this. Admin routes are protected by the `EnsureIsAdmin` middleware.

### Security Settings

Authenticated users can access `/security` to change their password. The current password is verified before allowing a change.

---

## 4. Middleware

All middleware lives in `app/Http/Middleware/`. Registration is in `bootstrap/app.php`.

### Applied to all web routes (global)

| Middleware | Class | Purpose |
|---|---|---|
| Track Page Views | `TrackPageView` | Records GET requests to `page_views` table for analytics. Skips admin routes, bots, asset proxy. |
| Security Headers | `SecurityHeaders` | Adds X-Frame-Options, X-Content-Type-Options, HSTS, CSP, Permissions-Policy headers to every response. Removes X-Powered-By. |
| Detect Suspicious Input | `DetectSuspiciousInput` | Scans all request input for SQL injection, XSS, path traversal, command injection, SSRF, template injection, and null bytes. Logs matches to activity_logs without blocking the request. |
| Force Reset Check | `CheckForcePasswordReset` | Redirects users with `force_password_reset = true` to the password reset page. Bypassed for the reset route itself and logout. |

### Applied to specific route groups

| Alias | Class | Applied to |
|---|---|---|
| `auth` | Laravel built-in | All authenticated routes |
| `track.active` | `TrackLastActive` | Authenticated routes — updates `last_active_at` at most once per 5 minutes |
| `force.reset` | `CheckForcePasswordReset` | Most authenticated routes (not the force-reset route itself) |
| `admin` | `EnsureIsAdmin` | All `/admin/*` routes — checks `isAdmin()`, logs security event and force-logs-out non-admins |
| `guest` | Laravel built-in | Login/register routes — redirects authenticated users away |

### Rate Limiting

Custom throttle keys are registered in `bootstrap/app.php`:
- `throttle:login` — 5 attempts per minute per IP
- `throttle:register` — 3 attempts per minute per IP
- `throttle:password-reset` — 3 attempts per minute per IP
- `throttle:60,1` — Search route (60 requests per minute per IP)
- `throttle:30,1` — Postcode lookup endpoint

---

## 5. Routing

All routes are defined in `routes/web.php`. Key groupings:

### Public routes
- `GET /` — Home page
- `GET /game/{slug}` — Game detail page (canonical slug URL)
- `GET /game/{id}` — Legacy numeric ID (301 redirects to slug)
- `GET /search` — Browse/search games
- `GET /platform/{id}/{name}` — Platform games page
- `GET /platforms` — All platforms overview
- `GET /genre/{id}/{name}` — Genre games page
- `GET /genres` — All genres overview
- `GET /blog`, `GET /blog/{slug}` — Blog
- `GET /gaming-timeline`, `GET /gaming-legends` — Content pages
- `GET /snake`, `POST /snake/score` — Snake game + leaderboard
- `GET /sitemap.xml`, `GET /robots.txt` — SEO

### Auth routes (guest only)
- `GET/POST /register`
- `GET/POST /login`
- `GET/POST /forgot-password`
- `GET/POST /reset-password/{token}`

### Authenticated routes (`auth` + `track.active` + `force.reset`)
- `GET/PUT/DELETE /profile`
- `GET /security`, `PUT /security/password`
- `GET /recently-viewed`
- `GET/POST/DELETE /wishlist`
- `GET/POST/PATCH/DELETE /cash-basket`
- `GET/POST /cash-orders`, `GET /cash-orders/create`, `GET /cash-orders/{ref}`, `POST /cash-orders/{ref}/cancel`

### Admin routes (`auth` + `track.active` + `admin`, prefix `/admin`)
- Dashboard, users, blacklist, contact submissions, activity logs, analytics
- Game prices, no-price review, settings, email templates
- Blog and FAQ management
- Orders management

---

## 6. Controllers

### Public / User-facing

**`HomeController`**  
Renders the homepage. Fetches 4 lists from IGDB: trending, top rated, recent releases, and upcoming games. Also loads recently viewed games from the session. All lists are passed through the price sync pipeline before rendering.

**`GameController`**  
Two entry points: `showBySlug()` (canonical) and `show()` (legacy numeric ID, redirects to slug). Fetches full game detail from IGDB, resolves platform data, syncs pricing, and renders the game page. Also records the game to the session's recently viewed list.

**`SearchController`**  
Handles three modes: keyword search (`?q=`), franchise filter (`?franchise=`), and default trending browse. Franchise and trending results are cached for 10 minutes. All results pass through `stripFreeGames()`, `HiddenGame::strip()`, `NoPriceReview::strip()`, and `PriceSyncService::ensureForGames()`.

**`PlatformController` / `GenreController`**  
Individual platform/genre game listing pages. Fetch games from IGDB filtered by platform or genre ID, run through the standard price pipeline.

**`PlatformsController` / `GenresController`**  
Overview pages listing all platforms/genres with sample games. Games per section are cached for 6 hours.

**`BlogController`**  
Respects the `blog_visible` setting (admin-toggleable). Lists published posts paginated by 9. Individual posts are fetched by slug.

**`ContactController`**  
Saves contact form submissions to `contact_submissions`. Rate-limited at the route level.

**`SitemapController`**  
Generates `sitemap.xml` including static pages, platform pages, genre pages, blog posts, and content pages.

**`ImageProxyController`**  
Proxies IGDB cover images through the application. Accepts a base64url-encoded image path, fetches from IGDB, caches locally in `storage/app/img-cache/`, and serves with 30-day cache headers. Prevents direct exposure of IGDB URLs.

**`RecentlyViewedController`**  
Reads the `recently_viewed` session key (array of IGDB game IDs), fetches the games from IGDB, and renders the page.

### Authentication

**`AuthController`** — Register, login, logout  
**`PasswordResetController`** — Forgot password flow  
**`ForcePasswordResetController`** — Handles forced reset on next login  
**`SecurityController`** — Change password for authenticated users  
**`ProfileController`** — View/update/delete user account

### Cash Flow

**`WishlistController`**  
Add/remove/list games in the user's wishlist. Store saves IGDB game ID, title, and cover URL.

**`CashBasketController`**  
Add/remove games, update condition per item. The `updateCondition()` method returns JSON with the recalculated item price, basket total, and whether all items have a condition selected — consumed by the basket page's AJAX handler.

**`CashOrderController`**  
- `create()` — shows checkout page, validates basket has items with conditions and meets the minimum order value
- `store()` — creates the `CashOrder` record, clears the basket, sends confirmation email to user and alert email to admin
- `confirmation()` — shows the post-submission confirmation page
- `show()` — order detail for the user
- `cancel()` — allows cancellation within the configured window (default 2 hours)

### Admin

**`AdminController`**  
The primary admin controller. Handles: dashboard stats, user list/detail/delete/force-reset, blacklisted passwords, contact submissions, activity logs (list/delete/clear/export CSV), email templates (view/save/test), and global settings.

**`AdminAnalyticsController`**  
Renders the analytics page with page view data, visitor counts, top pages, and referrer stats — all read from the `page_views` table.

**`AdminGamePricesController`**  
Paginated table of all `game_prices` records. Supports filtering by source (steam/cheapshark) and searching by title. Allows inline price overrides per platform and hiding specific platform/game combinations. Breakdown view shows the step-by-step price calculation.

**`AdminNoPriceController`**  
Lists all `game_prices` rows that have no effective price (no Steam, no CheapShark, no override). Allows the admin to manually set a price or dismiss a record from review.

**`AdminBlogController`** / **`AdminFaqController`**  
Full CRUD for blog posts and FAQ entries. Blog posts support a rich-text content field and a published_at datetime for scheduling.

### Content Pages

**`GamingTimelineController`**  
Holds 35 historical gaming events (1958–2024). Makes a single IGDB batch query for game slugs to fetch cover art, cached for 24 hours. Passes events and an image map to the view.

**`GamingLegendsController`**  
Holds data for 20 notable people in gaming. Fetches headshot images from Wikipedia's pageimages API in a single batch call, cached for 24 hours. Falls back to an initials avatar if no photo is found.

**`SnakeController`**  
Renders the snake game page with the top 10 leaderboard. `store()` validates a submitted score (name max 30 chars, score max 99999), saves it, and returns the player's rank and the updated top 10 as JSON.

---

## 7. Models

### User
**Table:** `users`  
**Key fields:** `first_name`, `surname`, `username`, `email`, `contact_number`, `password`, `role` (`user`/`admin`), `force_password_reset` (boolean), `last_active_at`  
**Relationships:** hasMany `LoginAttempt`, `Wishlist`, `CashBasketItem`, `CashOrder`  
**Key method:** `isAdmin()` — returns `role === 'admin'`

### GamePrice
**Table:** `game_prices` | **Primary key:** `igdb_game_id` (not auto-incrementing)  
**Key fields:** `game_title`, `slug`, `steam_app_id`, `platform_ids` (JSON array of IGDB platform IDs), `franchise_names` (JSON array), `is_free`, `steam_gbp`, `cheapshark_usd`, `base_price_gbp`, `price_overrides` (JSON, keyed by platform ID)  
**Key methods:**
- `getComputedPrice(array $franchiseNames, string $title)` — runs the full pricing formula and returns a display-ready array
- `stripFreeGames(array $games)` — filters IGDB game array to remove free-to-play titles
- `urlForId(int $igdbId)` — returns the canonical game URL

### CashOrder
**Table:** `cash_orders`  
**Key fields:** `order_ref` (format: `GC-XXXXXXXX`), `user_id`, `status` (`pending`/`contacted`/`completed`/`cancelled`), `items` (JSON array of order lines), `total_gbp`, `admin_notes`, full address fields  
**Key methods:**
- `canCancel()` — checks status is pending and within `cancel_window_minutes` setting
- `generateRef()` — generates a unique reference
- `statusLabel()` / `statusClass()` — display helpers

### Setting
**Table:** `settings` | **Primary key:** `key` (string)  
Used throughout the app for runtime-configurable values. All read/write goes through `Setting::get(key, default)` and `Setting::set(key, value)`.

**Known settings keys:**

| Key | Default | Purpose |
|---|---|---|
| `pricing_discount_percent` | 85 | % of market price offered to customer |
| `usd_to_gbp_rate` | 1.36 | Exchange rate for CheapShark USD prices |
| `age_reduction_per_year` | 1 | £ reduction per year since release |
| `min_order_gbp` | 20 | Minimum basket value to submit a quote |
| `cancel_window_minutes` | 120 | How long users can cancel a submitted order |
| `condition_new_pct` | +20 | % price adjustment for Brand New condition |
| `condition_complete_pct` | 0 | % adjustment for Complete (In Case) |
| `condition_disk_pct` | -50 | % adjustment for Disk Only |
| `low_price_boost_gbp` | 0.10 | Minimum price floor to keep offers meaningful |
| `platform_modifier_{id}` | 0 | Per-platform price modifier |
| `platform_modifier_type_{id}` | percent | `percent` or `gbp` |
| `blog_visible` | true | Show/hide blog across entire site |
| `admin_notification_email` | — | Where new user/order alerts are sent |
| `email_*` | — | Editable email template body text |

### Other Models

| Model | Table | Purpose |
|---|---|---|
| `CashBasketItem` | `cash_basket_items` | Items in a user's active basket |
| `Wishlist` | `wishlists` | Saved games (no pricing logic) |
| `LoginAttempt` | `login_attempts` | Per-user login history with IP and location |
| `ActivityLog` | `activity_logs` | Audit log (search, login, filter, quote, security events) |
| `BlacklistedPassword` | `blacklisted_passwords` | Disallowed passwords checked at registration |
| `HiddenGame` | `hidden_games` | Admin-hidden game/platform combinations |
| `NoPriceReview` | `no_price_reviews` | Games flagged as having no obtainable price |
| `FranchiseAdjustment` | `franchise_adjustments` | Price adjustments for specific franchises |
| `ContactSubmission` | `contact_submissions` | Contact form submissions with read/unread state |
| `PageView` | `page_views` | Anonymous page view analytics |
| `BlogPost` | `blog_posts` | Blog content with scheduling support |
| `Faq` | `faqs` | FAQ entries with sort order |
| `SnakeScore` | `snake_scores` | Snake game leaderboard |

---

## 8. Services

### IgdbService (`app/Services/IgdbService.php`)
The central service for all IGDB API communication. Handles OAuth2 token management (token cached to `storage/app/igdb_token.json`, auto-refreshed on expiry).

**Core method:** `query(string $endpoint, string $body): array` — executes any IGDB query language request.

**Convenience methods:** `getTrendingGames()`, `searchGames()`, `getGame()`, `getGameBySlug()`, `getGamesByPlatform()`, `getGamesByFranchise()`, `getGamesByGenre()`, `getSteamAppId()`, and others.

Mobile-platform games (Android, iOS, iPad: platform IDs 34, 39, 55, 122) are filtered out of results.

### PriceSyncService (`app/Services/PriceSyncService.php`)
Ensures `game_prices` records exist and are up to date for a given array of IGDB games. Called after every IGDB fetch.

Flow:
1. Checks which games already have fresh price records (updated < 6 hours ago)
2. Batch-fetches Steam App IDs from IGDB for games that need syncing
3. Fetches Steam prices in parallel (6 at a time)
4. Fetches CheapShark prices in parallel
5. Upserts `game_prices` records
6. Creates `NoPriceReview` entries for paid games with no price data

### CexService (`app/Services/CexService.php`)
Fetches cash and exchange prices from the CeX API by game title. Results are cached for 24 hours. Uses title similarity matching (>65% threshold) to avoid false positives. Maps CeX category names to IGDB platform IDs.

### ActivityLogger (`app/Services/ActivityLogger.php`)
Static facade for writing to the activity log. Methods: `search()`, `login()`, `filter()`, `quote()`, `security()`. Each writes a typed record to `activity_logs` with the user ID (if authenticated) and IP address.

### GeoLocationService (`app/Services/GeoLocationService.php`)
Resolves IP addresses to human-readable locations using ip-api.com. Returns `"Local"` for loopback, `"Private Network"` for RFC1918 ranges, or `"City, Region, Country"` for public IPs. Used for login attempt records.

---

## 9. Pricing Engine

The pricing engine is the core business logic of the application. It lives in `GamePrice::getComputedPrice()` and is driven by admin-configurable settings.

### Formula

```
base_price = cheapshark_usd × (1 / usd_to_gbp_rate)
             OR steam_gbp (whichever is available; CheapShark preferred)

franchise_adj = sum of FranchiseAdjustment.adjustment_gbp for matching franchises

platform_mod = platform_modifier_{id} setting (% or £ absolute)

age_reduction = age_reduction_per_year × years_since_release

price = (base_price + franchise_adj) × (pricing_discount_percent / 100)
        ± platform_mod
        - age_reduction

if price < 0.05: price = low_price_boost_gbp (floor)
if price < 0.01: price = 0 (free/worthless)

condition_multiplier:
  new      → price × (1 + condition_new_pct / 100)       [e.g. +20%]
  complete → price × (1 + condition_complete_pct / 100)   [e.g. ±0%]
  disk     → price × (1 + condition_disk_pct / 100)       [e.g. -50%]

price_override: if set for a specific platform, replaces the computed base entirely
```

The admin can inspect the full breakdown for any game/platform combination at `/admin/game-prices/{igdbId}/{platformId}/breakdown`.

---

## 10. Admin Panel

Accessible at `/admin` — requires `role = admin`. Protected by `EnsureIsAdmin` middleware which logs and force-logs-out any non-admin who attempts access.

### Sections

| Section | URL | Purpose |
|---|---|---|
| Dashboard | `/admin` | Key stats: users, orders, no-price count, page views |
| Users | `/admin/users` | List, view detail, force-reset, delete users |
| Blacklist | `/admin/blacklist` | Manage disallowed passwords |
| Contact | `/admin/contact-submissions` | View/delete contact form submissions |
| Activity Logs | `/admin/activity-logs` | Filterable audit log with CSV export |
| Analytics | `/admin/analytics` | Page views, top pages, referrers |
| Game Prices | `/admin/game-prices` | Search/filter all game price records, set overrides, hide platform entries |
| No-Price Review | `/admin/no-price-review` | Games with no obtainable price — set manually or dismiss |
| Orders | `/admin/orders` | All cash quote orders, filter by status, update status, add notes |
| Settings | `/admin/settings` | All pricing parameters, condition modifiers, platform modifiers, blog visibility |
| Email Templates | `/admin/email-templates` | Edit body text for all transactional emails, set admin notification address, send test emails |
| Blog | `/admin/blog` | Create/edit/delete blog posts, toggle visibility |
| FAQs | `/admin/faqs` | Create/edit/delete/reorder FAQ entries |

---

## 11. Email System

Mail is sent using Laravel's `Mail` facade. The driver is configured in `.env` (`MAIL_MAILER`).

### Mailable Classes

| Class | Trigger | Recipient |
|---|---|---|
| `WelcomeEmail` | User registration | New user |
| `PasswordResetMail` | Forgot password request | User |
| `OrderConfirmationMail` | Quote submission | User |
| `AdminNewUserMail` | User registration | Admin notification email |
| `AdminNewQuoteMail` | Quote submission | Admin notification email |

### Template Customisation

All email body text is stored in the `settings` table and editable via **Admin → Email Templates**. The admin notification email address is also stored there (`admin_notification_email`).

Available placeholders: `{first_name}`, `{site_name}`, `{username}`, `{email}`, `{order_ref}`, `{total}`, `{items_count}`.

Admin emails fail silently — if delivery fails, the user-facing action (registration or order submission) is not affected.

---

## 12. Database Schema

The application uses **SQLite** (single file at `database/database.sqlite`). Below are the key tables and their most important columns.

### users
```
id, name, first_name, surname, username (unique), email (unique),
contact_number, password, role (user/admin), force_password_reset,
last_active_at, remember_token, created_at, updated_at
```

### game_prices
```
igdb_game_id (PK), game_title, slug, steam_app_id, release_date,
platform_ids (JSON), franchise_names (JSON), is_free, is_bundle,
steam_gbp, cheapshark_usd, base_price_gbp,
price_overrides (JSON: {platformId: gbpValue}), updated_at
```

### cash_orders
```
id, order_ref (unique), user_id (FK), status, items (JSON),
total_gbp, admin_notes, house_name_number, address_line1,
address_line2, address_line3, city, county, postcode,
agreed_terms, confirmed_contents, created_at, updated_at
```

### cash_basket_items
```
id, user_id (FK), igdb_game_id, platform_id, condition
(new/complete/disk/null), game_title, cover_url, steam_app_id, release_date
```

### activity_logs
```
id, user_id (FK, nullable), type (search/login/filter/quote/security),
description, ip_address, created_at
```

### settings
```
key (PK, string), value, updated_at
```

### page_views
```
id, session_id, ip_hash, path, referrer, created_at
```

### blog_posts
```
id, title, slug (unique), content (HTML), excerpt, author,
image (gaming/news/review/deals), published_at (nullable), created_at, updated_at
```

---

## 13. Configuration

### `config/igdb.php`

The main application config file. Contains:

- **`client_id` / `client_secret`** — Twitch/IGDB API credentials (from `.env`)
- **`platforms`** — 7 detailed platform objects. Each has: `id` (IGDB platform ID), `icon`, `short`, `slug`, `desc`, `highlights` (array of 5 bullet points), `seo` (heading, body, body2 for SEO content)
- **`all_platforms`** — 12 platforms mapped `id → name`, used in admin dropdowns
- **`genres`** — 12 genre `name → IGDB ID` mappings
- **`genre_descriptions`** — SEO heading and body text per genre ID
- **`franchises`** — 12 franchise name strings for the search franchise filter

### `.env` keys required

```
APP_NAME=
APP_URL=
APP_KEY=

DB_CONNECTION=sqlite

MAIL_MAILER=
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=

IGDB_CLIENT_ID=
IGDB_CLIENT_SECRET=
```

---

## 14. External API Integrations

### IGDB (via Twitch OAuth)
- **Purpose:** All game data — titles, covers, platforms, genres, release dates, franchises
- **Auth:** Client credentials OAuth2. Token cached to `storage/app/igdb_token.json`
- **Rate limits:** Handled by IGDB (4 req/s on free tier). The app does not implement its own IGDB rate limiting beyond caching
- **Credentials:** `IGDB_CLIENT_ID` + `IGDB_CLIENT_SECRET` in `.env`

### Steam Store API
- **Purpose:** UK GBP prices for games
- **Auth:** None required
- **Endpoint:** `https://store.steampowered.com/api/appdetails?appids={id}&cc=gb`
- **Caching:** Prices cached 6 hours in `game_prices.steam_gbp`

### CheapShark API
- **Purpose:** All-time low USD prices (broader coverage than Steam alone)
- **Auth:** None required
- **Endpoint:** `https://www.cheapshark.com/api/1.0/`
- **Caching:** Prices cached 6 hours in `game_prices.cheapshark_usd`

### postcodes.io
- **Purpose:** UK postcode lookup on the checkout page (auto-fill city/county)
- **Auth:** None required — fully free and open
- **Implementation:** Laravel route `/postcode-lookup/{postcode}` proxies the request server-side to avoid browser CORS issues

### ip-api.com
- **Purpose:** Geolocation of IP addresses for login attempt records
- **Auth:** None required on the free tier
- **Timeout:** 2 seconds — fails silently if unavailable

### Wikipedia API
- **Purpose:** Headshot photos for the Gaming Legends page
- **Auth:** None required
- **Implementation:** Single batch request for all people, cached 24 hours

---

## 15. SEO & Sitemap

### Meta tags
Every page sets title, meta description, canonical URL, Open Graph tags, and Twitter Card tags via the main layout (`layouts/app.blade.php`). Individual views override these using `@section('title')`, `@section('meta_description')`, etc.

### Structured data (JSON-LD)
- **Blog posts:** `BlogPosting` schema + `BreadcrumbList`
- All structured data is rendered inline in `<head>` via `@push('head_meta')`

### Sitemap (`/sitemap.xml`)
Generated dynamically by `SitemapController`. Includes:
- Home, Search, Platforms overview, Genres overview (high priority)
- All individual platform and genre pages
- Gaming Timeline, Gaming Legends, Snake (content pages)
- Blog index and all published blog posts

### robots.txt (`/robots.txt`)
Generated dynamically. Disallows:
- All admin/auth/account pages (`/admin`, `/login`, `/register`, `/profile`, `/security`, `/cash-basket`, `/cash-orders`, `/wishlist`, `/recently-viewed`, `/password`)
- Internal API endpoints (`/snake/score`, `/img/`, `/up`)
- References the sitemap URL

---

## 16. Content Pages

Beyond the core commerce flow, the application includes several content/engagement pages:

| Page | Route | Controller | Description |
|---|---|---|---|
| Gaming Timeline | `/gaming-timeline` | `GamingTimelineController` | 35 historical events 1958–2024 with IGDB cover art, cached 24h |
| Gaming Legends | `/gaming-legends` | `GamingLegendsController` | 20 notable people in gaming with Wikipedia photos, filter by category |
| Snake | `/snake` | `SnakeController` | Playable Snake game with persistent leaderboard |
| Blog | `/blog` | `BlogController` | Admin-managed blog, togglable via settings |
| FAQ | `/faq` | Route closure | Reads from `faqs` table, admin-managed sort order |
| About | `/about` | Static view | — |
| Contact | `/contact` | `ContactController` | Form saved to DB, admin-viewable |
| Gaming-Timeline | `/gaming-timeline` | `GamingTimelineController` | — |
| Sitemap (HTML) | `/sitemap` | Static view | Human-readable sitemap |
