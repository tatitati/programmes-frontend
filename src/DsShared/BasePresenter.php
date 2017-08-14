<?php
declare(strict_types = 1);
namespace App\DsShared;

use App\Exception\InvalidOptionException;

/**
 * Base Class for a all design system Presenters
 */
abstract class BasePresenter
{
    /** @var array */
    protected $options = [];

    /** @var string */
    protected $templatePath;

    /** @var string */
    private $templateVariableName;

    /** @var string */
    protected $uniqueId;

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
        $this->validateOptions($this->options);
    }

    /**
     * Full path to the twig template the presenter class renders
     */
    public function getTemplatePath(): string
    {
        if (!$this->templatePath) {
            $classPath = str_replace('App\\Ds' . $this->getDesignSystem() . '\\', '', static::class);

            $parts = implode('/', explode('\\', $classPath, -1));
            $pathPrefix = '@Ds' . $this->getDesignSystem() . '/' . $parts . ($parts ? '/' : '');

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
        if (array_key_exists($keyOption, $this->options)) {
            return $this->options[$keyOption];
        }

        throw new InvalidOptionException(sprintf(
            'Called getOption with an invalid value. Expected one of %s but got "%s"',
            '"' . implode('", "', array_keys($this->options)) . '"',
            $keyOption
        ));
    }

    /**
     * The name of the design system
     *
     * @return string
     */
    abstract protected function getDesignSystem(): string;

    /**
     * Get or generate a unique ID. Once generated once the same one will be used
     * Only used for unique references in a single render
     */
    protected function getUniqueID(): string
    {
        if (!$this->uniqueId) {
            $parts = explode('\\', static::class);
            $class = end($parts);
            $this->uniqueId = 'ds-' . strtolower($this->getDesignSystem()) . '-' . $class . '-' . mt_rand();
        }

        return $this->uniqueId;
    }

    /**
     * Validate options. Should be overridden.
     */
    protected function validateOptions(array $options): void
    {
    }

    /**
     * Calculates the presenter name for use in referencing the template and
     * the name of the variable to be passed to that template.
     * e.g. App\DsShared\Organism\ExampleThingPresenter becomes example_thing
     *
     */
    protected static function snakeCasePresenterName(): string
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
