<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Section\Footer;

use App\Ds2013\Presenters\Section\Footer\FooterPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Format;
use BBC\ProgrammesPagesService\Domain\Entity\Genre;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\ValueObject\Nid;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class FooterPresenterTest extends TestCase
{
    /** @var Programme|PHPUnit_Framework_MockObject_MockObject */
    private $mockProgramme;

    public function setUp()
    {
        $mockImage = $this->createMock(Image::class);
        $mockImage->method('getUrl')->with(112, 'n')->willReturn('image/url.png');

        $mockNetwork = $this->createMock(Network::class);
        $mockNetwork->method('getUrlKey')->willReturn('bbcone');
        $mockNetwork->method('getName')->willReturn('BBC One');

        $navLinks = ['title' => 'Schedule', 'url' => 'path/to/schedule'];
        $mockNetwork->method('getOption')->with('navigation_links')->willReturn($navLinks);

        $mockNetwork->method('getNid')->willReturn(new Nid('bbc_one'));
        $mockNetwork->method('getImage')->willReturn($mockImage);

        $this->mockProgramme = $this->createMock(Programme::class);
        $this->mockProgramme->method('getNetwork')->willReturn($mockNetwork);
    }

    public function testRetrieveNetworkData()
    {
        $footerPresenter = new FooterPresenter($this->mockProgramme, []);

        $this->assertEquals('BBC One', $footerPresenter->getNetworkName());
        $this->assertEquals('bbcone', $footerPresenter->getNetworkUrlKey());
        $this->assertEquals('bbc_one', $footerPresenter->getNid());
        $this->assertEquals('image/url.png', $footerPresenter->getNetworkImageUrl());
        $this->assertEquals(['title' => 'Schedule', 'url' => 'path/to/schedule'], $footerPresenter->getNavigationLinks());
    }

    public function testRetrieveProgrammeGenres()
    {
        $parentGenre = new Genre([0], 'parent_id', 'Parent Title', 'parent_url_key', null);
        $genre = new Genre([0, 1], 'id', 'Title', 'url_key', $parentGenre);
        $someOtherGenre = new Genre([2], 'other_id', 'Other Title', 'other_url_key', null);

        $this->mockProgramme->method('getGenres')->willReturn([$genre, $someOtherGenre]);

        $footerPresenter = new FooterPresenter($this->mockProgramme, []);
        $this->assertEquals([$someOtherGenre, $genre], $footerPresenter->getGenres());
    }

    public function testRetrieveProgrammeFormats()
    {
        $format = new Format([3], 'format_id', 'format_title', 'format_url_key');
        $otherFormat = new Format([4], 'other_format_id', 'other_format_title', 'other_format_url_key');

        $this->mockProgramme->method('getFormats')->willReturn([$format, $otherFormat]);

        $footerPresenter = new FooterPresenter($this->mockProgramme, []);
        $this->assertEquals([$format, $otherFormat], $footerPresenter->getFormats());
    }

    public function testIsWorldNews()
    {
        $mockWorldNewsNetwork = $this->createConfiguredMock(Network::class, [
            'getNid' => new Nid('bbc_world_news'),
        ]);

        $mockWorldProgramme = $this->createConfiguredMock(Programme::class, [
            'getNetwork' => $mockWorldNewsNetwork,
        ]);

        $footerPresenter = new FooterPresenter($mockWorldProgramme, []);
        $this->assertEquals(true, $footerPresenter->isWorldNews());
    }
}
