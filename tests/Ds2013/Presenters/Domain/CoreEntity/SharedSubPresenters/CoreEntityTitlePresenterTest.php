<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Domain\CoreEntity\SharedSubPresenters;

use App\Builders\ClipBuilder;
use App\Builders\MasterBrandBuilder;
use App\Builders\NetworkBuilder;
use App\Ds2013\Presenters\Domain\CoreEntity\SharedSubPresenters\CoreEntityTitlePresenter;
use App\DsShared\Helpers\StreamableHelper;
use App\DsShared\Helpers\TitleLogicHelper;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\Enumeration\MediaTypeEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\Nid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\App\TwigEnvironmentProvider;

class CoreEntityTitlePresenterTest extends TestCase
{
    private $mockRouter;

    private $mockTitleLogicHelper;

    private $streamUrlHelper;

    public function setUp()
    {
        $this->mockRouter = $this->createMock(UrlGeneratorInterface::class);
        $this->mockTitleLogicHelper = $this->createMock(TitleLogicHelper::class);
        $this->streamUrlHelper = $this->createMock(StreamableHelper::class);
    }

    public function testGetTitleLinkUrl()
    {
        $this->mockRouter->expects($this->once())
            ->method('generate')
            ->with('find_by_pid', ['pid' => 'b006m86d'])
            ->willReturn('/programmes/b006m86d');

        $programme = $this->createMock(Brand::class);
        $programme->expects($this->once())->method('getPid')->willReturn(new Pid('b006m86d'));
        $programmeBodyPresenter = new CoreEntityTitlePresenter(
            $this->mockRouter,
            $this->mockTitleLogicHelper,
            $programme,
            $this->streamUrlHelper
        );
        $this->assertEquals('/programmes/b006m86d', $programmeBodyPresenter->getTitleLinkUrl());
    }

    public function testGetTitleLinkUrlForClip()
    {
        $router = TwigEnvironmentProvider::getSymfonyRouter();
        $network = NetworkBuilder::any()->with(['nid' => new Nid('bbc_radio_three')])->build();

        // Audio Clip whitelisted to go to playspace
        $masterBrand = MasterBrandBuilder::any()->with(['network' => $network, 'streamableInPlayspace' => true])->build();
        $playspaceAudioClip = ClipBuilder::any()->with([
            'mediaType' => MediaTypeEnum::AUDIO,
            'masterBrand' => $masterBrand,
            'pid' => new Pid('b09z67gw'),
        ])->build();

        $programmeBodyPresenter = new CoreEntityTitlePresenter(
            $router,
            $this->mockTitleLogicHelper,
            $playspaceAudioClip,
            new StreamableHelper()
        );

        // Asserts
        $this->assertEquals('http://localhost/radio/play/b09z67gw', $programmeBodyPresenter->getTitleLinkUrl());
    }
}
