<?php
declare(strict_types = 1);
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class CloudLabsController extends BaseController
{
    public function showAction(Request $request)
    {
        return $this->render('cloud_labs/show.html.twig');
    }
}
