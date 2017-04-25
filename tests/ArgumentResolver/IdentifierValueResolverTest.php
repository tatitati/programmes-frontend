<?php
declare(strict_types = 1);
namespace Tests\App\ArgumentResolver;

use App\ArgumentResolver\IdentifierValueResolver;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Sid;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpFoundation\Request;

class IdentifierValueResolverTest extends TestCase
{
    /** @var ArgumentResolver */
    private static $resolver;

    public static function setUpBeforeClass()
    {
        self::$resolver = new ArgumentResolver(null, [new IdentifierValueResolver()]);
    }

    public function testResolvePid()
    {
        $request = Request::create('/');
        $request->attributes->set('pid', 'b0000001');
        $controller = function (Pid $pid) {
        };

        $this->assertEquals(
            [new Pid('b0000001')],
            self::$resolver->getArguments($request, $controller)
        );
    }

    public function testResolveSid()
    {
        $request = Request::create('/');
        $request->attributes->set('sid', 'bbc_one_london');
        $controller = function (Sid $sid) {
        };

        $this->assertEquals(
            [new Sid('bbc_one_london')],
            self::$resolver->getArguments($request, $controller)
        );
    }
}
