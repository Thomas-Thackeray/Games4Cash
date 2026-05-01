<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/igdb.php';

$query  = trim($_GET['q']    ?? '');
$sort   = $_GET['sort']      ?? 'trending';
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 24;
$offset = ($page - 1) * $limit;

$games  = [];
$error  = null;

try {
    $igdb = new IGDB();

    if ($query !== '') {
        $games = $igdb->searchGames($query, $limit, $offset);
    } else {
        switch ($sort) {
            case 'top_rated':
                $games = $igdb->getTopRated($limit);
                break;
            case 'recent':
                $games = $igdb->getRecentGames($limit);
                break;
            case 'upcoming':
                $games = $igdb->getUpcomingGames($limit);
                break;
            default:
                $games = $igdb->getTrendingGames($limit);
        }
    }
} catch (RuntimeException $e) {
    $error = $e->getMessage();
}

renderHeader('search');
?>

<!-- ===== SEARCH HERO ===== -->
<div class="search-hero">
    <div class="container">
        <h1 class="section-title">
            <?php if ($query): ?>
                Results for "<?= htmlspecialchars($query) ?>"
            <?php else: ?>
                Browse All Games
            <?php endif; ?>
        </h1>
        <form class="search-bar" action="/search.php" method="GET">
            <input
                type="search"
                name="q"
                value="<?= htmlspecialchars($query) ?>"
                placeholder="Search for any game…"
                autocomplete="off"
                autofocus>
            <button type="submit">⌕ Search</button>
        </form>
    </div>
</div>

<!-- ===== FILTER BAR ===== -->
<?php if (!$query): ?>
<div class="container">
    <div class="filter-bar">
        <span class="filter-label">Sort by:</span>
        <div class="filter-chips">
            <?php
            $filters = [
                'trending'  => '🔥 Trending',
                'top_rated' => '⭐ Top Rated',
                'recent'    => '🆕 New Releases',
                'upcoming'  => '⏳ Upcoming',
            ];
            foreach ($filters as $key => $label):
            ?>
            <a href="/search.php?sort=<?= $key ?>"
               class="chip <?= $sort === $key ? 'active' : '' ?>">
                <?= $label ?>
            </a>
            <?php endforeach; ?>
        </div>

        <span class="filter-label" style="margin-left:auto">Platform:</span>
        <div class="filter-chips">
            <?php foreach (PLATFORMS as $name => $data): ?>
            <a href="/platform.php?id=<?= $data['id'] ?>&name=<?= urlencode($name) ?>" class="chip">
                <?= $data['short'] ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ===== RESULTS ===== -->
<section class="section--tight">
    <div class="container">
        <?php if ($error): ?>
        <div class="error-banner">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php elseif (empty($games)): ?>
        <div class="empty-state">
            <div class="icon">🔍</div>
            <h3>No games found</h3>
            <p>Try a different search term or browse by platform.</p>
            <a href="/search.php" class="btn btn--primary" style="margin-top:1.5rem">Browse All</a>
        </div>
        <?php else: ?>
        <div class="games-grid games-grid--large" style="margin-bottom: 2rem;">
            <?php foreach ($games as $game): renderGameCard($game); endforeach; ?>
        </div>

        <!-- Pagination (only for non-search browsing) -->
        <?php if (!$query): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?sort=<?= $sort ?>&page=<?= $page - 1 ?>" class="page-btn">← Prev</a>
            <?php endif; ?>

            <?php
            $maxPages = 8;
            for ($i = max(1, $page - 2); $i <= min($maxPages, $page + 4); $i++):
            ?>
            <a href="?sort=<?= $sort ?>&page=<?= $i ?>"
               class="page-btn <?= $i === $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>

            <a href="?sort=<?= $sort ?>&page=<?= $page + 1 ?>" class="page-btn">Next →</a>
        </div>
        <?php elseif (count($games) === $limit): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?q=<?= urlencode($query) ?>&page=<?= $page - 1 ?>" class="page-btn">← Prev</a>
            <?php endif; ?>
            <span class="page-btn active"><?= $page ?></span>
            <a href="?q=<?= urlencode($query) ?>&page=<?= $page + 1 ?>" class="page-btn">Next →</a>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- MOBILE NAV -->
<nav class="mobile-nav" id="mobile-nav">
    <a href="/">🏠 Home</a>
    <span class="m-section-title">PLATFORMS</span>
    <?php foreach (PLATFORMS as $name => $data): ?>
    <a href="/platform.php?id=<?= $data['id'] ?>&name=<?= urlencode($name) ?>"><?= $data['icon'] ?> <?= $name ?></a>
    <?php endforeach; ?>
</nav>

<?php renderFooter(); ?>
