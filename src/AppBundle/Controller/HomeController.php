<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class HomeController extends BaseController
{
    public function showAction(Request $request)
    {
        return $this->renderWithChrome('@App/home/show.html.twig');
    }
}
