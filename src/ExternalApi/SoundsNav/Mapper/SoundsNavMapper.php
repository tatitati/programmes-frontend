<?php
declare(strict_types = 1);

namespace App\ExternalApi\SoundsNav\Mapper;

use App\ExternalApi\SoundsNav\Domain\SoundsNav;

class SoundsNavMapper
{
    public function mapItem(array $data): SoundsNav
    {
        return new SoundsNav(trim($data['head']), trim($data['body']), trim($data['foot']));
    }
}
