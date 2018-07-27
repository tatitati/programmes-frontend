<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Isite\Domain;

use App\Builders\ProfileBuilder;
use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
{
    /**
     * @dataProvider titlesAndSlugsProvider
     */
    public function testSlugify($withTitle, $expectSlug)
    {
        $profile = ProfileBuilder::any()->with(['title' => $withTitle])->build();
        $this->assertSame($expectSlug, $profile->getSlug());
    }

    public function titlesAndSlugsProvider()
    {
        return [
            'Alpha:' => ['title', 'title'],
            'Special-chars:' => ['Title-of an    article!@£$%^&*()', 'title-of-an-article'],
            'Quotes:' => ['A title~with "quotes" that should/ strip', 'a-title-with-quotes-that-should-strip'],
            'Apostrophes:' => ['A title~with apostrophe\'s that should/ strip', 'a-title-with-apostrophes-that-should-strip'],
            'Accents:' => ['A cööl titlé wîth accènts', 'a-cool-title-with-accents'],
        ];
    }
}
