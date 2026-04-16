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
        'PlayStation 5' => [
            'id'    => 167,
            'icon'  => '🎮',
            'short' => 'PS5',
            'desc'  => 'PS5 games hold their value well thanks to a strong exclusive library and continued high demand. Titles like Spider-Man 2, God of War Ragnarök, and Horizon Forbidden West regularly attract competitive cash offers. If you\'ve finished your collection or simply want to free up some shelf space, selling your PS5 games with us is quick and hassle-free.',
            'meta'  => 'Sell your PlayStation 5 games for cash. Get an instant price for PS5 titles including Spider-Man 2, God of War Ragnarök, and more — free door-to-door collection across the UK.',
        ],
        'PlayStation 4' => [
            'id'    => 48,
            'icon'  => '🎮',
            'short' => 'PS4',
            'desc'  => 'With hundreds of millions of units sold worldwide, PS4 games remain in strong demand and trade in reliably. The platform\'s enormous back catalogue — spanning Red Dead Redemption 2, The Last of Us Part II, and Ghost of Tsushima — means most titles still command fair cash offers. If you\'ve upgraded to PS5, selling your PS4 library is a great way to fund your next purchase.',
            'meta'  => 'Sell your PlayStation 4 games for cash. Browse PS4 titles, get an instant price, and book a free collection. We buy everything from blockbusters to hidden gems.',
        ],
        'Xbox Series X|S' => [
            'id'    => 169,
            'icon'  => '🟩',
            'short' => 'XSX',
            'desc'  => 'Xbox Series X|S has a growing library of exclusive titles, and many games support Smart Delivery — we buy them regardless of which version you own. First-party titles from Xbox Game Studios and high-profile third-party releases like Starfield can attract strong cash offers. We make it easy to sell your Series X|S collection with no fuss.',
            'meta'  => 'Sell your Xbox Series X|S games for cash. Get instant prices for Series X and Series S titles — free UK collection, fast payment.',
        ],
        'Xbox One' => [
            'id'    => 49,
            'icon'  => '🟩',
            'short' => 'XBO',
            'desc'  => 'Xbox One has a decade-long library packed with popular titles that still attract solid trade-in interest. Whether you\'ve moved on to the newer Xbox Series X|S or are simply clearing your collection, we buy standard and special editions alike. Games like Forza Horizon 4, Halo 5, and the Gears of War series are always in demand.',
            'meta'  => 'Sell your Xbox One games for cash. We buy the full Xbox One library — get an instant price and arrange a free collection today.',
        ],
        'Nintendo Switch' => [
            'id'    => 130,
            'icon'  => '🔴',
            'short' => 'NSW',
            'desc'  => 'Nintendo Switch games are famous for holding their value longer than almost any other platform — first-party Nintendo titles rarely drop significantly in price. If you have Zelda, Mario Kart, Pokémon, or Animal Crossing titles sitting unplayed, you could get a surprisingly competitive cash offer. We buy both standard Switch and Switch Lite compatible games.',
            'meta'  => 'Sell your Nintendo Switch games for cash. Switch titles hold their value well — get an instant price for Zelda, Mario Kart, Pokémon, and more.',
        ],
        'PC' => [
            'id'    => 6,
            'icon'  => '💻',
            'short' => 'PC',
            'desc'  => 'We buy physical PC games and boxed collector\'s editions. While most modern PC gaming has moved to digital storefronts, there is still strong collector interest in older boxed releases and special editions — particularly for classic RPGs, strategy titles, and limited-print games. If you have physical PC games gathering dust, get a quote today.',
            'meta'  => 'Sell your physical PC games for cash. We buy boxed PC titles, collector\'s editions, and classic releases — get an instant price and free collection.',
        ],
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
