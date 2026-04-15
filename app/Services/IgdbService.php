<?php

namespace App\Services;

use RuntimeException;

class IgdbService
{
    private string $clientId;
    private string $clientSecret;
    private string $accessToken;
    private string $cacheFile;

    public function __construct()
    {
        $this->clientId     = config('igdb.client_id');
        $this->clientSecret = config('igdb.client_secret');
        $this->cacheFile    = storage_path('app/igdb_token.json');
        $this->accessToken  = $this->getAccessToken();
    }

    // ----------------------------------------------------------
    //  OAuth token management (cached to file)
    // ----------------------------------------------------------
    private function getAccessToken(): string
    {
        $dir = dirname($this->cacheFile);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (file_exists($this->cacheFile)) {
            $data = json_decode(file_get_contents($this->cacheFile), true);
            if ($data && isset($data['expires_at']) && time() < $data['expires_at']) {
                return $data['access_token'];
            }
        }

        return $this->fetchNewToken();
    }

    private function fetchNewToken(): string
    {
        $url = config('igdb.token_url') . '?' . http_build_query([
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
            throw new RuntimeException('Failed to obtain IGDB access token. Check your Client ID and Secret in .env');
        }

        $cache = [
            'access_token' => $data['access_token'],
            'expires_at'   => time() + $data['expires_in'] - 300,
        ];
        file_put_contents($this->cacheFile, json_encode($cache));
        return $data['access_token'];
    }

    // ----------------------------------------------------------
    //  Core query method
    // ----------------------------------------------------------
    public function query(string $endpoint, string $body): array
    {
        $ch = curl_init(config('igdb.base_url') . '/' . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Client-ID: '             . $this->clientId,
                'Authorization: Bearer '  . $this->accessToken,
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
    //  Convenience methods
    // ----------------------------------------------------------

    public function getTrendingGames(int $limit = 12): array
    {
        $body = "fields id,name,cover.image_id,rating,genres.name,platforms.id,first_release_date;
                 where rating != null & cover != null & version_parent = null & themes != (42);
                 sort rating_count desc;
                 limit {$limit};";
        return $this->query('games', $body);
    }

    public function getTopRated(int $limit = 8): array
    {
        $body = "fields id,name,cover.image_id,rating,rating_count,genres.name,platforms.id,first_release_date;
                 where rating > 85 & rating_count > 200 & cover != null & version_parent = null & themes != (42);
                 sort rating desc;
                 limit {$limit};";
        return $this->query('games', $body);
    }

    public function getRecentGames(int $limit = 8): array
    {
        $now         = time();
        $sixMonthsAgo = $now - (60 * 60 * 24 * 180);
        $body = "fields id,name,cover.image_id,rating,genres.name,platforms.id,first_release_date;
                 where first_release_date > {$sixMonthsAgo} & first_release_date < {$now} & cover != null & version_parent = null;
                 sort first_release_date desc;
                 limit {$limit};";
        return $this->query('games', $body);
    }

    public function getUpcomingGames(int $limit = 8): array
    {
        $now  = time();
        $body = "fields id,name,cover.image_id,genres.name,platforms.id,first_release_date;
                 where first_release_date > {$now} & cover != null & version_parent = null;
                 sort first_release_date asc;
                 limit {$limit};";
        return $this->query('games', $body);
    }

    public function searchGames(string $term, int $limit = 20, int $offset = 0): array
    {
        $safe = addslashes($term);
        $body = "fields id,name,cover.image_id,rating,genres.name,platforms.id,first_release_date;
                 search \"{$safe}\";
                 where version_parent = null & cover != null;
                 limit {$limit};
                 offset {$offset};";
        return $this->query('games', $body);
    }

    public function getGame(int $id): ?array
    {
        $body = "fields id,name,summary,storyline,cover.image_id,rating,rating_count,
                 genres.name,platforms.name,platforms.id,
                 screenshots.image_id,artworks.image_id,
                 involved_companies.company.name,involved_companies.developer,involved_companies.publisher,
                 game_modes.name,themes.name,
                 first_release_date,websites.url,websites.category,
                 similar_games.name,similar_games.cover.image_id,similar_games.rating,similar_games.first_release_date,
                 videos.video_id,videos.name;
                 where id = {$id};
                 limit 1;";
        $results = $this->query('games', $body);
        return $results[0] ?? null;
    }

    public function getGamesByPlatform(int $platformId, int $limit = 24, int $offset = 0): array
    {
        $body = "fields id,name,cover.image_id,rating,genres.name,platforms.id,first_release_date;
                 where platforms = ({$platformId}) & cover != null & version_parent = null & themes != (42) & rating != null;
                 sort rating_count desc;
                 limit {$limit};
                 offset {$offset};";
        return $this->query('games', $body);
    }

    public function getGamesByFranchise(string $franchiseName, int $limit = 24, int $offset = 0): array
    {
        $safe = addslashes($franchiseName);
        $body = "fields id,name,cover.image_id,rating,genres.name,platforms.id,first_release_date;
                 search \"{$safe}\";
                 where cover != null & version_parent = null;
                 limit {$limit};
                 offset {$offset};";
        return $this->query('games', $body);
    }

    public function getGamesByIds(array $ids, int $limit = 24, int $offset = 0): array
    {
        if (empty($ids)) {
            return [];
        }
        $page   = array_slice($ids, $offset, $limit);
        $idList = implode(',', array_map('intval', $page));
        if (empty($idList)) {
            return [];
        }
        $body = "fields id,name,cover.image_id,rating,genres.name,platforms.id,first_release_date;
                 where id = ({$idList}) & cover != null & version_parent = null;
                 sort rating_count desc;
                 limit {$limit};";
        return $this->query('games', $body);
    }

    public function getGamesByGenre(int $genreId, int $limit = 24, int $offset = 0): array
    {
        $body = "fields id,name,cover.image_id,rating,genres.name,platforms.id,first_release_date;
                 where genres = ({$genreId}) & cover != null & version_parent = null & themes != (42) & rating > 70;
                 sort rating_count desc;
                 limit {$limit};
                 offset {$offset};";
        return $this->query('games', $body);
    }

    public function getPlatform(int $id): ?array
    {
        $body = "fields id,name,abbreviation,summary,logo.image_id;
                 where id = {$id};
                 limit 1;";
        $results = $this->query('platforms', $body);
        return $results[0] ?? null;
    }

    /**
     * Resolve Steam App IDs for multiple IGDB game IDs in a single API call.
     * Returns [igdbGameId => steamAppId].
     */
    public function getSteamAppIds(array $gameIds): array
    {
        if (empty($gameIds)) {
            return [];
        }

        $idList  = implode(',', array_map('intval', $gameIds));
        $body    = "fields uid,url,category,game; where game = ({$idList}); limit 500;";
        $results = $this->query('external_games', $body);

        $map = [];

        // Prefer URL-based detection (most reliable)
        foreach ($results as $entry) {
            $igdbId = $entry['game'] ?? null;
            $uid    = (string) ($entry['uid'] ?? '');
            $url    = (string) ($entry['url'] ?? '');
            if (! $igdbId || ! ctype_digit($uid) || isset($map[$igdbId])) {
                continue;
            }
            if (str_contains($url, 'steampowered.com')) {
                $map[$igdbId] = (int) $uid;
            }
        }

        // Fallback: category = 1 entries
        foreach ($results as $entry) {
            $igdbId = $entry['game'] ?? null;
            if (! $igdbId || isset($map[$igdbId])) {
                continue;
            }
            $uid = (string) ($entry['uid'] ?? '');
            if (($entry['category'] ?? null) === 1 && ctype_digit($uid)) {
                $map[$igdbId] = (int) $uid;
            }
        }

        return $map;
    }

    public function getSteamAppId(int $gameId): ?int
    {
        // Fetch all external_games for this game with url + uid.
        // We cannot rely on category = 1 (Steam) because IGDB leaves it null
        // for many entries. Instead we identify Steam records via the URL or
        // by uid being a purely numeric string (Steam App IDs are all digits).
        $body    = "fields uid,url,category; where game = {$gameId}; limit 20;";
        $results = $this->query('external_games', $body);

        foreach ($results as $entry) {
            $uid = (string) ($entry['uid'] ?? '');
            $url = (string) ($entry['url'] ?? '');

            // Prefer URL-based detection (most reliable)
            if (str_contains($url, 'steampowered.com') && ctype_digit($uid)) {
                return (int) $uid;
            }
        }

        // Fallback: category = 1 entries that do have the field set
        foreach ($results as $entry) {
            if (($entry['category'] ?? null) === 1 && ctype_digit((string) ($entry['uid'] ?? ''))) {
                return (int) $entry['uid'];
            }
        }

        return null;
    }
}
