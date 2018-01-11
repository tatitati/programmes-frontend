<?php

namespace App\DsShared\Helpers;

use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use InvalidArgumentException;
use RuntimeException;

class TitleLogicHelper
{
    /**
     * Acceptable values for titleFormat
     * @var array
     */
    private $acceptedTitleFormats = array(
        'tleo::ancestry:item', // TLEO as title, Series => Episode => etc as subtitle. Default in programmes V2.
        'item::ancestry', // Item as title, ancestry as subtitle, TLEO => Series => Subseries. "bottom_up" in programmes V2.
    );

    /**
     * Programme titles are presented in a variety of HTML formats across the app,
     * however the logic for ordering them and placing them into a main title and a list
     * of subtitles remains consistent. This function accepts a programme, optional context and title format,
     * it returns an array with two items. The first, mainTitle, contains the programme object that should be rendered
     * as the <h4>Title</h4> bit. The second, subTitles contains the programmes to show together underneath that.
     *
     * @param CoreEntity $programme
     *      The programme being rendered
     * @param CoreEntity|null $context
     *      The "main" programme for the page if present and different to $programme
     * @param string $titleFormat
     *      see $this->acceptedTitleFormats
     * @return array
     *      [ Programme(or null) $mainTitleProgramme, array $subTitlesProgrammes ]
     */
    public function getOrderedProgrammesForTitle(CoreEntity $programme, ?CoreEntity $context = null, string $titleFormat = 'tleo::ancestry:item')
    {
        if (!in_array($titleFormat, $this->acceptedTitleFormats)) {
            throw new InvalidArgumentException(
                $titleFormat . ' is an invalid setting for title_format'
            );
        }
        $ancestryArray = $programme->getAncestry($context);
        $mainTitleProgramme = null;
        $subTitlesProgrammes = [];

        $count = count($ancestryArray);
        if ($count === 0) {
            throw new RuntimeException("Programme::getAncestry appears to have returned nothing. This should not be possible");
        }

        if ($count === 1) {
            // no ancestors. Title must be the item title
            $mainTitleProgramme = reset($ancestryArray);
        } elseif ($titleFormat === 'item::ancestry') {
            // Item title as main title
            $mainTitleProgramme = array_shift($ancestryArray);
            // Ancestry in TLEO => Series => Episode order is subtitle
            $subTitlesProgrammes = array_reverse($ancestryArray);
        } else {
            // Default tleo:ancestry:item
            $mainTitleProgramme = array_pop($ancestryArray);
            $subTitlesProgrammes = array_reverse($ancestryArray);
        }

        return [$mainTitleProgramme, $subTitlesProgrammes];
    }
}
