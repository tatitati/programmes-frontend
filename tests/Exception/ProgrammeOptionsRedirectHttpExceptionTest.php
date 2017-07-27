<?php
declare(strict_types = 1);
namespace Tests\App\Exception;

use App\Exception\ProgrammeOptionsRedirectHttpException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProgrammeOptionsRedirectHttpExceptionTest extends TestCase
{
    public function testConstructor()
    {
        $exception = new ProgrammeOptionsRedirectHttpException('http://example.com', 301);
        $this->assertInstanceOf(HttpException::class, $exception);
        $this->assertSame(301, $exception->getStatusCode());

        $expectedHeaders = [
            'location' => 'http://example.com',
            'cache-control' => 'public, max-age=3600',
        ];

        $this->assertSame($expectedHeaders, $exception->getHeaders());
        $this->assertSame(
            'Programme Options has triggered a "301" redirect to "http://example.com"',
            $exception->getMessage()
        );
    }
}
