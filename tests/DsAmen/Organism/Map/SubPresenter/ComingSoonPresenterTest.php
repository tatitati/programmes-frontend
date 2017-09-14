<?php
declare(strict_types = 1);

namespace Tests\App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Organism\Map\SubPresenter\ComingSoonPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use PHPUnit\Framework\TestCase;

class ComingSoonPresenterTest extends TestCase
{
    /**
     * @dataProvider invalidOptionProvider
     * @param mixed[] $options
     * @param string $expectedExceptionMessage
     */
    public function testInvalidOptions(array $options, string $expectedExceptionMessage)
    {
        $programme = $this->createMock(Programme::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        new ComingSoonPresenter($programme, null, $options);
    }

    public function invalidOptionProvider(): array
    {
        return [
            'invalid-show_mini_map' => [['show_mini_map' => 'bar', 'show_synopsis' => false], 'show_mini_map option must be a boolean'],
            'invalid-show_synopsis' => [['show_mini_map' => true, 'show_synopsis' => 'baz'], 'show_synopsis option must be a boolean'],
        ];
    }
}
