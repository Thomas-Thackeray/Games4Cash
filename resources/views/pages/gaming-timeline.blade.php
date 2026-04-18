@extends('layouts.app')

@section('title', 'History of Gaming — A Timeline of Key Moments')
@section('meta_description', 'From Tennis for Two in 1958 to the latest generation of consoles — explore the most important events in video game history.')
@section('canonical', route('gaming-timeline'))

@push('head_meta')
<style>
    .tl-hero {
        background: linear-gradient(135deg, var(--bg-2) 0%, var(--bg) 100%);
        border-bottom: 1px solid var(--border);
        padding: 3.5rem 0 3rem;
    }

    /* Timeline layout */
    .timeline {
        position: relative;
        max-width: 860px;
        margin: 0 auto;
        padding: 2rem 0 4rem;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 50%;
        top: 0; bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, transparent, var(--border) 5%, var(--border) 95%, transparent);
        transform: translateX(-50%);
    }

    .tl-item {
        display: grid;
        grid-template-columns: 1fr 60px 1fr;
        gap: 0;
        margin-bottom: 2.5rem;
        position: relative;
    }

    /* Left-side card */
    .tl-item:nth-child(odd)  .tl-card { grid-column: 1; text-align: right; padding-right: 2rem; }
    .tl-item:nth-child(odd)  .tl-mid  { grid-column: 2; }
    .tl-item:nth-child(odd)  .tl-empty{ grid-column: 3; }

    /* Right-side card */
    .tl-item:nth-child(even) .tl-empty{ grid-column: 1; }
    .tl-item:nth-child(even) .tl-mid  { grid-column: 2; }
    .tl-item:nth-child(even) .tl-card { grid-column: 3; text-align: left;  padding-left: 2rem; }

    .tl-card {
        background: var(--bg-2);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 1.1rem 1.25rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .tl-card:hover {
        border-color: rgba(230,57,70,0.35);
        box-shadow: 0 4px 20px rgba(230,57,70,0.06);
    }

    .tl-mid {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0;
        position: relative;
        z-index: 1;
    }
    .tl-year {
        background: var(--accent);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        padding: 0.3rem 0.55rem;
        border-radius: 6px;
        white-space: nowrap;
        margin-top: 1rem;
    }
    .tl-dot {
        width: 12px; height: 12px;
        border-radius: 50%;
        background: var(--accent);
        border: 3px solid var(--bg);
        margin-top: 0.5rem;
        flex-shrink: 0;
    }

    .tl-tag {
        display: inline-block;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
        margin-bottom: 0.5rem;
    }
    .tl-tag--console  { background: rgba(59,130,246,0.15);  color: #60a5fa; }
    .tl-tag--game     { background: rgba(230,57,70,0.12);   color: var(--accent); }
    .tl-tag--industry { background: rgba(16,185,129,0.12);  color: #34d399; }
    .tl-tag--tech     { background: rgba(251,191,36,0.12);  color: #fbbf24; }
    .tl-tag--mobile   { background: rgba(167,139,250,0.12); color: #a78bfa; }

    .tl-title {
        font-size: 0.97rem;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 0.4rem;
        line-height: 1.35;
    }
    .tl-desc {
        font-size: 0.83rem;
        color: var(--text-muted);
        line-height: 1.65;
        margin: 0;
    }

    /* Mobile: single column */
    @media (max-width: 640px) {
        .timeline::before { left: 20px; }
        .tl-item { grid-template-columns: 40px 1fr; gap: 0; }
        .tl-item:nth-child(odd)  .tl-card,
        .tl-item:nth-child(even) .tl-card  { grid-column: 2; text-align: left; padding-left: 1.25rem; padding-right: 0; }
        .tl-item:nth-child(odd)  .tl-mid,
        .tl-item:nth-child(even) .tl-mid   { grid-column: 1; }
        .tl-item:nth-child(odd)  .tl-empty,
        .tl-item:nth-child(even) .tl-empty { display: none; }
        .tl-year { font-size: 0.65rem; padding: 0.2rem 0.4rem; }
    }
</style>
@endpush

@section('content')

<!-- ===== HERO ===== -->
<div class="tl-hero">
    <div class="container">
        <p style="font-size:0.75rem; letter-spacing:0.15em; text-transform:uppercase; color:var(--accent); font-weight:700; margin-bottom:0.5rem;">History</p>
        <h1 class="section-title">The History of Video Games</h1>
        <p style="color:var(--text-muted); font-size:0.97rem; line-height:1.75; margin-top:0.85rem; max-width:600px;">
            From a physics experiment in 1958 to a global industry worth hundreds of billions — the story of video games is one of the fastest and most remarkable cultural revolutions in human history.
        </p>
        <div style="display:flex; flex-wrap:wrap; gap:0.5rem; margin-top:1.5rem;">
            <span class="tl-tag tl-tag--console">Console</span>
            <span class="tl-tag tl-tag--game">Landmark Game</span>
            <span class="tl-tag tl-tag--industry">Industry</span>
            <span class="tl-tag tl-tag--tech">Technology</span>
            <span class="tl-tag tl-tag--mobile">Mobile</span>
        </div>
    </div>
</div>

<!-- ===== TIMELINE ===== -->
<section class="section">
    <div class="container">
    <div class="timeline">

        @php
        $events = [
            ['year'=>'1958','tag'=>'game',    'title'=>'Tennis for Two',                  'desc'=>'Physicist William Higinbotham creates Tennis for Two on an oscilloscope at Brookhaven National Laboratory — widely regarded as the first interactive electronic game. It was never commercially released.'],
            ['year'=>'1962','tag'=>'game',    'title'=>'Spacewar!',                       'desc'=>'MIT students develop Spacewar! — the first widely influential video game, played on the PDP-1 mainframe. It inspired virtually every space shooter that followed and was hugely influential on the early game industry.'],
            ['year'=>'1972','tag'=>'console', 'title'=>'Magnavox Odyssey & Pong',         'desc'=>'The Magnavox Odyssey becomes the first commercial home console. Atari then releases Pong as an arcade machine — a table tennis simulation so addictive that the first unit broke down because the coin box was too full.'],
            ['year'=>'1975','tag'=>'console', 'title'=>'Home Pong',                       'desc'=>'Atari releases a home version of Pong through Sears, selling 150,000 units. It marks the true beginning of the home console market and sparks a wave of Pong clones from competitors.'],
            ['year'=>'1977','tag'=>'console', 'title'=>'Atari 2600',                      'desc'=>'The Atari 2600 launches with interchangeable cartridges, giving players access to an ever-growing library of games. It dominates living rooms for years and is still regarded as one of the most important consoles ever made.'],
            ['year'=>'1978','tag'=>'game',    'title'=>'Space Invaders',                  'desc'=>'Taito\'s Space Invaders arrives in arcades and causes a national coin shortage in Japan. It becomes the first "killer app" of gaming — a title so popular it drives console sales on its own when ported to the Atari 2600.'],
            ['year'=>'1980','tag'=>'game',    'title'=>'Pac-Man',                         'desc'=>'Namco releases Pac-Man, which becomes a global cultural phenomenon. The game is credited with attracting a broader audience to video games — including women — and remains one of the highest-grossing entertainment franchises of all time.'],
            ['year'=>'1981','tag'=>'game',    'title'=>'Donkey Kong & Mario\'s Debut',    'desc'=>'Nintendo releases Donkey Kong, introducing a carpenter named Jumpman — later renamed Mario. It is the first game with a true narrative arc and platform jumping mechanics that would define a genre.'],
            ['year'=>'1983','tag'=>'industry','title'=>'The Video Game Crash',             'desc'=>'The North American video game market collapses, losing around $3 billion in revenue. Caused by market oversaturation and a flood of low-quality games — most notoriously the Atari port of E.T. — the crash nearly kills the entire industry.'],
            ['year'=>'1984','tag'=>'game',    'title'=>'Tetris',                          'desc'=>'Soviet software engineer Alexey Pajitnov creates Tetris on a Soviet Elektronika 60. The game spreads rapidly across the USSR before making its way west, eventually becoming the pack-in game for the Game Boy and one of the best-selling games of all time.'],
            ['year'=>'1985','tag'=>'console', 'title'=>'Nintendo NES & Super Mario Bros', 'desc'=>'Nintendo releases the NES in North America, single-handedly reviving the video game market after the 1983 crash. Bundled with Super Mario Bros, it sets new standards for platform game design and redefines what home gaming can be.'],
            ['year'=>'1986','tag'=>'game',    'title'=>'The Legend of Zelda',             'desc'=>'Nintendo\'s The Legend of Zelda introduces open-world exploration, non-linear gameplay, and save functionality to home consoles. Its influence on action-adventure game design is still felt in virtually every open-world game made today.'],
            ['year'=>'1989','tag'=>'console', 'title'=>'Game Boy',                       'desc'=>'Nintendo releases the Game Boy — a handheld console with a long battery life, durable build, and a killer app in Tetris. It dominates portable gaming for over a decade and launches a lineage that continues through the Nintendo DS and 3DS.'],
            ['year'=>'1991','tag'=>'game',    'title'=>'Sonic the Hedgehog',              'desc'=>'Sega launches Sonic the Hedgehog alongside the Mega Drive (Genesis), setting up one of gaming\'s greatest console rivalries. Sonic\'s speed-focused gameplay and attitude-heavy marketing challenge Nintendo\'s dominance for the first time.'],
            ['year'=>'1992','tag'=>'game',    'title'=>'Mortal Kombat & the ESRB',        'desc'=>'Mortal Kombat\'s graphic violence sparks congressional hearings in the United States, leading directly to the creation of the ESRB ratings system in 1994. Gaming would never be treated as purely children\'s entertainment again.'],
            ['year'=>'1993','tag'=>'game',    'title'=>'Doom',                            'desc'=>'id Software releases Doom and effectively invents the modern first-person shooter. Distributed via shareware, it reaches millions of players and introduces concepts — modding, online multiplayer, level editors — that remain central to gaming culture.'],
            ['year'=>'1994','tag'=>'console', 'title'=>'PlayStation Launches in Japan',   'desc'=>'Sony enters the gaming market with the PlayStation, initially developed in partnership with Nintendo. Its CD-ROM format allows for larger, cinematic games and attracts a wave of third-party developers away from Nintendo and Sega.'],
            ['year'=>'1996','tag'=>'game',    'title'=>'Super Mario 64 & 3D Gaming',      'desc'=>'Nintendo releases Super Mario 64 alongside the Nintendo 64, demonstrating how 3D game worlds should work. Its camera system, movement mechanics, and open level design become the template for 3D platformers for the next two decades.'],
            ['year'=>'1997','tag'=>'game',    'title'=>'Final Fantasy VII & Nokia Snake',  'desc'=>'Final Fantasy VII brings cinematic storytelling to a mainstream console audience, proving games can carry emotional narratives. The same year, Nokia developer Taneli Armanto ships Snake on the Nokia 6110 — reaching more players than almost any other game in history.'],
            ['year'=>'1998','tag'=>'game',    'title'=>'The Legend of Zelda: Ocarina of Time','desc'=>'Widely considered the greatest video game ever made, Ocarina of Time translates Zelda\'s exploration and puzzle design into 3D with extraordinary polish. Its Z-targeting combat system and dungeon design set the gold standard for action-adventure games.'],
            ['year'=>'2001','tag'=>'console', 'title'=>'Xbox & Halo Launch',              'desc'=>'Microsoft enters the console market with the Xbox, bundling Halo: Combat Evolved as a launch title. Halo redefines first-person shooters on console with its two-weapon system, regenerating shields, and epic campaign — and the Xbox goes on to become the dominant console brand in North America.'],
            ['year'=>'2002','tag'=>'tech',    'title'=>'Xbox Live',                       'desc'=>'Microsoft launches Xbox Live, the first mainstream online console gaming service. It normalises broadband multiplayer on console, introduces friend lists and voice chat, and sets the template for PlayStation Network and every online gaming service that follows.'],
            ['year'=>'2004','tag'=>'game',    'title'=>'World of Warcraft',               'desc'=>'Blizzard Entertainment launches World of Warcraft, which peaks at over 12 million subscribers and dominates the MMO genre for a decade. Its influence on game design, social dynamics, and subscription monetisation is still felt throughout the industry.'],
            ['year'=>'2005','tag'=>'console', 'title'=>'Xbox 360',                       'desc'=>'Microsoft launches the Xbox 360 a year ahead of Sony and Nintendo, gaining a significant head start in the seventh console generation. High-definition gaming becomes the new standard, and Xbox Live evolves into a thriving online marketplace.'],
            ['year'=>'2006','tag'=>'console', 'title'=>'PlayStation 3 & Nintendo Wii',   'desc'=>'Sony launches the PS3 with the powerful but expensive Cell processor and a built-in Blu-ray drive. Nintendo counters with the Wii — motion controls, accessible design, and a price point that makes it a phenomenon, outselling both competitors.'],
            ['year'=>'2007','tag'=>'mobile',  'title'=>'The iPhone Changes Everything',   'desc'=>'Apple launches the iPhone, and within a year the App Store transforms mobile phones into a gaming platform. Casual games like Angry Birds and Fruit Ninja reach billions of players and introduce the free-to-play model to a mass audience.'],
            ['year'=>'2009','tag'=>'game',    'title'=>'Minecraft Alpha',                 'desc'=>'Markus "Notch" Persson releases Minecraft in alpha. Its procedurally generated worlds and creative freedom captivate millions — it goes on to become the best-selling video game of all time, with over 300 million copies sold across all platforms.'],
            ['year'=>'2011','tag'=>'game',    'title'=>'The Elder Scrolls V: Skyrim',     'desc'=>'Bethesda releases Skyrim to universal acclaim. Its vast open world, emergent storytelling, and modding scene make it a cultural landmark — and a game that is still being re-released and actively played well over a decade later.'],
            ['year'=>'2013','tag'=>'console', 'title'=>'PlayStation 4 & Xbox One',       'desc'=>'Sony and Microsoft launch the eighth console generation within a week of each other. The PS4 launches at £349 versus the Xbox One\'s £429, and Sony\'s stronger indie support and lack of DRM restrictions help it dominate the generation with over 117 million units sold.'],
            ['year'=>'2016','tag'=>'mobile',  'title'=>'Pokémon GO',                     'desc'=>'Niantic releases Pokémon GO, which becomes a global phenomenon almost overnight. At its peak, over 232 million people play it monthly. The game introduces augmented reality gaming to a mainstream audience and generates over $1 billion in revenue in its first year.'],
            ['year'=>'2017','tag'=>'console', 'title'=>'Nintendo Switch',                'desc'=>'Nintendo releases the Switch — a hybrid console that works both as a home console and a handheld. Launching alongside The Legend of Zelda: Breath of the Wild, it becomes one of the fastest-selling consoles in history and reinvigorates Nintendo\'s position in the market.'],
            ['year'=>'2018','tag'=>'game',    'title'=>'Fortnite & the Battle Royale Era','desc'=>'Epic Games releases Fortnite Battle Royale as a free-to-play title, rapidly growing to over 125 million players. Its combination of building mechanics, seasonal content, and celebrity crossovers creates the blueprint for live-service gaming.'],
            ['year'=>'2020','tag'=>'console', 'title'=>'PlayStation 5 & Xbox Series X|S','desc'=>'The ninth console generation launches during a global pandemic, with demand massively outstripping supply due to chip shortages. The PS5 and Xbox Series X|S introduce ultra-fast SSD loading and ray tracing, while Game Pass reshapes how players access games.'],
            ['year'=>'2022','tag'=>'game',    'title'=>'Elden Ring',                     'desc'=>'FromSoftware releases Elden Ring — a collaboration with author George R.R. Martin — to near-universal critical acclaim. It sells over 20 million copies and proves the Souls formula can work for a mainstream audience with the addition of an open world.'],
            ['year'=>'2023','tag'=>'game',    'title'=>'Baldur\'s Gate 3',               'desc'=>'Larian Studios releases Baldur\'s Gate 3, an extraordinarily deep and polished RPG that wins multiple Game of the Year awards. Its success reignites mainstream interest in traditional RPGs and demonstrates what a fully realised single-player game can still achieve in the live-service era.'],
            ['year'=>'2024','tag'=>'industry','title'=>'The Era of Consolidation',        'desc'=>'Microsoft completes its $69 billion acquisition of Activision Blizzard, the largest deal in gaming history. As publishers consolidate and Xbox titles begin appearing on PlayStation, the traditional console exclusivity model starts to shift — raising questions about what the next era of gaming will look like.'],
        ];
        @endphp

        @foreach($events as $event)
        <div class="tl-item fade-up">
            @if($loop->odd)
                <div class="tl-card">
                    <span class="tl-tag tl-tag--{{ $event['tag'] }}">{{ ucfirst($event['tag'] === 'game' ? 'Landmark Game' : ($event['tag'] === 'tech' ? 'Technology' : ($event['tag'] === 'mobile' ? 'Mobile' : ucfirst($event['tag'])))) }}</span>
                    <div class="tl-title">{{ $event['title'] }}</div>
                    <p class="tl-desc">{{ $event['desc'] }}</p>
                </div>
                <div class="tl-mid">
                    <div class="tl-year">{{ $event['year'] }}</div>
                    <div class="tl-dot"></div>
                </div>
                <div class="tl-empty"></div>
            @else
                <div class="tl-empty"></div>
                <div class="tl-mid">
                    <div class="tl-year">{{ $event['year'] }}</div>
                    <div class="tl-dot"></div>
                </div>
                <div class="tl-card">
                    <span class="tl-tag tl-tag--{{ $event['tag'] }}">{{ ucfirst($event['tag'] === 'game' ? 'Landmark Game' : ($event['tag'] === 'tech' ? 'Technology' : ($event['tag'] === 'mobile' ? 'Mobile' : ucfirst($event['tag'])))) }}</span>
                    <div class="tl-title">{{ $event['title'] }}</div>
                    <p class="tl-desc">{{ $event['desc'] }}</p>
                </div>
            @endif
        </div>
        @endforeach

    </div>
    </div>
</section>

<!-- ===== CTA ===== -->
<section class="section" style="border-top:1px solid var(--border); background:rgba(255,255,255,0.02);">
    <div class="container" style="text-align:center;">
        <h2 style="font-size:1.2rem; font-weight:700; margin-bottom:0.75rem;">Own a Piece of Gaming History?</h2>
        <p style="color:var(--text-muted); font-size:0.93rem; max-width:460px; margin:0 auto 1.5rem; line-height:1.7;">
            If you have games from any era sitting on the shelf, find out what they're worth and sell them for cash — fast, free collection, no hassle.
        </p>
        <a href="{{ route('search') }}" class="btn btn--primary">💰 See What Your Games Are Worth</a>
    </div>
</section>

@endsection
