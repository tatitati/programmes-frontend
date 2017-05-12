<?php
declare(strict_types = 1);
namespace App\Ds2013;

/**
 * Base Class for a Presenter
 */
abstract class Presenter
{
    private const TWIG_SUFFIX = '.html.twig';

    /** @var array */
    protected $options = [];

    /** @var string */
    protected $uniqueId;

    /** @var PresenterFactory */
    protected $presenterFactory;

    public function __construct(
        PresenterFactory $presenterFactory,
        array $options = []
    ) {
        $this->presenterFactory = $presenterFactory;
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Full path to the twig template the presenter class renders
     */
    public function getTemplatePath(): string
    {
        return '@Ds2013/' . $this->getClassPath() . self::TWIG_SUFFIX;
    }

    /**
     * Get the base property that the twig template will be expecting
     * to find it's variables under. Matches the presenter name (e.g programme).
     * Therefore the Twig template would use {{ programme.title }}
     */
    public function getBase(): string
    {
        $classPath = $this->getClassPath();
        $parts = explode('/', $classPath);
        return end($parts);
    }

    public function getOption($keyOption)
    {
        if (isset($this->options[$keyOption])) {
            return $this->options[$keyOption];
        }

        return null;
    }

    /**
     * Get or generate a unique ID. Once generated once the same one will be used
     * Only used for unique references in a single render
     */
    protected function getUniqueID(): string
    {
        if (!$this->uniqueId) {
            $parts = explode('\\', get_called_class());
            $class = end($parts);
            $this->uniqueId = 'ds2013-' . $class . '-' . mt_rand();
        }

        return $this->uniqueId;
    }

    protected function buildCssClasses(array $cssClassTests = []): string
    {
        $cssClasses = [];
        foreach ($cssClassTests as $cssClass => $shouldSet) {
            if ($shouldSet) {
                $cssClasses[] = $cssClass;
            }
        }

        return trim(implode(' ', $cssClasses));
    }


    protected function tr(
        string $key,
        $substitutions = [],
        $numPlurals = null,
        ?string $domain = null
    ): string {
        // this code is duplicating the function extension for the twig template. Same code. Can be improved this?
        if (is_int($substitutions) && is_null($numPlurals)) {
            $numPlurals = $substitutions;
            $substitutions = array('%count%' => $numPlurals);
        }

        if (is_int($numPlurals) && !isset($substitutions['%count%'])) {
            $substitutions['%count%'] = $numPlurals;
        }

        return $this->presenterFactory->getTranslate()->translate($key, $substitutions, $numPlurals, $domain);
    }

    /**
     * Auto calculates the path to the Presenter Class
     * So that every presenter doesn't need to restate its template path and base
     */
    private function getClassPath(): string
    {
        $className = get_called_class();
        // strip off the namespace
        $classPath = str_replace('App\Ds2013\\', '', $className);
        // split by backslash
        $parts = explode('\\', $classPath);
        // get the last bit (the class name)
        $last = array_pop($parts);
        // Trim the class name from the word 'Presenter'
        $last = substr($last, 0, strpos($last, 'Presenter'));
        // Add the class name back to the parts (lower case first character)
        $parts[] = lcfirst($last);
        // Recombobulate with forward slashes
        return implode('/', $parts);
    }
}
