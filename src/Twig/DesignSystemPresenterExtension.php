<?php
declare(strict_types = 1);
namespace App\Twig;

use App\Ds2013\PresenterFactory as Ds2013PresenterFactory;
use Twig_Environment;
use Twig_Extension;
use Twig_Function;
use RMP\Translate\Translate;

class DesignSystemPresenterExtension extends Twig_Extension
{
    private $translate;

    private $ds2013PresenterFactory;

    private $iconCache = [];

    public function __construct(
        Translate $translate,
        Ds2013PresenterFactory $ds2013PresenterFactory
    ) {
        $this->translate = $translate;
        $this->ds2013PresenterFactory = $ds2013PresenterFactory;
    }

    public function getTranslate(): Translate
    {
        return $this->translate;
    }

    public function setTranslate(Translate $translate): void
    {
        $this->translate = $translate;
        $this->ds2013PresenterFactory->setLocale($this->translate->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_Function('tr', [$this, 'tr']),
            new Twig_Function('ds2013', [$this, 'ds2013'], [
                'is_safe' => ['html'],
                'is_variadic' => true,
                'needs_environment' => true,
            ]),
        ];
    }

    public function tr(
        string $key,
        $substitutions = [],
        $numPlurals = null,
        ?string $domain = null
    ): string {
        if (is_int($substitutions) && is_null($numPlurals)) {
            $numPlurals = $substitutions;
            $substitutions = array('%count%' => $numPlurals);
        }

        if (is_int($numPlurals) && !isset($substitutions['%count%'])) {
            $substitutions['%count%'] = $numPlurals;
        }

        return $this->translate->translate($key, $substitutions, $numPlurals, $domain);
    }

    public function ds2013(
        Twig_Environment $twigEnv,
        string $presenterName,
        array $presenterArguments = []
    ): string {
        $presenter = $this->ds2013PresenterFactory->{$presenterName . 'Presenter'}(...$presenterArguments);

        return $twigEnv->render($presenter->getTemplatePath(), [$presenter->getBase() => $presenter]);
    }
}
