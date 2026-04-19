@extends('layouts.app')
@section('title', 'Bulk Import Custom Games')

@section('content')
<div class="admin-page">

    <div class="admin-header">
        <div>
            <h1 class="admin-title">Bulk Import Games</h1>
            <p class="admin-subtitle"><a href="{{ route('admin.custom-games.index') }}" style="color:var(--accent);">← Custom Games</a></p>
        </div>
    </div>

    @if(session('flash_error'))
    <div class="alert alert--error" style="margin-bottom:1.5rem;">{{ session('flash_error') }}</div>
    @endif

    {{-- ===== INSTRUCTIONS ===== --}}
    <div class="settings-card settings-card--wide" style="margin-bottom:1.5rem;">
        <h2 class="settings-card__title">How to Format Your CSV</h2>
        <p class="settings-hint" style="margin-top:0.5rem;">Upload a <code>.csv</code> file with the columns below. The first row must be the header row. Only <strong>title</strong> is required — all other columns are optional.</p>

        <div style="overflow-x:auto; margin-top:1.25rem;">
            <table class="admin-table" style="font-size:0.82rem;">
                <thead>
                    <tr>
                        <th>Column Header</th>
                        <th>Required</th>
                        <th>Example</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><code>title</code></td><td><strong style="color:var(--accent);">Yes</strong></td><td>Sonic the Hedgehog</td><td>The game's full title. Used to generate the URL slug.</td></tr>
                    <tr><td><code>summary</code></td><td>No</td><td>A classic platformer...</td><td>Short description shown on the game page.</td></tr>
                    <tr><td><code>developer</code></td><td>No</td><td>Sega</td><td></td></tr>
                    <tr><td><code>publisher</code></td><td>No</td><td>Sega</td><td></td></tr>
                    <tr><td><code>release_year</code></td><td>No</td><td>1991</td><td>4-digit year.</td></tr>
                    <tr><td><code>mode</code></td><td>No</td><td>Single player</td><td>e.g. Single player, Multiplayer</td></tr>
                    <tr><td><code>genres</code></td><td>No</td><td>Platformer, Adventure</td><td>Comma-separated within the cell. Wrap in quotes if using commas: <code>"Platformer, Adventure"</code></td></tr>
                    <tr><td><code>published</code></td><td>No</td><td>true</td><td>Accepts: <code>true / false / 1 / 0 / yes / no</code>. Defaults to <code>true</code>.</td></tr>
                    <tr style="border-top:2px solid var(--border);"><td><code>price_pc</code></td><td>No</td><td>2.50</td><td>Cash price for PC in £.</td></tr>
                    <tr><td><code>price_wii</code></td><td>No</td><td>1.00</td><td>Wii</td></tr>
                    <tr><td><code>price_ps2</code></td><td>No</td><td>3.00</td><td>PlayStation 2</td></tr>
                    <tr><td><code>price_ps3</code></td><td>No</td><td>4.50</td><td>PlayStation 3</td></tr>
                    <tr><td><code>price_xbox</code></td><td>No</td><td>2.00</td><td>Original Xbox</td></tr>
                    <tr><td><code>price_xbox_360</code></td><td>No</td><td>3.50</td><td>Xbox 360</td></tr>
                    <tr><td><code>price_wii_u</code></td><td>No</td><td>5.00</td><td>Wii U</td></tr>
                    <tr><td><code>price_ps4</code></td><td>No</td><td>8.00</td><td>PlayStation 4</td></tr>
                    <tr><td><code>price_xbox_one</code></td><td>No</td><td>7.00</td><td>Xbox One</td></tr>
                    <tr><td><code>price_switch</code></td><td>No</td><td>12.00</td><td>Nintendo Switch</td></tr>
                    <tr><td><code>price_ps5</code></td><td>No</td><td>22.00</td><td>PlayStation 5</td></tr>
                    <tr><td><code>price_xbox_series</code></td><td>No</td><td>18.00</td><td>Xbox Series X|S</td></tr>
                </tbody>
            </table>
        </div>

        <div style="margin-top:1.5rem; padding:1rem; background:rgba(230,57,70,0.05); border:1px solid rgba(230,57,70,0.2); border-radius:8px;">
            <p style="font-size:0.85rem; margin:0 0 0.5rem; font-weight:600;">Example CSV row</p>
            <pre style="font-size:0.78rem; color:var(--text-muted); overflow-x:auto; margin:0; white-space:pre;">title,developer,release_year,genres,price_ps4,price_ps5,price_switch
Sonic the Hedgehog,Sega,1991,"Platformer, Adventure",3.50,0.00,6.00
Halo 3,Bungie,2007,Shooter,2.00,0.00,0.00</pre>
        </div>

        <div style="margin-top:1rem; padding:1rem; background:rgba(var(--accent-2-rgb,249,168,38),0.06); border:1px solid rgba(249,168,38,0.25); border-radius:8px;">
            <p style="font-size:0.85rem; margin:0; color:var(--text-muted);">
                <strong style="color:var(--text);">After import:</strong> Each game is created without a cover image (a placeholder is shown instead).
                Visit <a href="{{ route('admin.custom-games.index') }}" style="color:var(--accent);">Custom Games</a> and click <strong>Edit</strong> on each game to upload its cover image.
            </p>
        </div>
    </div>

    {{-- ===== UPLOAD FORM ===== --}}
    <div class="settings-card settings-card--wide">
        <h2 class="settings-card__title">Upload CSV</h2>
        <form method="POST" action="{{ route('admin.custom-games.import.store') }}" enctype="multipart/form-data" style="margin-top:1.25rem;">
            @csrf
            <div class="form-group">
                <label class="form-label">CSV File <span class="required">*</span></label>
                <input type="file" name="csv_file" accept=".csv,text/csv"
                    class="form-input {{ $errors->has('csv_file') ? 'is-invalid' : '' }}"
                    style="padding:0.5rem;">
                <p class="settings-hint">Max 2 MB. Must be a valid .csv file with a header row.</p>
                @error('csv_file')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1rem;">
                <a href="{{ route('admin.custom-games.index') }}" class="btn btn--outline">Cancel</a>
                <button type="submit" class="btn btn--primary">Import Games</button>
            </div>
        </form>
    </div>

</div>
@endsection
