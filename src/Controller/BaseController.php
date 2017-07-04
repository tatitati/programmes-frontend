<?php
declare(strict_types = 1);

namespace App\Controller;

use App\ValueObject\MetaContext;
use BBC\BrandingClient\BrandingClient;
use BBC\BrandingClient\OrbitClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Twig\DesignSystemPresenterExtension;
use RMP\Translate\TranslateFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class BaseController extends Controller
{
    private $brandingId = 'br-00001';
    private $context;

    protected function getCanonicalUrl(): string
    {
        $requestAttributes = $this->container->get('request_stack')->getCurrentRequest()->attributes;
        $route = $requestAttributes->get('_route');
        $routeParams = $requestAttributes->get('_route_params');
        return $this->generateUrl($route, $routeParams, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    protected function setBrandingId(string $brandingId)
    {
        $this->brandingId = $brandingId;
    }

    protected function setContext($context)
    {
        $this->context = $context;
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
        $locale = $branding->getLanguage();
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
            'canonical_url' => $this->getCanonicalUrl(),
            'meta_context' => new MetaContext($this->context),
            'orb' => $orb,
            'branding' => $branding,
        ], $parameters);

        return $this->render($view, $parameters, $response);
    }
}
