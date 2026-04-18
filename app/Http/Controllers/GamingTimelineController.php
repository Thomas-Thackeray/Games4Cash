<?php

namespace App\Http\Controllers;

use App\Services\IgdbService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class GamingTimelineController extends Controller
{
    public function index(): View
    {
        $events = $this->events();

        $slugs = array_filter(array_column($events, 'igdb_slug'));

        $imageMap = Cache::remember('gaming_timeline_images', now()->addHours(24), function () use ($slugs) {
            if (empty($slugs)) {
                return [];
            }

            try {
                $slugList = implode(',', array_map(fn ($s) => '"' . $s . '"', $slugs));
                $igdb     = new IgdbService();
                $results  = $igdb->query('games', "fields slug,cover.image_id; where slug = ({$slugList}); limit 50;");

                $map = [];
                foreach ($results as $game) {
                    if (!empty($game['cover']['image_id'])) {
                        $map[$game['slug']] = 'https://images.igdb.com/igdb/image/upload/t_cover_big/' . $game['cover']['image_id'] . '.jpg';
                    }
                }
                return $map;
            } catch (\Throwable) {
                return [];
            }
        });

        return view('pages.gaming-timeline', compact('events', 'imageMap'));
    }

    private function events(): array
    {
        return [
            ['year' => '1958', 'tag' => 'game',     'title' => 'Tennis for Two',                   'igdb_slug' => null,                                        'desc' => 'Physicist William Higinbotham creates Tennis for Two on an oscilloscope at Brookhaven National Laboratory — widely regarded as the first interactive electronic game. It was never commercially released.'],
            ['year' => '1962', 'tag' => 'game',     'title' => 'Spacewar!',                         'igdb_slug' => null,                                        'desc' => 'MIT students develop Spacewar! — the first widely influential video game, played on the PDP-1 mainframe. It inspired virtually every space shooter that followed and was hugely influential on the early game industry.'],
            ['year' => '1972', 'tag' => 'console',  'title' => 'Magnavox Odyssey & Pong',           'igdb_slug' => null,                                        'desc' => 'The Magnavox Odyssey becomes the first commercial home console. Atari then releases Pong as an arcade machine — a table tennis simulation so addictive that the first unit broke down because the coin box was too full.'],
            ['year' => '1975', 'tag' => 'console',  'title' => 'Home Pong',                         'igdb_slug' => null,                                        'desc' => 'Atari releases a home version of Pong through Sears, selling 150,000 units. It marks the true beginning of the home console market and sparks a wave of Pong clones from competitors.'],
            ['year' => '1977', 'tag' => 'console',  'title' => 'Atari 2600',                        'igdb_slug' => null,                                        'desc' => 'The Atari 2600 launches with interchangeable cartridges, giving players access to an ever-growing library of games. It dominates living rooms for years and is still regarded as one of the most important consoles ever made.'],
            ['year' => '1978', 'tag' => 'game',     'title' => 'Space Invaders',                    'igdb_slug' => 'space-invaders',                            'desc' => 'Taito\'s Space Invaders arrives in arcades and causes a national coin shortage in Japan. It becomes the first "killer app" of gaming — a title so popular it drives console sales on its own when ported to the Atari 2600.'],
            ['year' => '1980', 'tag' => 'game',     'title' => 'Pac-Man',                           'igdb_slug' => 'pac-man',                                   'desc' => 'Namco releases Pac-Man, which becomes a global cultural phenomenon. The game is credited with attracting a broader audience to video games — including women — and remains one of the highest-grossing entertainment franchises of all time.'],
            ['year' => '1981', 'tag' => 'game',     'title' => 'Donkey Kong & Mario\'s Debut',      'igdb_slug' => 'donkey-kong',                               'desc' => 'Nintendo releases Donkey Kong, introducing a carpenter named Jumpman — later renamed Mario. It is the first game with a true narrative arc and platform jumping mechanics that would define a genre.'],
            ['year' => '1983', 'tag' => 'industry', 'title' => 'The Video Game Crash',              'igdb_slug' => null,                                        'desc' => 'The North American video game market collapses, losing around $3 billion in revenue. Caused by market oversaturation and a flood of low-quality games — most notoriously the Atari port of E.T. — the crash nearly kills the entire industry.'],
            ['year' => '1984', 'tag' => 'game',     'title' => 'Tetris',                            'igdb_slug' => 'tetris',                                    'desc' => 'Soviet software engineer Alexey Pajitnov creates Tetris on a Soviet Elektronika 60. The game spreads rapidly across the USSR before making its way west, eventually becoming the pack-in game for the Game Boy and one of the best-selling games of all time.'],
            ['year' => '1985', 'tag' => 'console',  'title' => 'Nintendo NES & Super Mario Bros',   'igdb_slug' => 'super-mario-bros',                          'desc' => 'Nintendo releases the NES in North America, single-handedly reviving the video game market after the 1983 crash. Bundled with Super Mario Bros, it sets new standards for platform game design and redefines what home gaming can be.'],
            ['year' => '1986', 'tag' => 'game',     'title' => 'The Legend of Zelda',               'igdb_slug' => 'the-legend-of-zelda',                       'desc' => 'Nintendo\'s The Legend of Zelda introduces open-world exploration, non-linear gameplay, and save functionality to home consoles. Its influence on action-adventure game design is still felt in virtually every open-world game made today.'],
            ['year' => '1989', 'tag' => 'console',  'title' => 'Game Boy',                          'igdb_slug' => 'tetris--1',                                 'desc' => 'Nintendo releases the Game Boy — a handheld console with a long battery life, durable build, and a killer app in Tetris. It dominates portable gaming for over a decade and launches a lineage that continues through the Nintendo DS and 3DS.'],
            ['year' => '1991', 'tag' => 'game',     'title' => 'Sonic the Hedgehog',                'igdb_slug' => 'sonic-the-hedgehog',                        'desc' => 'Sega launches Sonic the Hedgehog alongside the Mega Drive (Genesis), setting up one of gaming\'s greatest console rivalries. Sonic\'s speed-focused gameplay and attitude-heavy marketing challenge Nintendo\'s dominance for the first time.'],
            ['year' => '1992', 'tag' => 'game',     'title' => 'Mortal Kombat & the ESRB',          'igdb_slug' => 'mortal-kombat',                             'desc' => 'Mortal Kombat\'s graphic violence sparks congressional hearings in the United States, leading directly to the creation of the ESRB ratings system in 1994. Gaming would never be treated as purely children\'s entertainment again.'],
            ['year' => '1993', 'tag' => 'game',     'title' => 'Doom',                              'igdb_slug' => 'doom',                                      'desc' => 'id Software releases Doom and effectively invents the modern first-person shooter. Distributed via shareware, it reaches millions of players and introduces concepts — modding, online multiplayer, level editors — that remain central to gaming culture.'],
            ['year' => '1994', 'tag' => 'console',  'title' => 'PlayStation Launches in Japan',     'igdb_slug' => null,                                        'desc' => 'Sony enters the gaming market with the PlayStation, initially developed in partnership with Nintendo. Its CD-ROM format allows for larger, cinematic games and attracts a wave of third-party developers away from Nintendo and Sega.'],
            ['year' => '1996', 'tag' => 'game',     'title' => 'Super Mario 64 & 3D Gaming',        'igdb_slug' => 'super-mario-64',                            'desc' => 'Nintendo releases Super Mario 64 alongside the Nintendo 64, demonstrating how 3D game worlds should work. Its camera system, movement mechanics, and open level design become the template for 3D platformers for the next two decades.'],
            ['year' => '1997', 'tag' => 'game',     'title' => 'Final Fantasy VII & Nokia Snake',   'igdb_slug' => 'final-fantasy-vii',                         'desc' => 'Final Fantasy VII brings cinematic storytelling to a mainstream console audience, proving games can carry emotional narratives. The same year, Nokia developer Taneli Armanto ships Snake on the Nokia 6110 — reaching more players than almost any other game in history.'],
            ['year' => '1998', 'tag' => 'game',     'title' => 'The Legend of Zelda: Ocarina of Time', 'igdb_slug' => 'the-legend-of-zelda-ocarina-of-time',   'desc' => 'Widely considered the greatest video game ever made, Ocarina of Time translates Zelda\'s exploration and puzzle design into 3D with extraordinary polish. Its Z-targeting combat system and dungeon design set the gold standard for action-adventure games.'],
            ['year' => '2001', 'tag' => 'console',  'title' => 'Xbox & Halo Launch',                'igdb_slug' => 'halo-combat-evolved',                       'desc' => 'Microsoft enters the console market with the Xbox, bundling Halo: Combat Evolved as a launch title. Halo redefines first-person shooters on console with its two-weapon system, regenerating shields, and epic campaign — and the Xbox goes on to become the dominant console brand in North America.'],
            ['year' => '2002', 'tag' => 'tech',     'title' => 'Xbox Live',                         'igdb_slug' => null,                                        'desc' => 'Microsoft launches Xbox Live, the first mainstream online console gaming service. It normalises broadband multiplayer on console, introduces friend lists and voice chat, and sets the template for PlayStation Network and every online gaming service that follows.'],
            ['year' => '2004', 'tag' => 'game',     'title' => 'World of Warcraft',                 'igdb_slug' => 'world-of-warcraft',                         'desc' => 'Blizzard Entertainment launches World of Warcraft, which peaks at over 12 million subscribers and dominates the MMO genre for a decade. Its influence on game design, social dynamics, and subscription monetisation is still felt throughout the industry.'],
            ['year' => '2005', 'tag' => 'console',  'title' => 'Xbox 360',                          'igdb_slug' => null,                                        'desc' => 'Microsoft launches the Xbox 360 a year ahead of Sony and Nintendo, gaining a significant head start in the seventh console generation. High-definition gaming becomes the new standard, and Xbox Live evolves into a thriving online marketplace.'],
            ['year' => '2006', 'tag' => 'console',  'title' => 'PlayStation 3 & Nintendo Wii',      'igdb_slug' => null,                                        'desc' => 'Sony launches the PS3 with the powerful but expensive Cell processor and a built-in Blu-ray drive. Nintendo counters with the Wii — motion controls, accessible design, and a price point that makes it a phenomenon, outselling both competitors.'],
            ['year' => '2007', 'tag' => 'mobile',   'title' => 'The iPhone Changes Everything',     'igdb_slug' => null,                                        'desc' => 'Apple launches the iPhone, and within a year the App Store transforms mobile phones into a gaming platform. Casual games like Angry Birds and Fruit Ninja reach billions of players and introduce the free-to-play model to a mass audience.'],
            ['year' => '2009', 'tag' => 'game',     'title' => 'Minecraft Alpha',                   'igdb_slug' => 'minecraft',                                 'desc' => 'Markus "Notch" Persson releases Minecraft in alpha. Its procedurally generated worlds and creative freedom captivate millions — it goes on to become the best-selling video game of all time, with over 300 million copies sold across all platforms.'],
            ['year' => '2011', 'tag' => 'game',     'title' => 'The Elder Scrolls V: Skyrim',       'igdb_slug' => 'the-elder-scrolls-v-skyrim',                'desc' => 'Bethesda releases Skyrim to universal acclaim. Its vast open world, emergent storytelling, and modding scene make it a cultural landmark — and a game that is still being re-released and actively played well over a decade later.'],
            ['year' => '2013', 'tag' => 'console',  'title' => 'PlayStation 4 & Xbox One',          'igdb_slug' => null,                                        'desc' => 'Sony and Microsoft launch the eighth console generation within a week of each other. The PS4 launches at £349 versus the Xbox One\'s £429, and Sony\'s stronger indie support and lack of DRM restrictions help it dominate the generation with over 117 million units sold.'],
            ['year' => '2016', 'tag' => 'mobile',   'title' => 'Pokémon GO',                        'igdb_slug' => 'pokemon-go',                                'desc' => 'Niantic releases Pokémon GO, which becomes a global phenomenon almost overnight. At its peak, over 232 million people play it monthly. The game introduces augmented reality gaming to a mainstream audience and generates over $1 billion in revenue in its first year.'],
            ['year' => '2017', 'tag' => 'console',  'title' => 'Nintendo Switch',                   'igdb_slug' => 'the-legend-of-zelda-breath-of-the-wild',   'desc' => 'Nintendo releases the Switch — a hybrid console that works both as a home console and a handheld. Launching alongside The Legend of Zelda: Breath of the Wild, it becomes one of the fastest-selling consoles in history and reinvigorates Nintendo\'s position in the market.'],
            ['year' => '2018', 'tag' => 'game',     'title' => 'Fortnite & the Battle Royale Era',  'igdb_slug' => 'fortnite',                                  'desc' => 'Epic Games releases Fortnite Battle Royale as a free-to-play title, rapidly growing to over 125 million players. Its combination of building mechanics, seasonal content, and celebrity crossovers creates the blueprint for live-service gaming.'],
            ['year' => '2020', 'tag' => 'console',  'title' => 'PlayStation 5 & Xbox Series X|S',   'igdb_slug' => null,                                        'desc' => 'The ninth console generation launches during a global pandemic, with demand massively outstripping supply due to chip shortages. The PS5 and Xbox Series X|S introduce ultra-fast SSD loading and ray tracing, while Game Pass reshapes how players access games.'],
            ['year' => '2022', 'tag' => 'game',     'title' => 'Elden Ring',                        'igdb_slug' => 'elden-ring',                                'desc' => 'FromSoftware releases Elden Ring — a collaboration with author George R.R. Martin — to near-universal critical acclaim. It sells over 20 million copies and proves the Souls formula can work for a mainstream audience with the addition of an open world.'],
            ['year' => '2023', 'tag' => 'game',     'title' => 'Baldur\'s Gate 3',                  'igdb_slug' => 'baldurs-gate-3',                            'desc' => 'Larian Studios releases Baldur\'s Gate 3, an extraordinarily deep and polished RPG that wins multiple Game of the Year awards. Its success reignites mainstream interest in traditional RPGs and demonstrates what a fully realised single-player game can still achieve in the live-service era.'],
            ['year' => '2024', 'tag' => 'industry', 'title' => 'The Era of Consolidation',          'igdb_slug' => null,                                        'desc' => 'Microsoft completes its $69 billion acquisition of Activision Blizzard, the largest deal in gaming history. As publishers consolidate and Xbox titles begin appearing on PlayStation, the traditional console exclusivity model starts to shift — raising questions about what the next era of gaming will look like.'],
        ];
    }
}
