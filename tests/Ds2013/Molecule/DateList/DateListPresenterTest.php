<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Molecule\DateList;

use App\Ds2013\InvalidOptionException;
use App\Ds2013\Molecule\DateList\DateListPresenter;
use App\Ds2013\Molecule\DateList\DayDateListItemPresenter;
use App\Ds2013\Molecule\DateList\MonthDateListItemPresenter;
use App\Ds2013\Molecule\DateList\YearDateListItemPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DateListPresenterTest extends TestCase
{
    /**
     * @dataProvider formatTestProvider
     *
     * @param string $format
     * @param string $className
     */
    public function testCreatingDateListItem(string $format, string $className)
    {
        $presenter = $this->createPresenter(['format' => $format]);
        $this->assertInstanceOf($className, $presenter->getDateListItem(0));
    }

    public function formatTestProvider(): array
    {
        return [
            ['day', DayDateListItemPresenter::class],
            ['month', MonthDateListItemPresenter::class],
            ['year', YearDateListItemPresenter::class],
        ];
    }

    public function testInvalidOptions()
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage("Format must be 'day', 'month' or 'year'");
        $this->createPresenter(['format' => 'zzzz']);
    }

    private function createPresenter(array $options = [])
    {
        $urlGeneratorInterface = $this->createMock(UrlGeneratorInterface::class);
        $service = $this->createMock(Service::class);

        return new DateListPresenter($urlGeneratorInterface, Chronos::now(), $service, $options);
    }
}
