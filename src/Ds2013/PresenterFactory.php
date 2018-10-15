<?php
declare(strict_types = 1);
namespace App\Ds2013;

use App\Ds2013\Presenters\Domain\Broadcast\BroadcastPresenter;
use App\Ds2013\Presenters\Domain\BroadcastEvent\BroadcastEventPresenter;
use App\Ds2013\Presenters\Domain\ContentBlock\Faq\FaqPresenter;
use App\Ds2013\Presenters\Domain\ContentBlock\Galleries\GalleriesPresenter;
use App\Ds2013\Presenters\Domain\ContentBlock\Image\ImagePresenter;
use App\Ds2013\Presenters\Domain\ContentBlock\Links\LinksPresenter;
use App\Ds2013\Presenters\Domain\ContentBlock\Promotions\PromotionsPresenter;
use App\Ds2013\Presenters\Domain\ContentBlock\Table\TablePresenter;
use App\Ds2013\Presenters\Domain\CoreEntity\Group\GroupPresenter;
use App\Ds2013\Presenters\Domain\CoreEntity\Programme\BroadcastProgrammePresenter;
use App\Ds2013\Presenters\Domain\CoreEntity\Programme\CollapsedBroadcastProgrammePresenter;
use App\Ds2013\Presenters\Domain\CoreEntity\Programme\ProgrammePresenter;
use App\Ds2013\Presenters\Domain\Profile\ProfilePresenter;
use App\Ds2013\Presenters\Domain\Promotion\PromotionPresenter;
use App\Ds2013\Presenters\Domain\Recipe\RecipePresenter;
use App\Ds2013\Presenters\Domain\Superpromo\SuperpromoPresenter;
use App\Ds2013\Presenters\Pages\EpisodeGuideList\EpisodeGuideListPresenter;
use App\Ds2013\Presenters\Pages\Schedules\NoSchedule\NoSchedulePresenter;
use App\Ds2013\Presenters\Section\Clip\Details\ClipDetailsPresenter;
use App\Ds2013\Presenters\Section\Clip\Playout\ClipPlayoutPresenter;
use App\Ds2013\Presenters\Section\Episode\Map\EpisodeMapPresenter;
use App\Ds2013\Presenters\Section\EpisodesSubNav\EpisodesSubNavPresenter;
use App\Ds2013\Presenters\Section\Footer\FooterPresenter;
use App\Ds2013\Presenters\Section\RelatedTopics\RelatedTopicsPresenter;
use App\Ds2013\Presenters\Section\Segments\SegmentsListPresenter;
use App\Ds2013\Presenters\Section\SupportingContent\SupportingContentPresenter;
use App\Ds2013\Presenters\Utilities\Calendar\CalendarPresenter;
use App\Ds2013\Presenters\Utilities\Credits\CreditsPresenter;
use App\Ds2013\Presenters\Utilities\Cta\CtaPresenter;
use App\Ds2013\Presenters\Utilities\DateList\DateListPresenter;
use App\Ds2013\Presenters\Utilities\Download\DownloadPresenter;
use App\Ds2013\Presenters\Utilities\SMP\SmpPresenter;
use App\DsShared\Helpers\HelperFactory;
use App\ExternalApi\Electron\Domain\SupportingContentItem;
use App\ExternalApi\Isite\Domain\ContentBlock\AbstractContentBlock;
use App\ExternalApi\Isite\Domain\ContentBlock\Faq;
use App\ExternalApi\Isite\Domain\ContentBlock\Galleries;
use App\ExternalApi\Isite\Domain\ContentBlock\Image;
use App\ExternalApi\Isite\Domain\ContentBlock\Links;
use App\ExternalApi\Isite\Domain\ContentBlock\Promotions;
use App\ExternalApi\Isite\Domain\ContentBlock\Table;
use App\ExternalApi\Isite\Domain\Profile;
use App\ExternalApi\Recipes\Domain\Recipe;
use App\Translate\TranslateProvider;
use App\ValueObject\CosmosInfo;
use BBC\ProgrammesPagesService\Domain\Entity\Broadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Clip;
use BBC\ProgrammesPagesService\Domain\Entity\CollapsedBroadcast;
use BBC\ProgrammesPagesService\Domain\Entity\Contribution;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\Group;
use BBC\ProgrammesPagesService\Domain\Entity\Podcast;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeItem;
use BBC\ProgrammesPagesService\Domain\Entity\Promotion;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\Entity\Version;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Cake\Chronos\ChronosInterface;
use Cake\Chronos\Date;
use InvalidArgumentException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Ds2013 Factory Class for creating presenters.
 *
 * This abstraction shall allow us to have a single entry point to create any
 * Presenter. This is particularly valuable in two cases:
 * 1) When presenters require Translate, we have a single point to inject it
 * 2) When we have multiple Domain objects that should all be rendered using the
 *    same template. This factory allows us to choose the correct presenter for
 *    a given domain object.
 *
 * This class has create methods for all molecules, organisms and templates
 * which have presenters.
 * Each respective group MUST have the methods kept in alphabetical order
 *
 * To instantiate Ds2013 you MUST pass it an instance of TranslateProvider
 * All presenters MUST be created using this factory.
 * All presenters MUST call the base Presenter __construct method
 *
 */
class PresenterFactory
{
    /** @var TranslateProvider */
    private $translateProvider;

    /** @var UrlGeneratorInterface */
    private $router;

    /** @var HelperFactory */
    private $helperFactory;

    /** @var CosmosInfo */
    private $cosmosInfo;

    public function __construct(
        TranslateProvider $translateProvider,
        UrlGeneratorInterface $router,
        HelperFactory $helperFactory,
        CosmosInfo $cosmosInfo
    ) {
        $this->translateProvider = $translateProvider;
        $this->router = $router;
        $this->helperFactory = $helperFactory;
        $this->cosmosInfo = $cosmosInfo;
    }

    /**
     * Molecules
     */
    public function calendarPresenter(
        Date $date,
        Service $service,
        array $options = []
    ): CalendarPresenter {
        return new CalendarPresenter(
            $date,
            $service,
            $options
        );
    }

    public function clipPlayoutPresenter(
        Clip $clip,
        ?Version $streamableVersion,
        array $segmentEvents,
        string $analyticsCounterName,
        array $istatsAnalyticsLabels,
        array $options = []
    ) : ClipPlayoutPresenter {
        return new ClipPlayoutPresenter(
            $this,
            $this->helperFactory->getStreamUrlHelper(),
            $clip,
            $streamableVersion,
            $segmentEvents,
            $analyticsCounterName,
            $istatsAnalyticsLabels,
            $options
        );
    }

    /**
     * @param Contribution[] $contributions
     * @param mixed[] $options
     * @return CreditsPresenter
     */
    public function creditsPresenter(
        array $contributions,
        array $options = []
    ): CreditsPresenter {
        return new CreditsPresenter(
            $contributions,
            $options
        );
    }

    public function ctaPresenter(
        CoreEntity $coreEntity,
        array $options = []
    ): CtaPresenter {
        return new CtaPresenter(
            $coreEntity,
            $this->helperFactory->getPlayTranslationsHelper(),
            $this->router,
            $this->helperFactory->getStreamUrlHelper(),
            $options
        );
    }

    public function dateListPresenter(
        ChronosInterface $datetime,
        Service $service,
        array $options = []
    ): DateListPresenter {
        return new DateListPresenter(
            $this->router,
            $datetime,
            $service,
            $options
        );
    }

    public function noSchedulePresenter(
        Service $service,
        ChronosInterface $start,
        ChronosInterface $end,
        array $options = []
    ): NoSchedulePresenter {
        return new NoSchedulePresenter(
            $service,
            $start,
            $end,
            $options
        );
    }

    public function episodeGuideListPresenter(
        ProgrammeContainer $contextProgramme,
        array $programmes,
        array $upcomingBroadcasts,
        int $nestedLevel
    ): EpisodeGuideListPresenter {
        return new EpisodeGuideListPresenter(
            $contextProgramme,
            $programmes,
            $upcomingBroadcasts,
            $nestedLevel
        );
    }

    /**
     * Organisms
     */
    public function broadcastEventPresenter(
        CollapsedBroadcast $collapsedBroadcast,
        array $options = []
    ): BroadcastEventPresenter {
        return new BroadcastEventPresenter(
            $collapsedBroadcast,
            $this->helperFactory->getBroadcastNetworksHelper(),
            $this->helperFactory->getLocalisedDaysAndMonthsHelper(),
            $this->helperFactory->getLiveBroadcastHelper(),
            $this->router,
            $options
        );
    }

    public function contentBlockPresenter(AbstractContentBlock $contentBlock, bool $inPrimaryColumn = true, array $options = []): Presenter
    {
        if ($contentBlock instanceof Faq) {
            return new FaqPresenter($contentBlock, $inPrimaryColumn, $options);
        }
        if ($contentBlock instanceof Galleries) {
            return new GalleriesPresenter($contentBlock, $inPrimaryColumn, $options);
        }
        if ($contentBlock instanceof Image) {
            return new ImagePresenter($contentBlock, $inPrimaryColumn, $options);
        }
        if ($contentBlock instanceof Links) {
            return new LinksPresenter($contentBlock, $inPrimaryColumn, $options);
        }
        if ($contentBlock instanceof Promotions) {
            return new PromotionsPresenter($contentBlock, $inPrimaryColumn, $options);
        }
        if ($contentBlock instanceof Table) {
            return new TablePresenter($contentBlock, $inPrimaryColumn, $options);
        }

        throw new InvalidArgumentException(sprintf(
            '$block was not a valid type. Found instance of "%s"',
            (\is_object($contentBlock) ? \get_class($contentBlock) : gettype($contentBlock))
        ));
    }

    /**
     * Create a group presenter class
     */
    public function groupPresenter(Group $group, array $options = []): GroupPresenter
    {
        return new GroupPresenter($this->router, $this->helperFactory, $group, $options);
    }

    /**
     * Create a programme presenter class
     */
    public function programmePresenter(
        Programme $programme,
        array $options = []
    ): ProgrammePresenter {
        return new ProgrammePresenter(
            $this->router,
            $this->helperFactory,
            $programme,
            $options
        );
    }

    public function episodeMapPresenter(
        Episode $programme,
        array $versions,
        ?CollapsedBroadcast $upcomingCollapsedBroadcast,
        ?CollapsedBroadcast $lastOnCollapsedBroadcast,
        ?Episode $nextEpisode,
        ?Episode $previousEpisode,
        ?Podcast $podcast
    ) :EpisodeMapPresenter {
        return new EpisodeMapPresenter(
            $this->router,
            $this->helperFactory->getLiveBroadcastHelper(),
            $this->helperFactory->getStreamUrlHelper(),
            $this->helperFactory->getPlayTranslationsHelper(),
            $programme,
            $upcomingCollapsedBroadcast,
            $lastOnCollapsedBroadcast,
            $versions,
            $nextEpisode,
            $previousEpisode,
            $podcast
        );
    }

    public function profilePresenter(Profile $profile, array $options = []): ProfilePresenter
    {
        return new ProfilePresenter($profile, $options);
    }

    public function segmentsListPresenter(
        ProgrammeItem $programmeItem,
        array $segmentEvents,
        ?CollapsedBroadcast $firstBroadcast = null,
        array $options = []
    ): SegmentsListPresenter {
        return new SegmentsListPresenter(
            $this->helperFactory->getLiveBroadcastHelper(),
            $this->helperFactory->getPlayTranslationsHelper(),
            $programmeItem,
            $segmentEvents,
            $firstBroadcast,
            $options
        );
    }

    public function smpPresenter(
        ProgrammeItem $programmeItem,
        Version $streamableVersion,
        array $segmentEvents,
        string $analyticsCounterName,
        array $analyticsLabels,
        array $options = []
    ): SmpPresenter {
        return new SmpPresenter(
            $programmeItem,
            $streamableVersion,
            $segmentEvents,
            $analyticsCounterName,
            $analyticsLabels,
            $this->helperFactory->getSmpPlaylistHelper(),
            $this->router,
            $this->cosmosInfo,
            $options
        );
    }

    /**
     * A Broadcast Programme is a special case of the Programme Presenter, that
     * contains additional information about a given broadcast of a programme.
     *
     * Usually this shall be a CollapasedBroadcast, however sometimes you may
     * only have a Broadcast to hand.
     *
     * You may pass in an explicit programme in as an argument in case the
     * programme attached to $broadcast does not have a full hierarchy attached
     * to it.
     *
     * @param Broadcast|CollapsedBroadcast $broadcast
     * @param Programme|null $programme
     */
    public function broadcastProgrammePresenter(
        $broadcast,
        ?Programme $programme = null,
        array $options = []
    ) {
        if ($broadcast instanceof CollapsedBroadcast) {
            return new CollapsedBroadcastProgrammePresenter(
                $this->router,
                $this->helperFactory,
                $broadcast,
                $programme ?? $broadcast->getProgrammeItem(),
                $options
            );
        }

        if ($broadcast instanceof Broadcast) {
            return new BroadcastProgrammePresenter(
                $this->router,
                $this->helperFactory,
                $broadcast,
                $programme ?? $broadcast->getProgrammeItem(),
                $options
            );
        }

        throw new InvalidArgumentException(sprintf(
            'Expected $broadcast to be an instance of "%s" or "%s". Found instance of "%s"',
            Broadcast::class,
            CollapsedBroadcast::class,
            (is_object($broadcast) ? get_class($broadcast) : gettype($broadcast))
        ));
    }

    public function broadcastPresenter(
        $broadcast,
        ?CollapsedBroadcast $collapsedBroadcast,
        array $options = []
    ): BroadcastPresenter {
        return new BroadcastPresenter(
            $broadcast,
            $collapsedBroadcast,
            $options
        );
    }

    public function promotionPresenter(
        Promotion $promotion,
        array $options = []
    ): PromotionPresenter {
        return new PromotionPresenter(
            $this->router,
            $promotion,
            $options
        );
    }

    public function recipePresenter(
        Recipe $recipe,
        array $options = []
    ) :RecipePresenter {
        return new RecipePresenter($recipe, $options);
    }

    /**
     * @param Clip $clip
     * @param Contribution[] $contributions
     * @param Version|null $version
     * @param Podcast|null $podcast
     * @param array $options
     */
    public function clipDetailsPresenter(
        Clip $clip,
        array $contributions,
        ?Version $version,
        ?Podcast $podcast,
        array $options = []
    ): ClipDetailsPresenter {
        return new ClipDetailsPresenter(
            $this->helperFactory->getPlayTranslationsHelper(),
            $clip,
            $contributions,
            $version,
            $podcast,
            $options
        );
    }

    /**
     * @param ProgrammeItem $programmeItem
     * @param Version $version
     * @param Podcast|null $podcast
     * @param array $options
     */
    public function downloadPresenter(
        ProgrammeItem $programmeItem,
        Version $version,
        ?Podcast $podcast,
        array $options = []
    ): DownloadPresenter {
        return new DownloadPresenter($this->router, $programmeItem, $version, $podcast, $options);
    }

    public function superpromoPresenter(Promotion $promotion, array $options = []): SuperpromoPresenter
    {
        return new SuperpromoPresenter($this->router, $promotion, $options);
    }

    public function supportingContentPresenter(
        SupportingContentItem $supportingContentItem,
        array $options = []
    ): SupportingContentPresenter {
        return new SupportingContentPresenter($supportingContentItem, $options);
    }

    /* Sections */

    /**
     * @param Programme $programme
     * @param array $options
     */
    public function footerPresenter(Programme $programme, array $options = []): FooterPresenter
    {
        return new FooterPresenter($programme, $options);
    }

    public function episodesSubNavPresenter(string $currentRoute, bool $isDomestic, bool $hasBroadcasts, int $availableEpisodeCount, Pid $pid, int $upcomingBroadcastCount): EpisodesSubNavPresenter
    {
        return new EpisodesSubNavPresenter($currentRoute, $isDomestic, $hasBroadcasts, $availableEpisodeCount, $pid, $upcomingBroadcastCount);
    }

    public function relatedTopicsPresenter(array $relatedTopics, string $linkTrack, array $options = []): RelatedTopicsPresenter
    {
        return new RelatedTopicsPresenter($relatedTopics, $linkTrack, $options);
    }
}
