<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\CustomGame;
use App\Models\GamePrice;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    private const STABLE_DATE = '2025-01-01T00:00:00+00:00';
    private const GAME_LIMIT  = 10000; // cap to prevent memory exhaustion

    public function xml(): Response
    {
        $xml = Cache::remember('sitemap_xml', now()->addHours(6), fn () => $this->build());

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=21600');
    }

    private function build(): string
    {
        $urls = '';

        foreach ($this->staticPages() as $page) {
            $urls .= $this->urlTag($page);
        }

        foreach ($this->platformPages() as $page) {
            $urls .= $this->urlTag($page);
        }

        foreach ($this->platformSellPages() as $page) {
            $urls .= $this->urlTag($page);
        }

        foreach ($this->genrePages() as $page) {
            $urls .= $this->urlTag($page);
        }

        foreach ($this->blogPages() as $page) {
            $urls .= $this->urlTag($page);
        }

        // Stream game pages in chunks to avoid memory exhaustion
        $count = 0;
        GamePrice::whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('id')
            ->select(['slug', 'updated_at'])
            ->chunk(500, function ($records) use (&$urls, &$count) {
                foreach ($records as $gp) {
                    if ($count >= self::GAME_LIMIT) return false; // stop chunking
                    $mod    = $gp->updated_at ? $gp->updated_at->toAtomString() : self::STABLE_DATE;
                    $urls  .= $this->urlTag(['url' => route('game.show', $gp->slug), 'priority' => '0.6', 'freq' => 'weekly', 'mod' => $mod]);
                    $count++;
                }
            });

        // Custom game pages
        CustomGame::where('published', true)
            ->orderBy('id')
            ->select(['slug', 'updated_at'])
            ->chunk(200, function ($records) use (&$urls) {
                foreach ($records as $cg) {
                    $mod   = $cg->updated_at ? $cg->updated_at->toAtomString() : self::STABLE_DATE;
                    $urls .= $this->urlTag(['url' => route('game.show', $cg->slug), 'priority' => '0.65', 'freq' => 'weekly', 'mod' => $mod]);
                }
            });

        return '<?xml version="1.0" encoding="UTF-8"?>'
            . "\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">"
            . $urls
            . "\n</urlset>";
    }

    // -----------------------------------------------------------------------

    private function staticPages(): array
    {
        $now = now()->toAtomString();

        return [
            ['url' => route('home'),              'priority' => '1.0', 'freq' => 'daily',   'mod' => $now],
            ['url' => route('search'),            'priority' => '0.9', 'freq' => 'daily',   'mod' => $now],
            ['url' => route('platforms.index'),   'priority' => '0.8', 'freq' => 'weekly',  'mod' => self::STABLE_DATE],
            ['url' => route('genres.index'),      'priority' => '0.8', 'freq' => 'weekly',  'mod' => self::STABLE_DATE],
            ['url' => route('about'),             'priority' => '0.5', 'freq' => 'monthly', 'mod' => self::STABLE_DATE],
            ['url' => route('faq'),               'priority' => '0.5', 'freq' => 'monthly', 'mod' => self::STABLE_DATE],
            ['url' => route('gaming-timeline'),   'priority' => '0.5', 'freq' => 'monthly', 'mod' => self::STABLE_DATE],
            ['url' => route('gaming-legends'),    'priority' => '0.5', 'freq' => 'monthly', 'mod' => self::STABLE_DATE],
            ['url' => route('contact'),           'priority' => '0.4', 'freq' => 'yearly',  'mod' => self::STABLE_DATE],
            ['url' => route('snake'),             'priority' => '0.3', 'freq' => 'monthly', 'mod' => self::STABLE_DATE],
            ['url' => route('terms'),             'priority' => '0.3', 'freq' => 'yearly',  'mod' => self::STABLE_DATE],
            ['url' => route('privacy'),           'priority' => '0.3', 'freq' => 'yearly',  'mod' => self::STABLE_DATE],
        ];
    }

    private function platformPages(): array
    {
        $pages = [];
        foreach (config('igdb.platforms', []) as $name => $data) {
            $pages[] = [
                'url'      => route('platform.show', ['id' => $data['id'], 'name' => $data['slug'] ?? $name]),
                'priority' => '0.8',
                'freq'     => 'weekly',
                'mod'      => self::STABLE_DATE,
            ];
        }
        return $pages;
    }

    private function platformSellPages(): array
    {
        $pages = [];
        foreach (config('igdb.platforms', []) as $name => $data) {
            $pages[] = [
                'url'      => route('sell.platform', Str::slug($name)),
                'priority' => '0.85',
                'freq'     => 'weekly',
                'mod'      => self::STABLE_DATE,
            ];
        }
        return $pages;
    }

    private function genrePages(): array
    {
        $pages = [];
        foreach (config('igdb.genres', []) as $name => $id) {
            $pages[] = [
                'url'      => route('genre.show', ['id' => $id, 'name' => $name]),
                'priority' => '0.7',
                'freq'     => 'weekly',
                'mod'      => self::STABLE_DATE,
            ];
        }
        return $pages;
    }

    private function blogPages(): array
    {
        $pages   = [];
        $pages[] = [
            'url'      => route('blog.index'),
            'priority' => '0.7',
            'freq'     => 'weekly',
            'mod'      => self::STABLE_DATE,
        ];

        BlogPost::published()->latest('published_at')->select(['slug', 'updated_at'])->each(function (BlogPost $post) use (&$pages) {
            $mod     = $post->updated_at ? $post->updated_at->toAtomString() : self::STABLE_DATE;
            $pages[] = [
                'url'      => route('blog.show', $post->slug),
                'priority' => '0.6',
                'freq'     => 'monthly',
                'mod'      => $mod,
            ];
        });

        return $pages;
    }

    private function urlTag(array $page): string
    {
        return sprintf(
            "\n    <url>\n        <loc>%s</loc>\n        <lastmod>%s</lastmod>\n        <changefreq>%s</changefreq>\n        <priority>%s</priority>\n    </url>",
            htmlspecialchars($page['url'], ENT_XML1),
            htmlspecialchars($page['mod'],  ENT_XML1),
            $page['freq'],
            $page['priority']
        );
    }
}
