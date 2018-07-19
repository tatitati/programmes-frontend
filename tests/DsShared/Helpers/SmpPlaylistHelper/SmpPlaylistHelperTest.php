<?php
declare(strict_types = 1);

namespace Tests\App\DsShared\Helpers\SmpPlaylistHelper;

use App\DsShared\Helpers\SmpPlaylistHelper;
use PHPUnit\Framework\TestCase;
use Tests\App\DataFixtures\PagesService\SegmentEventsFixture;
use Tests\App\DataFixtures\PagesService\VersionsFixture;

class SmpPlaylistHelperTest extends TestCase
{
    private const BASE_FEED = [
        'info' => [
            'readme' => 'For the use of Radio, Music and Programmes only',
        ],
        'statsObject' => [],
        'defaultAvailableVersion' => null,
        'allAvailableVersions' => [],
    ];

    /** @var SmpPlaylistHelper */
    private $helper;

    public function setUp()
    {
        $this->helper = new SmpPlaylistHelper();
    }

    public function testEmptyEpisodeFeed()
    {
        $version = VersionsFixture::eastendersEpisodeUnavailable();
        $episode = $version->getProgrammeItem();
        $feed = $this->helper->getLegacyJsonPlaylist($episode, $version);

        $expectedFeed = self::BASE_FEED;
        $expectedFeed['statsObject']['parentPID'] = 'p0000001';
        $expectedFeed['statsObject']['parentPIDType'] = 'episode';
        $expectedFeed['holdingImage'] = '//ichef.bbci.co.uk/images/ic/976x549/p01vg679.jpg';

        $this->assertEquals($expectedFeed, $feed);
    }

    public function testAvailableClipOneVersionNoSegmentsCompetitionWarning()
    {
        $version = VersionsFixture::eastendersClipAvailable();
        $clip = $version->getProgrammeItem();

        $expectedVersionFeed = [
            'pid' => 'v0010001',
            'types' => [
                0 => 'original',
            ],
            'smpConfig' => [
                'title' => 'EastEnders, An Episode of Eastenders, Available Eastenders Clip',
                'summary' => 'Short Synopsis',
                'masterBrandName' => 'BBC One London',
                'items' => [
                    0 => [
                        'vpid' => 'p025x55x',
                        'kind' => 'warning',
                    ],
                    1 => [
                        'vpid' => 'v0010001',
                        'kind' => 'programme',
                        'duration' => 3600,
                    ],
                ],
                'holdingImageURL' => '//ichef.bbci.co.uk/images/ic/$recipe/p01vg679.jpg',
                'guidance' => null,
                'embedRights' => 'allowed',
            ],
            'markers' => [],
        ];

        $expectedFeed = self::BASE_FEED;
        $expectedFeed['statsObject']['parentPID'] = 'clp00001';
        $expectedFeed['statsObject']['parentPIDType'] = 'clip';
        $expectedFeed['defaultAvailableVersion'] = $expectedVersionFeed;
        $expectedFeed['allAvailableVersions'] = [$expectedVersionFeed];
        $expectedFeed['holdingImage'] = '//ichef.bbci.co.uk/images/ic/976x549/p01vg679.jpg';
        $this->assertEquals($expectedFeed, $this->helper->getLegacyJsonPlaylist($clip, $version));
    }

    public function testAvailableEpisodeWithSegments()
    {
        $version = VersionsFixture::eastendersEpisodeAvailable();
        $episode = $version->getProgrammeItem();
        $segmentEvents = SegmentEventsFixture::eastendersEpisodeAvailable();

        $expectedVersionFeed = [
            'pid' => 'v0020001',
            'types' => [
                0 => 'original',
            ],
            'smpConfig' => [
                'title' => 'EastEnders, An Episode of Eastenders',
                'summary' => 'Short Synopsis',
                'masterBrandName' => 'BBC One London',
                'items' => [
                    0 => [
                        'vpid' => 'v0020001',
                        'kind' => 'programme',
                        'duration' => 1800,
                    ],
                ],
                'holdingImageURL' => '//ichef.bbci.co.uk/images/ic/$recipe/p01vg679.jpg',
                'guidance' => null,
                'embedRights' => 'blocked',
            ],
            'markers' => [],
        ];

        $expectedMarkers = [
            [
                'id' => 's0010003',
                'type' => 'chapter',
                'start' => 150,
                'end' => 300,
                'text' => 'Segment 1',
                'description' => 'Segment 1',
            ],
            [
                'id' => 's0010004',
                'type' => 'chapter',
                'start' => 300,
                'end' => 500,
                'text' => 'Segment 2',
                'description' => 'Segment 2: The revenge',
            ],
        ];
        $expectedFeed = self::BASE_FEED;
        $expectedFeed['statsObject']['parentPID'] = 'p0000001';
        $expectedFeed['statsObject']['parentPIDType'] = 'episode';
        $expectedFeed['defaultAvailableVersion'] = $expectedVersionFeed;
        $expectedFeed['defaultAvailableVersion']['markers'] = $expectedMarkers;
        $expectedFeed['allAvailableVersions'] = [$expectedVersionFeed];
        $expectedFeed['holdingImage'] = '//ichef.bbci.co.uk/images/ic/976x549/p01vg679.jpg';
        $this->assertEquals($expectedFeed, $this->helper->getLegacyJsonPlaylist($episode, $version, $segmentEvents));
    }

    public function testAvailableEpisodeWithSegmentsAndTracklistTimings()
    {
        $version = VersionsFixture::wordsAndMusicAvailable();
        $episode = $version->getProgrammeItem();
        $segmentEvents = SegmentEventsFixture::wordsAndMusicAvailable();

        $expectedVersionFeed = [
            'pid' => 'v0010002',
            'types' => [
                0 => 'original',
            ],
            'smpConfig' => [
                'title' => 'Words and Music, The Chessboard',
                'summary' => 'Readings from Adjoa Andoh and Henry Goodman. Music from Shostakovich to The Rolling Stones',
                'masterBrandName' => 'BBC Radio 3',
                'items' => [
                    0 => [
                        'vpid' => 'v0010002',
                        'kind' => 'radioProgramme',
                        'duration' => 4500,
                    ],
                ],
                'holdingImageURL' => '//ichef.bbci.co.uk/images/ic/$recipe/p069d242.jpg',
                'guidance' => null,
                'embedRights' => 'blocked',
            ],
            'markers' => [],
        ];

        $expectedMarkers = [
            [
                'id' => 's0010001',
                'type' => 'key',
                'start' => 0,
                'text' => 'Fugue No.1 in C Major',
            ],
            [
                'id' => 's0010003',
                'type' => 'key',
                'start' => 160,
                'text' => 'Etude in G Flat major, Op.10, No.5 ("Black Key")',
            ],
        ];
        $expectedFeed = self::BASE_FEED;
        $expectedFeed['statsObject']['parentPID'] = 'p0000221';
        $expectedFeed['statsObject']['parentPIDType'] = 'episode';
        $expectedFeed['defaultAvailableVersion'] = $expectedVersionFeed;
        $expectedFeed['defaultAvailableVersion']['markers'] = $expectedMarkers;
        $expectedFeed['allAvailableVersions'] = [$expectedVersionFeed];
        $expectedFeed['holdingImage'] = '//ichef.bbci.co.uk/images/ic/976x549/p069d242.jpg';
        $this->assertEquals($expectedFeed, $this->helper->getLegacyJsonPlaylist($episode, $version, $segmentEvents));
    }
}
