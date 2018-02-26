<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Utilities\Credits;

use App\Ds2013\Presenters\Utilities\Credits\CreditsPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use PHPUnit\Framework\TestCase;

class CreditsPresenterTest extends TestCase
{
    public function testShowingCreditRoleColumn()
    {
        $contribution1 = $this->createMock(Contribution::class);
        $contribution1->method('getCreditRole')->willReturn('');
        $contribution2 = $this->createMock(Contribution::class);
        $contribution2->method('getCreditRole')->willReturn('anything');
        $contributions = [$contribution1, $contribution2];
        $presenter = new CreditsPresenter($contributions);
        $this->assertTrue($presenter->shouldShowRoleColumn());
    }

    public function testHidingCreditRoleColumn()
    {
        $contributions = [];
        $contributions[] = $this->createMock(Contribution::class);
        $contributions[] = $this->createMock(Contribution::class);
        foreach ($contributions as $contribution) {
            $contribution->method('getCreditRole')->willReturn('');
        }
        $presenter = new CreditsPresenter($contributions);
        $this->assertFalse($presenter->shouldShowRoleColumn());
    }
}
