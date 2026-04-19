<?php

namespace App\Http\Controllers;

use App\Models\CustomGame;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCustomGameImportController extends Controller
{
    // CSV column → platform ID mapping (header names users write in the CSV)
    private const PLATFORM_COLUMNS = [
        'price_pc'            => 6,
        'price_wii'           => 5,
        'price_ps2'           => 8,
        'price_ps3'           => 9,
        'price_xbox'          => 11,
        'price_xbox_360'      => 12,
        'price_wii_u'         => 41,
        'price_ps4'           => 48,
        'price_xbox_one'      => 49,
        'price_switch'        => 130,
        'price_ps5'           => 167,
        'price_xbox_series'   => 169,
    ];

    public function create(): View
    {
        return view('admin.custom-games.import');
    }

    public function store(Request $request): View|RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $path    = $request->file('csv_file')->getRealPath();
        $handle  = fopen($path, 'r');
        $headers = array_map(fn($h) => strtolower(trim($h)), fgetcsv($handle) ?? []);

        if (empty($headers) || !in_array('title', $headers)) {
            fclose($handle);
            return back()->with('flash_error', 'Invalid CSV: missing required "title" column.');
        }

        $created = 0;
        $skipped = [];
        $rowNum  = 1;
        $placeholder = 'img/coming-soon.svg';

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) < count($headers)) continue;

            $data = array_combine($headers, array_map('trim', $row));

            $title = $data['title'] ?? '';
            if ($title === '') {
                $skipped[] = "Row {$rowNum}: empty title, skipped.";
                continue;
            }

            // Build platform prices
            $prices = [];
            foreach (self::PLATFORM_COLUMNS as $col => $platformId) {
                $val = $data[$col] ?? '';
                if ($val !== '' && is_numeric($val) && (float)$val > 0) {
                    $prices[(string)$platformId] = (float)$val;
                }
            }

            // Parse genres (comma-separated within the cell)
            $genres = [];
            if (!empty($data['genres'])) {
                $genres = array_filter(array_map('trim', explode(',', $data['genres'])));
            }

            $slug = $this->uniqueSlug($title);

            $published = isset($data['published'])
                ? in_array(strtolower($data['published']), ['1', 'true', 'yes'])
                : true;

            CustomGame::create([
                'title'           => $title,
                'slug'            => $slug,
                'summary'         => $data['summary'] ?? null,
                'developer'       => $data['developer'] ?? null,
                'publisher'       => $data['publisher'] ?? null,
                'release_year'    => isset($data['release_year']) && is_numeric($data['release_year']) ? (int)$data['release_year'] : null,
                'mode'            => $data['mode'] ?? null,
                'genres'          => $genres,
                'platform_prices' => $prices,
                'cover_image_path'=> null,
                'published'       => $published,
            ]);

            $created++;
        }

        fclose($handle);

        return view('admin.custom-games.import-results', compact('created', 'skipped'));
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i    = 2;
        while (CustomGame::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
