<?php
declare(strict_types=1);

namespace App\ExternalApi\RmsPodcast\Domain;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

class RmsPodcast
{
    /** @var Pid  */
    private $pid;

    /** @var string */
    private $territory;

    public function __construct(Pid $pid, string $territory)
    {
        $this->pid = $pid;
        $this->territory = $territory;
    }

    public function getPid(): Pid
    {
        return $this->pid;
    }

    public function isOnlyInUk(): bool
    {
        return $this->territory == 'uk';
    }
}
