<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/igdb.php';

$genreId   = isset($_GET['id'])   ? (int)$_GET['id']   : 0;
$genreName = isset($_GET['name']) ? $_GET['name']       : 'Unknown Genre';
$page      = max(1, (int)($_GET['page'] ?? 1));
$limit     = 24;
$offset    = ($page - 1) * $limit;

$games = [];
$error = null;

if (!$genreId) {
    header('Location: /');
    exit;
}

try {
    $igdb  = new IGDB();
    $games = $igdb->getGamesByGenre($genreId, $limit, $offset);
} catch (RuntimeException $e) {
    $error = $e->getMessage();
}

$canonicalBase = SITE_URL . '/genre/' . $genreId . '/' . rawurlencode($genreName);
$canonicalUrl  = $page > 1 ? $canonicalBase . '?page=' . $page : $canonicalBase;
$pageTitle     = htmlspecialchars($genreName) . ' Games – ' . SITE_NAME;
$pageDesc      = 'Browse the best ' . htmlspecialchars($genreName) . ' games. Discover top-rated titles, new releases and hidden gems.';

renderHeader('', $canonicalUrl, $pageTitle, $pageDesc);
?>

<!-- ===== GENRE HERO ===== -->
<div class="platform-hero">
    <div class="container">
        <div class="platform-hero__inner">
            <div class="platform-logo" style="font-size:1.8rem;">🎯</div>
            <div>
                <p style="font-size:0.8rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--accent); font-weight:700; margin-bottom:0.5rem;">
                    Genre
                </p>
                <h1 class="section-title"><?= htmlspecialchars($genreName) ?></h1>
            </div>
        </div>

        <!-- Genre nav -->
        <div style="display:flex; gap:0.75rem; margin-top:2rem; flex-wrap:wrap;">
            <?php foreach (GENRES as $name => $id): ?>
            <a href="/genre/<?= $id ?>/<?= rawurlencode($name) ?>"
               class="chip <?= $id === $genreId ? 'active' : '' ?>">
                <?= $name ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ===== GAMES GRID ===== -->
<section class="section">
    <div class="container">
        <?php if ($error): ?>
        <div class="error-banner">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php elseif (empty($games)): ?>
        <div class="empty-state">
            <div class="icon">🎮</div>
            <h3>No games found</h3>
        </div>
        <?php else: ?>
        <div class="section-header fade-up">
            <h2 class="section-title"><?= htmlspecialchars($genreName) ?> Games</h2>
            <span style="color:var(--text-muted); font-size:0.875rem;">Page <?= $page ?></span>
        </div>

        <div class="games-grid games-grid--large fade-up">
            <?php foreach ($games as $game): renderGameCard($game); endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php
            $pgBase = '/genre/' . $genreId . '/' . rawurlencode($genreName);
            $pgLink = fn(int $p) => $p > 1 ? $pgBase . '?page=' . $p : $pgBase;
            ?>
            <?php if ($page > 1): ?>
            <a href="<?= $pgLink($page - 1) ?>" class="page-btn">← Prev</a>
            <?php endif; ?>
            <?php for ($i = max(1, $page - 2); $i <= min(20, $page + 4); $i++): ?>
            <a href="<?= $pgLink($i) ?>"
               class="page-btn <?= $i === $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
            <?php if (count($games) === $limit): ?>
            <a href="<?= $pgLink($page + 1) ?>" class="page-btn">Next →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- MOBILE NAV -->
<nav class="mobile-nav" id="mobile-nav">
    <a href="/">🏠 Home</a>
    <span class="m-section-title">GENRES</span>
    <?php foreach (GENRES as $name => $id): ?>
    <a href="/genre/<?= $id ?>/<?= rawurlencode($name) ?>"><?= $name ?></a>
    <?php endforeach; ?>
</nav>

<?php renderFooter(); ?>
