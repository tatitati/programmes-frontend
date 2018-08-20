<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\SoundsNav\Domain;

use App\ExternalApi\SoundsNav\Domain\SoundsNav;
use PHPUnit\Framework\TestCase;

class SoundsNavTest extends TestCase
{
    public function testConstructor()
    {
        $button = new SoundsNav('Do', 'Re', 'Mi');

        $this->assertEquals('Do', $button->getHead());
        $this->assertEquals('Re', $button->getBody());
        $this->assertEquals('Mi', $button->getFoot());
    }
}
