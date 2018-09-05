<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Isite\Mapper;

use App\Controller\Helpers\IsiteKeyHelper;
use App\ExternalApi\Isite\Domain\ContentBlock\Table;
use App\ExternalApi\Isite\Mapper\ContentBlockMapper;
use App\ExternalApi\Isite\Mapper\MapperFactory;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

class ContentBlockMapperTest extends TestCase
{
    /** @var ContentBlockMapper */
    private $mapper;

    public function setUp()
    {
        $keyHelper = new IsiteKeyHelper();
        $this->mapper = new ContentBlockMapper(new MapperFactory($keyHelper), $keyHelper);
    }

    public function testMappingTableObject()
    {
        $xml = new SimpleXMLElement(file_get_contents(__DIR__ . '/table.xml'));

        $block = $this->mapper->getDomainModel($xml);

        $this->assertInstanceOf(Table::class, $block);
        $this->assertEquals('This table has 2 columns and 2 rows, but I\'ve only populated the 1st column of each row', $block->getTitle());
        $this->assertEquals(['Country', 'Capital'], $block->getHeadings());
        $this->assertEquals([['Italy', ''], ['Rome', '']], $block->getRows());
    }
}
