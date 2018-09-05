<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Domain\ContentBlock\Table;

use App\Ds2013\Presenter;
use App\ExternalApi\Isite\Domain\ContentBlock\Table;

class TablePresenter extends Presenter
{
    /** @var bool */
    private $inPrimaryColumn;

    /** @var Table */
    private $tableBlock;

    public function __construct(Table $tableBlock, bool $inPrimaryColumn, array $options = [])
    {
        parent::__construct($options);
        $this->tableBlock = $tableBlock;
        $this->inPrimaryColumn = $inPrimaryColumn;
    }

    public function getCellClasses(): string
    {
        if ($this->inPrimaryColumn) {
            return 'br-box-subtle br-page-bg-onborder';
        }

        return 'br-box-page br-subtle-bg-onborder';
    }

    public function getHeaderClasses(): string
    {
        if ($this->inPrimaryColumn) {
            return 'br-box-highlight br-page-bg-onborder';
        }

        return 'br-box-highlight br-subtle-bg-onborder';
    }

    public function getTableBlock(): Table
    {
        return $this->tableBlock;
    }

    public function isInPrimaryColumn(): bool
    {
        return $this->inPrimaryColumn;
    }
}
