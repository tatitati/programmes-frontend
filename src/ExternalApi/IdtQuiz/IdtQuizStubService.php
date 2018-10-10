<?php

namespace App\ExternalApi\IdtQuiz;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

class IdtQuizStubService extends IdtQuizService
{
    public function getQuizContentPromise(string $quizId): PromiseInterface
    {
        return new FulfilledPromise(['']);
    }
}
