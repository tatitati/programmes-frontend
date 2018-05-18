<?php
declare(strict_types=1);

namespace App\ExternalApi\RmsPodcast\Domain;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;

class RmsPodcast
{
    /** @var Pid  */
    private $pid;

    public function __construct(Pid $pid)
    {
        $this->pid = $pid;
    }

    public function getPid(): Pid
    {
        return $this->pid;
    }
}
