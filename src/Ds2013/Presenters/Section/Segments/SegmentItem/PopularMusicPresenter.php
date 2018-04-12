<?php
declare(strict_types=1);

namespace App\Ds2013\Presenters\Section\Segments\SegmentItem;

use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use Exception;

class PopularMusicPresenter extends AbstractMusicSegmentItemPresenter
{
    protected function setupContributions(): void
    {
        $primaryContributors = [];
        $featuredContributors = [];
        $versusContributors = [];
        $otherContributors = [];

        /** @var Contribution $contribution */
        foreach ($this->segment->getContributions() as $contribution) {
            $role = strtolower($contribution->getCreditRole());
            $contributorPid = (string) $contribution->getContributor()->getPid();

            if (in_array($role, ['performer', 'dj', 'mc'])) {
                if (!isset($primaryContributors[$contributorPid])) {
                    $primaryContributors[$contributorPid] = true;
                    $this->primaryContributions[] = $contribution;
                }
            } elseif ($role === 'featured artist') {
                if (!isset($featuredContributors[$contributorPid])) {
                    $featuredContributors[$contributorPid] = true;
                    $this->featuredContributions[] = $contribution;
                }
            } elseif ($role === 'vs artist') {
                if (!isset($versusContributors[$contributorPid])) {
                    $versusContributors[$contributorPid] = true;
                    $this->versusContributions[] = $contribution;
                }
            } else {
                if (!isset($otherContributors[$contributorPid])) {
                    $otherContributors[$contributorPid] = true;
                    $this->otherContributions[] = $contribution;
                }
            }
        }

        if (empty($this->primaryContributions) && !empty($this->otherContributions)) {
            $this->primaryContributions[] = array_shift($this->otherContributions);
        }
    }
}
