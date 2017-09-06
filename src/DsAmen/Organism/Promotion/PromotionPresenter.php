<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Promotion;

use App\DsAmen\Presenter;
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

    /** @var string[] */
    private $actionIcon = [];

    /** @var int */
    private $duration = 0;

    /** @var array */
    protected $options = [
        // display options
        'show_image' => true,
        'show_synopsis' => true,
        'show_related_links' => true,

        // classes & elements
        'h_tag' => 'h4',
        'title_size' => 'gel-pica-bold',
        'img_sizes' => [],
        'media_variant' => 'media--column media--card',
        'cta_class' => 'icon-cta--dark',
        'media_panel_class' => '1/1',
        'branding_name' => 'subtle',
        'link_location_prefix' => 'promotionobject_',
    ];

    public function __construct(
        UrlGeneratorInterface $router,
        Promotion $promotion,
        array $options = []
    ) {
        parent::__construct($options);
        $this->router = $router;
        $this->promotion = $promotion;

        // Build ActionIcon
        $promotedEntity = $this->promotion->getPromotedEntity();
        if ($promotedEntity instanceof ProgrammeItem && $promotedEntity->isStreamable()) {
            $this->actionIcon = ['set' => 'audio-visual', 'icon' => 'play'];
        } elseif ($this->isExternalLink($this->getUrl())) {
            $this->actionIcon = ['set' => 'basics', 'icon' => 'external'];
        }

        // Build Duration - only set for Clips and not-TV Episodes
        if ($promotedEntity instanceof ProgrammeItem &&
            !($promotedEntity instanceof Episode && $promotedEntity->isTv())
        ) {
            $this->duration = $promotedEntity->getDuration();
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
        if (!$this->options['show_related_links']) {
            return [];
        }

        return $this->promotion->getRelatedLinks();
    }

    /**
     * @return string[]
     */
    public function getActionIcon(): array
    {
        return $this->actionIcon;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function getBrandingBoxClass(): string
    {
        if (empty($this->options['branding_name'])) {
            return '';
        }

        return 'br-box-' . $this->options['branding_name'];
    }

    public function getTextBrandingClass(): string
    {
        if (empty($this->options['branding_name'])) {
            return '';
        }

        return 'br-' . $this->options['branding_name'] . '-text-ontext';
    }

    public function isExternalLink($url): bool
    {
        return !!preg_match('~^(https?:)?//(?![^/]*bbc\.co(m|\.uk))~', $url);
    }

    protected function validateOptions(array $options): void
    {
        if (!is_bool($options['show_image'])) {
            throw new InvalidOptionException('show_image option must be a boolean');
        }

        if (!is_bool($options['show_synopsis'])) {
            throw new InvalidOptionException('show_synopsis option must be a boolean');
        }

        if (!is_bool($options['show_related_links'])) {
            throw new InvalidOptionException('show_related_links option must be a boolean');
        }
    }
}
