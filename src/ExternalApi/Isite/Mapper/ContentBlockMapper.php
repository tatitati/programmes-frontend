<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Mapper;

use App\ExternalApi\Isite\Domain\ContentBlock\AbstractContentBlock;
use App\ExternalApi\Isite\Domain\ContentBlock\Image;
use App\ExternalApi\Isite\Domain\ContentBlock\Links;
use Exception;
use SimpleXMLElement;

class ContentBlockMapper extends Mapper
{
    /**
     * public function getDomainModel(SimpleXMLElement $isiteObject): AbstractContentBlock
     */
    public function getDomainModel(SimpleXMLElement $isiteObject)
    {
        $type = $this->getType($isiteObject);
        if (!$type) {
            return null;
        }

        $form = $this->getForm($isiteObject);

        $contentBlock = null;

        switch ($type) {
            case 'image':
                $contentBlockData = $form->content;
                $contentBlock = new Image(
                    $this->getString($contentBlockData->image),
                    $this->getString($contentBlockData->title),
                    // @codingStandardsIgnoreStart
                    $this->getString($contentBlockData->image_caption)
                    // @codingStandardsIgnoreEnd
                );
                break;
            case 'links':
                $contentBlockData = $form->content;
                $links = [];
                foreach ($contentBlockData->links as $link) {
                    // @codingStandardsIgnoreStart
                    $links[$this->getString($link->link_title)] = $this->getString($link->url);
                    // @codingStandardsIgnoreEnd
                }
                $contentBlock = new Links(
                    $this->getString($contentBlockData->title),
                    $links
                );
                break;
            default:
//                throw new Exception('Invalid content block type. Found ' . $type);
                break;
        }

        return $contentBlock;
    }

    private function getType(SimpleXMLElement $isiteObject): ?string
    {
        $typeWithPrefix = $this->getMetaData($isiteObject)->type;
        if ($typeWithPrefix !== null) {
            return str_replace('programmes-content-', '', $typeWithPrefix);
        }

        return null;
    }
}
