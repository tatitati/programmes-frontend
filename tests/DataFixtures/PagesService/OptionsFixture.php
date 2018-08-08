<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\PagesService;

use BBC\ProgrammesPagesService\Domain\Entity\Options;

class OptionsFixture
{
    public static function empty()
    {
        return new Options([]);
    }

    public static function bbcOne()
    {
        return new Options(
            [
                'navigation_links' => [
                    [
                      'title' =>  'Schedule',
                      'url' =>  '/iplayer/schedules/bbcone',
                    ],
                    [
                      'title' =>  'TV Guide',
                      'url' =>  '/iplayer/guide',
                    ],
                ],
                'branding_id' => 'br-00117',
                'theme' => '',
                'language' => 'en',
                'nav_override' => 'no',
                'brand_layout' => '',
                'pulse_survey' => 'bbcone-programmes',
                'show_tracklist_inadvance' => 'yes',
                'show_tracklist_timings' => 'yes',
                'pid_override_url' => '',
                'pid_override_code' => 'code-302',
                'show_enhanced_navigation' => '',
                'podcast_rss_redirect' => '',
                'brand_2016_layout' => 'yes',
                'brand_2016_layout_use_minimap' => '',
                'double_width_first_promo' => '',
                'show_gallery_cards' => '',
                'show_clip_cards' => '',
                'pid_override' => null,
                'promoted_programmes' => [],
                'comments_clips_id' => null,
                'comments_clips_enabled' =>  false,
                'comments_episodes_id' => null,
                'comments_episodes_enabled' => false,
                'playlister_popularity_enabled' => false,
                'recipes_enabled' => false,
                'live_stream_id' => null,
                'live_stream_heading' => null,
                'ivote' => null,
                'coming_soon' => null,
            ]
        );
    }

    public static function radioFour()
    {
        return new Options(
            [
                'twitter_block' => '',
                'telescope_block' => '',
                'thingstodo_block' => '',
                'comingsoon_block' => '',
                'comments_clips_enabled' => false,
                'comments_clips_id' => '',
                'comments_episodes_enabled' => false,
                'comments_episodes_id' => '',
                'playlister_popularity_enabled' => 'no',
                'bbc_site' => '',
                'recipes_enabled' => '',
                'live_stream_id' => '',
                'live_stream_heading' => '',
                'control-17' => '',
                'project_space' => 'progs-radio4and4extra',
                'navigation_links' => [
                    [
                        'title' => 'Schedule',
                        'url' => '/radio4extra/programmes/schedules/this_week',
                    ],
                    [
                        'title' => 'Downloads',
                        'url' => '/podcasts/radio4extra',
                    ],
                    [
                        'title' => 'Presenters',
                        'url' => '/programmes/p00sqbw3/profiles/presenters',
                    ],
                ],
                'promoted_programmes' => [
                    [
                        'promoted_programmes_pid' => 'b00zwnrx',
                    ],
                    [
                        'promoted_programmes_pid' => 'b010m2mj',
                    ],
                    [
                        'promoted_programmes_pid' => 'b00zwlh8',
                    ],
                ],
                'branding_id' => 'br-00050',
                'theme' => '',
                'language' => 'en',
                'nav_override' => 'no',
                'brand_layout' => '',
                'pulse_survey' => '',
                'show_tracklist_inadvance' => false,
                'show_tracklist_timings' => false,
                'pid_override_url' => '',
                'pid_override_code' => 'code-302',
                'show_enhanced_navigation' => '',
                'podcast_rss_redirect' => '',
                'brand_2016_layout' => false,
                'brand_2016_layout_use_minimap' => false,
                'show_clip_cards' => true,
                'show_gallery_cards' => true,
                'double_width_first_promo' => false,
                'pid_override' => null,
                'ivote' => null,
                'coming_soon' => null,
            ]
        );
    }

    public static function radioThree(): Options
    {
        return new Options([
            'twitter_block' => null,
            'ivote_block' => null,
            'thingstodo_block' => null,
            'comingsoon_block' => null,
            'comments_clips_enabled' => null,
            'comments_clips_id' => null,
            'comments_episodes_enabled' => null,
            'comments_episodes_id' => null,
            'playlister_popularity_enabled' => false,
            'recipes_enabled' => null,
            'live_stream_id' => null,
            'live_stream_heading' => null,
            'control-17' => null,
            'project_space' => 'progs-radio3',
            'navigation_links' => [
                0 => [
                    'title' => 'Schedule',
                    'url' => '/radio3/programmes/schedules/this_week',
                ],
                1 => [
                    'title' => 'Podcasts',
                    'url' => '/podcasts/radio3',
                ],
                2 => [
                    'title' => 'Composers',
                    'url' => '/programmes/b006tnxf/features/composer-a-z',
                ],
            ],
            'promoted_programmes' => [
                0 => [
                    'promoted_programmes_pid' => 'b006tmr6',
                ],
                1 => [
                    'promoted_programmes_pid' => 'b014r87y',
                ],
                2 => [
                    'promoted_programmes_pid' => 'b0144txn',
                ],
                3 => [
                    'promoted_programmes_pid' => 'b006tp0c',
                ],
                4 => [
                    'promoted_programmes_pid' => 'b006tp52',
                ],
                5 => [
                    'promoted_programmes_pid' => 'b03q8r97',
                ],
            ],
            'branding_id' => 'br-00065',
            'theme' => null,
            'language' => null,
            'nav_override' => false,
            'brand_layout' => null,
            'pulse_survey' => null,
            'show_tracklist_inadvance' => true,
            'show_tracklist_timings' => true,
            'pid_override_url' => null,
            'pid_override_code' => null,
            'show_enhanced_navigation' => null,
            'podcast_rss_redirect' => null,
            'brand_2016_layout' => null,
            'brand_2016_layout_use_minimap' => null,
            'double_width_first_promo' => null,
            'show_gallery_cards' => null,
            'show_clip_cards' => null,
        ]);
    }

    public static function worldServiceRadio()
    {
        return new Options(
            [
                'twitter_block' => '',
                'telescope_block' => '',
                'thingstodo_block' => '',
                'comingsoon_block' => '',
                'comments_clips_enabled' => false,
                'comments_clips_id' => '',
                'comments_episodes_enabled' => false,
                'comments_episodes_id' => '',
                'playlister_popularity_enabled' => 'no',
                'bbc_site' => '',
                'recipes_enabled' => '',
                'live_stream_id' => '',
                'live_stream_heading' => '',
                'control-17' => '',
                'project_space' => 'progs-worldservice',
                'navigation_links' => [
                    [
                        "title" => "Online schedule",
                        "url" => "http =>\/\/www.bbc.co.uk\/worldserviceradio\/programmes\/schedules",
                    ],
                    [
                        "title" => "Programmes",
                        "url" => "\/worldserviceradio\/programmes\/a-z",
                    ],
                    [
                        "title" => "Downloads",
                        "url" => "\/podcasts\/worldservice",
                    ],
                    [
                        "title" => "Help & FAQs",
                        "url" => "http =>\/\/www.bbc.co.uk\/worldserviceradio\/help\/faq",
                    ],
                    [
                        "title" => "Contact us",
                        "url" => "http =>\/\/www.bbc.co.uk\/worldserviceradio\/help\/contact",
                    ],
                    [
                        "title" => "News in 28 languages",
                        "url" => "http:\/\/www.bbc.co.uk\/worldservice\/languages\/index.shtml",
                    ],
                ],
                'promoted_programmes' => [
                    [
                        "promoted_programmes_pid" => "p007dhp8",
                    ],
                    [
                        "promoted_programmes_pid" => "p0299wgd",
                    ],
                    [
                        "promoted_programmes_pid" => "p029399x",
                    ],
                    [
                        "promoted_programmes_pid" => "p028z2z0",
                    ],
                    [
                        "promoted_programmes_pid" => "p0290t8h",
                    ],
                    [
                        "promoted_programmes_pid" => "p016tmfz",
                    ],
                ],
                'branding_id' => 'br-00035',
                'theme' => '',
                'language' => 'en',
                'nav_override' => 'no',
                'brand_layout' => '',
                'pulse_survey' => '',
                'show_tracklist_inadvance' => false,
                'show_tracklist_timings' => false,
                'pid_override_url' => '',
                'pid_override_code' => 'code-302',
                'show_enhanced_navigation' => '',
                'podcast_rss_redirect' => '',
                'brand_2016_layout' => false,
                'brand_2016_layout_use_minimap' => false,
                'show_clip_cards' => true,
                'show_gallery_cards' => true,
                'double_width_first_promo' => false,
                'pid_override' => null,
                'ivote' => null,
                'coming_soon' => null,
            ]
        );
    }

    public static function eastEnders()
    {
        return new Options(
            [
                'twitter_block' => '',
                'telescope_block' => '',
                'thingstodo_block' => '',
                'comingsoon_block' => '',
                'navigation_links' => [
                    [
                        'title' => 'Home',
                        'url' => '/programmes/b006m86d',
                    ],
                    [
                        'title' => 'Episodes',
                        'url' => '/programmes/b006m86d/episodes/guide',
                    ],
                    [
                        'title' => 'Previews & Catch-ups',
                        'url' => '/programmes/articles/5R9PFhPQGHpYZzhByTH6cMb/previews-and-catch-ups',
                    ],
                    [
                        'title' => 'Characters',
                        'url' => 'http://www.bbc.co.uk/programmes/profiles/2nv0rtJZlqCpyLc2qW3w6F7/characters',
                    ],
                    [
                        'title' => 'Latest News',
                        'url' => 'http://www.bbc.co.uk/programmes/articles/84MPm8ytQGf870zpkZxBqv/latest-news',
                    ],
                    [
                        'title' => 'Backstage',
                        'url' => '/programmes/articles/3TFBf6P4YPyJd3pqLPhDYCg/backstage',
                    ],
                    [
                        'title' => 'Games',
                        'url' => '/programmes/articles/Bpdh9gLX8Dz9LzLCYBf3gj/fun-stuff-games-and-quizzes',
                    ],
                    [
                        'title' => 'Soap Factory',
                        'url' => 'https://www.mixital.co.uk/channel/eastenders-soap-factory',
                    ],
                ],
                'comments_clips_enabled' => false,
                'comments_clips_id' => '',
                'comments_episodes_enabled' => false,
                'comments_episodes_id' => '',
                'playlister_popularity_enabled' => 'no',
                'bbc_site' => '',
                'recipes_enabled' => '',
                'live_stream_id' => '',
                'live_stream_heading' => '',
                'control-17' => '',
                'comingsoon_textonly' => '',
                'livepromo_block' => '',
                'prioritytext_block' => '',
                'project_space' => 'progs-eastenders',
                'branding_id' => 'br-03777',
                'theme' => '',
                'language' => 'en',
                'nav_override' => 'no',
                'brand_layout' => 'availability',
                'pulse_survey' => '',
                'show_tracklist_inadvance' => 'yes',
                'show_tracklist_timings' => 'yes',
                'pid_override_url' => '',
                'pid_override_code' => 'code-302',
                'show_enhanced_navigation' => '',
                'podcast_rss_redirect' => '',
                'brand_2016_layout' => 'yes',
                'brand_2016_layout_use_minimap' => '',
                'double_width_first_promo' => '',
                'show_gallery_cards' => 'no',
                'show_clip_cards' => 'no',
                'pid_override' => null,
                'promoted_programmes' => [],
                'ivote' => null,
                'coming_soon' => null,
            ]
        );
    }
}
