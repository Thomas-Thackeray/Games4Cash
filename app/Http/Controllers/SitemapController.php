<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function xml(): Response
    {
        $pages = $this->staticPages();
        $pages = array_merge($pages, $this->platformPages());
        $pages = array_merge($pages, $this->genrePages());

        $xml = $this->buildXml($pages);

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }

    // -----------------------------------------------------------------------

    private function staticPages(): array
    {
        $now = now()->toAtomString();

        return [
            ['url' => route('home'),                                    'priority' => '1.0', 'freq' => 'daily',   'mod' => $now],
            ['url' => route('search'),                                  'priority' => '0.9', 'freq' => 'daily',   'mod' => $now],
            ['url' => route('about'),                                   'priority' => '0.5', 'freq' => 'monthly', 'mod' => $now],
            ['url' => route('faq'),                                     'priority' => '0.5', 'freq' => 'monthly', 'mod' => $now],
            ['url' => route('contact'),                                 'priority' => '0.4', 'freq' => 'yearly',  'mod' => $now],
            ['url' => route('terms'),                                   'priority' => '0.3', 'freq' => 'yearly',  'mod' => $now],
            ['url' => route('privacy'),                                 'priority' => '0.3', 'freq' => 'yearly',  'mod' => $now],
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
                'mod'      => now()->toAtomString(),
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
                'mod'      => now()->toAtomString(),
            ];
        }
        return $pages;
    }

    private function buildXml(array $pages): string
    {
        $urls = '';
        foreach ($pages as $page) {
            $urls .= sprintf(
                "\n    <url>\n        <loc>%s</loc>\n        <lastmod>%s</lastmod>\n        <changefreq>%s</changefreq>\n        <priority>%s</priority>\n    </url>",
                htmlspecialchars($page['url'], ENT_XML1),
                htmlspecialchars($page['mod'],  ENT_XML1),
                $page['freq'],
                $page['priority']
            );
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'
            . "\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">"
            . $urls
            . "\n</urlset>";
    }
}
