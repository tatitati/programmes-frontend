<?php
declare(strict_types = 1);

namespace Tests\App\Fixture;

use App\Fixture\UrlMatching\UrlReverseEvangelist;
use PHPUnit\Framework\TestCase;

class UrlReverseEvangelistTest extends TestCase
{
    /**
     * @dataProvider isFixturableDataProvider
     */
    public function testIsFixturable($url, $expected)
    {
        $urlReverseEvangelist = new UrlReverseEvangelist();
        $this->assertEquals($expected, $urlReverseEvangelist->isFixturable($url));
    }

    public function isFixturableDataProvider()
    {
        return [
            ['https://navigation.api.bbci.co.uk/api', true],
            ['https://navigation.test.api.bbci.co.uk/api', true],
            ['https://navigation.int.api.bbci.co.uk/api', true],
            ['https://navigation.int.bbci.co.uk/api', false],
            ['http://navigation.api.bbci.co.uk/api', true],
            ['https://navigation.wibble.api.bbci.co.uk/api', false],
            ['http://navigationz.api.bbci.co.uk/api', false],
            ['https://branding.files.bbci.co.uk/branding/live/projects/br-00397.json', true],
            ['https://branding.test.files.bbci.co.uk/branding/test/projects/br-00397.json', true],
            ['https://branding.test.files.bbci.co.uk/branding/int/projects/br-00397.json?something=wombat', true],
            ['https://branding.test.files.bbci.co.uk:443/branding/int/projects/br-00397.json?something=wombat', true],
            ['http://branding.test.files.bbci.co.uk:80/branding/int/projects/br-00397.json?something=wombat', true],
            ['http://www.google.com', false],
        ];
    }

    /**
     * @dataProvider envAgnosticUrlProvider
     */
    public function testMakeEnvAgnostic($url, $expected)
    {
        $urlReverseEvangelist = new UrlReverseEvangelist();
        $this->assertEquals($expected, $urlReverseEvangelist->makeEnvAgnostic($url));
    }

    public function envAgnosticUrlProvider()
    {
        return [
            ['https://navigation.api.bbci.co.uk/api', 'https://navigation.api.bbci.co.uk/api'],
            ['https://navigation.test.api.bbci.co.uk/api', 'https://navigation.api.bbci.co.uk/api'],
            ['https://navigation.int.api.bbci.co.uk/api', 'https://navigation.api.bbci.co.uk/api'],
            ['https://navigation.int.bbci.co.uk/api', 'https://navigation.int.bbci.co.uk/api'],
            ['http://navigation.api.bbci.co.uk/api', 'http://navigation.api.bbci.co.uk/api'],
            ['https://navigation.wibble.api.bbci.co.uk/api', 'https://navigation.wibble.api.bbci.co.uk/api'],
            ['http://navigationz.api.bbci.co.uk/api', 'http://navigationz.api.bbci.co.uk/api'],
            ['https://branding.files.bbci.co.uk/branding/live/projects/br-00397.json', 'https://branding.files.bbci.co.uk/branding/live/projects/br-00397.json'],
            ['https://branding.test.files.bbci.co.uk/branding/test/projects/anything/here?asadsad=asdfasgf', 'https://branding.files.bbci.co.uk/branding/live/projects/anything/here?asadsad=asdfasgf'],
            ['https://branding.test.files.bbci.co.uk/branding/test/projects/br-00397.json', 'https://branding.files.bbci.co.uk/branding/live/projects/br-00397.json'],
            ['https://branding.test.files.bbci.co.uk:443/branding/int/projects/br-00397.json?something=wombat', 'https://branding.files.bbci.co.uk/branding/live/projects/br-00397.json?something=wombat'],
            ['http://www.google.com', 'http://www.google.com'],
        ];
    }
}
