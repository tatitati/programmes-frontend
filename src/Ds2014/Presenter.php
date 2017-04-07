<?php
declare(strict_types = 1);
namespace App\Ds2014;

use stdClass;

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
        return '@Ds2014/' . $this->getClassPath() . self::TWIG_SUFFIX;
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

    /**
     * Convert the options to an object and return
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get or generate a unique ID. Once generated once the same one will be used
     * Only used for unique references in a single render
     */
    public function getUniqueID(): string
    {
        if (!$this->uniqueId) {
            $parts = explode('\\', get_called_class());
            $class = end($parts);
            $this->uniqueId = 'ds2014-' . $class . '-' . mt_rand();
        }

        return $this->uniqueId;
    }

    /**
     * Auto calculates the path to the Presenter Class
     * So that every presenter doesn't need to restate its template path and base
     */
    private function getClassPath(): string
    {
        $className = get_called_class();
        // strip off the namespace
        $classPath = str_replace('App\Ds2014\\', '', $className);
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
