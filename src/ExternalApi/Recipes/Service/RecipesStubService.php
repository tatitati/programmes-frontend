<?php
declare(strict_types=1);

namespace App\ExternalApi\Recipes\Service;

use App\ExternalApi\Recipes\Domain\RecipesApiResult;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;

class RecipesStubService extends RecipesService
{
    public function fetchRecipesByPid(string $pid, int $limit = 4, int $page = 1): RecipesApiResult
    {
        return new RecipesApiResult([], 0);
    }
}
