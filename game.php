<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/igdb.php';

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$game = null;
$error = null;

if (!$id) {
    header('Location: /');
    exit;
}

try {
    $igdb = new IGDB();
    $game = $igdb->getGame($id);
} catch (RuntimeException $e) {
    $error = $e->getMessage();
}

if (!$game && !$error) {
    header('HTTP/1.0 404 Not Found');
    renderHeader('', SITE_URL . '/');
    echo '<div class="container"><div class="empty-state"><div class="icon">🎮</div><h3>Game not found</h3><p>That game doesn\'t exist in the IGDB database.</p><a href="/" class="btn btn--primary" style="margin-top:1.5rem">Back Home</a></div></div>';
    renderFooter();
    exit;
}

$name        = htmlspecialchars($game['name'] ?? 'Unknown');
$summary     = htmlspecialchars($game['summary'] ?? '');
$storyline   = htmlspecialchars($game['storyline'] ?? '');
$rating      = isset($game['rating']) ? round($game['rating']) : null;
$ratingCount = $game['rating_count'] ?? 0;
$rClass      = $rating ? getRatingClass($rating) : '';
$releaseDate = isset($game['first_release_date']) ? formatDate($game['first_release_date']) : 'TBA';
$coverId     = $game['cover']['image_id'] ?? null;
$coverUrl    = $coverId ? IGDB::imgUrl($coverId, 'cover_big') : '/assets/img/placeholder.jpg';
$backdropUrl = $coverId ? IGDB::imgUrl($coverId, '1080p') : '';

$platforms   = $game['platforms'] ?? [];
$genres      = $game['genres'] ?? [];
$modes       = $game['game_modes'] ?? [];
$themes      = $game['themes'] ?? [];
$screenshots = $game['screenshots'] ?? [];
$artworks    = $game['artworks'] ?? [];
$videos      = $game['videos'] ?? [];
$similar     = $game['similar_games'] ?? [];
$websites    = $game['websites'] ?? [];

// Developer / publisher
$developer  = '';
$publisher  = '';
foreach (($game['involved_companies'] ?? []) as $ic) {
    if (!empty($ic['developer']))  $developer = $ic['company']['name'] ?? '';
    if (!empty($ic['publisher']))  $publisher = $ic['company']['name'] ?? '';
}

$canonicalUrl = SITE_URL . '/game/' . $id;
$gameTitle    = ($game['name'] ?? 'Unknown') . ' – ' . SITE_NAME;
$gameDesc     = truncate($game['summary'] ?? '', 160);

renderHeader('', $canonicalUrl, $gameTitle, $gameDesc);
?>

<!-- Lightbox -->
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true">
    <button class="lightbox-close" id="lb-close" aria-label="Close">✕</button>
    <img src="" id="lb-img" alt="Screenshot">
</div>

<!-- ===== GAME HERO ===== -->
<div class="game-detail-hero">
    <?php if ($backdropUrl): ?>
    <div class="gd-backdrop" style="background-image:url('<?= $backdropUrl ?>')"></div>
    <?php endif; ?>
    <div class="container">
        <div class="gd-inner">
            <div class="gd-cover">
                <img src="<?= $coverUrl ?>" alt="<?= $name ?> cover">
            </div>

            <div class="gd-info">
                <!-- Platforms -->
                <?php if ($platforms): ?>
                <div class="gd-platforms">
                    <?php foreach ($platforms as $p): ?>
                    <span class="gd-platform-tag"><?= htmlspecialchars($p['abbreviation'] ?? $p['name'] ?? '') ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <h1 class="gd-title"><?= $name ?></h1>

                <!-- Rating -->
                <?php if ($rating): ?>
                <div class="gd-rating-block">
                    <span class="gd-score <?= $rClass ?>"><?= $rating ?></span>
                    <div>
                        <?= renderStarRating($rating) ?>
                        <div style="font-size:0.8rem; color:var(--text-muted); margin-top:4px">
                            Based on <?= number_format($ratingCount) ?> ratings
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Meta grid -->
                <div class="gd-meta-grid">
                    <?php if ($releaseDate !== 'TBA' || true): ?>
                    <div class="gd-meta-item">
                        <label>Released</label>
                        <span><?= $releaseDate ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($developer): ?>
                    <div class="gd-meta-item">
                        <label>Developer</label>
                        <span><?= htmlspecialchars($developer) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($publisher && $publisher !== $developer): ?>
                    <div class="gd-meta-item">
                        <label>Publisher</label>
                        <span><?= htmlspecialchars($publisher) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($genres): ?>
                    <div class="gd-meta-item">
                        <label>Genre</label>
                        <span><?= implode(', ', array_column($genres, 'name')) ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($modes): ?>
                    <div class="gd-meta-item">
                        <label>Mode</label>
                        <span><?= htmlspecialchars(implode(', ', array_column($modes, 'name'))) ?></span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($summary): ?>
                <p class="gd-summary"><?= nl2br($summary) ?></p>
                <?php endif; ?>

                <!-- Websites -->
                <?php if ($websites): ?>
                <div style="margin-top:1.5rem; display:flex; gap:0.75rem; flex-wrap:wrap;">
                    <?php
                    $cats = [1=>'🌐 Official Site', 2=>'🎮 IGN', 3=>'📖 Wikipedia', 9=>'YouTube', 13=>'Steam', 16=>'🎮 IGDB'];
                    foreach ($websites as $w):
                        $label = $cats[$w['category']] ?? '🔗 Link';
                    ?>
                    <a href="<?= htmlspecialchars($w['url']) ?>" target="_blank" rel="noopener" class="btn btn--outline btn--sm">
                        <?= $label ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ===== SCREENSHOTS ===== -->
<?php if (!empty($screenshots) || !empty($artworks)): ?>
<section class="section">
    <div class="container">
        <div class="section-header fade-up">
            <h2 class="section-title">Screenshots</h2>
        </div>
        <div class="screenshots-grid fade-up">
            <?php
            $allShots = [...$screenshots, ...$artworks];
            foreach (array_slice($allShots, 0, 9) as $shot):
                $thumbUrl = IGDB::imgUrl($shot['image_id'], 'screenshot_med');
                $fullUrl  = IGDB::imgUrl($shot['image_id'], 'screenshot_big');
            ?>
            <div class="screenshot" data-full="<?= $fullUrl ?>">
                <img src="<?= $thumbUrl ?>" alt="<?= $name ?> screenshot" loading="lazy">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== VIDEOS ===== -->
<?php if (!empty($videos)): ?>
<section class="section" style="background: var(--bg-2); padding:4rem 0;">
    <div class="container">
        <div class="section-header fade-up">
            <h2 class="section-title">Videos</h2>
        </div>
        <div style="display:grid; grid-template-columns: repeat(auto-fill,minmax(340px,1fr)); gap:1.5rem;" class="fade-up">
            <?php foreach (array_slice($videos, 0, 3) as $vid): ?>
            <div style="border-radius:var(--radius); overflow:hidden; aspect-ratio:16/9; border:1px solid var(--border);">
                <iframe
                    src="https://www.youtube.com/embed/<?= htmlspecialchars($vid['video_id']) ?>"
                    title="<?= htmlspecialchars($vid['name'] ?? 'Trailer') ?>"
                    frameborder="0"
                    allow="autoplay; encrypted-media"
                    allowfullscreen
                    style="width:100%; height:100%;"
                    loading="lazy">
                </iframe>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== STORYLINE ===== -->
<?php if ($storyline && $storyline !== $summary): ?>
<section class="section">
    <div class="container" style="max-width: 800px;">
        <div class="section-header fade-up">
            <h2 class="section-title">Storyline</h2>
        </div>
        <p class="fade-up" style="color:var(--text-muted); line-height:1.9; font-size:1.05rem;">
            <?= nl2br($storyline) ?>
        </p>
    </div>
</section>
<?php endif; ?>

<!-- ===== SIMILAR GAMES ===== -->
<?php if (!empty($similar)): ?>
<section class="section" style="background: var(--bg-2); padding:4rem 0;">
    <div class="container">
        <div class="section-header fade-up">
            <h2 class="section-title">You Might Also Like</h2>
        </div>
        <div class="games-grid fade-up">
            <?php foreach (array_slice($similar, 0, 6) as $sg): renderGameCard($sg); endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- MOBILE NAV -->
<nav class="mobile-nav" id="mobile-nav">
    <a href="/">🏠 Home</a>
    <span class="m-section-title">PLATFORMS</span>
    <?php foreach (PLATFORMS as $name => $data): ?>
    <a href="/platform/<?= $data['id'] ?>/<?= rawurlencode($name) ?>"><?= $data['icon'] ?> <?= $name ?></a>
    <?php endforeach; ?>
</nav>

<?php renderFooter(); ?>
