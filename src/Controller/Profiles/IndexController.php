<?php
declare(strict_types = 1);

namespace App\Controller\Profiles;

use App\Controller\BaseController;
use App\ExternalApi\Isite\Domain\Profile;
use App\ExternalApi\Isite\Service\IsiteService;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class IndexController extends BaseController
{
    public function __invoke(CoreEntity $coreEntity, IsiteService $isiteService)
    {
        $this->setContextAndPreloadBranding($coreEntity);

        /** @var Profile[] $profiles */
        $profiles = [];
        $parameters = ['coreEntity' => $coreEntity, 'profiles' => $profiles];
        if ($coreEntity instanceof Programme) {
            $parameters['programme'] = $coreEntity; //so the the base 2013 template sets the footer

            $profilesResult = $isiteService->getProfilesByProgramme($coreEntity, $this->getPage())->wait();
            $profiles = $profilesResult->getDomainModels();
        }

        if (empty($profiles)) {
            $this->response()->setStatusCode(404);
        } else {
            $groupProfiles = [];
            /** @var string $project */
            $project = $coreEntity->getOption('project_space');
            foreach ($profiles as $profile) {
                if ($profile->getType() === 'group') {
                    $groupProfiles[] = $profile;
                }
            }
            $childPromise = $isiteService->setChildProfilesOn($groupProfiles, $project);
            $this->resolvePromises([$childPromise]);
            $parameters['profiles'] = $profiles;
        }

        return $this->renderWithChrome('profiles/index.html.twig', $parameters);
    }
}
