<?php
declare(strict_types = 1);

namespace Tests\App\DsShared\Helpers\GuidanceWarningHelper;

use App\DsShared\Helpers\Enumeration\GuidanceWarningEnum;
use App\DsShared\Helpers\GuidanceWarningHelper;
use PHPUnit\Framework\TestCase;

class GuidanceWarningHelperTest extends TestCase
{
    /** @var GuidanceWarningHelper */
    private $helper;

    public function setUp()
    {
        $this->helper = new GuidanceWarningHelper();
    }

    /** @dataProvider getAllCombinationsProvider */
    public function testAllCombinations($adult, $language, $sex, $violence, $disturbing, $rfi, $watershed, $shortText, $longText)
    {
        $codes = $adult . ',' . $language . ',' . $sex . ',' . $violence . ',' . $disturbing . ',' . $rfi . ',' . $watershed;
        $this->assertSame($longText, $this->helper->getText($codes));
        $this->assertSame($shortText, $this->helper->getText($codes, GuidanceWarningEnum::TEXT_SHORT));
    }

    public function getAllCombinationsProvider(): array
    {
        $data = [];
        $handle = fopen(realpath('./') . '/tests/DsShared/Helpers/GuidanceWarningHelper/guidance_combination.csv', 'r');
        while (($line = fgetcsv($handle, 1000, ',')) !== false) {
            $data[] = $line;
        }
        fclose($handle);
        return $data;
    }
}
