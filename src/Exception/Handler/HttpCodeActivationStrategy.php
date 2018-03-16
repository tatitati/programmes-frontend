<?php

namespace App\Exception\Handler;

use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Activation strategy that ignores certain HTTP codes.
 */
class HttpCodeActivationStrategy extends ErrorLevelActivationStrategy
{
    private $excludedCodes;
    private $requestStack;

    public function __construct(RequestStack $requestStack, array $excludedCodes, $actionLevel)
    {
        parent::__construct($actionLevel);

        $this->requestStack = $requestStack;
        $this->excludedCodes = $excludedCodes;
    }

    public function isHandlerActivated(array $record)
    {
        $isActivated = parent::isHandlerActivated($record);

        if ($isActivated && isset($record['context']['exception']) && $record['context']['exception'] instanceof HttpException) {
            return !in_array($record['context']['exception']->getStatusCode(), $this->excludedCodes);
        }

        return $isActivated;
    }
}
