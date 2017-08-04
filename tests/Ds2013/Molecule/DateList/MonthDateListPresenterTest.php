<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Molecule\DateList;

use App\Ds2013\Molecule\DateList\MonthDateListItemPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MonthDateListPresenterTest extends TestCase
{
    public function testGetLink()
    {
        $now = Chronos::now();
        $offset = 3;
        $pid = new Pid('xxxxxxxx');
        $service = $this->createMock(Service::class);
        $service->method('getPid')->willReturn($pid);
        $urlGeneratorInterface = $this->createMock(UrlGeneratorInterface::class);
        $urlGeneratorInterface->expects($this->once())
            ->method('generate')
            ->with(
                'schedules_by_month',
                ['pid' => (string) $pid, 'date' => $now->addMonths($offset)->format('Y-m')],
                UrlGeneratorInterface::ABSOLUTE_URL
            )->willReturn('aUrl');
        $presenter = new MonthDateListItemPresenter($urlGeneratorInterface, $now, $service, $offset);
        $presenter->getLink();
    }

    public function testIsLink()
    {
        $network = $this->createMock(Network::class);
        $network->method('getMedium')->willReturn('tv');
        $service = $this->createMock(Service::class);
        $service->method('getStartDate')->willReturn(null);
        $service->method('getEndDate')->willReturn(null);
        $service->method('getNetwork')->willReturn($network);
        $presenter = $this->createPresenter($service, -1);
        $this->assertTrue($presenter->isLink());
    }

    public function testIsNotLink()
    {
        $service = $this->createMock(Service::class);
        $service->method('isActiveAt')->willReturn(true);
        $presenter = $this->createPresenter($service, 0);
        $this->assertFalse($presenter->isLink());

        $service = $this->createMock(Service::class);
        $service->method('isActiveAt')->willReturn(false);
        $presenter = $this->createPresenter($service, -1);
        $this->assertFalse($presenter->isLink());
    }

    private function createPresenter(Service $service, int $offset, array $options = [])
    {
        $urlGeneratorInterface = $this->createMock(UrlGeneratorInterface::class);
        return new MonthDateListItemPresenter($urlGeneratorInterface, Chronos::now(), $service, $offset, $options);
    }
}
