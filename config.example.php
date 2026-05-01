<?php
// ============================================================
//  IGDB / Twitch API Credentials
//  1. Go to https://dev.twitch.tv/console and create an app
//  2. Set OAuth Redirect URL to: http://localhost
//  3. Client Type: Confidential
//  4. Paste your Client ID and Client Secret below
// ============================================================

define('IGDB_CLIENT_ID',     'YOUR_TWITCH_CLIENT_ID');
define('IGDB_CLIENT_SECRET', 'YOUR_TWITCH_CLIENT_SECRET');

define('IGDB_BASE_URL',   'https://api.igdb.com/v4');
define('TWITCH_TOKEN_URL','https://id.twitch.tv/oauth2/token');

// Token cache file (writable by web server)
define('TOKEN_CACHE_FILE', __DIR__ . '/cache/token.json');

// Image base
define('IGDB_IMG', 'https://images.igdb.com/igdb/image/upload');

// Site info
define('SITE_NAME', 'GameVault');
define('SITE_TAGLINE', 'Discover. Explore. Play.');
define('SITE_URL', 'https://your-domain.com');
