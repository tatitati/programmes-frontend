<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Programme;

use App\DsAmen\Organism\Programme\SubPresenters\ProgrammeBodyPresenter;
use App\DsAmen\Organism\Programme\SubPresenters\ProgrammeCtaPresenter;
use App\DsAmen\Organism\Programme\SubPresenters\ProgrammeImagePresenter;
use App\DsAmen\Organism\Programme\SubPresenters\ProgrammeTitlePresenter;
use App\DsAmen\Presenter;
use App\DsShared\Helpers\HelperFactory;
use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProgrammePresenter extends Presenter
{
    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var HelperFactory */
    protected $helperFactory;

    /** @var Programme */
    protected $programme;

    /** @var ProgrammeCtaPresenter|null */
    protected $ctaPresenter;

    protected $options = [
        'branding_name' => 'subtle',
        'context_programme' => null,
        'media_variant' => 'media--column media--card',
        'media_details_class' => 'media__details',
        'show_image' => true,
        'force_iplayer_linking' => false, // force linking the whole programme object to iPlayer instead of just the CTA button

        // Subpresenter options
        'body_options' => [],
        'cta_options' => [],
        'image_options' => [],
        'title_options' => [],
    ];

    /**
     * Define the set of options that are needed by all sub-presenters too
     *
     * @var array
     */
    protected $sharedOptions = [
        'context_programme',
        'show_image',
        'branding_name',
        'force_iplayer_linking',
    ];

    public function __construct(
        UrlGeneratorInterface $router,
        HelperFactory $helperFactory,
        Programme $programme,
        array $options = []
    ) {

        parent::__construct($options);
        $this->router = $router;
        $this->helperFactory = $helperFactory;
        $this->programme = $programme;
        $this->ctaPresenter = $this->buildProgrammeCtaPresenter();
    }

    public function getBrandingClass(): string
    {
        if (!$this->getOption('branding_name')) {
            return '';
        }

        return 'br-box-' . $this->getOption('branding_name');
    }

    public function getMediaDetailsClass(): ?string
    {
        if (!$this->getOption('media_details_class')) {
            return '';
        }

        if (!$this->getOption('show_image')) {
            return $this->getOption('media_details_class') . ' media__details--noimage';
        }

        return $this->getOption('media_details_class');
    }

    public function getProgrammeBodyPresenter(): ProgrammeBodyPresenter
    {
        return new ProgrammeBodyPresenter(
            $this->programme,
            $this->subPresenterOptions('body_options')
        );
    }

    public function getProgrammeImagePresenter(): ProgrammeImagePresenter
    {
        return new ProgrammeImagePresenter(
            $this->programme,
            $this->getProgrammeCtaPresenter(),
            $this->subPresenterOptions('image_options')
        );
    }

    public function getProgrammeTitlePresenter(): ProgrammeTitlePresenter
    {
        return new ProgrammeTitlePresenter(
            $this->programme,
            $this->router,
            $this->helperFactory->getTitleLogicHelper(),
            $this->subPresenterOptions('title_options')
        );
    }

    public function getProgrammeCtaPresenter(): ?ProgrammeCtaPresenter
    {
        return $this->ctaPresenter;
    }

    public function showStandaloneCta(): bool
    {
        return !$this->getOption('show_image') && $this->getProgrammeCtaPresenter();
    }

    protected function buildProgrammeCtaPresenter(): ?ProgrammeCtaPresenter
    {
        $ctaPresenter = null;
        if ($this->programme instanceof ProgrammeItem && $this->programme->isStreamable()) {
            $ctaPresenter = new ProgrammeCtaPresenter(
                $this->programme,
                $this->router,
                $this->subPresenterOptions('cta_options')
            );
        }

        return $ctaPresenter;
    }

    protected function validateOptions(array $options): void
    {
        if (isset($options['context_programme']) && !($options['context_programme'] instanceof Programme)) {
            throw new InvalidOptionException('context_programme option must be null or a Programme domain object');
        }

        if (!is_bool($options['show_image'])) {
            throw new InvalidOptionException('show_image option must be a boolean');
        }

        if (!is_array($options['image_options']) || !is_array($options['title_options']) || !is_array($options['body_options'])) {
            throw new InvalidOptionException('Subpresenter options must be passed in arrays');
        }
    }

    /**
     * Sub-presenters require some of the same options as their parents, which options is defined
     * in $this->sharedOptions
     */
    protected function subPresenterOptions(string $optionsKey)
    {
        $options = $this->options[$optionsKey];

        foreach ($this->sharedOptions as $sharedOptionKey) {
            $options[$sharedOptionKey] = $this->options[$sharedOptionKey];
        }
        return $options;
    }
}
