<?php
declare(strict_types = 1);
namespace App\Controller;

class SchedulesHomeController extends BaseController
{
    public function __invoke()
    {
        return $this->renderWithChrome('schedules/home.html.twig', [
            'serviceList' => [],
        ]);
    }
}
