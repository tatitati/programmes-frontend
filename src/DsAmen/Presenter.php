<?php
declare(strict_types = 1);
namespace App\DsAmen;

use App\DsShared\BasePresenter;

/**
 * Base Class for a DsAmen Presenter
 */
abstract class Presenter extends BasePresenter
{
    final protected function getDesignSystem(): string
    {
        return 'Amen';
    }
}
