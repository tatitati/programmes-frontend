<?php
declare(strict_types = 1);

namespace App\DsShared\Helpers;

use App\DsShared\Helpers\Enumeration\GuidanceWarningEnum;

class GuidanceWarningHelper
{

    /**
     * Given a list of guidance warning codes separated by comas it returns the guidance warning text
     *
     * @param string $guidanceWarningCodes
     * @param string $textLength
     * @return string
     */
    public function getText(string $guidanceWarningCodes, $textLength = GuidanceWarningEnum::TEXT_LONG): string
    {
        $codes = array_map('trim', explode(',', $guidanceWarningCodes));
        $codes = $this->orderCodes($codes);
        $numberOfCodes = count($codes);
        if ($numberOfCodes === 0) {
            return '';
        }
        // start building the text
        $text = 'Contains ';
        $and = ' and ';
        // Handle a different behaviour for and edge case
        if ($textLength === GuidanceWarningEnum::TEXT_LONG && in_array(GuidanceWarningEnum::W2, $codes)) {
            $text = 'Deals ';
            $and = ' and contains ';
        }
        $newCodes = [];
        foreach ($codes as $code) {
            $newCodes[] = $this->getTextForSingleCode($code, $textLength);
        }
        $last = array_pop($newCodes);
        if (empty($newCodes)) {
            return $text . $last . '.';
        }
        $text .= implode(', ', $newCodes) . $and . $last . '.';
        return $text;
    }

    private function getTextForSingleCode(string $code, $textLength): string
    {
        return constant(GuidanceWarningEnum::class . '::' . $code . '_' . $textLength);
    }

    /**
     *  Order codes by text display priority.
     *  Every display priority block only expect a maximum of one code.
     *
     * @param array $codes
     * @return array
     */
    private function orderCodes(array $codes): array
    {
        $orderedCodes = [];
        foreach ($codes as $code) {
            // first "watershed"
            if (in_array($code, GuidanceWarningEnum::CODES_WATERSHED)) {
                $orderedCodes[0] = $code;
                continue;
            }
            // second "language"
            if (in_array($code, GuidanceWarningEnum::CODES_LANGUAGE)) {
                $orderedCodes[1] = $code;
                continue;
            }
            // third "adult humour"
            if (in_array($code, GuidanceWarningEnum::CODES_HUMOUR)) {
                $orderedCodes[2] = $code;
                continue;
            }
            // forth "sex"
            if (in_array($code, GuidanceWarningEnum::CODES_SEX)) {
                $orderedCodes[3] = $code;
                continue;
            }
            // fifth "violence"
            if (in_array($code, GuidanceWarningEnum::CODES_VIOLENCE)) {
                $orderedCodes[4] = $code;
                continue;
            }
            // sixth "disturbing"
            if (in_array($code, GuidanceWarningEnum::CODES_DISTURBING)) {
                $orderedCodes[5] = $code;
                continue;
            }
            // seventh "flashing images"
            if (in_array($code, GuidanceWarningEnum::CODES_FLASHING)) {
                $orderedCodes[6] = $code;
                continue;
            }
        }
        // Is required to sort the array by key because numeric indexes
        // don't necessarily match the order of the array
        ksort($orderedCodes);
        return $orderedCodes;
    }
}
