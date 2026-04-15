<?php

return [

    'client_id'     => env('IGDB_CLIENT_ID', ''),
    'client_secret' => env('IGDB_CLIENT_SECRET', ''),
    'base_url'      => 'https://api.igdb.com/v4',
    'token_url'     => 'https://id.twitch.tv/oauth2/token',
    'image_base'    => 'https://images.igdb.com/igdb/image/upload',

    // Full ID → name map used server-side to label platforms in the Get Cash dropdown.
    // Covers all platforms listed in the admin settings panel.
    'all_platforms' => [
        6   => 'PC',
        5   => 'Wii',
        8   => 'PlayStation 2',
        9   => 'PlayStation 3',
        11  => 'Xbox',
        12  => 'Xbox 360',
        41  => 'Wii U',
        48  => 'PlayStation 4',
        49  => 'Xbox One',
        130 => 'Nintendo Switch',
        167 => 'PlayStation 5',
        169 => 'Xbox Series X|S',
    ],

    'platforms' => [
        'PlayStation 5'   => ['id' => 167, 'icon' => '🎮', 'short' => 'PS5'],
        'PlayStation 4'   => ['id' => 48,  'icon' => '🎮', 'short' => 'PS4'],
        'Xbox Series X|S' => ['id' => 169, 'icon' => '🟩', 'short' => 'XSX'],
        'Xbox One'        => ['id' => 49,  'icon' => '🟩', 'short' => 'XBO'],
        'Nintendo Switch' => ['id' => 130, 'icon' => '🔴', 'short' => 'NSW'],
        'PC'              => ['id' => 6,   'icon' => '💻', 'short' => 'PC'],
    ],

    // Franchise names used for name-based IGDB search (no IDs needed)
    'franchises' => [
        'Assassins Creed',
        'Battlefield',
        'Call of Duty',
        'FIFA',
        'Final Fantasy',
        'Grand Theft Auto',
        'Halo',
        'Mario',
        'Need for Speed',
        'Pokémon',
        'Resident Evil',
        'The Legend of Zelda',
    ],

    // IGDB genre IDs — https://api.igdb.com/v4/genres
    'genres' => [
        'Adventure'    => 31,
        'Fighting'     => 4,
        'Hack & Slash' => 25,
        'Indie'        => 32,
        'Platform'     => 8,
        'Puzzle'       => 9,
        'Racing'       => 10,
        'RPG'          => 12,
        'Shooter'      => 5,
        'Simulation'   => 13,
        'Sports'       => 14,
        'Strategy'     => 15,
    ],

];
