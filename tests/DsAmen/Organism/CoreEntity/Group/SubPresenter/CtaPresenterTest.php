<?php
declare(strict_types=1);

namespace Tests\App\DsAmen\Organism\CoreEntity\Group\SubPresenter;

use App\DsAmen\Organism\CoreEntity\Group\SubPresenter\CtaPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Collection;
use BBC\ProgrammesPagesService\Domain\Entity\Gallery;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\App\DsAmen\Organism\CoreEntity\BaseSubPresenterTest;

class CtaPresenterTest extends BaseSubPresenterTest
{
    /** @var UrlGeneratorInterface */
    private $router;

    public function setUp()
    {
        $this->router = $this->createRouter();
    }

    /** @dataProvider getMediaIconNameProvider */
    public function testGetMediaIconName($group, string $expected)
    {
        $ctaPresenter = new CtaPresenter($group, $this->router);
        $this->assertSame($expected, $ctaPresenter->getMediaIconName());
    }

    public function getMediaIconNameProvider(): array
    {
        $gallery = $this->createMock(Gallery::class);
        $collection = $this->createMock(Collection::class);

        return [
            'Collection returns collection icon' => [$collection, 'collection'],
            'Gallery returns image icon' => [$gallery, 'image'],
        ];
    }

    public function testGetLabelTranslationReturnsEmptyString()
    {
        $gallery = $this->createMock(Gallery::class);
        $ctaPresenter = new CtaPresenter($gallery, $this->router);
        $this->assertSame('', $ctaPresenter->getLabelTranslation());
    }

    public function testGetUrlReturnsFindByPidRoute()
    {
        $gallery = $this->createConfiguredMock(Gallery::class, ['getPid' => new Pid('g0000001')]);
        $ctaPresenter = new CtaPresenter($gallery, $this->router);
        $this->assertSame('http://localhost/programmes/g0000001', $ctaPresenter->getUrl());
    }
}
