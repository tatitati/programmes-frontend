<?php
declare(strict_types = 1);

namespace App\ExternalApi\Electron\Mapper;

use App\ExternalApi\Electron\Domain\SupportingContentItem;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use DateTime;
use DateTimeImmutable;
use SimpleXMLElement;

class SupportingContentMapper
{
    public function mapItem(SimpleXMLElement $page): ?SupportingContentItem
    {
        $title = (string) $page->{'title'};
        // @TODO clean this to a reasonable set of tags?
        $htmlContent = (string) $page->{'wysiwyg'};
        $image = null;
        if (!empty($page->{'image_url'}) && !empty($page->{'image_pid'})) {
            $extension = 'jpg';
            if (preg_match('/\.(\w{3,4})$/', (string) $page->{'image_url'}, $matches)) {
                $extension = $matches[1];
            }
            $altText = '';
            if (!empty($page->{'image_alt'})) {
                $altText = (string) $page->{'image_alt'};
            }
            $image = new Image(new Pid((string) $page->{'image_pid'}), $altText, '', '', '', $extension);
        }
        if (!empty($page->{'startDateTime'})) {
            $startDateTime = DateTimeImmutable::createFromFormat(DateTime::ISO8601, (string) $page->{'startDateTime'});
            if ($startDateTime > ApplicationTime::getTime()) {
                // Item is not valid yet
                return null;
            }
        }
        if (!empty($page->{'endDateTime'})) {
            $endDateTime = DateTimeImmutable::createFromFormat(DateTime::ISO8601, (string) $page->{'endDateTime'});
            if ($endDateTime < ApplicationTime::getTime()) {
                // Item is expired
                return null;
            }
        }
        return new SupportingContentItem($title, $htmlContent, $image);
    }
}
