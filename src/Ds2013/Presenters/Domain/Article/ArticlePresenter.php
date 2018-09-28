<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Domain\Article;

use App\Ds2013\Presenter;
use App\ExternalApi\Isite\Domain\Article;

class ArticlePresenter extends Presenter
{
    protected $options = [
        'heading_level' => 'h2',
        'show_synopsis' => true,
    ];

    /** @var Article */
    private $article;

    public function __construct(Article $article, array $options = [])
    {
        parent::__construct($options);
        $this->article = $article;
    }

    public function getArticle(): Article
    {
        return $this->article;
    }
}
