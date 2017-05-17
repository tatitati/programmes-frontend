<?php
declare(strict_types = 1);
namespace App\Ds2013;

/**
 * Base Class for a Presenter
 */
abstract class Presenter
{
    /** @var array */
    protected $options = [];

    /** @var string */
    private $templatePath;

    /** @var string */
    private $templateVariableName;

    /** @var string */
    private $uniqueId;

    public function __construct(
        array $options = []
    ) {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Full path to the twig template the presenter class renders
     */
    public function getTemplatePath(): string
    {
        if (!$this->templatePath) {
            $classPath = str_replace('App\Ds2013\\', '', static::class);

            $parts = implode('/', explode('\\', $classPath, -1));
            $pathPrefix = '@Ds2013/' . $parts . ($parts ? '/' : '');

            $this->templatePath = $pathPrefix . static::snakeCasePresenterName() . '.html.twig';
        }

        return $this->templatePath;
    }

    /**
     * Get the base property that the twig template will be expecting
     * to find it's variables under. Twig conventions say variable names should
     * use snake_case. So the "myExamplePresenter" presenter's variable name
     * would be "my_example"
     */
    public function getTemplateVariableName(): string
    {
        if (!$this->templateVariableName) {
            $this->templateVariableName = static::snakeCasePresenterName();
        }

        return $this->templateVariableName;
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
            $parts = explode('\\', static::class);
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

    /**
     * Calculates the presenter name for use in referencing the template and
     * the name of the variable to be passed to that template.
     * e.g. App\Ds2013\Organism\ExampleThingPresenter becomes example_thing
     *
     */
    private static function snakeCasePresenterName(): string
    {
        $namespaceEnd = strrpos(static::class, '\\');
        $shortClassName = substr(static::class, $namespaceEnd + ($namespaceEnd ? 1: 0));

        // Trim the class name from the word 'Presenter'
        $presenterName = substr($shortClassName, 0, strpos($shortClassName, 'Presenter'));

        return strtolower(preg_replace(
            ["/([A-Z]+)/", "/_([A-Z]+)([A-Z][a-z])/"],
            ["_$1", "_$1_$2"],
            lcfirst($presenterName)
        ));
    }
}
