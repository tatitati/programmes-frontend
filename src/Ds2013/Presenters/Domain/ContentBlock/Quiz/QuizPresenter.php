<?php
declare(strict_types=1);

namespace App\Ds2013\Presenters\Domain\ContentBlock\Quiz;

use App\Ds2013\Presenters\Domain\ContentBlock\ContentBlockPresenter;
use App\ExternalApi\Isite\Domain\ContentBlock\Quiz;

class QuizPresenter extends ContentBlockPresenter
{
    public function __construct(Quiz $tableBlock, bool $inPrimaryColumn, array $options = [])
    {
        parent::__construct($tableBlock, $inPrimaryColumn, $options);
    }
}
