<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseController extends Controller
{
    private $brandingId = 'br-00001';

    protected function setBrandingId($brandingId)
    {
        $this->brandingId = $brandingId;
    }

    protected function renderWithChrome($view, array $parameters = array(), Response $response = null)
    {
        // Using $_GET is ugly, work out a way to get to the Request object
        // without having to pass it around everywhere
        $brandingClient = $this->get('app.branding_client');
        $branding = $brandingClient->getContent(
            $this->brandingId,
            $_GET[$brandingClient::PREVIEW_PARAM] ?? null
        );

        // We only need to change the translation language if it is different
        // to the language the translation extension was initially created with
        if ($branding->getOrbitLanguage() != $this->getParameter('app.default_locale')) {
            $translate = $this->get('app.translate_factory')->create($this->language);
            // $this->get('app.rmp_translate_extension')->setTranslate($translate);
        }

        $orb =$this->get('app.orbit_client')->getContent([
            'variant' => $branding->getOrbitVariant(),
            'language' => $branding->getOrbitLanguage(),
            'searchScope' => $branding->getOrbitSearchScope(),
        ]);

        $parameters = array_merge([
            'orb' => $orb,
            'branding' => $branding,
        ], $parameters);

        return $this->render($view, $parameters, $response);
    }
}
