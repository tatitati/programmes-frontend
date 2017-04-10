<?php
declare(strict_types = 1);
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;

class CloudLabsController extends BaseController
{
    public function __invoke(Request $request)
    {
        return $this->render('cloud_labs/show.html.twig');
    }
}
