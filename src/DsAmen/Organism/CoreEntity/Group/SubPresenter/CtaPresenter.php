<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\CoreEntity\Group\SubPresenter;

use App\DsAmen\Organism\CoreEntity\Base\SubPresenter\BaseCtaPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Collection;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CtaPresenter extends BaseCtaPresenter
{
    public function getMediaIconName(): string
    {
        if ($this->coreEntity instanceof Collection) {
            return 'collection';
        }

        return 'image';
    }

    public function getLabelTranslation(): string
    {
        return '';
    }

    public function getUrl(): string
    {
        return $this->router->generate(
            'find_by_pid',
            ['pid' => $this->coreEntity->getPid()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
