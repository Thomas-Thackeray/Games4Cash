# Changelog

All notable changes to Games4Cash will be documented here.
Format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [Unreleased]

### Fixed
- Mobile navigation hamburger menu not visible on screens ≤520px due to header search bar pushing nav-toggle off screen — hide search bar at that breakpoint

---

## [1.0.0] — 2026-04-15

Initial production release at https://games4cash.co.uk

### Added
- Game catalogue powered by IGDB API — browse by platform, genre, search
- Game detail pages with cover art, screenshots, ratings, release info, and similar games
- Get Cash feature — users can see estimated trade-in prices per platform/condition and add games to a cash basket
- Cash basket — add/remove games, set condition (Brand New / Complete / Just Disk), condition modifiers applied to base price
- Cash order (quote) submission — collects pickup address, terms acceptance, sends order confirmation email
- Order cancellation — users may cancel pending orders within 2 hours of submission
- My Submissions page — paginated list of all user quotes with status badges
- Order detail page — shows items, total, pickup address, cancellation window timer
- User registration and login with username, email, password
- Password reset flow — forgot password email with branded reset link (anti-enumeration)
- Force password reset — admin can require individual users or all users to reset on next login
- User profile page — update first name, surname, username, contact number
- Security page — change password
- Wishlist — add/remove games, persisted per user
- Admin dashboard — overview stats (users, orders, revenue, activity)
- Admin user management — view, delete users; force password reset per user or globally
- Admin cash orders — list all orders, view detail, update status (pending / contacted / completed / cancelled), add admin notes
- Admin settings — minimum order value, condition modifiers (new/complete/disk %), per-console price modifiers
- Admin FAQ management — add, edit, reorder, delete FAQ entries
- Admin contact submissions — view and delete contact form messages
- Admin activity logs — all events (auth, security, quote, admin actions) with type filter and IP logging
- Admin blacklisted passwords list — add/remove common passwords to block on registration/reset
- Contact Us page with form submission stored to DB
- FAQ page loaded dynamically from database
- About Us, Terms & Conditions (UK), Privacy Policy (UK GDPR), Sitemap pages
- Custom 404, 403, 500 error pages
- Cookie consent banner (essential cookies only)
- Back-to-top button
- Confirmation modal for destructive actions
- Rate limiting — login (5/min), register (3/hr), password reset (5/15min)
- Security middleware — non-admin users attempting /admin are logged out, incident logged
- Brevo transactional email via HTTP API (SMTP-free) — order confirmations, password resets
- SPF, DKIM, DMARC DNS records configured for games4cash.co.uk
- robots.txt and XML sitemap
- Responsive design — desktop and mobile navigation
- GitHub repository with main (protected) and develop branch workflow
- DigitalOcean VPS deployment — Ubuntu 22.04, Nginx, PHP 8.2, SQLite
- Let's Encrypt SSL certificate via Certbot
- GoDaddy DNS — A records and www CNAME pointing to DigitalOcean droplet

---

*Older entries will be added as history is reconstructed.*
