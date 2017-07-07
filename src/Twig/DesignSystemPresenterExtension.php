<?php
declare(strict_types = 1);
namespace App\Twig;

use App\Ds2013\Presenter as Ds2013Presenter;
use App\Ds2013\PresenterFactory as Ds2013PresenterFactory;
use App\Translate\TranslatableTrait;
use App\Translate\TranslateProvider;
use DateTimeInterface;
use Twig_Environment;
use Twig_Extension;
use Twig_Function;
use Twig_SimpleFilter;

class DesignSystemPresenterExtension extends Twig_Extension
{
    use TranslatableTrait;

    private $ds2013PresenterFactory;

    public function __construct(
        TranslateProvider $translateProvider,
        Ds2013PresenterFactory $ds2013PresenterFactory
    ) {
        $this->translateProvider = $translateProvider;
        $this->ds2013PresenterFactory = $ds2013PresenterFactory;
    }

    /**
     * @return Twig_SimpleFilter[]
     */
    public function getFilters(): array
    {
        return [
            new Twig_SimpleFilter('localDate', [$this, 'localDateWrapper']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_Function('tr', [$this, 'trWrapper']),
            new Twig_Function('ds2013', [$this, 'ds2013'], [
                'is_safe' => ['html'],
                'is_variadic' => true,
                'needs_environment' => true,
            ]),
            new Twig_Function('ds2013_presenter', [$this, 'ds2013Presenter'], [
                'is_safe' => ['html'],
                'needs_environment' => true,
            ]),
        ];
    }

    public function trWrapper(
        string $key,
        $substitutions = [],
        $numPlurals = null,
        ?string $domain = null
    ): string {
        return $this->tr($key, $substitutions, $numPlurals, $domain);
    }

    public function localDateWrapper(DateTimeInterface $dateTime, string $format): string
    {
        return $this->localDate($dateTime, $format);
    }

    public function ds2013(
        Twig_Environment $twigEnv,
        string $presenterName,
        array $presenterArguments = []
    ): string {
        $presenter = $this->ds2013PresenterFactory->{$presenterName . 'Presenter'}(...$presenterArguments);

        return $this->ds2013Presenter($twigEnv, $presenter);
    }

    public function ds2013Presenter(
        Twig_Environment $twigEnv,
        Ds2013Presenter $presenter
    ): string {
        return $twigEnv->render(
            $presenter->getTemplatePath(),
            [$presenter->getTemplateVariableName() => $presenter]
        );
    }
}
