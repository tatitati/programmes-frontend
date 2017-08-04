<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Organism\Programme\BroadcastSubPresenters;

use App\DsShared\Helpers\LiveBroadcastHelper;
use App\DsShared\Helpers\PlayTranslationsHelper;
use App\Ds2013\Organism\Programme\CollapsedBroadcastSubPresenters\CollapsedBroadcastProgrammeBodyPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\ValueObject\Nid;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BroadcastProgrammeBodyPresenterTest extends TestCase
{
    private $mockRouter;

    private $mockTranslationsHelper;

    private $mockLiveBroadcastHelper;

    public function setUp()
    {
        $this->mockRouter = $this->createMock(UrlGeneratorInterface::class);
        $this->mockTranslationsHelper = $this->createMock(PlayTranslationsHelper::class);
        $this->mockLiveBroadcastHelper = $this->createMock(LiveBroadcastHelper::class);
    }

    /**
     * @dataProvider worldServiceForeignLanguageDataProvider
     */
    public function testIsWorldServiceForeignLanguage(string $nid, bool $isWorldServiceInternational, bool $expected)
    {
        $network = $this->createMock(Network::class);
        $network->method('getNid')->willReturn(new Nid($nid));
        $network->method('isWorldServiceInternational')->willReturn($isWorldServiceInternational);

        $broadcast = $this->createMock(CollapsedBroadcast::class);
        $programme = $this->createMock(Programme::class);
        $programme->method('getNetwork')->willReturn($network);

        $presenter = new CollapsedBroadcastProgrammeBodyPresenter(
            $this->mockRouter,
            $this->mockTranslationsHelper,
            $this->mockLiveBroadcastHelper,
            $broadcast,
            $programme
        );

        $this->assertEquals($expected, $presenter->isWorldServiceForeignLanguage());
    }

    public function worldServiceForeignLanguageDataProvider()
    {
        return [
            ['something', true, true],
            ['bbc_world_service', true, false],
            ['anything', false, false],
        ];
    }
}
