# GameVault – IGDB-Powered Gaming Website

A full gaming portal website inspired by TheGameWorld, powered by the IGDB API.
Built with PHP, HTML, CSS, and JavaScript.

---

## 📁 File Structure

```
gameportal/
├── index.php          ← Homepage (trending, top rated, recent, upcoming)
├── game.php           ← Game detail page
├── search.php         ← Search & browse
├── platform.php       ← Platform browser (PS5, Xbox, Nintendo, PC…)
├── genre.php          ← Genre browser
├── config.php         ← ⚠️ YOUR API CREDENTIALS GO HERE
├── igdb.php           ← IGDB API wrapper class
├── helpers.php        ← Shared functions (header, footer, cards, etc.)
├── .htaccess          ← Apache URL rewriting + security headers
├── cache/             ← Auto-created; stores OAuth token (needs write perms)
└── assets/
    ├── css/style.css
    └── js/main.js
```

---

## ⚙️ Setup (5 minutes)

### 1. Get IGDB / Twitch API credentials

1. Go to **https://dev.twitch.tv/console**
2. Log in with a free Twitch account (or create one)
3. Click **Register Your Application**
   - Name: anything you like
   - OAuth Redirect URLs: `http://localhost`
   - Category: **Website Integration**
   - Client Type: **Confidential**
4. After creating the app, click **Manage**
5. Note your **Client ID**
6. Click **New Secret** to generate a **Client Secret**

### 2. Add credentials to config.php

```php
define('IGDB_CLIENT_ID',     'your_client_id_here');
define('IGDB_CLIENT_SECRET', 'your_client_secret_here');
```

### 3. Deploy to a PHP server

**Option A – Local development**
```bash
# PHP built-in server (from the gameportal/ directory)
php -S localhost:8000

# Then open: http://localhost:8000
```
> Note: .htaccess URL rewriting won't work with PHP's built-in server. All pages will still
> work via direct URLs (game.php?id=123, search.php?q=zelda, etc.)

**Option B – Apache / cPanel hosting**
- Upload all files to your `public_html` (or subdirectory)
- Make sure `mod_rewrite` is enabled
- The `cache/` folder will be auto-created with write permissions

**Option C – Nginx**
Add this to your server block:
```nginx
location / {
    try_files $uri $uri/ @php;
}
location @php {
    rewrite ^/game/([0-9]+)/?$                /game.php?id=$1 last;
    rewrite ^/platform/([0-9]+)/(.+)/?$       /platform.php?id=$1&name=$2 last;
    rewrite ^/genre/([0-9]+)/(.+)/?$          /genre.php?id=$1&name=$2 last;
}
```

### 4. Ensure cache/ directory is writable

```bash
mkdir -p cache
chmod 755 cache
```

---

## 🌟 Features

| Feature | Details |
|---|---|
| **Homepage** | Trending, Top Rated, Recent Releases, Upcoming |
| **Game Pages** | Cover art, rating, platforms, developer, summary, screenshots, videos, similar games |
| **Search** | Full-text game search across the IGDB database |
| **Platform Browse** | PS5, PS4, Xbox Series X, Xbox One, Switch, PC |
| **Genre Browse** | Action, RPG, Shooter, Horror, Racing, and more |
| **Responsive** | Mobile-friendly with slide-out nav |
| **Token caching** | OAuth tokens cached to disk (valid ~60 days) |
| **Screenshot lightbox** | Click any screenshot for a full-size overlay |
| **YouTube trailers** | Embedded trailers when available |

---

## 🎨 Design

- **Palette**: Deep dark background (`#080810`) with electric crimson accent (`#e63946`)
- **Typography**: Bebas Neue (display) + Outfit (body)
- **Cards**: Hover lift with glow border
- **Animations**: Scroll-triggered fade-up, smooth hover transitions

---

## 🔑 IGDB API Notes

- **Rate limit**: 4 requests/second, max 8 concurrent — stay well within this on a normal site
- **Token validity**: ~60 days; the site auto-refreshes tokens
- **Image sizes**: `cover_big` (264×374), `screenshot_big` (889×500), `1080p` (1920×1080)
- **Free tier**: Non-commercial use is free under Twitch Developer Service Agreement

---

## 📄 License

For personal/non-commercial use. Game data © IGDB / Twitch.
