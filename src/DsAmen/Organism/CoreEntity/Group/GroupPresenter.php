<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\CoreEntity\Group;

use App\DsAmen\Organism\CoreEntity\Base\BaseCoreEntityPresenter;
use App\DsAmen\Organism\CoreEntity\Base\SubPresenter\BaseTitlePresenter;
use App\DsAmen\Organism\CoreEntity\Base\SubPresenter\BaseBodyPresenter;
use App\DsAmen\Organism\CoreEntity\Base\SubPresenter\BaseCtaPresenter;
use App\DsAmen\Organism\CoreEntity\Base\SubPresenter\BaseImagePresenter;
use App\DsAmen\Organism\CoreEntity\Group\SubPresenter\CtaPresenter;
use App\DsAmen\Organism\CoreEntity\Group\SubPresenter\TitlePresenter;
use App\DsAmen\Organism\CoreEntity\Shared\SubPresenter\SharedBodyPresenter;
use App\DsAmen\Organism\CoreEntity\Shared\SubPresenter\SharedImagePresenter;

class GroupPresenter extends BaseCoreEntityPresenter
{
    public function getBodyPresenter(array $options = []): BaseBodyPresenter
    {
        $options = array_merge($this->subPresenterOptions('body_options'), $options);
        return new SharedBodyPresenter(
            $this->coreEntity,
            $options
        );
    }

    public function getCtaPresenter(array $options = []): ?BaseCtaPresenter
    {
        $options = array_merge($this->subPresenterOptions('cta_options'), $options);
        return new CtaPresenter(
            $this->coreEntity,
            $this->router,
            $options
        );
    }

    public function getImagePresenter(array $options = []): BaseImagePresenter
    {
        $options = array_merge($this->subPresenterOptions('image_options'), $options);
        $options['cta_options'] = $this->mergeWithSubPresenterOptions($options, 'cta_options');

        return new SharedImagePresenter(
            $this->coreEntity,
            $this->router,
            $this->getCtaPresenter(),
            $options
        );
    }

    public function getTitlePresenter(array $options = []): BaseTitlePresenter
    {
        $options = array_merge($this->subPresenterOptions('title_options'), $options);
        return new TitlePresenter(
            $this->coreEntity,
            $this->router,
            $this->helperFactory->getTitleLogicHelper(),
            $options
        );
    }
}
