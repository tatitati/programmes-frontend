<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Utilities\Calendar;

use App\Ds2013\Presenters\Utilities\Calendar\CalendarPresenter;
use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Cake\Chronos\Date;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

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
        /** @var Service|PHPUnit_Framework_MockObject_MockObject $service */
        $service = $this->createMock(Service::class);

        return new CalendarPresenter(Date::now(), $service, $options);
    }
}
