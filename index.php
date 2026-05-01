<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/igdb.php';

$error    = null;
$trending = [];
$topRated = [];
$recent   = [];
$upcoming = [];

try {
    $igdb     = new IGDB();
    $trending = $igdb->getTrendingGames(12);
    $topRated = $igdb->getTopRated(8);
    $recent   = $igdb->getRecentGames(8);
    $upcoming = $igdb->getUpcomingGames(6);
} catch (RuntimeException $e) {
    $error = $e->getMessage();
}

renderHeader('home', SITE_URL . '/');
?>

<!-- ===== HERO ===== -->
<section class="hero">
    <div class="hero__bg"></div>
    <div class="hero__grid-lines"></div>
    <div class="container">
        <div class="hero__content">
            <p class="hero__eyebrow">Your Ultimate Gaming Database</p>
            <h1 class="hero__title">
                Discover<br>Every <span>Game</span><br>Ever Made
            </h1>
            <p class="hero__desc">
                Browse thousands of titles across every platform and genre.
                Powered by IGDB — the world's largest gaming database.
            </p>
            <div class="hero__actions">
                <a href="/search.php" class="btn btn--primary">🎮 Browse All Games</a>
                <a href="/search.php?sort=top_rated" class="btn btn--outline">⭐ Top Rated</a>
            </div>
        </div>
    </div>
</section>

<!-- ===== PLATFORM STRIP ===== -->
<div class="container">
    <div class="platform-strip">
        <?php foreach (PLATFORMS as $name => $data): ?>
        <a href="/platform/<?= $data['id'] ?>/<?= rawurlencode($name) ?>" class="platform-pill">
            <span class="icon"><?= $data['icon'] ?></span>
            <?= $name ?>
        </a>
        <?php endforeach; ?>
        <?php foreach (GENRES as $name => $id): ?>
        <a href="/genre/<?= $id ?>/<?= rawurlencode($name) ?>" class="platform-pill">
            <?= $name ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($error): ?>
<div class="container">
    <div class="error-banner">
        ⚠️ <?= htmlspecialchars($error) ?>
        — Please add your IGDB credentials in <code>config.php</code>.
    </div>
</div>
<?php endif; ?>

<!-- ===== TRENDING ===== -->
<?php if (!empty($trending)): ?>
<section class="section">
    <div class="container">
        <div class="section-header fade-up">
            <h2 class="section-title">Trending Now</h2>
            <a href="/search.php">View all →</a>
        </div>
        <div class="games-grid fade-up">
            <?php foreach ($trending as $game): renderGameCard($game); endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== TOP RATED ===== -->
<?php if (!empty($topRated)): ?>
<section class="section" style="background: var(--bg-2); padding: 4rem 0;">
    <div class="container">
        <div class="section-header fade-up">
            <h2 class="section-title">All-Time Greats</h2>
            <a href="/search.php?sort=top_rated">See more →</a>
        </div>
        <div class="games-grid games-grid--large fade-up">
            <?php foreach ($topRated as $game): renderGameCard($game, 'cover_big'); endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== RECENT RELEASES ===== -->
<?php if (!empty($recent)): ?>
<section class="section">
    <div class="container">
        <div class="section-header fade-up">
            <h2 class="section-title">Recent Releases</h2>
            <a href="/search.php?sort=recent">See more →</a>
        </div>
        <div class="games-grid fade-up">
            <?php foreach ($recent as $game): renderGameCard($game); endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== UPCOMING ===== -->
<?php if (!empty($upcoming)): ?>
<section class="section" style="background: var(--bg-2); padding: 4rem 0;">
    <div class="container">
        <div class="section-header fade-up">
            <h2 class="section-title">Coming Soon</h2>
            <a href="/search.php?sort=upcoming">See all →</a>
        </div>
        <div class="games-grid fade-up">
            <?php foreach ($upcoming as $game): renderGameCard($game); endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- MOBILE NAV -->
<nav class="mobile-nav" id="mobile-nav">
    <span class="m-section-title">PLATFORMS</span>
    <?php foreach (PLATFORMS as $name => $data): ?>
    <a href="/platform/<?= $data['id'] ?>/<?= rawurlencode($name) ?>"><?= $data['icon'] ?> <?= $name ?></a>
    <?php endforeach; ?>
    <span class="m-section-title">GENRES</span>
    <?php foreach (GENRES as $name => $id): ?>
    <a href="/genre/<?= $id ?>/<?= rawurlencode($name) ?>"><?= $name ?></a>
    <?php endforeach; ?>
    <span class="m-section-title">DISCOVER</span>
    <a href="/search.php">Browse All Games</a>
    <a href="/search.php?sort=top_rated">Top Rated</a>
    <a href="/search.php?sort=recent">New Releases</a>
    <a href="/search.php?sort=upcoming">Upcoming</a>
</nav>

<?php renderFooter(); ?>
