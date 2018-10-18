<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Mapper;

use App\Controller\Helpers\IsiteKeyHelper;
use App\ExternalApi\IdtQuiz\IdtQuizService;
use App\ExternalApi\Isite\Domain\ContentBlock\AbstractContentBlock;
use App\ExternalApi\Isite\Domain\ContentBlock\Faq;
use App\ExternalApi\Isite\Domain\ContentBlock\Galleries;
use App\ExternalApi\Isite\Domain\ContentBlock\Image;
use App\ExternalApi\Isite\Domain\ContentBlock\Links;
use App\ExternalApi\Isite\Domain\ContentBlock\Promotions;
use App\ExternalApi\Isite\Domain\ContentBlock\Quiz;
use App\ExternalApi\Isite\Domain\ContentBlock\Prose;
use App\ExternalApi\Isite\Domain\ContentBlock\Table;
use App\ExternalApi\Isite\Domain\ContentBlock\Telescope;
use App\ExternalApi\Isite\Domain\ContentBlock\ThirdParty;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use App\ExternalApi\Isite\Domain\ContentBlock\Touchcast;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\CoreEntitiesService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use BBC\ProgrammesPagesService\Service\VersionsService;
use App\Exception\HasContactFormException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;

class ContentBlockMapper extends Mapper
{
    /** @var CoreEntitiesService */
    private $coreEntitiesService;

    /** @var IdtQuizService */
    private $idtQuizService;

    /** @var ProgrammesService */
    private $programmesService;

    /**  @var VersionsService  */
    private $versionsService;

    /** @var LoggerInterface */
    private $logger;

    /**
     * All available clip will be set here at once so we don't need to query the DB or Redis multiple times
     *
     * var Clip[]
     */
    private $clips = [];

    /** @var Version[] */
    private $streamableVersions = [];

    public function __construct(
        MapperFactory $mapperFactory,
        IsiteKeyHelper $isiteKeyHelper,
        CoreEntitiesService $coreEntitiesService,
        IdtQuizService $idtQuizService,
        ProgrammesService $programmesService,
        VersionsService $versionsService,
        LoggerInterface $logger
    ) {
        parent::__construct($mapperFactory, $isiteKeyHelper);
        $this->coreEntitiesService = $coreEntitiesService;
        $this->idtQuizService = $idtQuizService;
        $this->programmesService = $programmesService;
        $this->versionsService = $versionsService;
        $this->logger = $logger;
    }

    /**
     * @param array $contentBlocksList
     * @return AbstractContentBlock[]
     */
    public function getDomainModels(array $contentBlocksList): array
    {
        $clipsPid = [];
        // get clips queries
        foreach ($contentBlocksList as $block) {
            $type = $this->getType($block->result);
            $form = $this->getForm($block->result);
            if ('prose' === $type) {
                $clipPidString = $this->getString($form->content->clip);
                if (!empty($clipPidString)) {
                    try {
                        $clipsPid[] = new Pid($clipPidString);
                    } catch (InvalidArgumentException $e) {
                        $this->logger->error('Invalid clip PID: "' . $clipPidString . '" from iSite prose');
                    }
                }
            }
            if ('clips' === $type) {
                foreach ($form->content->clips as $isiteClip) {
                    $clipPidString = $this->getString($isiteClip->pid);
                    if (!empty($clipPidString)) {
                        try {
                            $clipsPid[] = new Pid($clipPidString);
                        } catch (InvalidArgumentException $e) {
                            $this->logger->error('Invalid clip PID: "' . $clipPidString . '" from iSite clips');
                        }
                    }
                }
            }
        }
        $this->clips = $this->coreEntitiesService->findByPids($clipsPid, 'Clip');
        $this->streamableVersions = $this->versionsService->findStreamableVersionForProgrammeItems($this->clips);

        $contentBlock = [];
        foreach ($contentBlocksList as $block) {
            $contentBlock[] = $this->getDomainModel($block->result);
        }
        return $contentBlock;
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
            case 'promotions':
                $contentBlockData = $form->content;
                $title = $this->getString($contentBlockData->title);
                $layout = $this->getString($contentBlockData->layout);
                $promotions = [];
                foreach ($contentBlockData->promotions as $promotion) {
                    // @codingStandardsIgnoreStart
                    $promotions[] = [
                        'promotionTitle' => $this->getString($promotion->promotion_title),
                        'url' => $this->getString($promotion->url),
                        'promotedItemId' => $this->getString($promotion->promoted_item_id),
                        'shortSynopsis' => $this->getString($promotion->short_synopsis),
                    ];
                    // @codingStandardsIgnoreEnd
                }
                $contentBlock = new Promotions($promotions, $layout, $title);
                break;
            case 'prose':
                $contentBlockData = $form->content;
                $clipPid = $this->getString($contentBlockData->clip);
                $clip = (isset($this->clips[$clipPid])) ? $this->clips[$clipPid] : null;
                $streamableVersions = (isset($this->streamableVersions[$clipPid])) ? $this->streamableVersions[$clipPid] : null;
                $contentBlock = new Prose(
                    $this->getString($contentBlockData->title),
                    $this->getString($contentBlockData->prose),
                    $this->getString($contentBlockData->image),
                    // @codingStandardsIgnoreStart
                    $this->getString($contentBlockData->image_caption),
                    $this->getString($contentBlockData->quote),
                    $this->getString($contentBlockData->quote_attribution),
                    $clip,
                    $this->getString($contentBlockData->media_position),
                    // @codingStandardsIgnoreEnd
                    $streamableVersions
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
            case 'idt-quiz':
                $quizId = $this->getString($form->content->idt_id);
                $htmlContent = $this->idtQuizService->getQuizContentPromise($quizId)->wait();

                $contentBlock = new Quiz(
                    $this->getString($form->content->title),
                    $this->getString($form->metadata->name),
                    $quizId,
                    $htmlContent
                );

                break;
            case 'telescope-vote':
                $contentBlockData = $form->content;

                // @codingStandardsIgnoreStart
                $contentBlock = new Telescope(
                    $this->getString($contentBlockData->title),
                    $this->getString($contentBlockData->vote_id),
                    $this->getString($form->metadata->name)
                );
                // @codingStandardsIgnoreEnd
                break;
            case 'thirdparty':
                $contentBlockData = $form->content;

                // @codingStandardsIgnoreStart
                $contentBlock = new ThirdParty(
                    $this->getString($contentBlockData->title),
                    $this->getString($contentBlockData->url),
                    $this->getString($contentBlockData->alt_text),
                    $this->getString($form->metadata->name)
                );
                // @codingStandardsIgnoreEnd
                break;
            case 'touchcast':
                $contentBlockData = $form->content;

                // @codingStandardsIgnoreStart
                $contentBlock = new Touchcast(
                    $this->getString($contentBlockData->title),
                    $this->getString($contentBlockData->touchcast_id)
                );
                // @codingStandardsIgnoreEnd
                break;
            case 'contactform':
                throw new HasContactFormException('Contact form found');
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
            return str_replace(
                ['programmes-content-', 'programmes-'],
                '',
                $typeWithPrefix
            );
        }

        return null;
    }
}
