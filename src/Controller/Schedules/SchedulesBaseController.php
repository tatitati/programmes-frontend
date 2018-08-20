<?php
declare(strict_types = 1);

namespace App\Controller\Schedules;

use App\Controller\BaseController;
use App\Cosmos\Dials;
use App\ExternalApi\SoundsNav\Service\SoundsNavService;

abstract class SchedulesBaseController extends BaseController
{
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            Dials::class,
            SoundsNavService::class,
        ]);
    }

    protected function preRender()
    {
        if ($this->shouldShowSoundsNav()) {
            $this->overrideBrandingOption('orb_header', 'white');
        }
        parent::preRender();
    }

    protected function renderWithChrome(string $view, array $parameters = [])
    {
        if ($this->shouldShowSoundsNav()) {
            $soundsNavPromise = $this->container->get(SoundsNavService::class)->getContent();
            $resolvedPromises = $this->resolvePromises(['soundsNav' => $soundsNavPromise]);
            if ($resolvedPromises['soundsNav'] !== null) {
                $parameters = array_merge($parameters, $resolvedPromises);
            }
        }
        return parent::renderWithChrome($view, $parameters);
    }

    private function shouldShowSoundsNav(): bool
    {
        return $this->context->isRadio() && $this->container->get(Dials::class)->get('sounds-nav') === 'true';
    }
}
