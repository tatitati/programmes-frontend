<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\CoreEntity\Programme\SubPresenter;

use App\DsAmen\Organism\CoreEntity\Base\SubPresenter\BaseCtaPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CtaPresenter extends BaseCtaPresenter
{
    public function getMediaIconName(): string
    {
        if ($this->coreEntity instanceof Episode) {
            if ($this->coreEntity->isAudio()) {
                return 'iplayer-radio';
            }

            return 'iplayer';
        }

        return 'play';
    }

    public function getLabelTranslation(): string
    {
        if ($this->coreEntity instanceof Episode) {
            return 'iplayer_play_episode';
        }

        return 'iplayer_play_clip';
    }

    public function getUrl(): string
    {
        if ($this->coreEntity instanceof Episode && $this->coreEntity->isVideo()) {
            return $this->router->generate(
                'iplayer_play',
                ['pid' => $this->coreEntity->getPid()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return $this->router->generate(
            'find_by_pid',
            ['pid' => $this->coreEntity->getPid(), '_fragment' => $this->coreEntity instanceof Episode ? 'play' : ''],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
