<?php
declare(strict_types = 1);

namespace Tests\App\DsShared\Utilities\Synopsis;

use App\DsShared\Utilities\Synopsis\SynopsisPresenter;
use BBC\ProgrammesPagesService\Domain\ValueObject\Synopses;
use PHPUnit\Framework\TestCase;

class SynopsisPresenterTest extends TestCase
{
    public function testNeedsShorterSynopsis()
    {
        $synopses = new Synopses('a', 'ab', 'abc');

        $presenter = new SynopsisPresenter($synopses, 2);
        $this->assertTrue($presenter->needsShorterSynopsis());

        $presenter = new SynopsisPresenter($synopses, 3);
        $this->assertFalse($presenter->needsShorterSynopsis());
    }

    public function testNeedsShorterSynopsisMB()
    {
        $synopses = new Synopses('অ', 'অং', 'অংশ');

        $presenter = new SynopsisPresenter($synopses, 2);
        $this->assertTrue($presenter->needsShorterSynopsis());

        $presenter = new SynopsisPresenter($synopses, 3);
        $this->assertFalse($presenter->needsShorterSynopsis());
    }

    public function testGetLongestSynopsis()
    {
        $synopses = new Synopses('a', 'ab', '');

        $presenter = new SynopsisPresenter($synopses, 123);
        $this->assertSame(['ab'], $presenter->getLongestSynopsis());
    }

    public function testGetShortSynopsis()
    {
        $synopses = new Synopses('a', 'ab', 'abc');

        $presenter = new SynopsisPresenter($synopses, 2);
        $this->assertSame(['ab'], $presenter->getShortSynopsis());

        $presenter = new SynopsisPresenter($synopses, 1);
        $this->assertSame(['a'], $presenter->getShortSynopsis());
    }

    public function testTagsAreStripped()
    {
        $synopses = new Synopses('a', 'ab', 'abc<input/>');

        $presenter = new SynopsisPresenter($synopses, 2);
        $this->assertSame(['abc'], $presenter->getLongestSynopsis());
    }

    public function testSpecialHtmlEntities()
    {
        $synopses = new Synopses('a', 'ab', 'abc&');

        $presenter = new SynopsisPresenter($synopses, 2);
        $this->assertSame(['abc&amp;'], $presenter->getLongestSynopsis());
    }

    public function testTwoNewlinesCreatesParagraph()
    {
        $longSynopsis = <<<LS
abc

def
LS;
        $synopses = new Synopses('a', 'ab', $longSynopsis);

        $presenter = new SynopsisPresenter($synopses, 2);
        $this->assertSame(['abc', 'def'], $presenter->getLongestSynopsis());
    }

    public function testSingleNewlineIsPreserved()
    {
        $longSynopsis = <<<LS
abc
def
LS;
        $synopses = new Synopses('a', 'ab', $longSynopsis);

        $presenter = new SynopsisPresenter($synopses, 2);
        $this->assertSame(['abc<br />def'], $presenter->getLongestSynopsis());
    }

    public function testDoubleNewlineWithSpacesBetweenCreatesParagraph()
    {
        $longSynopsis = <<<LS
abc
 
def
LS;
        $synopses = new Synopses('a', 'ab', $longSynopsis);

        $presenter = new SynopsisPresenter($synopses, 2);
        $this->assertSame(['abc', 'def'], $presenter->getLongestSynopsis());
    }
}
