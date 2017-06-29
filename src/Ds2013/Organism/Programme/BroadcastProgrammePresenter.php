<?php
declare(strict_types = 1);
namespace App\Ds2013\Organism\Programme;

use App\Ds2013\Helpers\HelperFactory;
use App\Ds2013\InvalidOptionException;
use App\Ds2013\Organism\Programme\BroadcastSubPresenters\BroadcastProgrammeBodyPresenter;
use App\Ds2013\Organism\Programme\BroadcastSubPresenters\BroadcastProgrammeTitlePresenter;
use App\Ds2013\Organism\Programme\BroadcastSubPresenters\BroadcastProgrammeImagePresenter;
use App\Ds2013\Organism\Programme\SubPresenters\ProgrammeBodyPresenter;
use App\Ds2013\Organism\Programme\SubPresenters\ProgrammeImagePresenter;
use App\Ds2013\Organism\Programme\SubPresenters\ProgrammeTitlePresenter;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * This is an override of the programme presenter that handles
 * cases where we wish to display broadcast (watch live/services)
 * information alongside a programme.
 */
class BroadcastProgrammePresenter extends ProgrammePresenter
{
    /** @var CollapsedBroadcast */
    private $collapsedBroadcast;

    private $additionalOptions = [
        'advanced_live' => false,
        'context_service' => null,
    ];

    /**
     * Define options needed by all broadcast sub-presenters too
     *
     * @var array
     */
    private $additionalSharedOptions = [
        'advanced_live',
        'context_service',
    ];

    public function __construct(
        UrlGeneratorInterface $router,
        HelperFactory $helperFactory,
        CollapsedBroadcast $collapsedBroadcast,
        Programme $programme,
        array $options = []
    ) {
        if (isset($options['context_service']) && !($options['context_service'] instanceof Service)) {
            throw new InvalidOptionException("context_service option must be null or a Service domain object");
        }
        $options = array_merge($this->additionalOptions, $options);
        $this->sharedOptions = array_merge($this->sharedOptions, $this->additionalSharedOptions);
        parent::__construct($router, $helperFactory, $programme, $options);
        $this->collapsedBroadcast = $collapsedBroadcast;
    }

    public function getProgrammeImagePresenter(): ProgrammeImagePresenter
    {
        return new BroadcastProgrammeImagePresenter(
            $this->router,
            $this->helperFactory->getPlayTranslationsHelper(),
            $this->helperFactory->getLiveBroadcastHelper(),
            $this->collapsedBroadcast,
            $this->programme,
            $this->subPresenterOptions('image_options')
        );
    }

    public function getProgrammeBodyPresenter(): ProgrammeBodyPresenter
    {
        return new BroadcastProgrammeBodyPresenter(
            $this->router,
            $this->helperFactory->getPlayTranslationsHelper(),
            $this->helperFactory->getLiveBroadcastHelper(),
            $this->collapsedBroadcast,
            $this->programme,
            $this->subPresenterOptions('body_options')
        );
    }

    public function getTemplatePath(): string
    {
        return '@Ds2013/Organism/Programme/programme.html.twig';
    }

    public function getTemplateVariableName(): string
    {
        return 'programme';
    }
}
