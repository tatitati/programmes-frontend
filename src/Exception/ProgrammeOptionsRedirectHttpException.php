<?php
declare(strict_types = 1);
namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Symfony provides several exceptions that result in the Framework emiting a
 * response, but it doesn't provide one for 301/302 redirects.
 *
 * This Exception takes a location and status code, and shall send a redirect
 * request to that location. This being an exception allows us to trigger the
 * redirect in places that are usually not able to return a response.
 */
class ProgrammeOptionsRedirectHttpException extends HttpException
{
    public function __construct(string $location, int $status = 301, \Exception $previous = null, $code = 0)
    {
        parent::__construct(
            $status,
            sprintf('Programme Options has triggered a "%s" redirect to "%s"', $status, $location),
            $previous,
            ['location' => $location, 'cache-control' => 'public, max-age=3600'],
            $code
        );
    }
}
