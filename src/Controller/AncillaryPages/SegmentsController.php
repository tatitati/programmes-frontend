<?php
declare(strict_types=1);

namespace App\Controller\AncillaryPages;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\Version;

class SegmentsController extends BaseController
{
    /**
     * This controller can go after v2 is dead for a while.
     * It's only here to avoid dead links and for SEO reasons
     */
    public function __invoke(Version $version)
    {
        return $this->redirectToRoute('find_by_pid', ['pid' => (string) $version->getProgrammeItem()->getPid()]);
    }
}
