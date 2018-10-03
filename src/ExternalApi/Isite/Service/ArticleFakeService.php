<?php

namespace App\ExternalApi\Isite\Service;

use App\ExternalApi\Isite\IsiteResult;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class ArticleFakeService extends ArticleService
{
    public function getByContentId(string $guid, bool $preview = false): PromiseInterface
    {
        $isiteResult = new IsiteResult(1, 10, 0, []);

        return new FulfilledPromise($isiteResult);
    }

    public function setChildrenOn(
        array $profiles,
        string $project,
        int $page = 1,
        int $limit = 48
    ): PromiseInterface {
        return new FulfilledPromise([new IsiteResult(1, 1, 0, [])]);
    }
}
