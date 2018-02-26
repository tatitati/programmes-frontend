<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Utilities\Credits;

use App\Ds2013\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Contribution;

class CreditsPresenter extends Presenter
{
    /** @var Contribution[] */
    private $contributions;

    /** @var bool */
    private $shouldShowRoleColumn;

    public function __construct(array $contributions, array $options = [])
    {
        parent::__construct($options);

        $this->contributions = $contributions;
        $this->shouldShowRoleColumn = $this->contributionsContainACreditRole($contributions);
    }

    public function getContributions(): array
    {
        return $this->contributions;
    }

    public function shouldShowRoleColumn(): bool
    {
        return $this->shouldShowRoleColumn;
    }

    /**
     * @param Contribution[] $contributions
     * @return bool
     */
    private function contributionsContainACreditRole(array $contributions): bool
    {
        foreach ($contributions as $contribution) {
            if ($contribution->getCreditRole()) {
                return true;
            }
        }

        return false;
    }
}
