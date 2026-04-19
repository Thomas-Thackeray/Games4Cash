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
            'id'         => 167,
            'icon'       => '🎮',
            'short'      => 'PS5',
            'slug'       => 'PlayStation-5',
            'desc'       => 'PS5 games hold their value well thanks to a strong exclusive library and continued high demand. Titles like Spider-Man 2, God of War Ragnarök, and Horizon Forbidden West regularly attract competitive cash offers. If you\'ve finished your collection or simply want to free up some shelf space, selling your PS5 games with us is quick and hassle-free.',
            'meta'       => 'Sell your PlayStation 5 games for cash. Get an instant price for PS5 titles including Spider-Man 2, God of War Ragnarök, and more — free door-to-door collection across the UK.',
            'highlights' => [
                'One of the strongest exclusive libraries of any generation',
                'Disc-only console model means physical games stay relevant',
                'Titles like Demon\'s Souls and Returnal rarely drop in price',
                'Growing back catalogue with consistent buyer demand',
                'Free UK-wide collection — no trips to the post office',
            ],
            'seo'        => [
                'heading' => 'Sell Your PlayStation 5 Games for Cash',
                'body'    => 'The PlayStation 5 has quickly built one of the strongest exclusive libraries of any console generation. Titles like Spider-Man 2, God of War Ragnarök, Demon\'s Souls, Returnal, and Horizon Forbidden West attract strong demand from buyers, which means your PS5 collection can hold real value even after you\'ve finished playing. Rather than letting your games sit on the shelf, sell them for cash and put the money towards your next purchase.',
                'body2'   => 'We offer instant prices, free door-to-door collection anywhere in the UK, and fast payment once your games have been received and checked. There are no seller fees, no private buyer negotiations, and no trips to the post office — just a straightforward cash offer for your PS5 titles. Add your games to the basket above, submit your quote, and we\'ll take care of everything else.',
            ],
        ],
        'PlayStation 4' => [
            'id'         => 48,
            'icon'       => '🎮',
            'short'      => 'PS4',
            'slug'       => 'PlayStation-4',
            'desc'       => 'With hundreds of millions of units sold worldwide, PS4 games remain in strong demand and trade in reliably. The platform\'s enormous back catalogue — spanning Red Dead Redemption 2, The Last of Us Part II, and Ghost of Tsushima — means most titles still command fair cash offers. If you\'ve upgraded to PS5, selling your PS4 library is a great way to fund your next purchase.',
            'meta'       => 'Sell your PlayStation 4 games for cash. Browse PS4 titles, get an instant price, and book a free collection. We buy everything from blockbusters to hidden gems.',
            'highlights' => [
                'Over 117 million units sold — one of the largest install bases ever',
                'Many titles backward-compatible with PS5, keeping demand high',
                'Exclusives like Bloodborne and Persona 5 rarely fall in price',
                'Deep back catalogue spanning 2013 to 2020',
                'We buy standard and special editions in good condition',
            ],
            'seo'        => [
                'heading' => 'Sell Your PlayStation 4 Games for Cash',
                'body'    => 'With over 117 million units sold, the PlayStation 4 has one of the largest game libraries ever assembled — and many of those titles are still actively sought after by buyers. From blockbusters like Red Dead Redemption 2, The Last of Us Part II, and Ghost of Tsushima to beloved exclusives like Bloodborne and Persona 5, PS4 games hold their value well, especially for popular or harder-to-find titles.',
                'body2'   => 'If you\'ve upgraded to PS5 or simply finished your backlog, selling your PS4 collection is an easy way to earn cash without any of the hassle of private selling. We accept all PS4 games in good condition, offer free UK-wide collection, and pay quickly once we\'ve checked your items. No auction, no waiting for a buyer, no listing fees — just a fair cash offer and a free pickup from your door.',
            ],
        ],
        'Xbox Series X|S' => [
            'id'         => 169,
            'icon'       => '🟩',
            'short'      => 'XSX',
            'slug'       => 'Xbox-Series-X-S',
            'desc'       => 'Xbox Series X|S has a growing library of exclusive titles, and many games support Smart Delivery — we buy them regardless of which version you own. First-party titles from Xbox Game Studios and high-profile third-party releases like Starfield can attract strong cash offers. We make it easy to sell your Series X|S collection with no fuss.',
            'meta'       => 'Sell your Xbox Series X|S games for cash. Get instant prices for Series X and Series S titles — free UK collection, fast payment.',
            'highlights' => [
                'Smart Delivery titles hold value on disc across both console versions',
                'Strong Xbox Game Studios exclusives with consistent buyer interest',
                'Physical games remain valuable even with Game Pass available',
                'Series X disc drive supports full back catalogue of Xbox One titles',
                'Instant cash prices — no seller fees or auction listings',
            ],
            'seo'        => [
                'heading' => 'Sell Your Xbox Series X|S Games for Cash',
                'body'    => 'The Xbox Series X|S generation has brought some of Microsoft\'s most ambitious releases to date. Titles like Halo Infinite, Forza Motorsport, Starfield, and a growing catalogue of third-party exclusives mean there\'s a steady market for physical Series X games. Many titles use Smart Delivery and carry value on disc regardless of which version of the console you own.',
                'body2'   => 'If you\'re clearing space, switching platforms, or just moving on, we\'ll give you a fair cash offer for your Xbox Series X|S games with no seller fees, no listing faff, and free collection straight from your door anywhere in the UK. Browse the titles above, check the cash value of your games, and submit a quote in minutes.',
            ],
        ],
        'Xbox One' => [
            'id'         => 49,
            'icon'       => '🟩',
            'short'      => 'XBO',
            'slug'       => 'Xbox-One',
            'desc'       => 'Xbox One has a decade-long library packed with popular titles that still attract solid trade-in interest. Whether you\'ve moved on to the newer Xbox Series X|S or are simply clearing your collection, we buy standard and special editions alike. Games like Forza Horizon 4, Halo 5, and the Gears of War series are always in demand.',
            'meta'       => 'Sell your Xbox One games for cash. We buy the full Xbox One library — get an instant price and arrange a free collection today.',
            'highlights' => [
                'Full decade of releases — 2013 through to the Series X transition era',
                'Many titles still played on Series X via backward compatibility',
                'Strong franchise lineup across Halo, Forza, and Gears of War',
                'Consistent buyer demand for popular and collector editions',
                'We buy Xbox One, One S, and One X compatible titles',
            ],
            'seo'        => [
                'heading' => 'Sell Your Xbox One Games for Cash',
                'body'    => 'The Xbox One library spans a full decade of releases, from the console\'s 2013 launch through to the transition era where titles also appeared on Series X. Popular franchises like Halo, Forza, Gears of War, and Assassin\'s Creed all have strong Xbox One entries that attract consistent interest from buyers. Many of these titles remain playable on Series X through backward compatibility, which keeps demand alive long after the hardware generation ended.',
                'body2'   => 'Whether you\'re upgrading to a newer console or simply want to clear your collection, we\'ll give you a straightforward cash offer — no auction, no waiting for a buyer, no posting. Browse the titles above, add your games to the basket, and we\'ll arrange free collection from your address and pay you quickly once we\'ve confirmed everything is in order.',
            ],
        ],
        'Nintendo Switch' => [
            'id'         => 130,
            'icon'       => '🔴',
            'short'      => 'NSW',
            'slug'       => 'Nintendo-Switch',
            'desc'       => 'Nintendo Switch games are famous for holding their value longer than almost any other platform — first-party Nintendo titles rarely drop significantly in price. If you have Zelda, Mario Kart, Pokémon, or Animal Crossing titles sitting unplayed, you could get a surprisingly competitive cash offer. We buy both standard Switch and Switch Lite compatible games.',
            'meta'       => 'Sell your Nintendo Switch games for cash. Switch titles hold their value well — get an instant price for Zelda, Mario Kart, Pokémon, and more.',
            'highlights' => [
                'First-party Nintendo titles rarely drop below RRP',
                'Over 140 million units sold — enormous and active install base',
                'Limited physical print runs drive strong collector demand',
                'Compatible with Switch, Switch Lite, and Switch OLED',
                'Some of the best value-holding physical games on the market',
            ],
            'seo'        => [
                'heading' => 'Sell Your Nintendo Switch Games for Cash',
                'body'    => 'Nintendo Switch games are widely regarded as the best value-holding physical games on the market. First-party titles from Nintendo — The Legend of Zelda: Tears of the Kingdom, Mario Kart 8 Deluxe, Pokémon Scarlet and Violet, Animal Crossing: New Horizons — rarely see the sharp price drops that affect other platforms, meaning your Switch collection is often worth more than you\'d expect.',
                'body2'   => 'Limited physical print runs and strong collector demand make Switch games a smart sell. We buy Switch games in good condition, offer free UK-wide collection, and pay out promptly — all without the hassle of selling privately. Whether you have a handful of titles or a full shelf, check what your collection is worth using the games above and get a quote in minutes.',
            ],
        ],
        'PC' => [
            'id'         => 6,
            'icon'       => '💻',
            'short'      => 'PC',
            'slug'       => 'PC',
            'desc'       => 'We buy physical PC games and boxed collector\'s editions. While most modern PC gaming has moved to digital storefronts, there is still strong collector interest in older boxed releases and special editions — particularly for classic RPGs, strategy titles, and limited-print games. If you have physical PC games gathering dust, get a quote today.',
            'meta'       => 'Sell your physical PC games for cash. We buy boxed PC titles, collector\'s editions, and classic releases — get an instant price and free collection.',
            'highlights' => [
                'Classic big-box PC games from the 90s and 00s highly sought after',
                'Digital shift means physical copies are increasingly scarce',
                'Collector editions and limited prints command strong prices',
                'Games no longer available digitally are especially valuable',
                'We buy boxed titles, complete editions, and sealed copies',
            ],
            'seo'        => [
                'heading' => 'Sell Your Physical PC Games for Cash',
                'body'    => 'Physical PC games occupy a unique space in the collector market. While mainstream PC gaming has largely shifted to digital storefronts like Steam, boxed PC titles — especially older releases, special editions, and games no longer available digitally — can command strong prices from collectors and enthusiasts. Classic boxed RPGs, strategy games, and big-box releases from the 1990s and 2000s are particularly sought after.',
                'body2'   => 'If you have physical PC games sitting in storage, it\'s worth checking what they could be worth. We offer instant cash prices, free collection from your door, and a simple, straightforward selling process with no hidden fees. Sealed copies, complete editions, and games with original manuals and inserts are always welcome.',
            ],
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

    // SEO descriptions shown at the bottom of each genre page (keyed by genre ID)
    'genre_descriptions' => [
        31 => [
            'heading' => 'Sell Your Adventure Games for Cash',
            'body'    => 'Adventure games have produced some of the most celebrated titles in gaming history — from the sweeping open worlds of Red Dead Redemption and The Legend of Zelda to the cinematic storytelling of Uncharted and The Last of Us. Their rich narratives and lasting reputations keep demand high long after release, meaning your collection can still hold real value. Whether you\'ve finished the story or simply moved on to your next journey, we\'ll give you a fast, fair cash offer with free door-to-door collection anywhere in the UK.',
        ],
        4 => [
            'heading' => 'Sell Your Fighting Games for Cash',
            'body'    => 'Fighting games have one of the most passionate communities in all of gaming. Franchises like Street Fighter, Mortal Kombat, Tekken, and Super Smash Bros. release new entries regularly, which means last year\'s edition is often sitting idle on the shelf. Physical fighting game titles trade well — particularly recent releases with strong competitive scenes. Check what your collection is worth and get a cash offer in minutes, with no hassle and no hidden fees.',
        ],
        25 => [
            'heading' => 'Sell Your Hack and Slash Games for Cash',
            'body'    => 'Hack and slash games deliver some of the most visceral and satisfying combat in gaming. From the stylish chaos of Devil May Cry to the mythological brutality of God of War and the relentless action of Bayonetta, these titles attract dedicated fanbases who keep demand consistently strong. If your collection has grown and your shelves are full, turn your hack and slash titles into cash today — add them to your basket and we\'ll collect them directly from your door.',
        ],
        32 => [
            'heading' => 'Sell Your Indie Games for Cash',
            'body'    => 'The indie scene has produced some of the most inventive games of the last decade. Hollow Knight, Hades, Celeste, Stardew Valley, and Undertale all began small and became cultural touchstones, with physical editions often limited in print run and sought after by collectors. Indie games may not have blockbuster marketing budgets, but they can command surprisingly strong cash prices. Browse your collection and see what your indie favourites are worth — you might be surprised.',
        ],
        8 => [
            'heading' => 'Sell Your Platformer Games for Cash',
            'body'    => 'Platform games have been at the heart of gaming since Mario first jumped across a screen, and they remain among the most popular and accessible titles on every console. From Nintendo\'s iconic first-party lineup to modern precision platformers like Crash Bandicoot, Ratchet & Clank, and Sonic Frontiers, these games appeal to players of all ages and consistently attract buyers. If your platform collection is gathering dust, let us turn it into cash — quickly, fairly, and with free collection from your home.',
        ],
        9 => [
            'heading' => 'Sell Your Puzzle Games for Cash',
            'body'    => 'Puzzle games reward creativity and lateral thinking, and the best of them — Portal, The Witness, Returnal, and Tetris Effect — leave a lasting impression long after the final solution clicks into place. Once completed, puzzle games often sit unplayed. Rather than leaving them on the shelf, add them to your basket and convert your finished collection into cash. We offer a straightforward selling process with no auctions, no buyers, and no waiting around.',
        ],
        10 => [
            'heading' => 'Sell Your Racing Games for Cash',
            'body'    => 'Racing games deliver the thrill of high-speed competition without leaving the sofa. Franchises like Gran Turismo, Forza Motorsport, F1, and Mario Kart have enormous audiences, and their physical editions remain in demand across generations of hardware. Whether you\'re upgrading to the latest instalment, switching platforms, or simply done with the track, we\'ll give you a competitive cash offer for your racing games — with free UK-wide collection and no seller fees.',
        ],
        12 => [
            'heading' => 'Sell Your RPG Games for Cash',
            'body'    => 'Role-playing games offer some of the deepest and most immersive experiences in gaming. With landmark series like Final Fantasy, The Witcher, Dark Souls, Elden Ring, and Persona, the genre is rich with titles that hold their value thanks to passionate fanbases and strong critical reputations. Limited physical editions and collector\'s releases can be particularly valuable. Check what your RPG collection is worth and get an instant cash offer — no listing, no waiting, just a straightforward sale.',
        ],
        5 => [
            'heading' => 'Sell Your Shooter Games for Cash',
            'body'    => 'Shooters dominate the gaming charts year after year, and for good reason — from sweeping single-player campaigns like Halo and Call of Duty to the relentless multiplayer of Battlefield and Destiny, the genre has something for everyone. Physical shooter titles trade reliably, especially recent entries in major franchises. If last year\'s shooter has been replaced by the next instalment, don\'t let it sit idle — check what it\'s worth and get a cash offer today.',
        ],
        13 => [
            'heading' => 'Sell Your Simulation Games for Cash',
            'body'    => 'Simulation games cover an enormous range — from farming and city-building to sports management and vehicle operation. Titles like Football Manager, The Sims, Farming Simulator, and Planet Coaster have built loyal audiences who invest hundreds of hours, and physical editions remain popular gifts and trade-ins. If you\'ve moved on to a newer version or simply want to free up shelf space, we\'ll give you a fair cash price for your simulation collection with free collection from your door.',
        ],
        14 => [
            'heading' => 'Sell Your Sports Games for Cash',
            'body'    => 'Sports games are among the best-selling titles every year — annual franchises like EA FC, NBA 2K, and WWE 2K move millions of copies and trade frequently between players looking for the latest edition. While last season\'s entry is quickly replaced, the demand for physical copies remains strong, and older titles in good condition still attract fair cash prices. Don\'t let your sports collection pile up — check what it\'s worth and sell it fast with free UK-wide collection.',
        ],
        15 => [
            'heading' => 'Sell Your Strategy Games for Cash',
            'body'    => 'Strategy games demand patience, forward thinking, and tactical decision-making — and franchises like XCOM, Civilization, Total War, and Fire Emblem have cultivated some of the most dedicated fanbases in gaming. Physical strategy titles, particularly on console, can hold their value well and are consistently sought after by collectors and new players alike. If your campaigns are complete and your shelves need clearing, add your strategy games to the basket and see how much cash your collection could earn.',
        ],
    ],

];
