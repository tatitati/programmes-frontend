<?php

namespace App\Builders;

use Exception;
use ReflectionClass;

abstract class AbstractBuilder
{
    protected $classTarget;
    protected $blueprintConstructorTarget;

    public static function any()
    {
        return new static();
    }

    public function with(array $withValues)
    {
        $this->validateKeys($withValues);
        $this->blueprintConstructorTarget = array_merge($this->blueprintConstructorTarget, $withValues);
        return $this;
    }

    public function build()
    {
        $class = new ReflectionClass($this->classTarget);
        return $class->newInstanceArgs($this->blueprintConstructorTarget);
    }

    protected function validateKeys(array $withValues)
    {
        $withKeys = array_keys($withValues);
        $supportedKeys = array_keys($this->blueprintConstructorTarget);

        foreach ($withKeys as $key) {
            if (!in_array($key, $supportedKeys)) {
                throw new Exception('You configured the builder to support some named keys in order to use the target constructor. However now
                is being used an unknown key. Wrong param name: ' . $key . '. When building a ' . $this->classTarget . ' object');
            }
        }
    }
}
