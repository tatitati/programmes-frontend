<?php
declare(strict_types = 1);

namespace App\ExternalApi\Isite\Mapper;

use App\Controller\Helpers\IsiteKeyHelper;
use BBC\ProgrammesPagesService\Service\CoreEntitiesService;

class MapperFactory
{
    protected $instances = [];

    /** @var CoreEntitiesService */
    private $coreEntitiesService;

    private $isiteKeyHelper;

    public function __construct(IsiteKeyHelper $isiteKeyHelper, CoreEntitiesService $coreEntitiesService)
    {
        $this->isiteKeyHelper = $isiteKeyHelper;
        $this->coreEntitiesService = $coreEntitiesService;
    }

    public function createArticleMapper(): ArticleMapper
    {
        return $this->findMapper(ArticleMapper::class);
    }

    public function createContentBlockMapper(): ContentBlockMapper
    {
        if (!isset($this->instances[ContentBlockMapper::class])) {
            $this->instances[ContentBlockMapper::class] = new ContentBlockMapper($this, $this->isiteKeyHelper, $this->coreEntitiesService);
        }
        return $this->instances[ContentBlockMapper::class];
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
