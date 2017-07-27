<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\Segment;

/**
 * Segment Page
 *
 * Show a list of all programmes a segment has appeared within
 */
class SegmentController extends BaseController
{
    public function __invoke(Segment $segment)
    {
        $this->setContext($segment);

        return $this->renderWithChrome('find_by_pid/example_entity.html.twig', [
            'segment' => $segment,
        ]);
    }
}
