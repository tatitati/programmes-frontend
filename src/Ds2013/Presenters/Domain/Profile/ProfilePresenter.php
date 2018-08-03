<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Domain\Profile;

use App\Ds2013\Presenter;
use App\ExternalApi\Isite\Domain\Profile;

class ProfilePresenter extends Presenter
{
    protected $options = [
        'heading_level' => 'h2',
        'show_synopsis' => true,
    ];

    /** @var Profile */
    private $profile;

    public function __construct(Profile $profile, array $options = [])
    {
        parent::__construct($options);
        $this->profile = $profile;
    }

    public function getProfile(): Profile
    {
        return $this->profile;
    }
}
