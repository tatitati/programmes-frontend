<?php
declare(strict_types = 1);

namespace Tests\App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Organism\Map\SubPresenter\ComingSoonPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use PHPUnit\Framework\TestCase;

class ComingSoonPresenterTest extends TestCase
{
    public function testComingSoonText()
    {
        $programmeContainer = $this->createMock(ProgrammeContainer::class);
        $programmeContainer->method('getOption')
            ->with('comingsoon_textonly')
            ->willReturn('some text');

        $presenter = new ComingSoonPresenter($programmeContainer, null);
        $this->assertSame('some text', $presenter->getComingSoonTextOnly());
    }
}
