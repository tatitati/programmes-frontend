<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\Map\SubPresenter;

use App\DsAmen\Presenter;
use BBC\ProgrammesPagesService\Domain\Entity\Programme;
use BBC\ProgrammesPagesService\Domain\Enumeration\ContactMediumEnum;
use BBC\ProgrammesPagesService\Domain\ValueObject\ContactDetails;

class SocialBarPresenter extends Presenter
{
    /** @var ContactDetails[] */
    private $socialMediaDetails;

    public function __construct(Programme $programme, array $options = [])
    {
        parent::__construct($options);
        $this->socialMediaDetails = $this->filterSocialMediaDetails($programme->getOption('contact_details') ?? []);
    }

    /** @return ContactDetails[] */
    public function getSocialMediaDetails(): array
    {
        return $this->socialMediaDetails;
    }

    /**
     * @param ContactDetails[] $details
     * @return ContactDetails[]
     */
    private function filterSocialMediaDetails(array $details): array
    {
        $details = array_filter($details, function (ContactDetails $details) {
            return !in_array($details->getType(), [
                ContactMediumEnum::EMAIL,
                ContactMediumEnum::SMS,
                ContactMediumEnum::PHONE,
                ContactMediumEnum::FAX,
                ContactMediumEnum::ADDRESS,
                ContactMediumEnum::OTHER,
            ]);
        });

        return array_slice($details, 0, 5);
    }
}
