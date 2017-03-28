<?php
declare(strict_types = 1);
namespace AppBundle\Controller;

use BBC\ProgrammesPagesService\Service\ProgrammesService;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends BaseController
{
    public function homeAction(Request $request, ProgrammesService $programmesService)
    {
        // TODO: Name this method __invoke rather than homeAction if
        // "controller.service_arguments" become supported on invokable controllers
        // https://github.com/symfony/symfony/issues/22202
        $programmeCount = $programmesService->countAll();

        return $this->renderWithChrome('@App/home/show.html.twig', [
            'programmeCount' => $programmeCount,
        ]);
    }
}
