<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Isite;

use App\ExternalApi\Isite\SearchQuery;
use PHPUnit\Framework\TestCase;

class SearchQueryTest extends TestCase
{
    public function testMetadataQuery()
    {
        $searchQuery = new SearchQuery();
        $searchQuery->setNamespace('profile', 'progs-eastenders')
            ->setProject('progs-eastenders')
            ->setDepth(0)// no depth as this is an aggregation - no need to fetch parents or content blocks
            ->setQuery([
                'and' => [
                    ['profile:parent_pid', '=', 'b006m86d'],
                    [
                        'not' => [
                            ['profile:parent', 'contains', 'urn:isite'],
                        ],
                    ],
                ],
            ])
            ->setSort([
                [
                    'elementPath' => '/profile:form/profile:metadata/profile:position',
                    'type' => 'numeric',
                    'direction' => 'asc',
                ],
                [
                    'elementPath' => '/profile:form/profile:metadata/profile:title',
                    'direction' => 'asc',
                ],
            ])
            ->setPage(1)
            ->setPageSize(48);

        $expected = (object) [
            'namespaces' => (object) ['profile' => 'https://production.bbc.co.uk/isite2/project/progs-eastenders/programmes-profile'],
            'project' => 'progs-eastenders',
            'depth' => 0,
            'query' => [
                'and' => [
                    ['profile:parent_pid', '=', 'b006m86d'],
                    [
                        'not' => [
                            ['profile:parent', 'contains', 'urn:isite'],
                        ],
                    ],
                ],
            ],
            'sort' => [
                [
                    'elementPath' => '/profile:form/profile:metadata/profile:position',
                    'type' => 'numeric',
                    'direction' => 'asc',
                ],
                [
                    'elementPath' => '/profile:form/profile:metadata/profile:title',
                    'direction' => 'asc',
                ],
            ],
            'page' => '1',
            'pageSize' => '48',
        ];

        $this->assertAttributeEquals($expected, 'q', $searchQuery);

        $this->assertEquals('/search?q=' . urlencode(json_encode($expected)), $searchQuery->getPath());
    }
}
