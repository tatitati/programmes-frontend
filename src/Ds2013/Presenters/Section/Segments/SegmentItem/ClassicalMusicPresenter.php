<?php
declare(strict_types=1);

namespace App\Ds2013\Presenters\Section\Segments\SegmentItem;

use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use Exception;

class ClassicalMusicPresenter extends AbstractMusicSegmentItemPresenter
{
    public function setupContributions(): void
    {
        /** @var Contribution $contribution */
        foreach ($this->segment->getContributions() as $contribution) {
            if (empty($this->primaryContributions) && strtolower($contribution->getCreditRole()) === 'composer') {
                $this->primaryContributions[] = $contribution;
            } else {
                $this->otherContributions[] = $contribution;
            }
        }

        if (empty($this->primaryContributions) && !empty($this->otherContributions)) {
            $this->primaryContributions[] = array_shift($this->otherContributions);
        }
    }
}
