<?php
declare(strict_types = 1);

namespace App\DsAmen\Organism\CoreEntity\Programme;

use App\DsAmen\Organism\CoreEntity\Base\BaseCoreEntityPresenter;
use App\DsAmen\Organism\CoreEntity\Base\SubPresenter\BaseBodyPresenter;
use App\DsAmen\Organism\CoreEntity\Base\SubPresenter\BaseCtaPresenter;
use App\DsAmen\Organism\CoreEntity\Base\SubPresenter\BaseImagePresenter;
use App\DsAmen\Organism\CoreEntity\Base\SubPresenter\BaseTitlePresenter;
use App\DsAmen\Organism\CoreEntity\Shared\SubPresenter\BodyPresenter;
use App\DsAmen\Organism\CoreEntity\Shared\SubPresenter\ImagePresenter;
use App\DsAmen\Organism\CoreEntity\Shared\SubPresenter\TitlePresenter;
use App\DsAmen\Organism\CoreEntity\Shared\SubPresenter\StreamableCtaPresenter;

class ProgrammePresenter extends BaseCoreEntityPresenter
{
    public function getBodyPresenter(array $options = []): BaseBodyPresenter
    {
        $options = array_merge($this->subPresenterOptions('body_options'), $options);
        return new BodyPresenter(
            $this->coreEntity,
            $options
        );
    }

    public function getCtaPresenter(array $options = []): ?BaseCtaPresenter
    {
        if ($this->isStreamable()) {
            $options = array_merge($this->subPresenterOptions('cta_options'), $options);
            return new StreamableCtaPresenter(
                $this->coreEntity,
                $this->router,
                $options
            );
        }
        return null;
    }

    public function getImagePresenter(array $options = []): BaseImagePresenter
    {
        $options = array_merge($this->subPresenterOptions('image_options'), $options);
        $options['cta_options'] = $this->mergeWithSubPresenterOptions($options, 'cta_options');
        return new ImagePresenter(
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

    public function showStandaloneCta(): bool
    {
        return (!$this->getOption('show_image') && $this->isStreamable());
    }
}
