<?php
declare(strict_types = 1);
namespace App\ArgumentResolver;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Sid;
use Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class IdentifierValueResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return in_array($argument->getType(), [Pid::class, Sid::class]) && !$argument->isVariadic() && $request->attributes->has($argument->getName());
    }

    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        $type = $argument->getType();
        yield new $type($request->attributes->get($argument->getName()));
    }
}
