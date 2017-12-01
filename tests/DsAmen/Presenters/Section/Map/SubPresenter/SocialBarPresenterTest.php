<?php
declare(strict_types=1);

namespace Tests\App\DsAmen\Presenters\Section\Map\SubPresenter;

use App\DsAmen\Presenters\Section\Map\SubPresenter\SocialBarPresenter;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Enumeration\ContactMediumEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\ContactDetails;
use PHPUnit\Framework\TestCase;

class SocialBarPresenterTest extends TestCase
{
    /** @dataProvider filterSocialMediaProvider */
    public function testFilterSocialMedia(?array $contactDetails, array $expected)
    {
        $programme = $this->createMock(Programme::class);
        $programme->method('getOption')->with('contact_details')->willReturn($contactDetails);

        $socialBar = new SocialBarPresenter($programme);
        $result = $socialBar->getSocialMediaDetails();

        $this->assertEquals(count($expected), count($result));
        for ($i = 0; $i < count($result); $i++) {
            $this->assertEquals($expected[$i]->getType(), $result[$i]->getType());
            $this->assertEquals($expected[$i]->getValue(), $result[$i]->getValue());
            $this->assertEquals($expected[$i]->getFreetext(), $result[$i]->getFreetext());
        }
    }

    public function filterSocialMediaProvider(): array
    {
        return [
            'empty' => ['contactDetails' => [], 'expected' => []],
            'null' => ['contactDetails' => null, 'expected' => []],
            'with valid social media' => [
                'contactDetails' => [new ContactDetails(ContactMediumEnum::TWITTER, 'twitter', '')],
                'expected' => [new ContactDetails(ContactMediumEnum::TWITTER, 'twitter', '')],
            ],
            'with non-social media contacts only' => [
                'contactDetails' => [
                    new ContactDetails(ContactMediumEnum::EMAIL, 'email', ''),
                    new ContactDetails(ContactMediumEnum::SMS, 'sms', ''),
                    new ContactDetails(ContactMediumEnum::PHONE, 'phone', ''),
                    new ContactDetails(ContactMediumEnum::FAX, 'fax', ''),
                    new ContactDetails(ContactMediumEnum::ADDRESS, 'address', ''),
                    new ContactDetails(ContactMediumEnum::OTHER, 'other', ''),
                ],
                'expected' => [],
            ],
            'mixed' => [
                'contactDetails' => [
                    new ContactDetails(ContactMediumEnum::EMAIL, 'email', ''),
                    new ContactDetails(ContactMediumEnum::TWITTER, 'twitter', ''),
                    new ContactDetails(ContactMediumEnum::PHONE, 'phone', ''),
                    new ContactDetails(ContactMediumEnum::FACEBOOK, 'facebook', ''),
                    new ContactDetails(ContactMediumEnum::ADDRESS, 'address', ''),
                    new ContactDetails(ContactMediumEnum::INSTAGRAM, 'instagram', ''),
                ],
                'expected' => [
                    new ContactDetails(ContactMediumEnum::TWITTER, 'twitter', ''),
                    new ContactDetails(ContactMediumEnum::FACEBOOK, 'facebook', ''),
                    new ContactDetails(ContactMediumEnum::INSTAGRAM, 'instagram', ''),
                ],
            ],
            'only shows 5 items' => [
                'contactDetails' => [
                    new ContactDetails(ContactMediumEnum::TWITTER, 'twitter', ''),
                    new ContactDetails(ContactMediumEnum::FACEBOOK, 'facebook', ''),
                    new ContactDetails(ContactMediumEnum::INSTAGRAM, 'instagram', ''),
                    new ContactDetails(ContactMediumEnum::GOOGLE_PLUS, 'google-plus', ''),
                    new ContactDetails(ContactMediumEnum::PINTEREST, 'pinterest', ''),
                    new ContactDetails(ContactMediumEnum::SPOTIFY, 'spotify', ''),
                ],
                'expected' => [
                    new ContactDetails(ContactMediumEnum::TWITTER, 'twitter', ''),
                    new ContactDetails(ContactMediumEnum::FACEBOOK, 'facebook', ''),
                    new ContactDetails(ContactMediumEnum::INSTAGRAM, 'instagram', ''),
                    new ContactDetails(ContactMediumEnum::GOOGLE_PLUS, 'google-plus', ''),
                    new ContactDetails(ContactMediumEnum::PINTEREST, 'pinterest', ''),
                ],
            ],
        ];
    }
}
