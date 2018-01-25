<?php
declare(strict_types = 1);
namespace App\Controller\FindByPid;

use App\Controller\BaseController;
use BBC\ProgrammesPagesService\Domain\Entity\Version;

/**
 * Version Page
 *
 * Redirect to the Episode that the Version belongs to
 */
class VersionController extends BaseController
{
    public function __invoke(Version $version)
    {
        $episodePid = (string) $version->getProgrammeItem()->getPid();
        return $this->cachedRedirectToRoute('find_by_pid', ['pid' => $episodePid], 303);
    }
}
