<?php
declare(strict_types = 1);
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Twig\DesignSystemPresenterExtension;

abstract class BaseController extends Controller
{
    private $brandingId = 'br-00001';

    protected function setBrandingId(string $brandingId)
    {
        $this->brandingId = $brandingId;
    }

    protected function renderWithChrome(string $view, array $parameters = [], Response $response = null)
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
        $locale = $branding->getOrbitLanguage();
        if ($locale != $this->getParameter('app.default_locale')) {
            $translate = $this->container->get('app.translate_factory')->create($locale);
            $this->container->get(DesignSystemPresenterExtension::class)->setTranslate($translate);
        }

        $orb = $this->get('app.orbit_client')->getContent([
            'variant' => $branding->getOrbitVariant(),
            'language' => $locale,
        ], [
            'searchScope' => $branding->getOrbitSearchScope(),
            'skipLinkTarget' => 'programmes-content',
        ]);

        $parameters = array_merge([
            'orb' => $orb,
            'branding' => $branding,
        ], $parameters);

        return $this->render($view, $parameters, $response);
    }
}
