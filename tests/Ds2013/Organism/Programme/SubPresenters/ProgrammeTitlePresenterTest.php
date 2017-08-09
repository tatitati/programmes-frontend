<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Organism\Programme\SubPresenters;

use App\Ds2013\Organism\Programme\SubPresenters\ProgrammeTitlePresenter;
use App\DsShared\Helpers\TitleLogicHelper;
use BBC\ProgrammesPagesService\Domain\Entity\Brand;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProgrammeTitlePresenterTest extends TestCase
{
    private $mockRouter;

    private $mockTitleLogicHelper;

    public function setUp()
    {
        $this->mockRouter = $this->createMock(UrlGeneratorInterface::class);
        $this->mockTitleLogicHelper = $this->createMock(TitleLogicHelper::class);
    }

    public function testGetTitleLinkUrl()
    {
        $this->mockRouter->expects($this->once())
            ->method('generate')
            ->with('find_by_pid', ['pid' => 'b006m86d'])
            ->willReturn('/programmes/b006m86d');

        $programme = $this->createMock(Brand::class);
        $programme->expects($this->once())->method('getPid')->willReturn(new Pid('b006m86d'));
        $programmeBodyPresenter = new ProgrammeTitlePresenter(
            $this->mockRouter,
            $this->mockTitleLogicHelper,
            $programme
        );
        $this->assertEquals('/programmes/b006m86d', $programmeBodyPresenter->getTitleLinkUrl());
    }
}
