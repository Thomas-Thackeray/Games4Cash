<?php

namespace App\Http\Controllers;

use App\Models\CustomGame;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCustomGameController extends Controller
{
    private function platforms(): array
    {
        return config('igdb.all_platforms', []);
    }

    private function genres(): array
    {
        return array_keys(config('igdb.genres', []));
    }

    public function index(): View
    {
        $games = CustomGame::latest()->paginate(20);

        return view('admin.custom-games.index', compact('games'));
    }

    public function create(): View
    {
        return view('admin.custom-games.form', [
            'game'      => null,
            'platforms' => $this->platforms(),
            'genres'    => $this->genres(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateGame($request);

        $game = CustomGame::create(array_merge($validated, [
            'slug'             => $this->uniqueSlug($validated['title']),
            'cover_image_path' => $this->handleCoverUpload($request, null),
        ]));

        return redirect()->route('admin.custom-games.edit', $game->id)
            ->with('flash_success', 'Custom game created. You can preview it at /custom-game/' . $game->slug);
    }

    public function edit(int $id): View
    {
        $game = CustomGame::findOrFail($id);

        return view('admin.custom-games.form', [
            'game'      => $game,
            'platforms' => $this->platforms(),
            'genres'    => $this->genres(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $game      = CustomGame::findOrFail($id);
        $validated = $this->validateGame($request, $game->id);

        $coverPath = $this->handleCoverUpload($request, $game->cover_image_path);

        $game->update(array_merge($validated, ['cover_image_path' => $coverPath]));

        return back()->with('flash_success', 'Custom game updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $game = CustomGame::findOrFail($id);

        if ($game->cover_image_path) {
            Storage::disk('public')->delete($game->cover_image_path);
        }

        $game->delete();

        return redirect()->route('admin.custom-games.index')
            ->with('flash_success', 'Custom game deleted.');
    }

    // ── Private helpers ─────────────────────────────────────────────────────

    private function validateGame(Request $request, ?int $ignoreId = null): array
    {
        $platforms = $this->platforms();

        $priceRules = [];
        foreach (array_keys($platforms) as $pid) {
            $priceRules["platform_prices.{$pid}"] = ['nullable', 'numeric', 'min:0', 'max:9999'];
        }

        $rules = array_merge([
            'title'        => ['required', 'string', 'max:255'],
            'summary'      => ['nullable', 'string', 'max:5000'],
            'developer'    => ['nullable', 'string', 'max:255'],
            'publisher'    => ['nullable', 'string', 'max:255'],
            'release_year' => ['nullable', 'integer', 'min:1950', 'max:' . (date('Y') + 2)],
            'mode'         => ['nullable', 'string', 'max:255'],
            'genres'       => ['nullable', 'array'],
            'genres.*'     => ['string'],
            'cover_image'  => ['nullable', 'image', 'max:5120'],
            'published'    => ['nullable', 'boolean'],
        ], $priceRules);

        $data = $request->validate($rules);

        // Build platform_prices: only keep non-empty values
        $prices = [];
        foreach (array_keys($platforms) as $pid) {
            $val = $request->input("platform_prices.{$pid}");
            if ($val !== null && $val !== '') {
                $prices[(string) $pid] = (float) $val;
            }
        }

        return [
            'title'          => $data['title'],
            'summary'        => $data['summary'] ?? null,
            'developer'      => $data['developer'] ?? null,
            'publisher'      => $data['publisher'] ?? null,
            'release_year'   => $data['release_year'] ?? null,
            'mode'           => $data['mode'] ?? null,
            'genres'         => $data['genres'] ?? [],
            'platform_prices'=> $prices,
            'published'      => $request->boolean('published', true),
        ];
    }

    private function handleCoverUpload(Request $request, ?string $existing): ?string
    {
        if ($request->hasFile('cover_image')) {
            if ($existing) {
                Storage::disk('public')->delete($existing);
            }
            return $request->file('cover_image')->store('custom-games', 'public');
        }

        return $existing;
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
