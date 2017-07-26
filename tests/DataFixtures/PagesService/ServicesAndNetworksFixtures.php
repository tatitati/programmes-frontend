<?php
declare(strict_types = 1);

namespace Tests\App\DataFixtures\PagesService;

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

    public static function serviceRadioFourFM(): Service
    {
        return self::internalServiceRadioFourFM(self::networkRadio4());
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
}
