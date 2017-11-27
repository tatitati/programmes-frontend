<?php
declare(strict_types=1);

namespace Tests\App\DsAmen\Organism\CoreEntity\Shared\SubPresenter;

use App\DsAmen\Organism\CoreEntity\Shared\SubPresenter\BodyPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\ValueObject\PartialDate;
use Tests\App\DsAmen\Organism\CoreEntity\BaseSubPresenterTest;

class BodyPresenterTest extends BaseSubPresenterTest
{
    /** @dataProvider hasFullDetailsProvider */
    public function testHasFullDetails(Programme $programme, bool $showSynopsis, bool $expected): void
    {
        $bodyPresenter = new BodyPresenter(
            $programme,
            ['show_release_date' => true, 'show_synopsis' => $showSynopsis]
        );

        $this->assertSame($expected, $bodyPresenter->hasFullDetails());
    }

    public function hasFullDetailsProvider(): array
    {
        $clip = $this->createMockClip();
        $clip->method('getReleaseDate')->willReturn(new PartialDate(2016, 12, 12));

        $clipWithoutDuration = $this->createMockClip();
        $clipWithoutDuration->method('getDuration')->willReturn(0);

        $brand = $this->createMockBrand();

        return [
            "Has release date and option is true, return true" => [$clip, true, true],
            "Has release date but option is false, return false" => [$clip, false, true],
            "Doesn't have release date but option is true, return true" => [$clipWithoutDuration, true, true],
            "Isn't programme item but option is true, return true" => [$brand, true, true],
        ];
    }

    /** @dataProvider hasReleaseDateProvider */
    public function testHasReleaseDate(Programme $programme, bool $showReleaseDate, bool $expected): void
    {
        $bodyPresenter = new BodyPresenter($programme, ['show_release_date' => $showReleaseDate]);
        $this->assertSame($expected, $bodyPresenter->hasReleaseDate());
    }

    public function hasReleaseDateProvider(): array
    {
        $clip = $this->createMockClip();
        $clip->method('getReleaseDate')->willReturn(new PartialDate(2016, 12, 12));

        $clipWithoutReleaseDate = $this->createMockClip();
        $clipWithoutReleaseDate->method('getReleaseDate')->willReturn(null);

        $brand = $this->createMockBrand();

        return [
            "Has release date and option is true, return true" => [$clip, true, true],
            "Has release date but option is false, return false" => [$clip, false, false],
            "Doesn't have release date and even though option is true, return false" => [$clipWithoutReleaseDate, true, false],
            "Isn't ProgrammeItem and even though option is true, return false" => [$brand, true, false],
        ];
    }
}
