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
        $self = new static();
        return $self->build();
    }

    public static function anyWith(array $withValues)
    {
        $self = new static();
        $self->validateKeys($withValues);

        $self->blueprintConstructorTarget = array_merge($self->blueprintConstructorTarget, $withValues);

        return $self->build();
    }

    protected function build()
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
