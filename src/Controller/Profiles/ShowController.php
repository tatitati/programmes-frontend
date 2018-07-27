<?php
declare(strict_types = 1);

namespace App\Controller\Profiles;

use App\Controller\BaseController;
use App\Controller\Helpers\IsiteKeyHelper;
use App\ExternalApi\Isite\Domain\Profile;
use App\ExternalApi\Isite\IsiteResult;
use App\ExternalApi\Isite\Service\IsiteService;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Service\CoreEntitiesService;
use Symfony\Component\HttpFoundation\Request;

class ShowController extends BaseController
{
    public function __invoke(string $key, string $slug, Request $request, IsiteService $isiteService, IsiteKeyHelper $isiteKeyHelper, CoreEntitiesService $coreEntitiesService)
    {
        $preview = false;
        if ($request->query->has('preview') && $request->query->get('preview')) {
            $preview = true;
        }

        if ($isiteKeyHelper->isKeyAGuid($key)) {
            return $this->redirectWith($isiteKeyHelper->convertGuidToKey($key), $slug, $preview);
        }

        $guid = $isiteKeyHelper->convertKeyToGuid($key);

        /** @var IsiteResult $isiteResult */
        $isiteResult = $isiteService->getByContentId($guid, $preview)->wait(true);
        /** @var Profile $profile */
        $profiles = $isiteResult->getDomainModels();
        if (!$profiles) {
            throw $this->createNotFoundException('No profiles found for guid');
        }

        $profile = reset($profiles);
        if ($slug != $profile->getSlug()) {
            return $this->redirectWith($profile->getKey(), $profile->getSlug(), $preview);
        }

        $context = null;
        if ('' !== $profile->getParentPid()) {
            $context = $coreEntitiesService->findByPidFull(new Pid($profile->getParentPid()));

            if ($profile->getProjectSpace() !== $context->getOption('project_space')) {
                throw $this->createNotFoundException('Project space Profile-Programme not matching');
            }
        }

        $this->setContext($context);

        if ('' !== $profile->getBrandingId()) {
            $this->setBrandingId($profile->getBrandingId());
        }

        if ($profile->isIndividual()) {
            return $this->renderWithChrome('profiles/individual.html.twig', ['profile' => $profile]);
        }

        $childPromise = $isiteService->setChildProfilesOn([$profile], $profile->getProjectSpace());
        $this->resolvePromises([$childPromise]);

        return $this->renderWithChrome('profiles/group.html.twig', ['profile' => $profile]);
    }

    private function redirectWith(string $key, string $slug, bool $preview)
    {
        $params = ['key' => $key, 'slug' => $slug];

        if ($preview) {
            $params['preview'] = 'true';
        }

        return $this->cachedRedirectToRoute('programme_profile', $params, 301);
    }
}
