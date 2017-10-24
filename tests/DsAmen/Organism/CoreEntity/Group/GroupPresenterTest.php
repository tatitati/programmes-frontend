<?php
declare(strict_types=1);

namespace Tests\App\DsAmen\Organism\CoreEntity\Group;

use App\DsAmen\Organism\CoreEntity\Group\GroupPresenter;
use App\DsAmen\Organism\CoreEntity\Group\SubPresenter\CtaPresenter;
use App\DsAmen\Organism\CoreEntity\Group\SubPresenter\TitlePresenter;
use App\DsAmen\Organism\CoreEntity\Shared\SubPresenter\SharedBodyPresenter;
use App\DsAmen\Organism\CoreEntity\Shared\SubPresenter\SharedImagePresenter;
use App\DsShared\Helpers\HelperFactory;
use BBC\ProgrammesPagesService\Domain\Entity\Group;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Routing\Generator\UrlGenerator;

class GroupPresenterTest extends TestCase
{
    /** @var UrlGenerator|PHPUnit_Framework_MockObject_MockObject */
    private $mockRouter;

    /** @var HelperFactory|PHPUnit_Framework_MockObject_MockObject */
    private $mockHelperFactory;

    /** @var Group|PHPUnit_Framework_MockObject_MockObject */
    private $mockGroup;

    public function setUp()
    {
        $this->mockRouter = $this->createMock(UrlGenerator::class);
        $this->mockHelperFactory = $this->createMock(HelperFactory::class);
        $this->mockGroup = $this->createMock(Group::class);
    }

    public function testGetCtaPresenterReturnsInstanceOfGroupCtaPresenter(): void
    {
        $groupPresenter = new GroupPresenter($this->mockGroup, $this->mockRouter, $this->mockHelperFactory);
        $this->assertInstanceOf(CtaPresenter::class, $groupPresenter->getCtaPresenter());
    }

    public function testGetBodyPresenterReturnsInstanceOfSharedBodyPresenter(): void
    {
        $groupPresenter = new GroupPresenter($this->mockGroup, $this->mockRouter, $this->mockHelperFactory);
        $this->assertInstanceOf(SharedBodyPresenter::class, $groupPresenter->getBodyPresenter());
    }

    public function testGetImagePresenterReturnsInstanceOfSharedImagePresenter(): void
    {
        $groupPresenter = new GroupPresenter($this->mockGroup, $this->mockRouter, $this->mockHelperFactory);
        $this->assertInstanceOf(SharedImagePresenter::class, $groupPresenter->getImagePresenter());
    }

    public function testGetTitlePresenterReturnsInstanceOfGroupTitlePresenter(): void
    {
        $groupPresenter = new GroupPresenter($this->mockGroup, $this->mockRouter, $this->mockHelperFactory);
        $this->assertInstanceOf(TitlePresenter::class, $groupPresenter->getTitlePresenter());
    }
}
