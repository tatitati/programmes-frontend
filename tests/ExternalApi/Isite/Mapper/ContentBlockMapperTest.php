<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Isite\Mapper;

use App\Controller\Helpers\IsiteKeyHelper;
use App\ExternalApi\Isite\Domain\ContentBlock\Faq;
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

    public function testMappingFaqObject()
    {
        $xml = new SimpleXMLElement(file_get_contents(__DIR__ . '/faq.xml'));

        $block = $this->mapper->getDomainModel($xml);

        $this->assertInstanceOf(Faq::class, $block);
        $this->assertEquals('This is a FAQ content box', $block->getTitle());
        $this->assertEquals('This is an optional intro', $block->getIntro());
        $this->assertEquals(
            [
                ['question' => 'What is the population of London?', 'answer' => '<p>8,825,000</p>'],
                ['question' => 'What is the population of Paris?', 'answer' => '<p>2,244,000</p>'],
                ['question' => 'What is the population of Buenos Aires?', 'answer' => '<p>2,891,000</p>'],
            ],
            $block->getQuestions()
        );
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
