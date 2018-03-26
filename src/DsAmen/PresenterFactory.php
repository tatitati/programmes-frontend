<?php
declare(strict_types = 1);

namespace App\DsAmen;

use App\DsAmen\Presenters\Domain\CoreEntity\CollapsedBroadcast\CollapsedBroadcastPresenter;
use App\DsAmen\Presenters\Domain\CoreEntity\Group\GroupPresenter;
use App\DsAmen\Presenters\Domain\CoreEntity\Programme\ProgrammePresenter;
use App\DsAmen\Presenters\Domain\Promotion\PromotionPresenter;
use App\DsAmen\Presenters\Domain\Recipe\RecipePresenter;
use App\DsAmen\Presenters\Domain\RelatedLink\RelatedLinkPresenter;
use App\DsAmen\Presenters\Domain\SupportingContent\SupportingContentPresenter;
use App\DsAmen\Presenters\Section\Footer\FooterPresenter;
use App\DsAmen\Presenters\Section\Map\MapPresenter;
use App\DsAmen\Presenters\Section\RelatedTopics\RelatedTopicsPresenter;
use App\DsAmen\Presenters\Utilities\Duration\DurationPresenter;
use App\DsShared\Helpers\HelperFactory;
use App\ExternalApi\Ada\Domain\AdaClass;
use App\ExternalApi\Electron\Domain\SupportingContentItem;
use App\ExternalApi\Recipes\Domain\Recipe;
use App\Translate\TranslateProvider;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Group;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use BBC\ProgrammesPagesService\Domain\Entity\RelatedLink;
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

    public function collapsedBroadcastPresenter(CollapsedBroadcast $collapsedBroadcast, array $options = []): CollapsedBroadcastPresenter
    {
        return new CollapsedBroadcastPresenter($collapsedBroadcast, $this->router, $this->translateProvider, $this->helperFactory, $options);
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

    public function relatedLinkPresenter(RelatedLink $supportingContent, array $options = []): RelatedLinkPresenter
    {
        return new RelatedLinkPresenter($supportingContent, $options);
    }

    public function supportingContentPresenter(SupportingContentItem $supportingContent, array $options = []): SupportingContentPresenter
    {
        return new SupportingContentPresenter($supportingContent, $options);
    }

    public function recipePresenter(Recipe $recipe, array $options = []): RecipePresenter
    {
        return new RecipePresenter($recipe, $options);
    }


    public function footerPresenter(Programme $programme, array $recommendations, array $options = []): FooterPresenter
    {
        return new FooterPresenter($programme, $recommendations, $options);
    }

    public function mapPresenter(
        ProgrammeContainer $programme,
        ?CollapsedBroadcast $upcomingBroadcast,
        ?CollapsedBroadcast $lastOn,
        ?Promotion $priorityPromotion,
        ?Promotion $comingSoonPromo,
        ?Episode $streamableEpisode,
        int $debutsCount,
        int $repeatsCount,
        bool $showMiniMap
    ): MapPresenter {
        return new MapPresenter(
            $this->helperFactory,
            $this->translateProvider,
            $this->router,
            $programme,
            $upcomingBroadcast,
            $lastOn,
            $priorityPromotion,
            $comingSoonPromo,
            $streamableEpisode,
            $debutsCount,
            $repeatsCount,
            $showMiniMap
        );
    }

    /**
     * @param AdaClass[] $adaClasses
     * @param array $options
     */
    public function relatedTopicsPresenter(array $adaClasses, array $options = []): RelatedTopicsPresenter
    {
        return new RelatedTopicsPresenter($adaClasses, $options);
    }
}
