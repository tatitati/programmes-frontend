<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\SoundsNav\Mapper;

use App\ExternalApi\SoundsNav\Domain\SoundsNav;
use App\ExternalApi\SoundsNav\Mapper\SoundsNavMapper;
use PHPUnit\Framework\TestCase;

class SoundsNavMapperTest extends TestCase
{
    public function testMapItem()
    {
        $data = [
            'head' => ' Do',
            'body' => 'Re ',
            'foot' => ' Mi ',
        ];

        $mapper = new SoundsNavMapper();

        $expectedEntity = new SoundsNav('Do', 'Re', 'Mi');

        $this->assertEquals($expectedEntity, $mapper->mapItem($data));
    }
}
