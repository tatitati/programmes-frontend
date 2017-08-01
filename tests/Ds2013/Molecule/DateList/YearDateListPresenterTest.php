<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Molecule\DateList;

use App\Ds2013\Molecule\DateList\YearDateListItemPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class YearDateListPresenterTest extends TestCase
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
                'schedules_by_year',
                ['pid' => (string) $pid, 'year' => $now->addYears($offset)->format('Y')],
                UrlGeneratorInterface::ABSOLUTE_URL
            )->willReturn('aUrl');
        $presenter = new YearDateListItemPresenter($urlGeneratorInterface, $now, $service, $offset);
        $presenter->getLink();
    }
}
