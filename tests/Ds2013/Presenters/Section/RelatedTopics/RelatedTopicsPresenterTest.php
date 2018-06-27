<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Section\RelatedTopics;

use App\Builders\AdaBuilder;
use App\Ds2013\Presenters\Section\RelatedTopics\RelatedTopicsPresenter;
use App\ExternalApi\Ada\Domain\AdaClass;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Symfony\Component\DomCrawler\Crawler;
use Tests\App\BaseTemplateTestCase;

class RelatedTopicsPresenterTest extends BaseTemplateTestCase
{
    /** @var Crawler */
    private $crawler;

    /**
     * @dataProvider contextPidsProvider
     */
    public function testBasicPresenter(?Pid $givenContextPid, string $expectedUrl): void
    {
        $ada = AdaBuilder::any()->with([
            'programmeItemCountContext' => $givenContextPid,
            'id' => 'any_id',
        ])->build();


        $this->renderPresenterWithAda($ada);

        $this->thenTopicLinkPointTo($expectedUrl);
    }

    public function contextPidsProvider(): array
    {
        return [
            'CASE A: presenter has a context Pid' => [new Pid('p002d80x'), '/programmes/p002d80x/topics/any_id'],
            'CASE B: presenter has not a context Pid' => [null, '/programmes/topics/any_id'],
        ];
    }

    /**
     * Helpers
     */
    private function renderPresenterWithAda(AdaClass $ada): void
    {
        $presenter = new RelatedTopicsPresenter([$ada], 'http://linkTrack.com');
        $this->crawler = new Crawler($this->presenterHtml($presenter));
    }

    private function thenTopicLinkPointTo(string $expectedUrl): void
    {
        $this->assertEquals(
            $expectedUrl,
            $this->crawler->filter('.related-topics li a')->first()->attr('href')
        );
    }
}
