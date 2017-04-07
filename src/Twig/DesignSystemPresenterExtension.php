<?php
declare(strict_types = 1);
namespace App\Twig;

use App\Ds2014\PresenterFactory as Ds2014PresenterFactory;
use Twig_Environment;
use Twig_Extension;
use Twig_Function;
use RMP\Translate\Translate;

class DesignSystemPresenterExtension extends Twig_Extension
{
    private $translate;

    private $ds2014PresenterFactory;

    public function __construct(
        Translate $translate,
        Ds2014PresenterFactory $ds2014PresenterFactory
    ) {
        $this->translate = $translate;
        $this->ds2014PresenterFactory = $ds2014PresenterFactory;
    }

    public function setTranslate(Translate $translate): void
    {
        $this->translate = $translate;
        $this->ds2014PresenterFactory->setLocale($this->translate->getLocale());
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new Twig_Function('tr', [$this, 'tr']),
            new Twig_Function('ds2014', [$this, 'ds2014'], [
                'is_safe' => ['html'],
                'is_variadic' => true,
                'needs_environment' => true,
            ]),
        ];
    }

    public function ds2014(
        Twig_Environment $twigEnv,
        string $presenterName,
        array $presenterArguments = []
    ): string {
        $presenter = $this->ds2014PresenterFactory->{$presenterName . 'Presenter'}(...$presenterArguments);

        return $twigEnv->render($presenter->getTemplatePath(), [$presenter->getBase() => $presenter]);
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
}
