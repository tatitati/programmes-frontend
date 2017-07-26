<?php

namespace App\ArgumentResolver;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Sid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class IdentifierValueResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return in_array($argument->getType(), [Pid::class, Sid::class]) && !$argument->isVariadic() && $request->attributes->has($argument->getName());
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $type = $argument->getType();
        yield new $type($request->attributes->get($argument->getName()));
    }
}
