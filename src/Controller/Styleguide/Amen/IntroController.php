<?php
declare(strict_types = 1);
namespace App\Controller\Styleguide\Amen;

use App\Controller\BaseController;

class IntroController extends BaseController
{
    public function __invoke()
    {
        return $this->renderWithChrome('styleguide/amen/intro.html.twig');
    }
}
