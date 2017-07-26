<?php
declare(strict_types = 1);

namespace Tests\App\Twig;

use App\Translate\TranslateProvider;
use App\Twig\TranslateAndTimeExtension;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use RMP\Translate\Translate;

class TranslateAndTimeExtensionTest extends TestCase
{
    private $mockTranslate;

    /** @var  TranslateAndTimeExtension */
    private $translateAndTimeExtension;

    public function setUp()
    {
        $this->mockTranslate = $this->createMock(Translate::class);
        $translateProvider = $this->createMock(TranslateProvider::class);
        $translateProvider->method('getTranslate')->willReturn($this->mockTranslate);
        $this->translateAndTimeExtension = new TranslateAndTimeExtension($translateProvider);
    }

    public function tearDown()
    {
        ApplicationTime::blank();
    }

    public function testTrWrapper()
    {
        $this->mockTranslate->expects($this->once())
            ->method('translate')
            ->with('wibble', ['%count%' => 'eleventy'], 110)
            ->willReturn('Utter Nonsense');
        $result = $this->translateAndTimeExtension->trWrapper('wibble', ['%count%' => 'eleventy'], 110);
        $this->assertEquals('Utter Nonsense', $result);
    }

    /**
     * @dataProvider localDateDataProvider
     */
    public function testLocalDate($time, $timeZone, $expected)
    {
        $dateTime = new DateTime($time, new DateTimeZone('UTC'));
        ApplicationTime::setTime($dateTime->getTimestamp());
        ApplicationTime::setLocalTimeZone($timeZone);
        $this->assertEquals(
            $expected,
            $this->translateAndTimeExtension->localDate(ApplicationTime::getTime(), 'Y-m-d H:i:s')
        );
    }

    public function localDateDataProvider()
    {
        return [
            ['2017-06-01 13:00:00', 'Europe/Berlin', '2017-06-01 15:00:00'],
            ['2017-06-01 13:00:00', 'Europe/London', '2017-06-01 14:00:00'],
            ['2017-01-01 13:00:00', 'Europe/London', '2017-01-01 13:00:00'],
            ['2017-06-01 13:00:00', 'UTC', '2017-06-01 13:00:00'],
        ];
    }

    public function testTimeZoneNoteEuropeLondon()
    {
        $dateTime = new DateTime('2017-06-01 13:00:00', new DateTimeZone('UTC'));
        ApplicationTime::setTime($dateTime->getTimestamp());
        ApplicationTime::setLocalTimeZone('Europe/London');
        $this->assertEquals(
            '',
            $this->translateAndTimeExtension->timeZoneNote(ApplicationTime::getTime())
        );
    }

    public function testTimeZoneNoteUtc()
    {
        $dateTime = new DateTime('2017-06-01 13:00:00', new DateTimeZone('UTC'));
        ApplicationTime::setTime($dateTime->getTimestamp());
        ApplicationTime::setLocalTimeZone('UTC');
        $this->mockTranslate->expects($this->once())
            ->method('translate')
            ->with('gmt')
            ->willReturn('GMT');

        $this->assertEquals(
            '<span class="timezone--note">GMT</span>',
            $this->translateAndTimeExtension->timeZoneNote(ApplicationTime::getTime())
        );
    }

    public function testTimeZoneNoteIntl()
    {
        $dateTime = new DateTime('2017-06-01 13:00:00', new DateTimeZone('UTC'));
        ApplicationTime::setTime($dateTime->getTimestamp());
        ApplicationTime::setLocalTimeZone('Pacific/Chatham');
        $this->mockTranslate->expects($this->once())
            ->method('translate')
            ->with('gmt')
            ->willReturn('GMT');

        $this->assertEquals(
            '<span class="timezone--note">GMT+12:45</span>',
            $this->translateAndTimeExtension->timeZoneNote(ApplicationTime::getTime())
        );
    }

    public function testTimeZoneNoteIntlNegative()
    {
        $dateTime = new DateTime('2017-06-12 12:00:00', new DateTimeZone('UTC'));
        ApplicationTime::setTime($dateTime->getTimestamp());
        ApplicationTime::setLocalTimeZone('Pacific/Marquesas');
        $this->mockTranslate->expects($this->once())
            ->method('translate')
            ->with('gmt')
            ->willReturn('GMT');

        $this->assertEquals(
            '<span class="timezone--note">GMT-09:30</span>',
            $this->translateAndTimeExtension->timeZoneNote(ApplicationTime::getTime())
        );
    }
}
