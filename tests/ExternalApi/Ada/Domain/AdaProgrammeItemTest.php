<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Ada\Domain;

use App\ExternalApi\Ada\Domain\AdaProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;

class AdaProgrammeItemTest extends TestCase
{
    public function testConstructor()
    {
        $programme = $this->createMock(Programme::class);
        $pid = 'b0000001';
        $title = "Unnamed Show";
        $programmeItemCount = 1;
        $adaClass = new AdaProgrammeItem($programme, $pid, $title, $programmeItemCount);

        $this->assertEquals($pid, $adaClass->getPid());
        $this->assertEquals($title, $adaClass->getTitle());
        $this->assertEquals($programmeItemCount, $adaClass->getProgrammeItemCount());
    }
}
