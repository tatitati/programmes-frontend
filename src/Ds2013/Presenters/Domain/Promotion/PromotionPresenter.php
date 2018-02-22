<?php
declare(strict_types=1);

namespace App\Ds2013\Presenters\Domain\Promotion;

use App\Ds2013\Presenter;
use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Image;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use BBC\ProgrammesPagesService\Domain\Entity\RelatedLink;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PromotionPresenter extends Presenter
{
    /** @var UrlGeneratorInterface */
    private $router;

    /** @var Promotion */
    private $promotion;

    /** @var array */
    protected $options = [
        // display options
        'show_image' => true,
        'show_synopsis' => true,
        'related_links_count' => 999,

        // classes & elements
        'img_default_width' => 320,
        'img_sizes' => [],
        'highlight_box_classes' => '',
        'img_classes' => '1/4@bpb1 1/3@bpb2 1/3@bpw',
    ];

    public function __construct(
        UrlGeneratorInterface $router,
        Promotion $promotion,
        array $options = []
    ) {
        parent::__construct($options);
        $this->promotion = $promotion;
        $this->router = $router;

        if ($this->options['highlight_box_classes']) {
            $this->options['highlight_box_classes'] .= ' br-keyline br-blocklink-page br-page-linkhover-onbg015--hover';
        }
    }

    public function getTitle(): string
    {
        return $this->promotion->getTitle();
    }

    public function getUrl(): string
    {
        $promotedEntity = $this->promotion->getPromotedEntity();

        if ($promotedEntity instanceof Image) {
            return $this->promotion->getUrl();
        }

        if ($promotedEntity instanceof CoreEntity) {
            return $this->router->generate('find_by_pid', ['pid' => $promotedEntity->getPid()]);
        }

        return '';
    }

    public function getImage(): ?Image
    {
        if (!$this->options['show_image']) {
            return null;
        }

        $promotedEntity = $this->promotion->getPromotedEntity();
        if ($promotedEntity instanceof Image) {
            return $promotedEntity;
        }

        if ($promotedEntity instanceof CoreEntity) {
            return $promotedEntity->getImage();
        }

        return null;
    }

    public function getSynopsis(): string
    {
        if (!$this->options['show_synopsis']) {
            return '';
        }

        return $this->promotion->getShortSynopsis();
    }

    /**
     * @return RelatedLink[]
     */
    public function getRelatedLinks(): array
    {
        return array_slice($this->promotion->getRelatedLinks(), 0, $this->options['related_links_count']);
    }

    protected function validateOptions(array $options): void
    {
        if (!is_bool($options['show_image'])) {
            throw new InvalidOptionException('show_image option must be a boolean');
        }

        if (!is_bool($options['show_synopsis'])) {
            throw new InvalidOptionException('show_synopsis option must be a boolean');
        }

        if (!is_int($options['related_links_count']) || $options['related_links_count'] < 0) {
            throw new InvalidOptionException('related_links_count option must 0 or a positive integer');
        }
    }
}
