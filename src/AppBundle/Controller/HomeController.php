<?php
declare(strict_types=1);
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class HomeController extends BaseController
{
    public function __invoke(Request $request)
    {
        // TODO swap this out for controllers-as-services and/or
        // getter injection in Symfony 3.3?
        $programmesService = $this->get('pps.programmes_service');

        $programmeCount = $programmesService->countAll();

        return $this->renderWithChrome('@App/home/show.html.twig', [
            'programmeCount' => $programmeCount,
        ]);
    }
}
