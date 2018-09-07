<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Mapper;

use App\Controller\Helpers\IsiteKeyHelper;
use App\ExternalApi\Isite\Domain\ContentBlock\AbstractContentBlock;
use App\ExternalApi\Isite\Domain\ContentBlock\Faq;
use App\ExternalApi\Isite\Domain\ContentBlock\Galleries;
use App\ExternalApi\Isite\Domain\ContentBlock\Image;
use App\ExternalApi\Isite\Domain\ContentBlock\Links;
use App\ExternalApi\Isite\Domain\ContentBlock\Table;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\CoreEntitiesService;
use Exception;
use SimpleXMLElement;

class ContentBlockMapper extends Mapper
{
    /** @var CoreEntitiesService */
    private $coreEntitiesService;

    public function __construct(MapperFactory $mapperFactory, IsiteKeyHelper $isiteKeyHelper, CoreEntitiesService $coreEntitiesService)
    {
        parent::__construct($mapperFactory, $isiteKeyHelper);
        $this->coreEntitiesService = $coreEntitiesService;
    }

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
            case 'faq':
                $contentBlockData = $form->content;
                $questions = [];
                foreach ($contentBlockData->questions as $q) {
                    $questions[] = [
                        'question' => $this->getString($q->question),
                        'answer' => $this->getString($q->answer),
                    ];
                }
                $contentBlock = new Faq(
                    $this->getString($contentBlockData->title),
                    // @codingStandardsIgnoreStart
                    $this->getString($contentBlockData->intro_paragraph),
                    // @codingStandardsIgnoreEnd
                    $questions
                );
                break;
            case 'galleries':
                $contentBlockData = $form->content;
                $galleryPids = [];
                foreach ($contentBlockData->galleries as $gallery) {
                    $galleryPids[] = new Pid($this->getString($gallery->pid));
                }
                $galleries = $this->coreEntitiesService->findByPids($galleryPids, 'Gallery');
                $contentBlock = new Galleries(
                    $this->getString($contentBlockData->title),
                    $galleries
                );
                break;
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
            case 'table':
                $contentBlockData = $form->content;
                // @codingStandardsIgnoreStart
                $oneEmpty = empty($this->getString($contentBlockData->heading_1));
                $twoEmpty = empty($this->getString($contentBlockData->heading_2));
                $threeEmpty = empty($this->getString($contentBlockData->heading_3));

                foreach($contentBlockData->row as $r) {
                    if (!empty($this->getString($r->column_1))) {
                        $oneEmpty = false;
                    }
                    if (!empty($this->getString($r->column_2))) {
                        $twoEmpty = false;
                    }
                    if (!empty($this->getString($r->column_3))) {
                        $threeEmpty = false;
                    }
                }

                $rows = [];

                foreach($contentBlockData->row as $r) {
                    $row = [];
                    if (!$oneEmpty) {
                        $row[] = $this->getString($r->column_1);
                    }
                    if (!$twoEmpty) {
                        $row[] = $this->getString($r->column_2);
                    }
                    if (!$threeEmpty) {
                        $row[] = $this->getString($r->column_3);
                    }
                    $rows[] = $row;
                }

                $headings = [];
                if (!$oneEmpty) {
                    $headings[] = $this->getString($contentBlockData->heading_1);
                }
                if (!$twoEmpty) {
                    $headings[] = $this->getString($contentBlockData->heading_2);
                }
                if (!$threeEmpty) {
                    $headings[] = $this->getString($contentBlockData->heading_3);
                }
                // @codingStandardsIgnoreEnd

                $contentBlock = new Table(
                    $this->getString($contentBlockData->title),
                    $headings,
                    $rows
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
