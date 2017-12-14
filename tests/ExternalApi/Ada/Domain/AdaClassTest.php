<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Ada\Domain;

use App\ExternalApi\Ada\Domain\AdaClass;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use PHPUnit\Framework\TestCase;

class AdaClassTest extends TestCase
{
    public function testConstructor()
    {
        $image = $this->createMock(Image::class);
        $adaClass = new AdaClass('id', 'title', 1, $image);

        $this->assertEquals('id', $adaClass->getId());
        $this->assertEquals('title', $adaClass->getTitle());
        $this->assertEquals(1, $adaClass->getEpisodeCount());
        $this->assertEquals($image, $adaClass->getImage());
    }
}
