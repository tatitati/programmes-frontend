<?php
declare(strict_types = 1);

namespace App\DsAmen\Presenters\Domain\CoreEntity\Base;

use App\DsAmen\Presenters\Domain\CoreEntity\Base\SubPresenter\BaseBodyPresenter;
use App\DsAmen\Presenters\Domain\CoreEntity\Base\SubPresenter\BaseCtaPresenter;
use App\DsAmen\Presenters\Domain\CoreEntity\Base\SubPresenter\BaseImagePresenter;
use App\DsAmen\Presenters\Domain\CoreEntity\Base\SubPresenter\BaseTitlePresenter;
use App\DsAmen\Presenter;
use App\DsShared\Helpers\HelperFactory;
use App\Exception\InvalidOptionException;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class BaseCoreEntityPresenter extends Presenter
{
    /** @var UrlGeneratorInterface */
    protected $router;

    /** @var HelperFactory */
    protected $helperFactory;

    /** @var CoreEntity */
    protected $coreEntity;

    /** @var BaseCtaPresenter|null */
    protected $ctaPresenter;

    protected $options = [
        'branding_name' => 'subtle',
        'context_programme' => null,
        'media_variant' => 'media--column media--card',
        'media_details_class' => 'media__details',
        'show_image' => true,
        'force_iplayer_linking' => false,
        'link_location_prefix' => 'programmeobject_',

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
        'link_location_prefix',
    ];

    public function __construct(
        CoreEntity $coreEntity,
        UrlGeneratorInterface $router,
        HelperFactory $helperFactory,
        array $options = []
    ) {

        parent::__construct($options);
        $this->router = $router;
        $this->helperFactory = $helperFactory;
        $this->coreEntity = $coreEntity;
    }

    public function getBrandingClass(): string
    {
        if (!$this->getOption('branding_name')) {
            return '';
        }

        return 'br-box-' . $this->getOption('branding_name');
    }

    abstract public function getBodyPresenter(array $options = []): BaseBodyPresenter;

    abstract public function getCtaPresenter(array $options = []): ?BaseCtaPresenter;

    abstract public function getImagePresenter(array $options = []): BaseImagePresenter;

    abstract public function getTitlePresenter(array $options = []): BaseTitlePresenter;

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
            if (array_key_exists($sharedOptionKey, $this->options)) {
                $options[$sharedOptionKey] = $this->options[$sharedOptionKey];
            }
        }

        return $options;
    }

    protected function mergeWithSubPresenterOptions(array $options, string $key): array
    {
        if (array_key_exists('cta_options', $options)) {
            array_merge($this->subPresenterOptions('cta_options'), $options['cta_options']);
        }

        return $this->subPresenterOptions('cta_options');
    }

    protected function isStreamable(): bool
    {
        return ($this->coreEntity instanceof ProgrammeItem && $this->coreEntity->isStreamable());
    }
}
