<?php
declare(strict_types = 1);

namespace App\DsAmen;

use App\DsAmen\Molecule\Duration\DurationPresenter;
use App\DsAmen\Molecule\Synopsis\SynopsisPresenter;
use App\DsAmen\Organism\Footer\FooterPresenter;
use App\DsAmen\Organism\Map\MapPresenter;
use App\DsAmen\Organism\CoreEntity\CollapsedBroadcast\CollapsedBroadcastPresenter;
use App\DsAmen\Organism\CoreEntity\Group\GroupPresenter;
use App\DsAmen\Organism\CoreEntity\Programme\ProgrammePresenter;
use App\DsAmen\Organism\Promotion\PromotionPresenter;
use App\DsAmen\Organism\Recipe\RecipePresenter;
use App\DsAmen\Organism\SupportingContent\SupportingContentPresenter;
use App\DsShared\Helpers\HelperFactory;
use App\ExternalApi\Electron\Domain\SupportingContentItem;
use App\ExternalApi\Recipes\Domain\Recipe;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Group;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * DsAmen Factory Class for creating presenters.
 */
class PresenterFactory
{
    /** @var TranslateProvider */
    private $translateProvider;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var HelperFactory */
    private $helperFactory;

    public function __construct(TranslateProvider $translateProvider, UrlGeneratorInterface $router, HelperFactory $helperFactory)
    {
        $this->translateProvider = $translateProvider;
        $this->router = $router;
        $this->helperFactory = $helperFactory;
    }

    public function durationPresenter(int $duration, array $options = []): DurationPresenter
    {
        return new DurationPresenter($duration, $this->translateProvider, $options);
    }

    public function mapPresenter(
        ProgrammeContainer $programme,
        ?CollapsedBroadcast $upcomingBroadcast,
        ?CollapsedBroadcast $lastOn,
        ?Promotion $firstPromo,
        ?Promotion $comingSoonPromo,
        ?Episode $streamableEpisode,
        int $debutsCount,
        int $repeatsCount,
        bool $isPromoPriority,
        bool $showMiniMap
    ): MapPresenter {
        return new MapPresenter(
            $this->helperFactory,
            $this->translateProvider,
            $this->router,
            $programme,
            $upcomingBroadcast,
            $lastOn,
            $firstPromo,
            $comingSoonPromo,
            $streamableEpisode,
            $debutsCount,
            $repeatsCount,
            $isPromoPriority,
            $showMiniMap
        );
    }

    public function collapsedBroadcastPresenter(CollapsedBroadcast $collapsedBroadcast, array $options = []): CollapsedBroadcastPresenter
    {
        return new CollapsedBroadcastPresenter($collapsedBroadcast, $this->router, $this->translateProvider, $this->helperFactory, $options);
    }

    public function footerPresenter(Programme $programme, array $options = []): FooterPresenter
    {
        return new FooterPresenter($programme, $options);
    }

    public function groupPresenter(Group $group, array $options = []): GroupPresenter
    {
        return new GroupPresenter($group, $this->router, $this->helperFactory, $options);
    }

    public function programmePresenter(Programme $programme, array $options = []): ProgrammePresenter
    {
        return new ProgrammePresenter($programme, $this->router, $this->helperFactory, $options);
    }

    public function promotionPresenter(Promotion $promotion, array $options = []): PromotionPresenter
    {
        return new PromotionPresenter($this->router, $promotion, $options);
    }

    public function supportingContentPresenter(SupportingContentItem $supportingContent, array $options = []): SupportingContentPresenter
    {
        return new SupportingContentPresenter($supportingContent, $options);
    }

    public function synopsisPresenter(Synopses $synopses, int $maxLength): SynopsisPresenter
    {
        return new SynopsisPresenter($synopses, $maxLength);
    }

    public function recipePresenter(Recipe $recipe, array $options = []): RecipePresenter
    {
        return new RecipePresenter($recipe, $options);
    }
}
