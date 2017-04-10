<?php
declare(strict_types = 1);
namespace App\Controller;

use BBC\ProgrammesPagesService\Service\ProgrammesService;

class HomeController extends BaseController
{
    public function __invoke(ProgrammesService $programmesService)
    {
        $programmeCount = $programmesService->countAll();

        return $this->renderWithChrome('home/show.html.twig', [
            'programmeCount' => $programmeCount,
        ]);
    }
}
