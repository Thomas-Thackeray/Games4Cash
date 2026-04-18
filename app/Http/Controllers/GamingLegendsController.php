<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class GamingLegendsController extends Controller
{
    public function index(): View
    {
        $people = $this->people();

        $wikiTitles = array_filter(array_column($people, 'wiki'));

        $photoMap = Cache::remember('gaming_legends_photos_v1', now()->addHours(24), function () use ($wikiTitles) {
            if (empty($wikiTitles)) {
                return [];
            }

            try {
                $titles   = implode('|', $wikiTitles);
                $response = Http::timeout(10)->get('https://en.wikipedia.org/w/api.php', [
                    'action'     => 'query',
                    'titles'     => $titles,
                    'prop'       => 'pageimages',
                    'format'     => 'json',
                    'pithumbsize'=> 300,
                    'pilicense'  => 'any',
                ]);

                if (! $response->successful()) {
                    return [];
                }

                $pages = $response->json('query.pages', []);
                $map   = [];

                foreach ($pages as $page) {
                    if (!empty($page['thumbnail']['source'])) {
                        $map[$page['title']] = $page['thumbnail']['source'];
                    }
                }

                return $map;
            } catch (\Throwable) {
                return [];
            }
        });

        return view('pages.gaming-legends', compact('people', 'photoMap'));
    }

    private function people(): array
    {
        return [
            [
                'name'     => 'Shigeru Miyamoto',
                'wiki'     => 'Shigeru Miyamoto',
                'role'     => 'Game Designer & Producer',
                'category' => 'designer',
                'flag'     => '🇯🇵',
                'born'     => '1952',
                'known'    => ['Super Mario Bros', 'The Legend of Zelda', 'Donkey Kong', 'Pikmin'],
                'bio'      => 'Nintendo\'s creative genius and the father of modern game design. Miyamoto invented platform gaming with Donkey Kong, then defined the genre with Super Mario Bros. His philosophy — that games should be intuitive and joyful for everyone — shaped the entire industry.',
            ],
            [
                'name'     => 'Hideo Kojima',
                'wiki'     => 'Hideo Kojima',
                'role'     => 'Game Director & Writer',
                'category' => 'designer',
                'flag'     => '🇯🇵',
                'born'     => '1963',
                'known'    => ['Metal Gear Solid', 'Death Stranding', 'Silent Hills (cancelled)'],
                'bio'      => 'The auteur of video games. Kojima pioneered the stealth genre with Metal Gear and brought cinematic storytelling to games decades before it was fashionable. After a public split from Konami, he founded Kojima Productions and released the genre-defying Death Stranding.',
            ],
            [
                'name'     => 'Hidetaka Miyazaki',
                'wiki'     => 'Hidetaka Miyazaki',
                'role'     => 'Game Director & President of FromSoftware',
                'category' => 'designer',
                'flag'     => '🇯🇵',
                'born'     => '1974',
                'known'    => ['Dark Souls', 'Bloodborne', 'Sekiro', 'Elden Ring'],
                'bio'      => 'The architect of the Souls genre. Miyazaki joined FromSoftware as an office worker and became its president through creative obsession. His games are famously difficult, deeply lore-rich, and have generated one of the most passionate communities in gaming history.',
            ],
            [
                'name'     => 'Gabe Newell',
                'wiki'     => 'Gabe Newell',
                'role'     => 'Co-founder & CEO of Valve',
                'category' => 'business',
                'flag'     => '🇺🇸',
                'born'     => '1962',
                'known'    => ['Half-Life', 'Counter-Strike', 'Steam', 'Dota 2'],
                'bio'      => 'Former Microsoft employee who co-founded Valve and built Steam into the dominant PC gaming marketplace. Half-Life redefined the FPS genre, and Steam\'s digital distribution model changed how the world buys and plays games. Few individuals have had a greater structural impact on PC gaming.',
            ],
            [
                'name'     => 'John Carmack',
                'wiki'     => 'John Carmack',
                'role'     => 'Programmer & Technical Director',
                'category' => 'developer',
                'flag'     => '🇺🇸',
                'born'     => '1970',
                'known'    => ['Doom', 'Quake', 'Wolfenstein 3D', 'id Tech engine'],
                'bio'      => 'Widely regarded as the greatest programmer in gaming history. Carmack\'s rendering and networking code for Doom and Quake defined the first-person shooter and established the template for 3D game engines. He later moved to Oculus VR to pursue his belief that VR is the future of computing.',
            ],
            [
                'name'     => 'Alexey Pajitnov',
                'wiki'     => 'Alexey Pajitnov',
                'role'     => 'Software Engineer & Game Designer',
                'category' => 'designer',
                'flag'     => '🇷🇺',
                'born'     => '1956',
                'known'    => ['Tetris'],
                'bio'      => 'Created Tetris in 1984 while working at the Soviet Academy of Sciences — purely for fun, on a Soviet Elektronika 60. The game spread across the USSR before making its way west, becoming one of the best-selling and most-played games in history. Pajitnov did not profit from it for years due to Soviet IP laws.',
            ],
            [
                'name'     => 'Nolan Bushnell',
                'wiki'     => 'Nolan Bushnell',
                'role'     => 'Entrepreneur & Atari Founder',
                'category' => 'pioneer',
                'flag'     => '🇺🇸',
                'born'     => '1943',
                'known'    => ['Pong', 'Atari', 'Chuck E. Cheese'],
                'bio'      => 'The father of the video game industry. Bushnell co-founded Atari in 1972 and released Pong — the first commercially successful arcade game. He created the template for the games industry as a business and is credited with turning video games from an academic curiosity into a global entertainment medium.',
            ],
            [
                'name'     => 'Will Wright',
                'wiki'     => 'Will Wright (game designer)',
                'role'     => 'Game Designer',
                'category' => 'designer',
                'flag'     => '🇺🇸',
                'born'     => '1960',
                'known'    => ['SimCity', 'The Sims', 'Spore'],
                'bio'      => 'Creator of the simulation genre. SimCity let players build cities for the first time in 1989; The Sims became the best-selling PC game franchise of all time. Wright\'s games are defined by emergent systems that let players tell their own stories rather than following a scripted narrative.',
            ],
            [
                'name'     => 'Markus "Notch" Persson',
                'wiki'     => 'Markus Persson',
                'role'     => 'Game Developer',
                'category' => 'developer',
                'flag'     => '🇸🇪',
                'born'     => '1979',
                'known'    => ['Minecraft'],
                'bio'      => 'Created Minecraft as a solo project in 2009. What began as an indie experiment grew into the best-selling video game of all time, with over 300 million copies sold. Persson sold Mojang to Microsoft for $2.5 billion in 2014. Minecraft\'s influence on sandbox gaming, education, and child development is unparalleled.',
            ],
            [
                'name'     => 'Satoru Iwata',
                'wiki'     => 'Satoru Iwata',
                'role'     => 'Nintendo CEO & Programmer',
                'category' => 'business',
                'flag'     => '🇯🇵',
                'born'     => '1959',
                'known'    => ['Nintendo DS', 'Wii', 'Nintendo Direct', 'HAL Laboratory'],
                'bio'      => 'The beloved Nintendo CEO who steered the company toward accessibility and innovation. Iwata personally debugged Pokémon Gold and Silver to fit both games onto a single cartridge. His "Nintendo Direct" format changed how game companies communicate with fans. He passed away in 2015 and is remembered as one of gaming\'s most admired leaders.',
            ],
            [
                'name'     => 'Todd Howard',
                'wiki'     => 'Todd Howard',
                'role'     => 'Game Director & Executive Producer',
                'category' => 'designer',
                'flag'     => '🇺🇸',
                'born'     => '1970',
                'known'    => ['The Elder Scrolls series', 'Fallout 3', 'Skyrim', 'Starfield'],
                'bio'      => 'The director behind two of the most influential open-world RPG franchises in history. Howard joined Bethesda in 1994 and has since shaped massive, explorable worlds that players lose hundreds of hours in. Skyrim alone has been released on virtually every platform imaginable and is still actively played over a decade after launch.',
            ],
            [
                'name'     => 'Tim Sweeney',
                'wiki'     => 'Tim Sweeney (game developer)',
                'role'     => 'Founder & CEO of Epic Games',
                'category' => 'business',
                'flag'     => '🇺🇸',
                'born'     => '1970',
                'known'    => ['Unreal Engine', 'Fortnite', 'Epic Games Store'],
                'bio'      => 'Founded Epic Games from his parents\' house and built Unreal Engine into the most widely used game engine in the world. Fortnite made Epic one of the most valuable private companies in the US. Sweeney has been a vocal critic of platform monopolies and fought Apple in court over App Store fees.',
            ],
            [
                'name'     => 'Peter Molyneux',
                'wiki'     => 'Peter Molyneux',
                'role'     => 'Game Designer',
                'category' => 'designer',
                'flag'     => '🇬🇧',
                'born'     => '1959',
                'known'    => ['Populous', 'Theme Park', 'Fable', 'Black & White'],
                'bio'      => 'The British visionary who invented the god game genre with Populous in 1989 and went on to create Theme Park, Fable, and Black & White. Famous for his boundless ambition and promises that occasionally exceeded his games\' delivery, Molyneux has nonetheless been one of gaming\'s most imaginative and influential designers.',
            ],
            [
                'name'     => 'Sid Meier',
                'wiki'     => 'Sid Meier',
                'role'     => 'Game Designer & Programmer',
                'category' => 'designer',
                'flag'     => '🇺🇸',
                'born'     => '1954',
                'known'    => ['Civilization', 'Pirates!', 'Railroad Tycoon', 'Firaxis Games'],
                'bio'      => 'Creator of Civilization — one of the most addictive strategy games ever made and a franchise that has kept players glued to their screens for over 30 years. Meier co-founded MicroProse and later Firaxis Games, and his design philosophy — that games should present players with "a series of interesting decisions" — remains a foundational principle of game design.',
            ],
            [
                'name'     => 'Amy Hennig',
                'wiki'     => 'Amy Hennig',
                'role'     => 'Game Director & Writer',
                'category' => 'designer',
                'flag'     => '🇺🇸',
                'born'     => '1964',
                'known'    => ['Uncharted 1–3', 'Legacy of Kain: Soul Reaver'],
                'bio'      => 'One of gaming\'s most accomplished narrative directors. Hennig created the Uncharted trilogy at Naughty Dog, blending cinematic action with genuinely compelling characters. Her work elevated narrative expectations across the industry and proved that games could tell blockbuster stories with the same craft as film.',
            ],
            [
                'name'     => 'Reggie Fils-Aimé',
                'wiki'     => 'Reggie Fils-Aimé',
                'role'     => 'President & COO of Nintendo of America',
                'category' => 'business',
                'flag'     => '🇺🇸',
                'born'     => '1961',
                'known'    => ['Nintendo DS launch', 'Wii launch', 'E3 presentations'],
                'bio'      => 'The most charismatic executive in gaming history. "My body is ready" became one of gaming\'s most iconic phrases after his 2007 E3 Wii Fit demonstration. Fils-Aimé led Nintendo of America for 15 years, overseeing the launches of the DS, Wii, and Switch, and built Nintendo\'s brand in North America into something genuinely beloved.',
            ],
            [
                'name'     => 'Yoko Taro',
                'wiki'     => 'Yoko Taro',
                'role'     => 'Game Director & Writer',
                'category' => 'designer',
                'flag'     => '🇯🇵',
                'born'     => '1970',
                'known'    => ['NieR: Automata', 'NieR Replicant', 'Drakengard'],
                'bio'      => 'The eccentric auteur behind NieR: Automata — a game that uses its medium to explore consciousness, existentialism, and what it means to be human. Taro is known for subverting player expectations at every turn, hiding story-critical content in unconventional places, and wearing an Emil mask at public appearances.',
            ],
            [
                'name'     => 'Ken Kutaragi',
                'wiki'     => 'Ken Kutaragi',
                'role'     => 'Engineer & Sony PlayStation Creator',
                'category' => 'pioneer',
                'flag'     => '🇯🇵',
                'born'     => '1950',
                'known'    => ['PlayStation', 'PlayStation 2', 'PlayStation 3', 'Cell processor'],
                'bio'      => 'The "Father of the PlayStation." Kutaragi built the PlayStation after Sony\'s collaboration with Nintendo fell apart, defying internal sceptics to create what became the most successful gaming brand in history. The PS2 remains the best-selling console ever made, with over 155 million units sold.',
            ],
            [
                'name'     => 'Donna Burke',
                'wiki'     => 'Donna Burke',
                'role'     => 'Voice Actress & Singer',
                'category' => 'creator',
                'flag'     => '🇦🇺',
                'born'     => '1965',
                'known'    => ['Metal Gear Solid V (MGSV)', 'Chill (iDOLM@STER)', 'MGSV soundtrack'],
                'bio'      => 'The Australian voice actress and singer who performed "Sins of the Father" for Metal Gear Solid V and voiced numerous iconic characters in the franchise. Her contribution to gaming soundtracks helped establish voice performance as a serious craft within the industry.',
            ],
            [
                'name'     => 'Phil Spencer',
                'wiki'     => 'Phil Spencer (business executive)',
                'role'     => 'CEO of Microsoft Gaming',
                'category' => 'business',
                'flag'     => '🇺🇸',
                'born'     => '1968',
                'known'    => ['Xbox Game Pass', 'Activision Blizzard acquisition', 'Xbox Series X|S'],
                'bio'      => 'The executive who repositioned Xbox from a struggling console brand into a gaming services powerhouse. Under Spencer, Game Pass transformed how players access games, and the $69 billion Activision Blizzard acquisition reshaped the entire industry\'s competitive landscape. Spencer is widely respected for his candour and player-first public messaging.',
            ],
        ];
    }
}
