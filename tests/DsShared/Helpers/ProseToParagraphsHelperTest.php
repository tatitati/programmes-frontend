<?php
declare(strict_types = 1);

namespace Tests\App\DsShared\Helpers;

use App\DsShared\Helpers\ProseToParagraphsHelper;
use App\Translate\TranslateProvider;
use PHPUnit\Framework\TestCase;
use RMP\Translate\Translate;

class ProseToParagraphsHelperTest extends TestCase
{
    /** @var ProseToParagraphsHelper */
    private $helper;

    /** @var string */
    private $mockText;

    public function setUp()
    {
        $mockTranslate = $this->createMock(Translate::class);
        $mockTranslateProvider = $this->createMock(TranslateProvider::class);
        $mockTranslateProvider->method('getTranslate')->willReturn($mockTranslate);
        $this->helper = new ProseToParagraphsHelper($mockTranslateProvider);
        $space = ' ';
        $this->mockText = "Lorem ipsum dolor sit
amet, consectetur adipiscing elit. Praesent vitae porta est.

Donec non lectus leo. Lorem ipsum dolor sit amet.


$space

And a third paragraph of stuff.";
    }

    public function testDefaultProseToParagraphsWithoutTruncating()
    {
        $this->assertEquals('<p>Lorem ipsum dolor sit<br />amet, consectetur adipiscing elit. Praesent vitae porta est.</p><p>Donec non lectus leo. Lorem ipsum dolor sit amet.</p><p>And a third paragraph of stuff.</p>', $this->helper->proseToParagraphs($this->mockText));
    }

    public function testProseToParagraphsWithTextShorterThanMaxTruncateLength()
    {
        $this->assertEquals('<p>Lorem ipsum dolor sit<br />amet, consectetur adipiscing elit. Praesent vitae porta est.</p><p>Donec non lectus leo. Lorem ipsum dolor sit amet.</p><p>And a third paragraph of stuff.</p>', $this->helper->proseToParagraphs($this->mockText, 500, 'testID'));
    }

    public function testProseToParagraphsAndTruncateToMaxLengthWithNearestExactWord()
    {
        $mockTranslate = $this->createMock(Translate::class);
        $mockTranslate->expects($this->at(0))->method('translate')->with('show_more')->willReturn('Show more');
        $mockTranslate->expects($this->at(1))->method('translate')->with('show_less')->willReturn('Show less');
        $mockTranslateProvider = $this->createMock(TranslateProvider::class);
        $mockTranslateProvider->method('getTranslate')->willReturn($mockTranslate);
        $helper = new ProseToParagraphsHelper($mockTranslateProvider);

        $this->assertEquals('<div class="ml">
    <input class="ml__status" id="ml-testID" type="checkbox" checked />
    <div class="ml__content prose text--prose">
        <p>Lorem ipsum dolor sit<br />amet,<span class="ml__ellipsis"><span class="ml__hidden"> consectetur adipiscing elit. Praesent vitae porta est.</span></span></p><p class="ml__hidden">Donec non lectus leo. Lorem ipsum dolor sit amet.</p><p class="ml__hidden">And a third paragraph of stuff.</p>
    </div>
    <label class="ml__button br-pseudolink" for="ml-testID">
        <span class="ml__label--more">Show more</span> <span class="ml__label--sep"> / </span> <span class="ml__label--less">Show less</span>
    </label>
</div>', $helper->proseToParagraphs($this->mockText, 50, 'testID'));
    }

    public function testProseToParagraphsEscapesHtml()
    {
        $text = "Some text\nA large quango of walruses\n\nRemember Kids 4>11>eleventy <a href=\"http://www.sanity.com\">Bibble Bibble purple aardvark</a>";
        $this->assertEquals(
            '<p>Some text<br />A large quango of walruses</p><p>Remember Kids 4&gt;11&gt;eleventy Bibble Bibble purple aardvark</p>',
            $this->helper->proseToParagraphs($text)
        );
    }
}
