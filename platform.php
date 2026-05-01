<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/igdb.php';

$platformId   = isset($_GET['id'])   ? (int)$_GET['id']      : 0;
$platformName = $_GET['name'] ?? 'Unknown Platform';
$page         = max(1, (int)($_GET['page'] ?? 1));
$limit        = 24;
$offset       = ($page - 1) * $limit;

$games    = [];
$platform = null;
$error    = null;

if (!$platformId) {
    header('Location: /');
    exit;
}

try {
    $igdb     = new IGDB();
    $games    = $igdb->getGamesByPlatform($platformId, $limit, $offset);
    $platform = $igdb->getPlatform($platformId);
} catch (RuntimeException $e) {
    $error = $e->getMessage();
}

// Pick icon from our platform map
$platformIcon = '🎮';
foreach (PLATFORMS as $name => $data) {
    if ($data['id'] === $platformId) {
        $platformIcon = $data['icon'];
        break;
    }
}

$canonicalBase = SITE_URL . '/platform/' . $platformId . '/' . rawurlencode($platformName);
$canonicalUrl  = $page > 1 ? $canonicalBase . '?page=' . $page : $canonicalBase;
$pageTitle     = htmlspecialchars($platformName) . ' Games – ' . SITE_NAME;
$pageDesc      = 'Browse the best ' . htmlspecialchars($platformName) . ' games. Discover top-rated titles, new releases and hidden gems.';

renderHeader('', $canonicalUrl, $pageTitle, $pageDesc);
?>

<!-- ===== PLATFORM HERO ===== -->
<div class="platform-hero">
    <div class="container">
        <div class="platform-hero__inner">
            <div class="platform-logo"><?= $platformIcon ?></div>
            <div>
                <p style="font-size:0.8rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--accent); font-weight:700; margin-bottom:0.5rem;">
                    Platform
                </p>
                <h1 class="section-title"><?= htmlspecialchars($platformName) ?></h1>
                <?php if (!empty($platform['summary'])): ?>
                <p style="color:var(--text-muted); max-width:600px; margin-top:0.75rem; font-size:0.95rem; line-height:1.7;">
                    <?= htmlspecialchars(truncate($platform['summary'], 200)) ?>
                </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Platform nav -->
        <div style="display:flex; gap:0.75rem; margin-top:2rem; flex-wrap:wrap;">
            <?php foreach (PLATFORMS as $name => $data): ?>
            <a href="/platform/<?= $data['id'] ?>/<?= rawurlencode($name) ?>"
               class="chip <?= $data['id'] === $platformId ? 'active' : '' ?>">
                <?= $data['icon'] ?> <?= $name ?>
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
            <p>No rated games found for this platform yet.</p>
        </div>
        <?php else: ?>
        <div class="section-header fade-up">
            <h2 class="section-title">
                <?= htmlspecialchars($platformName) ?> Games
            </h2>
            <span style="color:var(--text-muted); font-size:0.875rem;">Page <?= $page ?></span>
        </div>

        <div class="games-grid games-grid--large fade-up">
            <?php foreach ($games as $game): renderGameCard($game); endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php
            $pgBase = '/platform/' . $platformId . '/' . rawurlencode($platformName);
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
    <span class="m-section-title">PLATFORMS</span>
    <?php foreach (PLATFORMS as $name => $data): ?>
    <a href="/platform/<?= $data['id'] ?>/<?= rawurlencode($name) ?>"><?= $data['icon'] ?> <?= $name ?></a>
    <?php endforeach; ?>
</nav>

<?php renderFooter(); ?>
