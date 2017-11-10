<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Electron\Mapper;

use App\ExternalApi\Electron\Domain\SupportingContentItem;
use App\ExternalApi\Electron\Mapper\SupportingContentMapper;
use App\ExternalApi\XmlParser\XmlParser;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use PHPUnit\Framework\TestCase;

class SupportingContentMapperTest extends TestCase
{
    private $simpleXml;

    public function setUp()
    {
        $xml = file_get_contents(dirname(dirname(__DIR__)) . '/XmlParser/electron_eastenders.xml');
        $parser = new XmlParser() ;
        $this->simpleXml = $parser->parse($xml);
    }

    public function testMapItem()
    {
        $pages = $this->simpleXml->content->pages;
        $supportingContentMapper = new SupportingContentMapper();

        foreach ($pages->page as $page) {
            $item = $supportingContentMapper->mapItem($page);
            if ($item) {
                $items[] = $item;
            }
        }
        $this->assertCount(2, $items);
        $this->assertInstanceOf(SupportingContentItem::class, $items[0]);
        $this->assertEquals('The Queen Vic Jukebox on BBC Music', $items[0]->getTitle());
        $this->assertInstanceOf(Image::class, $items[0]->getImage());

        $this->assertEquals('This has no image', $items[1]->getTitle());
        $this->assertNull($items[1]->getImage());
    }
}
