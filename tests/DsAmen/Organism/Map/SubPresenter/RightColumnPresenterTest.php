<?php
declare(strict_types = 1);

namespace Tests\App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Organism\Map\SubPresenter\RightColumnPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use PHPUnit\Framework\TestCase;

class RightColumnPresenterTest extends TestCase
{
    /**
     * @dataProvider invalidOptionProvider
     * @param mixed[] $options
     * @param string $expectedExceptionMessage
     */
    public function testInvalidOptions(array $options, string $expectedExceptionMessage)
    {
        $programmeContainer = $this->createMock(ProgrammeContainer::class);

        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->getMockBuilder(RightColumnPresenter::class)
            ->setConstructorArgs([$programmeContainer, $options])
            ->getMockForAbstractClass();
    }

    public function invalidOptionProvider(): array
    {
        return [
            'invalid-show_mini_map' => [['show_mini_map' => 'bar'], 'show_mini_map option must be a boolean'],
        ];
    }
}
