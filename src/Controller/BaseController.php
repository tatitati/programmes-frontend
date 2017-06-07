<?php
declare(strict_types = 1);
namespace App\Controller;

use BBC\BrandingClient\BrandingClient;
use BBC\BrandingClient\OrbitClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Twig\DesignSystemPresenterExtension;
use RMP\Translate\TranslateFactory;

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
        $brandingClient = $this->container->get(BrandingClient::class);
        $branding = $brandingClient->getContent(
            $this->brandingId,
            $_GET[$brandingClient::PREVIEW_PARAM] ?? null
        );

        // We only need to change the translation language if it is different
        // to the language the translation extension was initially created with
        $locale = $branding->getOrbitLanguage();
        $designSystemPresenterExtension = $this->container->get(DesignSystemPresenterExtension::class);

        if ($locale != $designSystemPresenterExtension->getTranslate()->getLocale()) {
            $translate = $this->container->get(TranslateFactory::class)->create($locale);
            $designSystemPresenterExtension->setTranslate($translate);
        }

        $orb = $this->container->get(OrbitClient::class)->getContent([
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
