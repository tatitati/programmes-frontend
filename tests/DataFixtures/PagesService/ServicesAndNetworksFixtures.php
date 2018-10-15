<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\PagesService;

use App\Builders\NetworkBuilder;
use App\Builders\ServiceBuilder;
use BBC\ProgrammesPagesService\Domain\Entity\Network;
use BBC\ProgrammesPagesService\Domain\Entity\Service;
use BBC\ProgrammesPagesService\Domain\Enumeration\NetworkMediumEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\Nid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Sid;
use DateTimeImmutable;

class ServicesAndNetworksFixtures
{
    public static function serviceBbcOneLondon(): Service
    {
        return self::intervalServiceBbcOneLondon(self::networkBbcOne());
    }

    public static function serviceRadioThree(): Service
    {
        return self::internalServiceRadioThree(self::networkRadioThree());
    }

    public static function serviceRadioFourFM(): Service
    {
        return self::internalServiceRadioFourFM(self::networkRadioFour());
    }

    public static function serviceWorldService(): Service
    {
        return self::internalServiceWorldService(self::networkWorldService());
    }

    public static function networkBbcOne(): Network
    {
        return new Network(
            new Nid('bbc_one'),
            'BBC One',
            ImagesFixtures::bbcOneLogo(),
            OptionsFixture::bbcOne(),
            'bbcone',
            'TV',
            NetworkMediumEnum::TV,
            self::intervalServiceBbcOneLondon(),
            true,
            false,
            false,
            false,
            false
        );
    }

    public static function networkRadioFour(): Network
    {
        return new Network(
            new Nid('bbc_radio_four'),
            'BBC Radio 4',
            ImagesFixtures::radioFourLogo(),
            OptionsFixture::radioFour(),
            'radio4',
            'National Radio',
            NetworkMediumEnum::RADIO,
            self::internalServiceRadioFourFM(),
            true,
            false,
            false,
            false,
            false
        );
    }

    public static function networkRadioThree(): Network
    {
        return NetworkBuilder::any()->with([
            'nid' => new Nid('bbc_radio_three'),
            'name' => 'BBC Radio 3',
            'image' => ImagesFixtures::radioThreeLogo(),
            'options' => OptionsFixture::radioThree(),
            'type' => 'National Radio',
            'medium' => NetworkMediumEnum::RADIO,
            'defaultService' => self::internalServiceRadioThree(),
        ])->build();
    }

    public static function networkWorldService(): Network
    {
        return new Network(
            new Nid('bbc_world_service'),
            'BBC World Service',
            ImagesFixtures::worldServiceLogo(),
            OptionsFixture::worldServiceRadio(),
            'worldserviceradio',
            'National Radio',
            NetworkMediumEnum::RADIO,
            self::internalServiceWorldService(),
            true,
            false,
            true,
            true,
            false
        );
    }


    private static function intervalServiceBbcOneLondon(Network $network = null): Service
    {
        // Dodgy way to deal with circular dependencies...
        return new Service(
            100,
            new Sid('bbc_one_london'),
            new Pid('p00fzl6p'),
            'BBC One London',
            'London',
            'london',
            $network,
            new DateTimeImmutable('1964-04-20 00:01:00'),
            null,
            null
        );
    }

    private static function internalServiceRadioThree(Network $network = null): Service
    {
        return ServiceBuilder::anyRadioService()->with([
            'sid' => new Sid('bbc_radio_three'),
            'pid' => new Pid('p00fzl8t'),
            'name' => 'BBC Radio 3',
            'shortName' => 'BBC Radio 3',
            'urlKey' => 'bbc_radio_three',
            'network' => $network,
            'startDate' => new DateTimeImmutable('1967-09-30 08:00:00'),
            'endDate' => null,
            'liveStreamUrl' => null,
        ])->build();
    }

    private static function internalServiceRadioFourFM(Network $network = null): Service
    {
        return new Service(
            200,
            new Sid('bbc_radio_fourfm'),
            new Pid('p00fzl7j'),
            'BBC Radio 4 FM',
            'FM',
            'fm',
            $network,
            new DateTimeImmutable('1967-09-30 05:30:00'),
            null,
            null
        );
    }

    private static function internalServiceWorldService(Network $network = null): Service
    {
        return new Service(
            300,
            new Sid('bbc_world_service'),
            new Pid('p00fzl9p'),
            'BBC World Service Online',
            'Online',
            'bbc_world_service',
            $network,
            new DateTimeImmutable('2000-04-01 00:00:00'),
            null,
            null
        );
    }
}
