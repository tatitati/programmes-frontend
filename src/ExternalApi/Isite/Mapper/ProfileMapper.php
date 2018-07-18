<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Mapper;

use App\Controller\Helpers\IsiteKeyHelper;
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

        $key = $this->isiteKeyHelper->convertGuidToKey($this->getString($this->getMetaData($isiteObject)->guid));
        $title = $this->getString($formMetaData->title);
        $type = $this->getString($formMetaData->type);
        $fileId = $this->getString($this->getMetaData($isiteObject)->fileId); // NOTE: This is the metadata fileId, not the form data file_id

        return new Profile($title, $key, $fileId, $type);
    }

    protected function getForm(SimpleXMLElement $isiteObject): SimpleXMLElement
    {
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

    protected function getString(?SimpleXMLElement $val): ?string
    {
        if (empty($val)) {
            return null;
        }
        $val = (string) $val;
        return trim($val);
    }
}
