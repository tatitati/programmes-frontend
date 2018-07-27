<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Mapper;

use App\Controller\Helpers\IsiteKeyHelper;
use App\ExternalApi\Exception\ParseException;
use App\ExternalApi\Isite\Domain\Profile;
use SimpleXMLElement;

class ProfileMapper
{
    /** @var IsiteKeyHelper */
    private $isiteKeyHelper;

    public function __construct(IsiteKeyHelper $isiteKeyHelper)
    {
        $this->isiteKeyHelper = $isiteKeyHelper;
    }

    public function getDomainModel(SimpleXMLElement $isiteObject): Profile
    {
        $formMetaData = $this->getFormMetaData($isiteObject);
        // @codingStandardsIgnoreStart
        // Ignored PHPCS cause of snake variable fields included in the xml
        $longSynopsis = $this->getString($formMetaData->long_synopsis);
        $parentPid = $this->getString($formMetaData->parent_pid);
        $brandingId = $this->getString($formMetaData->branding_id);
        // @codingStandardsIgnoreEnd
        $projectSpace = $this->getProjectSpace($formMetaData);
        $key = $this->isiteKeyHelper->convertGuidToKey($this->getString($this->getMetaData($isiteObject)->guid));
        $title = $this->getString($formMetaData->title);
        $type = $this->getString($formMetaData->type);
        $fileId = $this->getString($this->getMetaData($isiteObject)->fileId); // NOTE: This is the metadata fileId, not the form data file_id

        return new Profile($title, $key, $fileId, $type, $projectSpace, $parentPid, $longSynopsis, $brandingId);
    }

    protected function getForm(SimpleXMLElement $isiteObject): SimpleXMLElement
    {
        if (empty($isiteObject->document->form)) {
            throw new ParseException("Invalid iSite XML document");
        }
        return $isiteObject->document->form;
    }

    /**
     * Gets the metadata directly from the header
     * @param  SimpleXMLElement $isiteObject
     * @return SimpleXMLElement
     */
    protected function getMetaData(SimpleXMLElement $isiteObject): SimpleXMLElement
    {
        return $isiteObject->metadata;
    }

    /**
     * Method to get the metadata form isite document
     * @param  SimpleXMLElement $isiteObject
     * @return SimpleXMLElement
     */
    protected function getFormMetaData(SimpleXMLElement $isiteObject): ?SimpleXMLElement
    {
        return $this->getForm($isiteObject)->metadata;
    }

    protected function getProjectSpace(SimpleXMLElement $form): string
    {
        $namespaces = $form->getNamespaces();
        $namespace = reset($namespaces);
        $matches = [];
        preg_match('{https://production(?:\.int|\.test|\.stage|\.live)?\.bbc\.co\.uk/isite2/project/([^/]+)/}', $namespace, $matches);
        if (empty($matches[1])) {
            throw new ParseException("iSite XML does not specify project space and is therefore invalid");
        }
        return $matches[1];
    }

    protected function getString(?SimpleXMLElement $val): ?string
    {
        if (empty($val)) {
            return null;
        }
        $val = (string) $val;
        return trim($val);
    }
}
