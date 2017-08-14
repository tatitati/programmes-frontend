<?php
declare(strict_types = 1);
namespace App\DsShared;

/**
 * Base Class for a DsShared Presenter
 */
abstract class Presenter extends BasePresenter
{
    final protected function getDesignSystem(): string
    {
        return 'Shared';
    }
}
