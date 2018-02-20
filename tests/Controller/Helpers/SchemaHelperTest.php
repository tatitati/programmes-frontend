<?php
declare(strict_types = 1);

namespace Tests\App\Controller\Helpers;

use App\Builders\BrandBuilder;
use App\Builders\BroadcastBuilder;
use App\Builders\CollapsedBroadcastBuilder;
use App\Builders\EpisodeBuilder;
use App\Builders\NetworkBuilder;
use App\Builders\SeriesBuilder;
use App\Builders\ServiceBuilder;
use App\Controller\Helpers\SchemaHelper;
use BBC\ProgrammesPagesService\Domain\Entity\MasterBrand;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SchemaHelperTest extends TestCase
{
    /**
     * @dataProvider broadcastTestProvider
     *
     * @param bool $isRadio
     * @param bool $hasParent
     * @param bool $parentIsTlec
     * @param bool $serviceHasNetwork
     */
    public function testBroadcast(bool $isRadio, bool $hasParent, bool $parentIsTlec, bool $serviceHasNetwork)
    {
        $router = $this->createMock(UrlGeneratorInterface::class);
        $helper = new SchemaHelper($router);
        $programmeItem = $this->createMock(ProgrammeItem::class);
        $programmeItem->method('isRadio')->willReturn($isRadio);
        if ($hasParent) {
            $mockProgramme = $this->createMock(Programme::class);
            $mockProgramme->method('getTitle')->willReturn('anything');
            $mockProgramme->method('getPid')->willReturn(new Pid('bcdfg123'));
            if ($parentIsTlec) {
                $mockProgramme->method('isTlec')->willReturn(true);
            } else {
                $mockProgramme2 = $this->createMock(Programme::class);
                $mockProgramme2->method('getTitle')->willReturn('thingany');
                $mockProgramme2->method('getPid')->willReturn(new Pid('123bcdfg'));
                $mockProgramme->method('isTlec')->willReturn(false);
                $mockProgramme->method('getParent')->willReturn($mockProgramme2);
            }
            $programmeItem->method('getParent')->willReturn($mockProgramme);
        } else {
            $programmeItem->method('getParent')->willReturn(null);
        }
        $builder = BroadcastBuilder::any();
        $builder->with(['programmeItem' => $programmeItem]);
        $serviceBuilder = ServiceBuilder::any();
        if ($serviceHasNetwork) {
            $serviceBuilder->with(['network' => NetworkBuilder::any()->build()]);
        }
        $builder->with(['service' => $serviceBuilder->build()]);
        $schemaContext = $helper->getSchemaForBroadcast($builder->build());

        $this->assertInternalType('array', $schemaContext);
        $this->assertArrayHasKey('@type', $schemaContext);
        $this->assertArrayHasKey('identifier', $schemaContext);
        $this->assertArrayHasKey('episodeNumber', $schemaContext);
        $this->assertArrayHasKey('description', $schemaContext);
        $this->assertArrayHasKey('datePublished', $schemaContext);
        $this->assertArrayHasKey('image', $schemaContext);
        $this->assertArrayHasKey('name', $schemaContext);
        $this->assertArrayHasKey('url', $schemaContext);
        $this->assertArrayHasKey('publication', $schemaContext);
        if ($hasParent) {
            $this->assertArrayHasKey('partOfSeries', $schemaContext);
            if ($parentIsTlec) {
                $this->assertArrayNotHasKey('partOfSeason', $schemaContext);
            } else {
                $this->assertArrayHasKey('partOfSeason', $schemaContext);
            }
        } else {
            $this->assertArrayNotHasKey('partOfSeries', $schemaContext);
            $this->assertArrayNotHasKey('partOfSeason', $schemaContext);
        }

        if ($isRadio) {
            $this->assertSame('RadioEpisode', $schemaContext['@type']);
        } else {
            $this->assertSame('TVEpisode', $schemaContext['@type']);
        }

        $this->assertInternalType('array', $schemaContext['publication']);
        $this->assertBroadcastEvent($schemaContext['publication'], $serviceHasNetwork, false);
    }

    /**
     * @dataProvider broadcastTestProvider
     *
     * @param bool $isRadio
     * @param bool $hasParent
     * @param bool $parentIsTlec
     * @param bool $serviceHasNetwork
     */
    public function testCollapsedBroadcast(bool $isRadio, bool $hasParent, bool $parentIsTlec, bool $serviceHasNetwork)
    {
        $router = $this->createMock(UrlGeneratorInterface::class);
        $helper = new SchemaHelper($router);
        $programmeItem = $this->createMock(ProgrammeItem::class);
        $programmeItem->method('isRadio')->willReturn($isRadio);
        if ($hasParent) {
            $mockProgramme = $this->createMock(Programme::class);
            $mockProgramme->method('getTitle')->willReturn('anything');
            $mockProgramme->method('getPid')->willReturn(new Pid('bcdfg123'));
            if ($parentIsTlec) {
                $mockProgramme->method('isTlec')->willReturn(true);
            } else {
                $mockProgramme2 = $this->createMock(Programme::class);
                $mockProgramme2->method('getTitle')->willReturn('thingany');
                $mockProgramme2->method('getPid')->willReturn(new Pid('123bcdfg'));
                $mockProgramme->method('isTlec')->willReturn(false);
                $mockProgramme->method('getParent')->willReturn($mockProgramme2);
            }
            $programmeItem->method('getParent')->willReturn($mockProgramme);
        } else {
            $programmeItem->method('getParent')->willReturn(null);
        }
        $builder = CollapsedBroadcastBuilder::any();
        $builder->with(['programmeItem' => $programmeItem]);
        $serviceBuilder = ServiceBuilder::any();
        if ($serviceHasNetwork) {
            $serviceBuilder->with(['network' => NetworkBuilder::any()->build()]);
        }
        $builder->with(['services' => [$serviceBuilder->build()]]);
        $schemaContext = $helper->getSchemaForCollapsedBroadcast($builder->build());

        $this->assertInternalType('array', $schemaContext);
        $this->assertArrayHasKey('@type', $schemaContext);
        $this->assertArrayHasKey('identifier', $schemaContext);
        $this->assertArrayHasKey('episodeNumber', $schemaContext);
        $this->assertArrayHasKey('description', $schemaContext);
        $this->assertArrayHasKey('datePublished', $schemaContext);
        $this->assertArrayHasKey('image', $schemaContext);
        $this->assertArrayHasKey('name', $schemaContext);
        $this->assertArrayHasKey('url', $schemaContext);
        $this->assertArrayHasKey('publication', $schemaContext);
        if ($hasParent) {
            $this->assertArrayHasKey('partOfSeries', $schemaContext);
            if ($parentIsTlec) {
                $this->assertArrayNotHasKey('partOfSeason', $schemaContext);
            } else {
                $this->assertArrayHasKey('partOfSeason', $schemaContext);
            }
        } else {
            $this->assertArrayNotHasKey('partOfSeries', $schemaContext);
            $this->assertArrayNotHasKey('partOfSeason', $schemaContext);
        }

        if ($isRadio) {
            $this->assertSame('RadioEpisode', $schemaContext['@type']);
        } else {
            $this->assertSame('TVEpisode', $schemaContext['@type']);
        }

        $this->assertInternalType('array', $schemaContext['publication']);
        $this->assertBroadcastEvent($schemaContext['publication'], $serviceHasNetwork, true);
    }

    public function broadcastTestProvider(): array
    {
        return [
            'radio-top-level-no-network' => [true, false, false, false],
            'radio-top-level-with-network' => [true, false, false, true],
            'radio-with-parent-no-network' => [true, true, false, false],
            'radio-with-parent-network' => [true, true, false, true],
            'radio-with-tlec-parent-no-network' => [true, true, true, false],
            'radio-with-tlec-parent-with-network' => [true, true, true, true],
            'tv-top-level-no-network' => [false, false, false, false],
            'tv-top-level-with-network' => [false, false, false, true],
            'tv-with-parent-no-network' => [false, true, false, false],
            'tv-with-parent-network' => [false, true, false, true],
            'tv-with-tlec-parent-no-network' => [false, true, true, false],
            'tv-with-tlec-parent-with-network' => [false, true, true, true],
        ];
    }

    /**
     * @dataProvider episodeTestProvider
     *
     * @param bool $isRadio
     * @param bool $hasParent
     * @param bool $parentIsTlec
     * @param bool $hasDates
     */
    public function testOnDemandEpisode(bool $isRadio, bool $hasParent, bool $parentIsTlec, bool $hasDates)
    {
        $router = $this->createMock(UrlGeneratorInterface::class);
        $helper = new SchemaHelper($router);

        $network = $this->createMock(Network::class);
        $network->method('isRadio')->willReturn($isRadio);
        $masterbrand = $this->createMock(MasterBrand::class);
        $masterbrand->method('getNetwork')->willReturn($network);
        $builder = EpisodeBuilder::any();
        $builder->with(['masterBrand' => $masterbrand]);
        if ($hasParent) {
            $mockProgramme = $this->createMock(Programme::class);
            $mockProgramme->method('getTitle')->willReturn('anything');
            $mockProgramme->method('getPid')->willReturn(new Pid('bcdfg123'));
            if ($parentIsTlec) {
                $mockProgramme->method('isTlec')->willReturn(true);
            } else {
                $mockProgramme2 = $this->createMock(Programme::class);
                $mockProgramme2->method('getTitle')->willReturn('thingany');
                $mockProgramme2->method('getPid')->willReturn(new Pid('123bcdfg'));
                $mockProgramme->method('isTlec')->willReturn(false);
                $mockProgramme->method('getParent')->willReturn($mockProgramme2);
            }
            $builder->with(['parent' => $mockProgramme]);
        }
        if ($hasDates) {
            $aTime = new Chronos();
            $builder->with(['streamableFrom' => $aTime, 'streamableUntil' => $aTime]);
        }

        $schemaContext = $helper->getSchemaForOnDemand($builder->build());

        $this->assertInternalType('array', $schemaContext);
        $this->assertArrayHasKey('@type', $schemaContext);
        $this->assertArrayHasKey('identifier', $schemaContext);
        $this->assertArrayHasKey('episodeNumber', $schemaContext);
        $this->assertArrayHasKey('description', $schemaContext);
        $this->assertArrayHasKey('datePublished', $schemaContext);
        $this->assertArrayHasKey('image', $schemaContext);
        $this->assertArrayHasKey('name', $schemaContext);
        $this->assertArrayHasKey('url', $schemaContext);
        $this->assertArrayHasKey('publication', $schemaContext);
        if ($hasParent) {
            $this->assertArrayHasKey('partOfSeries', $schemaContext);
            if ($parentIsTlec) {
                $this->assertArrayNotHasKey('partOfSeason', $schemaContext);
            } else {
                $this->assertArrayHasKey('partOfSeason', $schemaContext);
            }
        } else {
            $this->assertArrayNotHasKey('partOfSeries', $schemaContext);
            $this->assertArrayNotHasKey('partOfSeason', $schemaContext);
        }

        if ($isRadio) {
            $this->assertSame('RadioEpisode', $schemaContext['@type']);
        } else {
            $this->assertSame('TVEpisode', $schemaContext['@type']);
        }

        $this->assertInternalType('array', $schemaContext['publication']);
        $this->assertOnDemand($schemaContext['publication'], $hasDates);
    }

    public function episodeTestProvider(): array
    {
        return [
            'radio-top-level-no-dates' => [true, false, false, false],
            'radio-with-parent-no-dates' => [true, true, false, false],
            'radio-with-tlec-parent-no-dates' => [true, true, true, false],
            'tv-top-level-no-dates' => [false, false, false, false],
            'tv-with-parent-no-dates' => [false, true, false, false],
            'tv-with-tlec-parent-no-dates' => [false, true, true, false],
            'radio-top-level-with-dates' => [true, false, false, true],
            'radio-with-parent-with-dates' => [true, true, false, true],
            'radio-with-tlec-parent-with-dates' => [true, true, true, true],
            'tv-top-level-with-dates' => [false, false, false, true],
            'tv-with-parent-with-dates' => [false, true, false, true],
            'tv-with-tlec-parent-with-dates' => [false, true, true, true],
        ];
    }

    /**
     * @dataProvider tlecTestProvider
     *
     * @param bool $isRadio
     */
    public function testTlec(bool $isRadio)
    {
        $router = $this->createMock(UrlGeneratorInterface::class);
        $helper = new SchemaHelper($router);
        $network = $this->createMock(Network::class);
        $network->method('isRadio')->willReturn($isRadio);
        $masterbrand = $this->createMock(MasterBrand::class);
        $masterbrand->method('getNetwork')->willReturn($network);
        $builder = rand(0, 1) ? BrandBuilder::any() : SeriesBuilder::any();
        $builder->with(['masterBrand' => $masterbrand]);
        $schemaContext = $helper->getSchemaForSeries($builder->build());

        $this->assertInternalType('array', $schemaContext);
        $this->assertArrayHasKey('@type', $schemaContext);
        $this->assertArrayHasKey('image', $schemaContext);
        $this->assertArrayHasKey('description', $schemaContext);
        $this->assertArrayHasKey('identifier', $schemaContext);
        $this->assertArrayHasKey('name', $schemaContext);
        $this->assertArrayHasKey('url', $schemaContext);

        if ($isRadio) {
            $this->assertSame('RadioSeries', $schemaContext['@type']);
        } else {
            $this->assertSame('TVSeries', $schemaContext['@type']);
        }
    }

    public function tlecTestProvider(): array
    {
        return [
            'radio' => [true],
            'tv' => [false],
        ];
    }

    public function testPreparationWithSingleItem()
    {
        $router = $this->createMock(UrlGeneratorInterface::class);
        $helper = new SchemaHelper($router);
        $preparedArray = $helper->prepare(['foo' => 'bar']);
        $this->assertArrayHasKey('@context', $preparedArray);
        $this->assertArrayHasKey('foo', $preparedArray);
        $this->assertArrayNotHasKey('@graph', $preparedArray);
        $this->assertSame('http://schema.org', $preparedArray['@context']);
        $this->assertSame('bar', $preparedArray['foo']);
    }

    public function testPreparationWithMultipleItems()
    {
        $router = $this->createMock(UrlGeneratorInterface::class);
        $helper = new SchemaHelper($router);
        $preparedArray = $helper->prepare([['foo' => 'bar'], ['baz' => 'qux']], true);
        $this->assertArrayHasKey('@context', $preparedArray);
        $this->assertArrayHasKey('@graph', $preparedArray);
        $this->assertArrayNotHasKey('foo', $preparedArray);
        $this->assertArrayNotHasKey('baz', $preparedArray);
        $this->assertSame('http://schema.org', $preparedArray['@context']);
        $this->assertInternalType('array', $preparedArray['@graph']);
    }

    private function assertOnDemand(array $publication, bool $hasDates)
    {
        $this->assertArrayHasKey('@type', $publication);
        $this->assertArrayHasKey('publishedOn', $publication);
        if ($hasDates) {
            $this->assertArrayHasKey('startDate', $publication);
            $this->assertArrayHasKey('endDate', $publication);
        } else {
            $this->assertArrayNotHasKey('startDate', $publication);
            $this->assertArrayNotHasKey('endDate', $publication);
        }

        $this->assertSame('OnDemandEvent', $publication['@type']);
        $this->assertInternalType('array', $publication['publishedOn']);
        $this->assertBroadcastService($publication['publishedOn'], false);
    }

    private function assertBroadcastService(array $publishedOn, $serviceHasNetwork)
    {
        $this->assertArrayHasKey('@type', $publishedOn);
        $this->assertArrayHasKey('broadcaster', $publishedOn);
        $this->assertSame('BroadcastService', $publishedOn['@type']);
        $this->assertInternalType('array', $publishedOn['broadcaster']);

        if ($serviceHasNetwork) {
            $this->assertArrayHasKey('parentService', $publishedOn);
        } else {
            $this->assertArrayNotHasKey('parentService', $publishedOn);
        }
    }

    private function assertBroadcastEvent(array $publication, bool $serviceHasNetwork, bool $multipleBroadcasts)
    {
        $this->assertArrayHasKey('@type', $publication);
        $this->assertArrayHasKey('publishedOn', $publication);
        $this->assertSame('BroadcastEvent', $publication['@type']);

        $publishedOn = $publication['publishedOn'];
        $this->assertInternalType('array', $publication['publishedOn']);
        if ($multipleBroadcasts) {
            $this->assertInternalType('array', $publishedOn);
            foreach ($publishedOn as $pOn) {
                $this->assertInternalType('array', $pOn);
                $this->assertBroadcastService($pOn, $serviceHasNetwork);
            }
        } else {
            $this->assertBroadcastService($publication['publishedOn'], $serviceHasNetwork);
        }
    }
}
