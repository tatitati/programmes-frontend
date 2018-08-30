<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Mapper;

use App\Controller\Helpers\IsiteKeyHelper;

class MapperFactory
{
    protected $instances = [];

    private $isiteKeyHelper;

    public function __construct(IsiteKeyHelper $isiteKeyHelper)
    {
        $this->isiteKeyHelper = $isiteKeyHelper;
    }

    public function createArticleMapper(): ArticleMapper
    {
        return $this->findMapper(ArticleMapper::class);
    }

    public function createContentBlockMapper(): ContentBlockMapper
    {
        return $this->findMapper(ContentBlockMapper::class);
    }

    public function createKeyFactMapper(): KeyFactMapper
    {
        return $this->findMapper(KeyFactMapper::class);
    }

    public function createProfileMapper(): ProfileMapper
    {
        return $this->findMapper(ProfileMapper::class);
    }

    public function createRowMapper(): RowMapper
    {
        return $this->findMapper(RowMapper::class);
    }

    private function findMapper(string $mapperType)
    {
        if (!isset($this->instances[$mapperType])) {
            $this->instances[$mapperType] = new $mapperType($this, $this->isiteKeyHelper);
        }
        return $this->instances[$mapperType];
    }
}
