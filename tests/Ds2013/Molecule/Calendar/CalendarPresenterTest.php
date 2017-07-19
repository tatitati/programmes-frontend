<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Molecule\Calendar;

use App\Ds2013\InvalidOptionException;
use App\Ds2013\Molecule\Calendar\CalendarPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Date;
use PHPUnit\Framework\TestCase;

class CalendarPresenterTest extends TestCase
{
    public function testInvalidOptions()
    {
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage("hide_caption must a bool");
        $this->createPresenter(['hide_caption' => 'zzzz']);
    }

    private function createPresenter(array $options = [])
    {
        $service = $this->createMock(Service::class);

        return new CalendarPresenter(Date::now(), $service, $options);
    }
}
