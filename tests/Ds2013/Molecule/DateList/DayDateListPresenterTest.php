<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Molecule\DateList;

use App\Ds2013\Molecule\DateList\DayDateListItemPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DayDateListPresenterTest extends TestCase
{

    public function testIsGmt()
    {
        $presenter = $this->createPresenter(1);
        $this->assertTrue($presenter->isGmt());

        $presenter = $this->createPresenter(1, ['user_timezone' => 'GMT']);
        $this->assertTrue($presenter->isGmt());
    }

    public function testIsNotGmt()
    {
        $presenter = $this->createPresenter(1, ['user_timezone' => 'PMT']);
        $this->assertFalse($presenter->isGmt());
    }

    public function testGetLink()
    {
        $now = Chronos::now();
        $offset = 2;
        $pid = new Pid('xxxxxxxx');
        $service = $this->createMock(Service::class);
        $service->method('getPid')->willReturn($pid);
        $urlGeneratorInterface = $this->createMock(UrlGeneratorInterface::class);
        $urlGeneratorInterface->expects($this->once())
            ->method('generate')
            ->with(
                'schedules_by_day',
                ['pid' => (string) $pid, 'date' => $now->addDays($offset)->format('Y-m-d')],
                UrlGeneratorInterface::ABSOLUTE_URL
            )->willReturn('aUrl');
        $presenter = new DayDateListItemPresenter($urlGeneratorInterface, $now, $service, $offset);
        $presenter->getLink();
    }

    private function createPresenter(int $offset, array $options = [])
    {
        $urlGeneratorInterface = $this->createMock(UrlGeneratorInterface::class);
        $service = $this->createMock(Service::class);
        return new DayDateListItemPresenter($urlGeneratorInterface, Chronos::now(), $service, $offset, $options);
    }
}
