<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Translate\TranslateProvider;
use App\ValueObject\MetaContext;
use BBC\BrandingClient\BrandingClient;
use BBC\BrandingClient\OrbitClient;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Twig\DesignSystemPresenterExtension;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class BaseController extends AbstractController
{
    private $brandingId = 'br-00001';

    private $context;

    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            BrandingClient::class,
            OrbitClient::class,
            TranslateProvider::class,
        ]);
    }

    protected function getCanonicalUrl(): string
    {
        $requestAttributes = $this->container->get('request_stack')->getCurrentRequest()->attributes;
        return $this->generateUrl(
            $requestAttributes->get('_route'),
            $requestAttributes->get('_route_params'),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    protected function setBrandingId(string $brandingId)
    {
        $this->brandingId = $brandingId;
    }

    protected function setContext($context)
    {
        $this->context = $context;

        if ($context instanceof Programme || $context instanceof Network) {
            $this->setBrandingId($context->getOption('branding_id'));
        } elseif ($context instanceof Service) {
            $this->setBrandingId($context->getNetwork()->getOption('branding_id'));
        }
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
        $locale = $branding->getLocale();
        $translateProvider = $this->container->get(TranslateProvider::class);

        $translateProvider->setLocale($locale);

        $orb = $this->container->get(OrbitClient::class)->getContent([
            'variant' => $branding->getOrbitVariant(),
            'language' => $branding->getLanguage(),
        ], [
            'searchScope' => $branding->getOrbitSearchScope(),
            'skipLinkTarget' => 'programmes-content',
        ]);

        $parameters = array_merge([
            'orb' => $orb,
            'branding' => $branding,
            'meta_context' => new MetaContext($this->context, $this->getCanonicalUrl()),
        ], $parameters);

        return $this->render($view, $parameters, $response);
    }
}
