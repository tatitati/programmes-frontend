<?php
declare(strict_types = 1);

namespace Tests\App\Ds2013\Presenters\Section\Clip\Details;

use App\Builders\ClipBuilder;
use App\Builders\VersionBuilder;
use App\Ds2013\Presenters\Section\Clip\Details\ClipDetailsPresenter;
use App\DsShared\Helpers\PlayTranslationsHelper;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use Tests\App\BaseTemplateTestCase;

/**
 * @group MapClip
 */
class ClipDetailsPresenterDownloadTest extends BaseTemplateTestCase
{
    /**
     * Presenter HTML -- displaying download button
     */
    public function testDetailsInjectDownloadPresenter()
    {
        $givenDownloadableClip = ClipBuilder::anyWithMediaSets()->build();
        $givenAnyVersion = VersionBuilder::any()->with(['isDownloadable' => true])->build();

        $thenPresenter = $this->presenter($givenDownloadableClip, $givenAnyVersion);

        $this->assertTrue($thenPresenter->canBeDownloaded());
    }

    /**
     * HELPERS
     */
    private function presenter(Clip $clip, ?Version $version): ClipDetailsPresenter
    {
        $stubPlayTrans = $this->createMock(PlayTranslationsHelper::class);

        return new ClipDetailsPresenter($stubPlayTrans, $clip, [], $version, null, []);
    }
}
