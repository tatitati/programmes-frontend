<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;
use BBC\ProgrammesPagesService\Service\CoreEntitiesService;
use BBC\ProgrammesPagesService\Service\SegmentEventsService;
use App\DsShared\Helpers\PlayTranslationsHelper;

/**
 * Segment Page
 *
 * Show a list of all programmes a segment has appeared within
 */
class SegmentController extends BaseController
{
    public function __invoke(
        Segment $segment,
        PlayTranslationsHelper $playTranslationsHelper,
        CoreEntitiesService $coreEntitiesService,
        SegmentEventsService $segmentEventsService
    ) {
        $segmentEvents = $segmentEventsService->findBySegmentFull($segment, true, $segmentEventsService::NO_LIMIT);

        // findBySegmentFull returns the oldest SegmentEvent first, but we want
        // to display the most recent first
        $programmes = array_reverse(array_map(function ($se) {
            return $se->getVersion()->getProgrammeItem();
        }, $segmentEvents));

        $segmentOwner = $this->getSegmentOwner($programmes, $coreEntitiesService);

        $this->setIstatsProgsPageType('programmes_segment');
        $this->setContextAndPreloadBranding($segmentOwner);
        if ($segmentOwner) {
            $this->setInternationalStatusAndTimezoneFromContext($segmentOwner);
        }

        return $this->renderWithChrome('find_by_pid/segment.html.twig', [
            'segment' => $segment,
            'segmentDuration' => $segment->getDuration() ? $playTranslationsHelper->secondsToWords($segment->getDuration()) : null,
            'programmesContainingSegment' => $programmes,
        ]);
    }

    /**
     * Work out what entity should be used for the branding of the Segment by
     * looking at the Programmes this Segment has appeared on.
     *
     * The Segment Owner is the programme that the segment has appeared on
     * the most. This takes the parents of the ProgrammeItem that the
     * SegmentEvent was attached to into account.
     *
     * If a Segment has appeared only within a single Programme then use that
     * Programme for Branding. Note that this applies to all levels of a
     * hierarchy, so a Segment that has appeared within a single Episode, shall
     * use that episode as its owner, however if a Segment has appeared on
     * multiple episodes within a single Brand then the Brand shall be used as
     * the owner.
     *
     * This method is public to make it easier to test.
     *
     * @param Programme[] $programmeItems
     * @param CoreEntitiesService $coreEntitiesService
     * @return CoreEntity|null|Network
     */
    public function getSegmentOwner(array $programmeItems, CoreEntitiesService $coreEntitiesService)
    {
        // If a segment was not a part of any Programmes then don't set any
        // explicit branding
        if (empty($programmeItems)) {
            return null;
        }

        // Otherwise we need check for a ancestor that is common to all the
        // Programmes the Segment was a part of

        $brandingEntity = $this->getCommonProgrammeAncestor($programmeItems);
        if ($brandingEntity) {
            // If an ancestor is found, then we need to get it's full representation
            return $coreEntitiesService->findByPidFull($brandingEntity->getPid());
        }

        // If there is no common programme, check for a common network
        return $this->getCommonNetwork($programmeItems);
    }

    /**
     * Given a list of Programme Items, find a common ancestor that they all
     * share.
     *
     * @param Programme[] $programmeItems
     */
    private function getCommonProgrammeAncestor(array $programmeItems): ?Programme
    {
        // The initial list of potentialAncestors is the ancestry of the first
        // programme
        $potentialAncestors = $programmeItems[0]->getAncestry();

        // Check each of the other ancestries, and compare them against the
        // potential ancestors. If an item in the potentialAncestors is not
        // present in the current item's ancestry then remove it from the list
        // of potential ancestors.
        // Once this loop is complete then potential ancestors shall contain
        // only the items that are in every programme's ancestry
        foreach ($programmeItems as $programme) {
            $currentItemAncestryPids = array_map(function ($p) {
                return (string) $p->getPid();
            }, $programme->getAncestry());

            $potentialAncestors = array_filter($potentialAncestors, function ($potentialCommonAncestor) use ($currentItemAncestryPids) {
                $pid = (string) $potentialCommonAncestor->getPid();
                return in_array($pid, $currentItemAncestryPids, true);
            });

            // If there are no more possible programmes, then there is nothing
            // left to filter, so we can exit out of the loop
            if (empty($potentialAncestors)) {
                return null;
            }
        }

        // There may be a case where an Episode within a Container is the common
        // ancestor for a SegmentEvent. In this case both the Episode and its
        // parent (the Container) will be present in the list of potential
        // ancestors. We want to return the most specific item - the Episode.
        // Because getAncestry() is ordered by most specifc ancestor first (and
        // thus TLEO last) and because array_filter preserves array keys, in
        // cases where there are multiple items in the list of potential
        // ancestors the most specific potenial ancestor shall the at the start
        // of the array.
        return reset($potentialAncestors);
    }

    /**
     * Given a list of Programme Items, find a common network that they all
     * share.
     *
     * @param Programme[] $programmes
     */
    private function getCommonNetwork(array $programmes): ?Network
    {
        // The initial potential network is that of the first programme
        $potentialNetwork = $programmes[0]->getNetwork();
        if ($potentialNetwork == null) {
            return null;
        }

        // Check each of the other networks. If the potential network does not
        // match the current item's network then there is no common ancestor
        foreach ($programmes as $programme) {
            if ($potentialNetwork != $programme->getNetwork()) {
                return null;
            }
        }

        return $potentialNetwork;
    }
}
