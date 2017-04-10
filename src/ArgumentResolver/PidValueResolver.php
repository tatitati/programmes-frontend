<?php

namespace App\ArgumentResolver;

use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;

class PidValueResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return Pid::class == $argument->getType() && !$argument->isVariadic() && $request->attributes->has($argument->getName());
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        yield new Pid($request->attributes->get($argument->getName()));
    }
}
