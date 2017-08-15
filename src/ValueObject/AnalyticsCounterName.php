<?php
declare(strict_types = 1);

namespace App\ValueObject;

use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Service;

class AnalyticsCounterName
{
    /** @var string */
    private $counterName;

    public function __construct($context = null, string $relativePath = null)
    {
        // prefix
        $this->counterName = 'programmes';

        if ($context instanceof Service) {
            $this->counterName .= $this->getServiceCounterNamePart($context, $relativePath);
        } elseif ($context instanceof Programme) {
            $this->counterName .= $this->getProgrammeCounterNamePart($context, $relativePath);
        } else {
            $this->counterName .= $this->getDefaultCounterNamePart($relativePath);
        }

        // Suffix
        $this->counterName .= '.page';
    }

    public function __toString(): string
    {
        return $this->counterName;
    }

    private function getServiceCounterNamePart(Service $context, string $relativePath): string
    {
        // Example: programmes.schedules.bbc_one_cambridge.2017.07.17.page
        $pid = $context->getPid();
        $sid = $context->getSid();
        $partialString = str_replace($pid, $sid, $relativePath);
        $partialString = str_replace(['/', '-'], '.', $partialString);
        $partialString = $this->replaceDisallowedCharacters($partialString);
        return $partialString;
    }

    private function getProgrammeCounterNamePart(Programme $context, string $relativePath): string
    {
        // Example: programmes.doctor_who.brand.b006q2x0.page
        $partialString = $this->getParentTitlesRecursively($context);
        $partialString .= '.' . $context->getType();
        $partialString .= '.' . $context->getPid();

        // add the rest of the URL to the counter name
        $restOfUrl = str_replace(['/programmes/', $context->getPid()], '', $relativePath);
        $restOfUrl = str_replace('/', '.', $restOfUrl);
        $partialString .= $restOfUrl;
        return $partialString;
    }

    /**
     * This function uses recursion to climb up the parents tree and return a concatenation of the titles
     */
    private function getParentTitlesRecursively($context): string
    {
        if (isset($context)) {
            // concatenate title with the parents titles
            $titlesConcatenation = $this->getParentTitlesRecursively($context->getParent());
            return $titlesConcatenation . '.' . $this->replaceDisallowedCharacters($context->getTitle());
        }
        return '';
    }

    /**
     * Build the counter name based on the URL or use a predefined one if there is any for this route
     *
     * @param string $relativePath
     * @return string
     */
    private function getDefaultCounterNamePart(string $relativePath): string
    {
        // Example: programmes.home.schedules.page
        switch ($relativePath) {
            case '/programmes':
                $partialString = '.home';
                break;
            case '/schedules':
                $partialString = '.home.schedules';
                break;
            default:
                $partialString = str_replace('/programmes', '', $relativePath);
                $partialString = str_replace('/', '.', $partialString);
                $partialString = $this->replaceDisallowedCharacters($partialString);
        }
        return $partialString;
    }

    private function replaceDisallowedCharacters(string $string): string
    {
        $string = preg_replace(array('/[^a-zA-Z0-9_.]/', '{_+}', '{\._}'), '_', $string);
        $string = str_replace('_.', '.', $string);
        $string = preg_replace('{_+}', '_', $string);
        $string = trim($string, '_');
        $string = strtolower($string);
        return $string;
    }
}
