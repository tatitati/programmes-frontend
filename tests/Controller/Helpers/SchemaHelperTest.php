<?php
declare(strict_types = 1);

namespace Tests\App\Controller\Helpers;

use App\Builders\CollapsedBroadcastBuilder;
use App\Builders\EpisodeBuilder;
use App\Builders\ImageBuilder;
use App\Builders\SeriesBuilder;
use App\Builders\ServiceBuilder;
use App\Controller\Helpers\SchemaHelper;
use BBC\ProgrammesPagesService\Domain\ValueObject\PartialDate;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @group seo_schema
 */
class SchemaHelperTest extends TestCase
{
    /** @var SchemaHelper */
    private $helper;

    public function setUp()
    {
        $router = $this->createMock(UrlGeneratorInterface::class);
        $this->helper = new SchemaHelper($router);
    }

    public function testSchemaSeriesOutput()
    {
        $series = SeriesBuilder::anyRadioSeries()->with([
            'pid' => new Pid('b0000002'),
            'title' => 'some name',
            'synopses' => new Synopses('short', 'medium', 'long'),
            'image' => ImageBuilder::any()->with(['pid' => new Pid('b0000003')])->build(),
        ])->build();

        $schema = $this->helper->getSchemaForSeries($series);


        $this->assertEquals([
            '@type' => 'RadioSeries',
            'image' => 'https://ichef.bbci.co.uk/images/ic/480xn/b0000003.jpg',
            'description' => 'short',
            'identifier' => 'b0000002',
            'name' => 'some name',
            'url' => null,
        ], $schema);
    }

    public function testGetSchemaForEpisode()
    {
        $episode = EpisodeBuilder::anyRadioEpisode()->with([
            'pid' => new Pid('b0000002'),
            'title' => 'some name',
            'image' => ImageBuilder::any()->with(['pid' => new Pid('b0000003')])->build(),
            'position' => 12,
            'releaseDate' => new PartialDate(3000, 04, 15),
            'synopses' => new Synopses('short', 'medium', 'long'),
        ])->build();

        $schema = $this->helper->getSchemaForEpisode($episode);

        $this->assertEquals([
            '@type' => 'RadioEpisode',
            'identifier' => 'b0000002',
            'episodeNumber' => 12,
            'description' => 'short',
            'datePublished' => '3000-04-15',
            'image' => 'https://ichef.bbci.co.uk/images/ic/480xn/b0000003.jpg',
            'name' => 'some name',
            'url' => null,
        ], $schema);
    }

    public function testGetSchemaForOnDemandEventOutput()
    {
        $episode = EpisodeBuilder::anyRadioEpisode()->with([
            'streamableFrom' => new DateTimeImmutable('4000-02-03'),
            'streamableUntil' => new DateTimeImmutable('5000-02-03'),
        ])->build();

        $schema = $this->helper->getSchemaForOnDemandEvent($episode);

        $this->assertEquals([
            '@type' => 'OnDemandEvent',
            'url' => null,
            'publishedOn' => [
                '@type' => 'BroadcastService',
                'broadcaster' => [
                    '@type' => 'Organization',
                    'legalName' => 'British Broadcasting Corporation',
                    'logo' => 'http://ichef.bbci.co.uk/images/ic/1200x675/p01tqv8z.png',
                    'name' => 'BBC',
                    'url' => 'https://www.bbc.co.uk/',
                ],
                'name' => 'iPlayer',
            ],
            'duration' => 'PT' . $episode->getDuration() . 'S',
            'startDate' => '4000-02-03T00:00:00+00:00',
            'endDate' => '5000-02-03T00:00:00+00:00',
        ], $schema);
    }

    public function testGetSchemaForBroadcastEventOutput()
    {
        $broadcast = CollapsedBroadcastBuilder::any()->build();

        $schema = $this->helper->getSchemaForBroadcastEvent($broadcast);

        $this->assertEquals([
            '@type' => 'BroadcastEvent',
            'startDate' => $broadcast->getStartAt()->format(DATE_ATOM),
            'endDate' => $broadcast->getEndAt()->format(DATE_ATOM),
        ], $schema);
    }

    public function testGetSchemaForServiceOutput()
    {
        $service = ServiceBuilder::any()->build();

        $schema = $this->helper->getSchemaForService($service);

        $this->assertEquals([
            '@type' => 'BroadcastService',
            'broadcaster' => [
                '@type' => 'Organization',
                'legalName' => 'British Broadcasting Corporation',
                'logo' => 'http://ichef.bbci.co.uk/images/ic/1200x675/p01tqv8z.png',
                'name' => 'BBC',
                'url' => 'https://www.bbc.co.uk/',
            ],
            'name' => $service->getName(),
        ], $schema);
    }

    public function testGetSchemaForSeason()
    {
        $series = SeriesBuilder::anyRadioSeries()->with(['pid' => new Pid('b0000002')])->build();
        $schema = $this->helper->getSchemaForSeason($series);

        $this->assertEquals([
            '@type' => 'RadioSeason',
            'identifier' => 'b0000002',
            'position' => $series->getPosition(),
            'name' => $series->getTitle(),
            'url' => null,
        ], $schema);
    }
}
