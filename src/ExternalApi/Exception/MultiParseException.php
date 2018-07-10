<?php
declare(strict_types = 1);

namespace App\ExternalApi\Exception;

use Exception;

class MultiParseException extends \RuntimeException
{
    /** @var string */
    private $responseKey;

    public function __construct(string $responseKey, $message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->responseKey = $responseKey;
    }

    public function getResponseKey(): string
    {
        return $this->responseKey;
    }
}
