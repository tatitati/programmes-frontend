<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Domain\CoreEntity\Group\SubPresenter;

use App\DsAmen\Presenters\Domain\CoreEntity\Base\SubPresenter\BaseTitlePresenter;
use Symfony\Component\Routing\Generator\UrlGenerator;

class TitlePresenter extends BaseTitlePresenter
{
    public function getUrl(): string
    {
        return $this->router->generate(
            'find_by_pid',
            ['pid' => $this->coreEntity->getPid()],
            UrlGenerator::ABSOLUTE_URL
        );
    }
}
