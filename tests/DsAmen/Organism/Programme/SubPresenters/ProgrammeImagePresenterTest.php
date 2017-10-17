<?php
declare(strict_types=1);

namespace Tests\App\DsAmen\Organism\Programme\SubPresenters;

use App\DsAmen\Organism\Programme\SubPresenters\ProgrammeCtaPresenter;
use App\DsAmen\Organism\Programme\SubPresenters\ProgrammeImagePresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use PHPUnit_Framework_MockObject_MockObject;
use Tests\App\DsAmen\Organism\Programme\BaseProgrammeSubPresenterTest;

class ProgrammeImagePresenterTest extends BaseProgrammeSubPresenterTest
{
    /** @var  Episode|PHPUnit_Framework_MockObject_MockObject */
    private $programme;

    /** @var  ProgrammeCtaPresenter|PHPUnit_Framework_MockObject_MockObject */
    private $ctaPresenter;

    public function setUp()
    {
        $this->programme = $this->createMock(Episode::class);
        $this->ctaPresenter = $this->createMock(ProgrammeCtaPresenter::class);
    }

    public function testGetImageReturnsNullWhenOptionIsFalse(): void
    {
        $imagePresenter = new ProgrammeImagePresenter($this->programme, $this->ctaPresenter, ['show_image' => false]);
        $this->assertNull($imagePresenter->getImage());
    }

    public function testGetImageReturnsImageWhenOptionIsTrue(): void
    {
        $imagePresenter = new ProgrammeImagePresenter($this->programme, $this->ctaPresenter, ['show_image' => true]);
        $this->assertNotNull($imagePresenter->getImage());
    }
}
