<?php
declare(strict_types = 1);
namespace Tests\App\Ds2013;

use App\Ds2013\PresenterFactory;
use App\Ds2013\Organism\Broadcast\BroadcastPresenter;
use App\Ds2013\Organism\Programme\ProgrammePresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use RMP\Translate\Translate;
use PHPUnit\Framework\TestCase;

/**
 * @covers App\Ds2013\PresenterFactory
 */
class PresenterFactoryTest extends TestCase
{
    /** @var Translate */
    private $translate;

    /** @var PresenterFactory */
    private $factory;

    public function setUp()
    {
        $this->translate = $this->createMock(Translate::class);
        $this->factory = new PresenterFactory($this->translate);
    }

    public function testGetSetTranslate()
    {
        $this->assertSame($this->translate, $this->factory->getTranslate());

        $newTranslate = $this->createMock(Translate::class);
        $this->factory->setTranslate($newTranslate);

        $this->assertSame($newTranslate, $this->factory->getTranslate());
    }

    public function testOrganismBroadcast()
    {
        $mockBroadcast = $this->createMock(Broadcast::class);

        $this->assertEquals(
            new BroadcastPresenter($mockBroadcast, ['opt' => 'foo']),
            $this->factory->broadcastPresenter($mockBroadcast, ['opt' => 'foo'])
        );
    }

    public function testOrganismProgramme()
    {
        $mockProgramme = $this->createMock(Programme::class);

        $this->assertEquals(
            new ProgrammePresenter($this->translate, $mockProgramme, ['opt' => 'foo']),
            $this->factory->programmePresenter($mockProgramme, ['opt' => 'foo'])
        );
    }
}
