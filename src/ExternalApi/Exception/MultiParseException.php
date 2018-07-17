<?php
declare(strict_types = 1);

namespace App\ExternalApi\Exception;

use Exception;

class MultiParseException extends \RuntimeException
{
    /** @var string|int */
    private $responseKey;

    /**
     * MultiParseException constructor.
     * @param int|string $responseKey
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($responseKey, $message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->responseKey = $responseKey;
    }

    /**
     * @return string|int
     */
    public function getResponseKey()
    {
        return $this->responseKey;
    }
}
