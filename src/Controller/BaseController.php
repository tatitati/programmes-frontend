<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Translate\TranslateProvider;
use App\ValueObject\MetaContext;
use BBC\BrandingClient\Branding;
use BBC\BrandingClient\BrandingClient;
use BBC\BrandingClient\BrandingException;
use BBC\BrandingClient\OrbitClient;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class BaseController extends AbstractController
{
    private $brandingId = 'br-00001';

    /**
     * Used in case a page requests a BrandingID that does not exist.
     * This may be changed depending upon the $context
     */
    private $fallbackBrandingId = 'br-00001';

    private $context;

    private $response;

    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            'logger' => LoggerInterface::class,
            BrandingClient::class,
            OrbitClient::class,
            TranslateProvider::class,
        ]);
    }

    public function __construct()
    {
        $this->response = new Response();
        // It is required to set the cache-control header when creating the response object otherwise Symfony
        // will create and set its value to "no-cache, private" by default
        $this->response()->setPublic()->setMaxAge(120);
    }

    protected function getCanonicalUrl(): string
    {
        $requestAttributes = $this->request()->attributes;
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

        // br-00002 is the default 'Programme Variant' - use that when we're
        // displaying programme/group/service pages.
        // TODO instanceof Group should use the brandingId from their options
        // and use br-00002 as a fallback (same as instanceof Programme), when
        // we create those domain models
        if ($context instanceof Programme || $context instanceof Network) {
            $this->setBrandingId($context->getOption('branding_id'));
            $this->fallbackBrandingId = 'br-00002';
        } elseif ($context instanceof Service) {
            $this->setBrandingId($context->getNetwork()->getOption('branding_id'));
            $this->fallbackBrandingId = 'br-00002';
        }
    }

    protected function response(): Response
    {
        return $this->response;
    }

    protected function renderWithChrome(string $view, array $parameters = [])
    {
        $branding = $this->requestBranding();

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

        return $this->render($view, $parameters, $this->response);
    }

    /**
     * Renders a view. Same as parent but using $this->response
     *
     * @param string        $view       The view name
     * @param array         $parameters An array of parameters to pass to the view
     * @param Response      $response   A response instance
     *
     * @return Response A Response instance
     */
    protected function render($view, array $parameters = [], Response $response = null): Response
    {
        return parent::render($view, $parameters, $response ?? $this->response);
    }

    private function requestBranding(): Branding
    {
        $brandingClient = $this->container->get(BrandingClient::class);
        $previewId = $this->request()->query->get($brandingClient::PREVIEW_PARAM, null);
        $usePreview = !is_null($previewId);

        try {
            $this->logger()->info(
                'Using BrandingID "{0}"' . ($usePreview ? ', but overridden previewing theme version "{1}"' : ''),
                $usePreview ? [$this->brandingId, $previewId] : [$this->brandingId]
            );

            $branding = $brandingClient->getContent(
                $this->brandingId,
                $previewId
            );
        } catch (BrandingException $e) {
            // Could not find that branding id (or preview id), someone probably
            // mistyped it. Use a default branding instead of blowing up.

            $this->logger()->warning(
                'Requested BrandingID "{0}"' . ($usePreview ? ', but overridden previewing theme version "{2}"' : '') .
                ' but it was not found. Using "{1}" as a fallback',
                $usePreview ? [$this->brandingId, $this->fallbackBrandingId, $previewId] : [$this->brandingId, $this->fallbackBrandingId]
            );

            $this->setBrandingId($this->fallbackBrandingId);
            $branding = $brandingClient->getContent($this->brandingId, null);
        }

        return $branding;
    }

    private function logger(): LoggerInterface
    {
        return $this->container->get('logger');
    }

    private function request(): Request
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }
}
