<?php
declare(strict_types=1);

namespace App\Controller\Styleguide\Ds2013;

use App\Controller\BaseController;

class IntroController extends BaseController
{
    public function __invoke()
    {
        return $this->renderWithChrome('styleguide/ds2013/intro.html.twig');
    }
}
