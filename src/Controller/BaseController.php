<?php
declare(strict_types = 1);

namespace App\Controller;

use App\Branding\BrandingPlaceholderResolver;
use App\Translate\TranslateProvider;
use App\ValueObject\AnalyticsCounterName;
use App\ValueObject\ComscoreAnalyticsLabels;
use App\ValueObject\CosmosInfo;
use App\ValueObject\IstatsAnalyticsLabels;
use App\ValueObject\MetaContext;
use BBC\BrandingClient\Branding;
use BBC\BrandingClient\BrandingClient;
use BBC\BrandingClient\BrandingException;
use BBC\BrandingClient\OrbitClient;
use BBC\ProgrammesPagesService\Domain\Entity\CoreEntity;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    private $istatsExtraLabels = [];

    private $istatsProgsPageType;

    protected $canonicalUrl;

    /** @var bool */
    protected $metaNoIndex;

    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), [
            'logger' => LoggerInterface::class,
            BrandingClient::class,
            BrandingPlaceholderResolver::class,
            OrbitClient::class,
            TranslateProvider::class,
            CosmosInfo::class,
        ]);
    }

    public function __construct()
    {
        $this->response = new Response();
        // It is required to set the cache-control header when creating the response object otherwise Symfony
        // will create and set its value to "no-cache, private" by default
        $this->response()->setPublic()->setMaxAge(120);
        // The page can only be displayed in a frame on the same origin as the page itself.
        $this->response()->headers->set('X-Frame-Options', 'SAMEORIGIN');
        // Blocks a request if the requested type is different from the MIME type
        $this->response()->headers->set('X-Content-Type-Options', 'nosniff');
    }

    protected function getCanonicalUrl(): string
    {
        if (!isset($this->canonicalUrl)) {
            $requestAttributes = $this->container->get('request_stack')->getMasterRequest()->attributes;
            $this->canonicalUrl = $this->generateUrl(
                $requestAttributes->get('_route'),
                $requestAttributes->get('_route_params'),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }
        return $this->canonicalUrl;
    }

    protected function getPage(): int
    {
        $pageString = $this->request()->query->get(
            'page',
            '1'
        );

        if (ctype_digit($pageString)) {
            $page = (int) $pageString;
            // Have a controlled upper-bound to stop people putting in clearly
            // absurdly large page sizes
            if ($page >= 1 && $page <= 9999) {
                return $page;
            }
        }

        throw $this->createNotFoundException('Page parameter must be a number between 1 and 9999');
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
        if ($context instanceof CoreEntity || $context instanceof Network) {
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

        // use controller name if this isn't set
        $this->istatsProgsPageType =  $this->istatsProgsPageType ?? $this->request()->attributes->get('_controller');

        $parameters = array_merge([
            'branding' => $branding,
            'with_chrome' => !$this->request()->query->has('no_chrome'),
        ], $parameters);

        // No need to process the ORB or Meta information if the Chrome is not being rendered.
        if ($parameters['with_chrome']) {
            $cosmosInfo = $this->container->get(CosmosInfo::class);
            $istatsAnalyticsLabels = new IstatsAnalyticsLabels($this->context, $this->istatsProgsPageType, $cosmosInfo->getAppVersion(), $this->istatsExtraLabels);
            $orb = $this->container->get(OrbitClient::class)->getContent([
                'variant' => $branding->getOrbitVariant(),
                'language' => $branding->getLanguage(),
            ], [
                'searchScope' => $branding->getOrbitSearchScope(),
                'skipLinkTarget' => 'programmes-content',
                'analyticsCounterName' => (string) new AnalyticsCounterName($this->context, $this->request()->getPathInfo()),
                'analyticsLabels' => $istatsAnalyticsLabels->orbLabels(),
            ]);

            $queryString = $this->request()->getQueryString();
            $urlQueryString =  is_null($queryString) ? '' : '?' . $queryString;

            $parameters = array_merge([
                'orb' => $orb,
                'meta_context' => new MetaContext($this->context, $this->getCanonicalUrl(), $this->getMetaNoIndex()),
                'comscore' => (new ComscoreAnalyticsLabels($this->context, $cosmosInfo, $istatsAnalyticsLabels, $this->getCanonicalUrl() . $urlQueryString))->getComscore(),
            ], $parameters);
        }

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

    /**
     * Returns a RedirectResponse to the given URL.
     * This picks up the default cache configuration of $this->response that was
     * set in the constructor
     *
     * @param string $url    The URL to redirect to
     * @param int    $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    protected function redirect($url, $status = 302): RedirectResponse
    {
        $headers = $this->response->headers->all();
        return new RedirectResponse($url, $status, $headers);
    }

    /**
     * @param mixed[] $labels associative array. example: ['schedule_offset' => '-3', 'schedule_context' => 'past']
     */
    protected function setIstatsExtraLabels(array $labels): void
    {
        $this->istatsExtraLabels = array_replace($this->istatsExtraLabels, $labels);
    }

    protected function setIstatsProgsPageType(string $label): void
    {
        $this->istatsProgsPageType = $label;
    }

    protected function request(): Request
    {
        return $this->container->get('request_stack')->getCurrentRequest();
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

        // Resolve branding placeholders
        if ($this->context) {
            $branding = $this->container->get(BrandingPlaceholderResolver::class)->resolve(
                $branding,
                $this->context
            );
        }

        return $branding;
    }

    private function logger(): LoggerInterface
    {
        return $this->container->get('logger');
    }

    private function getMetaNoIndex(): bool
    {
        if (!isset($this->metaNoIndex)) {
            $this->metaNoIndex = false;
        }

        return $this->metaNoIndex;
    }
}
