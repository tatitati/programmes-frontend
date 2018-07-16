<?php
declare(strict_types = 1);

namespace App\Controller\Helpers;

class IsiteKeyHelper
{
    private $guidCharacters = '0123456789abcdef';

    private $idCharacters = '0123456789bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ';

    public function convertGuidToKey(string $guid): string
    {
        return $this->convertBase(str_replace('-', '', $guid), $this->guidCharacters, $this->idCharacters);
    }

    public function convertKeyToGuid(string $key): string
    {
        $characters = $this->convertBase($key, $this->idCharacters, $this->guidCharacters);
        $characters = str_pad($characters, 32, '0', STR_PAD_LEFT);

        return substr($characters, 0, 8) . '-' .
            substr($characters, 8, 4) . '-' .
            substr($characters, 12, 4) . '-' .
            substr($characters, 16, 4) . '-' .
            substr($characters, 20, 12);
    }

    private function convertBase(string $characters, string $fromString, string $toString): string
    {
        $iFromBase = \strlen($fromString);
        $iToBase = \strlen($toString);

        $length = \strlen($characters);
        $result = '';
        $aDigits = [];
        for ($i = 0; $i < $length; $i++) {
            $aDigits[$i] = strpos($fromString, $characters{$i});
        }
        do { // Loop until whole number is converted
            $divide = 0;
            $newlen = 0;
            for ($i = 0; $i < $length; $i++) { // Perform division manually (which is why this works with big numbers)
                $divide = $divide * $iFromBase + $aDigits[$i];
                if ($divide >= $iToBase) {
                    $aDigits[$newlen++] = (int) ($divide / $iToBase);
                    $divide = $divide % $iToBase;
                } elseif ($newlen > 0) {
                    $aDigits[$newlen++] = 0;
                }
            }
            $length = $newlen;
            $result = $toString{$divide} . $result; // Divide is basically $characters % $iToBase (i.e. the new character)
        } while ($newlen !== 0);

        return $result;
    }
}
