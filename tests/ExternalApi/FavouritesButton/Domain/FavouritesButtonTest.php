<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\FavouritesButton\Domain;

use App\ExternalApi\FavouritesButton\Domain\FavouritesButton;
use PHPUnit\Framework\TestCase;

class FavouritesButtonTest extends TestCase
{
    public function testConstructor()
    {
        $button = new FavouritesButton('Do', 'Re', 'Mi');

        $this->assertEquals('Do', $button->getHead());
        $this->assertEquals('Re', $button->getScript());
        $this->assertEquals('Mi', $button->getBodyLast());
    }
}
