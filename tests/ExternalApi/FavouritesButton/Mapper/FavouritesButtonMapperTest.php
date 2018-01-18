<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\FavouritesButton\Mapper;

use App\ExternalApi\FavouritesButton\Domain\FavouritesButton;
use App\ExternalApi\FavouritesButton\Mapper\FavouritesButtonMapper;
use PHPUnit\Framework\TestCase;

class FavouritesButtonMapperTest extends TestCase
{
    public function testMapItem()
    {
        $data = [
            'head' => ' Do',
            'script' => 'Re ',
            'bodyLast' => ' Mi ',
        ];

        $mapper = new FavouritesButtonMapper();

        $expectedEntity = new FavouritesButton('Do', 'Re', 'Mi');

        $this->assertEquals($expectedEntity, $mapper->mapItem($data));
    }
}
