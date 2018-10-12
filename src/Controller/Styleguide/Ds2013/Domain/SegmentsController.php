<?php
declare(strict_types=1);

namespace App\Controller\Styleguide\Ds2013\Domain;

use App\Controller\BaseController;

class SegmentsController extends BaseController
{
    public function __invoke()
    {
        return $this->renderWithChrome('styleguide/ds2013/domain/segments.html.twig', [

        ]);
    }



}


