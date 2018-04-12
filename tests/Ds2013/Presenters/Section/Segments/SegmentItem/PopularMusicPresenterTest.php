<?php
declare(strict_types=1);

namespace Tests\App\Ds2013\Presenters\Section\Segments\SegmentItem;

use App\Builders\MusicSegmentBuilder;
use App\Builders\SegmentEventBuilder;
use App\Ds2013\Presenters\Section\Segments\SegmentItem\PopularMusicPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\Entity\Contributor;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Domain\Entity\SegmentEvent;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Tests\App\BaseTemplateTestCase;

class PopularMusicPresenterTest extends BaseTemplateTestCase
{
    public function testTemplate()
    {
        $composer = $this->createConfiguredMock(Contribution::class, [
            'getCreditRole' => 'composer',
            'getPid' => new Pid('cnt000001'),
        ]);
        $segment = MusicSegmentBuilder::any()->with(['contributions' => [$composer]])->build();
        $segmentEvent = SegmentEventBuilder::any()->with(['segment' => $segment])->build();
        $crawler = $this->presenterCrawler(new PopularMusicPresenter($segmentEvent, 'anything', null));

        $this->assertCount(1, $crawler->filter('div.segment--music'));
    }

    /** @dataProvider setupContributionsProvider */
    public function testSetupContributions(
        array $expectedPrimaryContributions,
        array $expectedSecondaryContributions,
        Contribution $primaryContribution,
        array $contributions
    ) {
        $segment = $this->createConfiguredMock(Segment::class, ['getContributions' => $contributions]);
        $segmentEvent = $this->createConfiguredMock(SegmentEvent::class, ['getSegment' => $segment]);
        $presenter = new PopularMusicPresenter($segmentEvent, 'anything', null);

        $this->assertEquals($expectedPrimaryContributions, $presenter->getPrimaryContributions());
        $this->assertEquals($expectedSecondaryContributions, $presenter->getOtherContributions());
        $this->assertEquals($primaryContribution, $presenter->getPrimaryContribution());
    }

    public function setupContributionsProvider(): array
    {
        $dj = $this->createConfiguredMock(Contribution::class, [
            'getCreditRole' => 'dj',
            'getContributor' => $this->createConfiguredMock(Contributor::class, ['getPid' => new Pid('cnt000001')]),
        ]);
        $mc = $this->createConfiguredMock(Contribution::class, [
            'getCreditRole' => 'mc',
            'getContributor' => $this->createConfiguredMock(Contributor::class, ['getPid' => new Pid('cnt000010')]),
        ]);
        $performer = $this->createConfiguredMock(Contribution::class, [
            'getCreditRole' => 'performer',
            'getContributor' => $this->createConfiguredMock(Contributor::class, ['getPid' => new Pid('cnt000002')]),
        ]);
        $transcriber = $this->createConfiguredMock(Contribution::class, [
            'getCreditRole' => 'transcriber',
            'getContributor' => $this->createConfiguredMock(Contributor::class, ['getPid' => new Pid('cnt000003')]),
        ]);

        return [
            'DJ only' => [[$dj], [], $dj, [$dj]],
            'DJ duplicated' => [[$dj], [], $dj, [$dj, $dj]],
            'DJ and MC' => [[$dj, $mc], [], $dj, [$dj, $mc]],
            'transcriber and DJ' => [[$dj], [$transcriber], $dj, [$transcriber, $dj]],
            'MC, performer and DJ' => [[$mc, $performer, $dj], [], $mc, [$mc, $performer, $dj]],
            'MC, performer, transcriber and DJ' => [[$mc, $performer, $dj], [$transcriber], $mc, [$transcriber, $mc, $performer, $dj]],
        ];
    }
}
