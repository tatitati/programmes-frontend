<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class HomeController extends BaseController
{
    public function showAction(Request $request)
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
