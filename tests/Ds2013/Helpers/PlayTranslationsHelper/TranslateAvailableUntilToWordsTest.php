<?php
declare(strict_types = 1);
namespace Tests\App\Ds2013\Helpers\PlayTranslationsHelper;

use App\Ds2013\Helpers\AvailabilityTimeToWordsHelper;
use App\Ds2013\Helpers\PlayTranslationsHelper;
use BBC\ProgrammesPagesService\Domain\ApplicationTime;
use BBC\ProgrammesPagesService\Domain\Entity\Episode;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\TestCase;
use RMP\Translate\Translate;
use App\Translate\TranslateProvider;
use DateTimeImmutable;

class TranslateAvailableUntilToWordsTest extends TestCase
{
    private $mockTranslate;

    /** @var PlayTranslationsHelper */
    private $helper;

    public function setUp()
    {
        $this->mockTranslate = $this->createMock(Translate::class);
        $this->mockTranslate->method('getLocale')->willReturn('en_GB');
        $mockTranslateProvider = $this->createMock(TranslateProvider::class);
        $mockTranslateProvider->method('getTranslate')->willReturn($this->mockTranslate);
        $this->helper = new PlayTranslationsHelper($mockTranslateProvider);
        ApplicationTime::setTime((new Chronos('2017-06-01T12:00:00'))->timestamp);
    }

    /**
     * @dataProvider indefiniteAvailabilityProvider
     */
    public function testTranslateIndefiniteAvailability($mediaType, $endDateTime, $expectedTranslationPlaceholder)
    {
        $programmeItem = $this->makeProgrammeItem($mediaType, $endDateTime);
        $this->mockTranslate->expects($this->once())
            ->method('translate')
            ->with($expectedTranslationPlaceholder)
            ->willReturn('An Indefinite Translation');

        $result = $this->helper->translateAvailableUntilToWords($programmeItem);
        $this->assertEquals('An Indefinite Translation', $result);
    }

    public function indefiniteAvailabilityProvider()
    {
        return [
            ['audio', null, 'iplayer_listen_now'],
            ['video', null, 'iplayer_watch_now'],
            ['radio', new DateTimeImmutable('2018-06-02T12:10:00'), 'iplayer_listen_now'],
            ['tv', new DateTimeImmutable('2077-07-01T12:00:00'), 'iplayer_watch_now'],
            ['unknown', new DateTimeImmutable('2077-07-01T12:00:00'), 'iplayer_play_now'],
        ];
    }

    /**
     * @dataProvider availabilityToWordsDataProvider
     */
    public function testAvailabilityToWords($mediaType, $endDateTime, $expectedTranslationPlaceholder, $expectedCount)
    {
        $programmeItem = $this->makeProgrammeItem($mediaType, $endDateTime);
        $this->mockTranslate->expects($this->once())
            ->method('translate')
            ->with($expectedTranslationPlaceholder, ['%count%' => $expectedCount])
            ->willReturn('The Corretc Translation');

        $result = $this->helper->translateAvailableUntilToWords($programmeItem);
        // @TODO work out localDate stuff and append it, it's incomplete at the mo so not testing
        $this->assertStringStartsWith('The Corretc Translation', $result);
    }

    public function availabilityToWordsDataProvider()
    {
        return [
            ['audio', new DateTimeImmutable('2017-06-01T12:01:00'), 'iplayer_listen_remaining_minutes', 1],
            ['video', new DateTimeImmutable('2017-06-01T12:59:00'), 'iplayer_watch_remaining_minutes', 59],
            ['radio', new DateTimeImmutable('2017-06-01T13:10:00'), 'iplayer_listen_remaining_hours', 1],
            ['unknown', new DateTimeImmutable('2017-06-04T14:10:00'), 'iplayer_play_remaining_days', 3],
            ['tv', new DateTimeImmutable('2018-01-01T12:00:01'), 'iplayer_watch_remaining_months', 7],
        ];
    }

    /**
     * @dataProvider translatePlayLiveDataProvider
     */
    public function testTranslatePlayLive($mediaType, $expectedTrPrefix)
    {
        $programmeItem = $this->makeProgrammeItem($mediaType, null);
        $this->mockTranslate->expects($this->once())
            ->method('translate')
            ->with($expectedTrPrefix)
            ->willReturn('The Corretc Translation');

        $result = $this->helper->translatePlayLive($programmeItem);
        $this->assertStringStartsWith('The Corretc Translation', $result);
    }

    public function translatePlayLiveDataProvider()
    {
        return [
            ['audio', 'iplayer_listen_live'],
            ['video', 'iplayer_watch_live'],
            ['radio', 'iplayer_listen_live'],
            ['other', 'iplayer_play_live'],
        ];
    }

    /**
     * @dataProvider translatePlayFromStartDataProvider
     */
    public function testTranslateFromStart($mediaType, $expectedTrPrefix)
    {
        $programmeItem = $this->makeProgrammeItem($mediaType, null);
        $this->mockTranslate->expects($this->once())
            ->method('translate')
            ->with($expectedTrPrefix)
            ->willReturn('The Corretc Translation');

        $result = $this->helper->translatePlayFromStart($programmeItem);
        $this->assertStringStartsWith('The Corretc Translation', $result);
    }

    public function translatePlayFromStartDataProvider()
    {
        return [
            ['audio', 'iplayer_listen_from_start'],
            ['video', 'iplayer_watch_from_start'],
            ['radio', 'iplayer_listen_from_start'],
            ['other', 'iplayer_play_from_start'],
        ];
    }
    private function setMockTranslate($translationKey, $count)
    {
        $this->mockTranslate->expects($this->once())
            ->method('translate')
            ->with(
                $translationKey,
                ['%count%' => $count],
                $count
            )->willReturn('The Corretc Translation');
    }

    private function makeProgrammeItem(string $programmeType, ?DateTimeImmutable $endAvailabilityTime)
    {
        $programmeItem = $this->createMock(Episode::class);
        if ($programmeType == 'audio') {
            $programmeItem->method('isAudio')->willReturn(true);
            $programmeItem->method('isVideo')->willReturn(false);
            $programmeItem->method('isRadio')->willReturn(false);
            $programmeItem->method('isTv')->willReturn(false);
        } elseif ($programmeType == 'video') {
            $programmeItem->method('isAudio')->willReturn(false);
            $programmeItem->method('isVideo')->willReturn(true);
            $programmeItem->method('isRadio')->willReturn(false);
            $programmeItem->method('isTv')->willReturn(false);
        } elseif ($programmeType == 'radio') {
            $programmeItem->method('isAudio')->willReturn(false);
            $programmeItem->method('isVideo')->willReturn(false);
            $programmeItem->method('isRadio')->willReturn(true);
            $programmeItem->method('isTv')->willReturn(false);
        } elseif ($programmeType == 'tv') {
            $programmeItem->method('isAudio')->willReturn(false);
            $programmeItem->method('isVideo')->willReturn(false);
            $programmeItem->method('isRadio')->willReturn(false);
            $programmeItem->method('isTv')->willReturn(true);
        } else {
            $programmeItem->method('isAudio')->willReturn(false);
            $programmeItem->method('isVideo')->willReturn(false);
            $programmeItem->method('isRadio')->willReturn(false);
            $programmeItem->method('isTv')->willReturn(false);
        }
        $programmeItem->method('isStreamable')->willReturn(true);
        $programmeItem->method('getStreamableUntil')->willReturn($endAvailabilityTime);
        return $programmeItem;
    }
}
