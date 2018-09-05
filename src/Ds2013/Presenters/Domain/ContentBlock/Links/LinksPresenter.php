<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Domain\ContentBlock\Links;

use App\Ds2013\Presenter;
use App\ExternalApi\Isite\Domain\ContentBlock\Links;

class LinksPresenter extends Presenter
{
    /** @var bool */
    private $inPrimaryColumn;

    /** @var Links */
    private $linksBlock;

    public function __construct(Links $linksBlock, bool $inPrimaryColumn, array $options = [])
    {
        parent::__construct($options);
        $this->linksBlock = $linksBlock;
        $this->inPrimaryColumn = $inPrimaryColumn;
    }

    public function getLinksBlock(): Links
    {
        return $this->linksBlock;
    }

    public function extractExternalHost(string $url): ?string
    {
        preg_match('@^(?:http[s]*://)?([^/?]+)@i', $url, $matches);
        if (!isset($matches[1])) {
            return null;
        }

        $host = $matches[1];
        $checkHostForBeeb = strpos($host, 'bbc.co.uk');
        if ($checkHostForBeeb === false) {
            return $host;
        }

        return null;
    }
}
