<?php

namespace App\ExternalApi\FavouritesButton\Mapper;

use App\ExternalApi\FavouritesButton\Domain\FavouritesButton;

class FavouritesButtonMapper
{
    public function mapItem(array $data): FavouritesButton
    {
        return new FavouritesButton(trim($data['head']), trim($data['script']), trim($data['bodyLast']));
    }
}
