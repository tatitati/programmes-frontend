<?php
declare(strict_types=1);

namespace App\ExternalApi\Recipes\Service;

use App\ExternalApi\Recipes\Domain\RecipesApiResult;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class RecipesStubService extends RecipesService
{
    public function fetchRecipesByPid(string $pid, int $limit = 4, int $page = 1): PromiseInterface
    {
        return new FulfilledPromise(new RecipesApiResult([], 0));
    }
}
