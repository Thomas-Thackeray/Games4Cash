<?php
require_once __DIR__ . '/config.php';

class IGDB {

    private string $clientId;
    private string $clientSecret;
    private string $accessToken;

    public function __construct() {
        $this->clientId     = IGDB_CLIENT_ID;
        $this->clientSecret = IGDB_CLIENT_SECRET;
        $this->accessToken  = $this->getAccessToken();
    }

    // ----------------------------------------------------------
    //  OAuth token management (cached to file)
    // ----------------------------------------------------------
    private function getAccessToken(): string {
        $cacheDir = dirname(TOKEN_CACHE_FILE);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        if (file_exists(TOKEN_CACHE_FILE)) {
            $data = json_decode(file_get_contents(TOKEN_CACHE_FILE), true);
            if ($data && isset($data['expires_at']) && time() < $data['expires_at']) {
                return $data['access_token'];
            }
        }

        return $this->fetchNewToken();
    }

    private function fetchNewToken(): string {
        $url = TWITCH_TOKEN_URL . '?' . http_build_query([
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type'    => 'client_credentials',
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (empty($data['access_token'])) {
            throw new RuntimeException('Failed to obtain IGDB access token. Check your Client ID and Secret.');
        }

        $cache = [
            'access_token' => $data['access_token'],
            'expires_at'   => time() + $data['expires_in'] - 300, // 5 min buffer
        ];
        file_put_contents(TOKEN_CACHE_FILE, json_encode($cache));
        return $data['access_token'];
    }

    // ----------------------------------------------------------
    //  Core query method
    // ----------------------------------------------------------
    public function query(string $endpoint, string $body): array {
        $ch = curl_init(IGDB_BASE_URL . '/' . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Client-ID: '    . $this->clientId,
                'Authorization: Bearer ' . $this->accessToken,
                'Accept: application/json',
            ],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return [];
        }
        return json_decode($response, true) ?? [];
    }

    // ----------------------------------------------------------
    //  Helper: image URL
    // ----------------------------------------------------------
    public static function imgUrl(string $imageId, string $size = 'cover_big'): string {
        return IGDB_IMG . "/t_{$size}/{$imageId}.jpg";
    }

    // ----------------------------------------------------------
    //  Convenience methods
    // ----------------------------------------------------------

    /** Trending / popular games */
    public function getTrendingGames(int $limit = 12): array {
        $body = "fields id,name,cover.image_id,rating,genres.name,platforms.abbreviation,first_release_date;
                 where rating != null & cover != null & version_parent = null & themes != (42);
                 sort rating_count desc;
                 limit {$limit};";
        return $this->query('games', $body);
    }

    /** Highly rated games */
    public function getTopRated(int $limit = 8): array {
        $body = "fields id,name,cover.image_id,rating,rating_count,genres.name,platforms.abbreviation,first_release_date;
                 where rating > 85 & rating_count > 200 & cover != null & version_parent = null & themes != (42);
                 sort rating desc;
                 limit {$limit};";
        return $this->query('games', $body);
    }

    /** Recently released games */
    public function getRecentGames(int $limit = 8): array {
        $now = time();
        $sixMonthsAgo = $now - (60 * 60 * 24 * 180);
        $body = "fields id,name,cover.image_id,rating,genres.name,platforms.abbreviation,first_release_date;
                 where first_release_date > {$sixMonthsAgo} & first_release_date < {$now} & cover != null & version_parent = null;
                 sort first_release_date desc;
                 limit {$limit};";
        return $this->query('games', $body);
    }

    /** Upcoming games */
    public function getUpcomingGames(int $limit = 8): array {
        $now = time();
        $body = "fields id,name,cover.image_id,genres.name,platforms.abbreviation,first_release_date;
                 where first_release_date > {$now} & cover != null & version_parent = null;
                 sort first_release_date asc;
                 limit {$limit};";
        return $this->query('games', $body);
    }

    /** Search games */
    public function searchGames(string $term, int $limit = 20, int $offset = 0): array {
        $safe = addslashes($term);
        $body = "fields id,name,cover.image_id,rating,genres.name,platforms.abbreviation,first_release_date;
                 search \"{$safe}\";
                 where version_parent = null & cover != null;
                 limit {$limit};
                 offset {$offset};";
        return $this->query('games', $body);
    }

    /** Single game detail */
    public function getGame(int $id): ?array {
        $body = "fields id,name,summary,storyline,cover.image_id,rating,rating_count,
                 genres.name,platforms.name,platforms.abbreviation,
                 screenshots.image_id,artworks.image_id,
                 involved_companies.company.name,involved_companies.developer,involved_companies.publisher,
                 game_modes.name,themes.name,
                 first_release_date,websites.url,websites.category,
                 similar_games.name,similar_games.cover.image_id,similar_games.rating,
                 videos.video_id,videos.name;
                 where id = {$id};
                 limit 1;";
        $results = $this->query('games', $body);
        return $results[0] ?? null;
    }

    /** Games by platform ID */
    public function getGamesByPlatform(int $platformId, int $limit = 24, int $offset = 0): array {
        $body = "fields id,name,cover.image_id,rating,genres.name,first_release_date;
                 where platforms = ({$platformId}) & cover != null & version_parent = null & themes != (42) & rating != null;
                 sort rating_count desc;
                 limit {$limit};
                 offset {$offset};";
        return $this->query('games', $body);
    }

    /** Games by genre ID */
    public function getGamesByGenre(int $genreId, int $limit = 24, int $offset = 0): array {
        $body = "fields id,name,cover.image_id,rating,genres.name,platforms.abbreviation,first_release_date;
                 where genres = ({$genreId}) & cover != null & version_parent = null & themes != (42) & rating > 70;
                 sort rating_count desc;
                 limit {$limit};
                 offset {$offset};";
        return $this->query('games', $body);
    }

    /** Get platform info */
    public function getPlatform(int $id): ?array {
        $body = "fields id,name,abbreviation,summary,logo.image_id;
                 where id = {$id};
                 limit 1;";
        $results = $this->query('platforms', $body);
        return $results[0] ?? null;
    }
}
