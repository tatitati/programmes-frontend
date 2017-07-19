<?php
declare(strict_types = 1);
namespace Tests\App\Branding;

use App\Branding\BrandingPlaceholderResolver;
use App\Translate\TranslateProvider;
use BBC\BrandingClient\Branding;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use RMP\Translate\Translate;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollectionBuilder;
use PHPUnit\Framework\TestCase;

class BrandingPlaceholderResolverTest extends TestCase
{
    private $resolver;

    public function setUp()
    {
        $routeCollectionBuilder = new RouteCollectionBuilder();
        $routeCollectionBuilder->add('/programmes/{pid}', '', 'find_by_pid');
        $routeCollectionBuilder->add('/programmes/{pid}/episodes', '', 'programme_episodes');
        $routeCollectionBuilder->add('/programmes/{pid}/clips', '', 'programme_clips');
        $routeCollectionBuilder->add('/programmes/{pid}/galleries', '', 'programme_galleries');

        $router = new UrlGenerator(
            $routeCollectionBuilder->build(),
            new RequestContext()
        );

        $translate = $this->createMock(Translate::class);

        $translate->method('translate')
            ->will($this->returnArgument(0));

        $translateProvider = $this->createMock(TranslateProvider::class);
        $translateProvider->method('getTranslate')->willReturn($translate);

        $this->resolver = new BrandingPlaceholderResolver($router, $translateProvider);
    }

    public function testContextIsNull()
    {
        $branding = $this->branding();
        $context = null;
        $this->assertSame($branding, $this->resolver->resolve($branding, $context));
    }

    public function testContextIsUnknownClass()
    {
        $branding = $this->branding();
        $context = $this->createMock(Service::class);
        $this->assertSame($branding, $this->resolver->resolve($branding, $context));
    }

    public function testContextTleoIsProgrammeContainerWithoutEpisodesClipsAndGalleries()
    {
        $resolvedBranding = $this->resolver->resolve(
            $this->branding(),
            $this->buildContext(ProgrammeContainer::class, 0, 0, 0)
        );

        $this->assertContains('<a href="/programmes/b0000001">MyTitle</a>', $resolvedBranding->getBodyFirst());

        // Only has a Home link
        $this->assertContains(
            '<li class="br-nav__item"><a class="br-nav__link" href="/programmes/b0000001" data-linktrack="nav_home">home</a></li>',
            $resolvedBranding->getBodyFirst()
        );
    }

    public function testContextTleoIsProgrammeContainerWithEpisodesClipsAndGalleries()
    {
        $resolvedBranding = $this->resolver->resolve(
            $this->branding(),
            $this->buildContext(ProgrammeContainer::class, 1, 1, 1)
        );

        $this->assertContains('<a href="/programmes/b0000001">MyTitle</a>', $resolvedBranding->getBodyFirst());

        $this->assertContains(
            '<li class="br-nav__item"><a class="br-nav__link" href="/programmes/b0000001" data-linktrack="nav_home">home</a></li>' .
            '<li class="br-nav__item"><a class="br-nav__link" href="/programmes/b0000001/episodes" data-linktrack="nav_episodes">episodes</a></li>' .
            '<li class="br-nav__item"><a class="br-nav__link" href="/programmes/b0000001/clips" data-linktrack="nav_clips">clips</a></li>' .
            '<li class="br-nav__item"><a class="br-nav__link" href="/programmes/b0000001/galleries" data-linktrack="nav_galleries">galleries</a></li>',
            $resolvedBranding->getBodyFirst()
        );
    }


    public function testContextTleoIsEpisodeWithOnlyClips()
    {
        $resolvedBranding = $this->resolver->resolve(
            $this->branding(),
            $this->buildContext(Episode::class, 0, 1, 0)
        );

        $this->assertContains('<a href="/programmes/b0000001">MyTitle</a>', $resolvedBranding->getBodyFirst());

        $this->assertContains(
            '<li class="br-nav__item"><a class="br-nav__link" href="/programmes/b0000001" data-linktrack="nav_home">home</a></li>' .
            '<li class="br-nav__item"><a class="br-nav__link" href="/programmes/b0000001/clips" data-linktrack="nav_clips">clips</a></li>',
            $resolvedBranding->getBodyFirst()
        );
    }

    public function testContextTleoIsEpisodeWithOnlyGalleries()
    {
        $resolvedBranding = $this->resolver->resolve(
            $this->branding(),
            $this->buildContext(Episode::class, 0, 0, 1)
        );

        $this->assertContains('<a href="/programmes/b0000001">MyTitle</a>', $resolvedBranding->getBodyFirst());

        $this->assertContains(
            '<li class="br-nav__item"><a class="br-nav__link" href="/programmes/b0000001" data-linktrack="nav_home">home</a></li>' .
            '<li class="br-nav__item"><a class="br-nav__link" href="/programmes/b0000001/galleries" data-linktrack="nav_galleries">galleries</a></li>',
            $resolvedBranding->getBodyFirst()
        );
    }

    private function branding()
    {
        return new Branding(
            '<branding-head/>',
            '<branding-bodyfirst><!--BRANDING_PLACEHOLDER_TITLE-->||<!--BRANDING_PLACEHOLDER_NAV-->||<!--BRANDING_PLACEHOLDER_SPONSOR--></branding-bodyfirst>',
            '<branding-bodylast/>',
            ['body' => ['bg' => '#eeeeee']],
            []
        );
    }

    private function buildContext(
        string $tleoClassName,
        int $episodesCount = 0,
        int $clipsCount = 0,
        int $galleriesCount = 0
    ) {
        $tleo = $this->createMock($tleoClassName);
        $tleo->method('getTitle')->willReturn('MyTitle');
        $tleo->method('getPid')->willReturn(new Pid('b0000001'));

        if (method_exists($tleo, 'getAggregatedEpisodesCount')) {
            $tleo->method('getAggregatedEpisodesCount')->willReturn($episodesCount);
        }

        if (method_exists($tleo, 'getAvailableClipsCount')) {
            $tleo->method('getAvailableClipsCount')->willReturn($clipsCount);
        }

        if (method_exists($tleo, 'getAvailableGalleriesCount')) {
            $tleo->method('getAvailableGalleriesCount')->willReturn($galleriesCount);
        }

        $context = $this->createMock(Episode::class);
        $context->method('getTleo')->willReturn($tleo);

        return $context;
    }
}
