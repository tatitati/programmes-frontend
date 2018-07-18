<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Isite\Domain;

use App\ExternalApi\Isite\Domain\Profile;
use PHPUnit\Framework\TestCase;

class ProfileTest extends TestCase
{
    public function testSlugify()
    {
        $profile = new Profile('title', 'anything', 'anything', 'anything');
        $this->assertSame('title', $profile->getSlug());

        $profile = new Profile('Title-of an    article!@£$%^&*()', 'anything', 'anything', 'anything');
        $this->assertSame('title-of-an-article', $profile->getSlug());

        $profile = new Profile('A title~with "quotes" that should/ strip', 'anything', 'anything', 'anything');
        $this->assertSame('a-title-with-quotes-that-should-strip', $profile->getSlug());

        $profile = new Profile('A title~with apostrophe\'s that should/ strip', 'anything', 'anything', 'anything');
        $this->assertSame('a-title-with-apostrophes-that-should-strip', $profile->getSlug());

        $profile = new Profile('A cööl titlé wîth accènts', 'anything', 'anything', 'anything');
        $this->assertSame('a-cool-title-with-accents', $profile->getSlug());
    }
}
