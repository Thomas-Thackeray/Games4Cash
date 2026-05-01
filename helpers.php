<?php
require_once __DIR__ . '/config.php';

// Platform map: name => [id, icon emoji]
define('PLATFORMS', [
    'PlayStation 5'   => ['id' => 167, 'icon' => '🎮', 'short' => 'PS5'],
    'PlayStation 4'   => ['id' => 48,  'icon' => '🎮', 'short' => 'PS4'],
    'Xbox Series X|S' => ['id' => 169, 'icon' => '🟩', 'short' => 'XSX'],
    'Xbox One'        => ['id' => 49,  'icon' => '🟩', 'short' => 'XBO'],
    'Nintendo Switch' => ['id' => 130, 'icon' => '🔴', 'short' => 'NSW'],
    'PC'              => ['id' => 6,   'icon' => '💻', 'short' => 'PC'],
]);

// Genre map: name => id
define('GENRES', [
    'Action'       => 14,
    'Adventure'    => 31,
    'RPG'          => 12,
    'Strategy'     => 15,
    'Shooter'      => 5,
    'Sports'       => 14,
    'Racing'       => 10,
    'Puzzle'       => 9,
    'Horror'       => 19,
    'Fighting'     => 4,
    'Simulation'   => 13,
    'Indie'        => 32,
]);

function renderHeader(string $activePage = '', string $canonical = '', string $title = '', string $description = ''): void {
    $siteName  = SITE_NAME;
    $platforms = PLATFORMS;
    $genres    = GENRES;
    $pageTitle = $title ?: ($siteName . ' – Discover Every Game');
    $pageDesc  = $description ?: 'Explore thousands of games, browse by platform or genre, and discover your next favourite title.';
    $canonUrl  = $canonical ?: (SITE_URL . '/');
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($canonUrl) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="site-header" id="site-header">
    <div class="header-inner">
        <a href="/" class="logo">
            <span class="logo-icon">◈</span>
            <span class="logo-text"><?= $siteName ?></span>
        </a>

        <nav class="main-nav">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="/" class="nav-link <?= $activePage === 'home' ? 'active' : '' ?>">Home</a>
                </li>
                <li class="nav-item has-dropdown">
                    <a href="#" class="nav-link">Platforms <span class="chevron">▾</span></a>
                    <div class="dropdown">
                        <?php foreach ($platforms as $name => $data): ?>
                        <a href="/platform/<?= $data['id'] ?>/<?= rawurlencode($name) ?>" class="dropdown-item">
                            <span class="di-icon"><?= $data['icon'] ?></span>
                            <?= $name ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </li>
                <li class="nav-item has-dropdown">
                    <a href="#" class="nav-link">Genres <span class="chevron">▾</span></a>
                    <div class="dropdown">
                        <?php foreach ($genres as $name => $id): ?>
                        <a href="/genre/<?= $id ?>/<?= rawurlencode($name) ?>" class="dropdown-item">
                            <?= $name ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="/search.php" class="nav-link <?= $activePage === 'search' ? 'active' : '' ?>">Browse</a>
                </li>
            </ul>
        </nav>

        <div class="header-right">
            <form class="header-search" action="/search.php" method="GET">
                <input type="search" name="q" placeholder="Search games…" class="hs-input" autocomplete="off">
                <button type="submit" class="hs-btn" aria-label="Search">⌕</button>
            </form>
            <button class="nav-toggle" id="nav-toggle" aria-label="Menu">☰</button>
        </div>
    </div>
</header>

<main class="site-main">
    <?php
}

function renderFooter(): void {
    $siteName = SITE_NAME;
    $platforms = PLATFORMS;
    $genres    = GENRES;
    ?>
</main>

<footer class="site-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <a href="/" class="logo logo--footer">
                <span class="logo-icon">◈</span>
                <span class="logo-text"><?= $siteName ?></span>
            </a>
            <p class="footer-tagline">Your ultimate gaming database, powered by IGDB.</p>
        </div>

        <div class="footer-cols">
            <div class="footer-col">
                <h4 class="footer-heading">Platforms</h4>
                <ul>
                    <?php foreach ($platforms as $name => $data): ?>
                    <li><a href="/platform/<?= $data['id'] ?>/<?= rawurlencode($name) ?>"><?= $name ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="footer-col">
                <h4 class="footer-heading">Genres</h4>
                <ul>
                    <?php foreach (array_slice($genres, 0, 6, true) as $name => $id): ?>
                    <li><a href="/genre/<?= $id ?>/<?= rawurlencode($name) ?>"><?= $name ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="footer-col">
                <h4 class="footer-heading">Discover</h4>
                <ul>
                    <li><a href="/search.php">All Games</a></li>
                    <li><a href="/search.php?sort=top_rated">Top Rated</a></li>
                    <li><a href="/search.php?sort=recent">New Releases</a></li>
                    <li><a href="/search.php?sort=upcoming">Upcoming</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>Game data provided by <a href="https://www.igdb.com" target="_blank" rel="noopener">IGDB</a>. 
        &copy; <?= date('Y') ?> <?= $siteName ?>. For personal/non-commercial use.</p>
    </div>
</footer>

<script src="main.js"></script>
</body>
</html>
    <?php
}

function renderStarRating(float $rating): string {
    $stars = round($rating / 20); // convert 0-100 to 0-5
    $html  = '<span class="stars">';
    for ($i = 1; $i <= 5; $i++) {
        $html .= '<span class="star ' . ($i <= $stars ? 'filled' : '') . '">★</span>';
    }
    $html .= '</span>';
    return $html;
}

function getRatingClass(float $rating): string {
    if ($rating >= 80) return 'rating--high';
    if ($rating >= 60) return 'rating--mid';
    return 'rating--low';
}

function formatDate(int $timestamp): string {
    return date('d M Y', $timestamp);
}

function truncate(string $text, int $length = 120): string {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '…';
}

function renderGameCard(array $game, string $size = 'cover_big'): void {
    $id      = $game['id'];
    $name    = htmlspecialchars($game['name'] ?? 'Unknown');
    $imgId   = $game['cover']['image_id'] ?? null;
    $imgUrl  = $imgId ? IGDB::imgUrl($imgId, $size) : '/assets/img/placeholder.jpg';
    $rating  = isset($game['rating']) ? round($game['rating']) : null;
    $rClass  = $rating ? getRatingClass($rating) : '';
    $genre   = $game['genres'][0]['name'] ?? '';
    $year    = isset($game['first_release_date']) ? date('Y', $game['first_release_date']) : '';
    ?>
    <a href="/game.php?id=<?= $id ?>" class="game-card">
        <div class="game-card__img-wrap">
            <img src="<?= $imgUrl ?>" alt="<?= $name ?>" loading="lazy" class="game-card__img">
            <?php if ($rating): ?>
            <span class="game-card__rating <?= $rClass ?>"><?= $rating ?></span>
            <?php endif; ?>
        </div>
        <div class="game-card__info">
            <h3 class="game-card__title"><?= $name ?></h3>
            <div class="game-card__meta">
                <?php if ($genre): ?><span class="tag"><?= htmlspecialchars($genre) ?></span><?php endif; ?>
                <?php if ($year): ?><span class="year"><?= $year ?></span><?php endif; ?>
            </div>
        </div>
    </a>
    <?php
}
