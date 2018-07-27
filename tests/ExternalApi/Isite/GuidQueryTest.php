<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Isite;

use App\ExternalApi\Isite\GuidQuery;
use PHPUnit\Framework\TestCase;

class GuidQueryTest extends TestCase
{
    public function testBlogMetadataQuery()
    {
        $query = (new GuidQuery())
            ->setDepth(2)
            ->setContentId('AAAAAAAAAAAAA')
            ->setAllowNonLive(true)
            ->setPreview(true);

        $this->assertEquals(
            '/content?' .
                'depth=2' .
                '&contentId=AAAAAAAAAAAAA' .
                '&allowNonLive=true' .
                '&preview=true',
            $query->getPath()
        );
    }
}
