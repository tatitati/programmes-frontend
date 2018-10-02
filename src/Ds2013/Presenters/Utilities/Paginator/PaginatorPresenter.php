<?php
declare(strict_types = 1);

namespace App\Ds2013\Presenters\Utilities\Paginator;

use App\Ds2013\Presenter;

class PaginatorPresenter extends Presenter
{
    /** @var int */
    private $currentPage;

    /** @var int */
    private $pageSize;

    /** @var int */
    private $totalItems;

    /** @var (int|string)[] */
    private $items;

    public function __construct(int $currentPage, int $pageSize, int $totalItems, array $options = [])
    {
        parent::__construct($options);
        $this->currentPage = $currentPage;
        $this->pageSize = $pageSize;
        $this->totalItems = $totalItems;

        $this->items = $this->buildItems();
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getOffset(int $item): int
    {
        return abs($item - $this->currentPage);
    }

    public function getPageCount(): int
    {
        return (int) ceil($this->totalItems / $this->pageSize);
    }

    public function shouldApplyNoHideClass(int $item): bool
    {
        $pageCount = $this->getPageCount();
        if ($this->shouldShowAllPagesWithoutSpacers()) {
            // We ALWAYS want to show ALL items if under 5, so this applies a CSS override for that case
            return true;
        }
        if (($item <= 3) && $this->getCurrentPage() <= 3) {
            // We should always show the first three items if the current page is less than 3
            return true;
        }
        if (($item >= ($pageCount - 2)) && $this->getCurrentPage() >= $pageCount - 2) {
            // We should show the last three items if the final page count is less than 3 from the end
            return true;
        }
        return false;
    }

    /**
     * This method builds the pagination items that are going to be shown.
     * @return array
     */
    private function buildItems(): array
    {
        $pages = $this->getPageCount();

        if ($this->shouldShowAllPagesWithoutSpacers()) {
            return range(1, $pages);
        }

        if ($this->normalSpacerBothEnds()) {
            return array_merge([1, 'spacer'], range($this->currentPage - 2, $this->currentPage + 2), ['spacer', $pages]);
        }

        if ($this->normalSpacerStartHiddenSpacerEnd()) {
            $numberOfPagesAfterSpacer = max(5, ($pages - $this->currentPage) + 3);
            return array_merge([1, 'spacer'], range($pages - ($numberOfPagesAfterSpacer - 1), $pages - 1), ['spacer-hidden', $pages]);
        }

        if ($this->hiddenSpacerStartNormalSpacerEnd()) {
            $numberOfPagesBeforeSpacer = max(5, $this->currentPage + 2);
            return array_merge([1, 'spacer-hidden'], range(2, $numberOfPagesBeforeSpacer), ['spacer', $pages]);
        }

        if ($this->hiddenSpacerBothEnds()) {
            return array_merge([1, 'spacer-hidden'], range(2, $pages -1), ['spacer-hidden', $pages]);
        }

        if ($this->spacerAtStartOnly()) {
            $numberOfPagesAfterSpacer = max(5, ($pages - $this->currentPage) + 3);
            return array_merge([1, 'spacer'], range($pages - ($numberOfPagesAfterSpacer - 1), $pages));
        }

        if ($this->spacerAtEndOnly()) {
            $numberOfPagesBeforeSpacer = max(5, $this->currentPage + 2);
            return array_merge(range(1, $numberOfPagesBeforeSpacer), ['spacer', $pages]);
        }

        if ($this->hiddenSpacerAtStartOnly()) {
            return array_merge([1, 'spacer-hidden'], range(2, $pages));
        }
        if ($this->hiddenSpacerAtEndOnly()) {
            $numberOfPagesBeforeSpacer = max($this->getPageCount() === 7 ? 6 : 5, $this->currentPage + 2);
            return array_merge(range(1, $numberOfPagesBeforeSpacer), ['spacer-hidden', $pages]);
        }

        return array_merge([1, 'spacer'], range($this->currentPage - 2, $this->currentPage + 2), ['spacer', $pages]);
    }

    private function shouldShowAllPagesWithoutSpacers(): bool
    {
        return $this->getPageCount() <= 5;
    }

    private function spacerAtEndOnly(): bool
    {
        return $this->currentPage < 5 && ($this->getPageCount() > 7);
    }

    private function spacerAtStartOnly(): bool
    {
        return $this->currentPage > $this->getPageCount() - 4 && ($this->getPageCount() > 7);
    }

    private function hiddenSpacerBothEnds(): bool
    {
        $currentPage = $this->getCurrentPage();
        $pageCount = $this->getPageCount();

        // Shouldn't have a hidden spacer at both ends if greater than 9, at 9 the desktop sized spacers kick in
        if (($pageCount > 9)) {
            return false;
        }

        if ($currentPage == 5 && $pageCount == 9) {
            // 9-5 is a weird exception to the rule
            return true;
        }

        return ($currentPage > 3) && ($currentPage < $pageCount - 2);
    }

    private function hiddenSpacerStartNormalSpacerEnd(): bool
    {
        $currentPage = $this->getCurrentPage();
        $pageCount = $this->getPageCount();

        if ($currentPage == 5 && $pageCount == 9) {
            // 9-5 is a weird exception to the rule
            return false;
        }

        if (($currentPage > 3) && ($currentPage < $pageCount - 2)) {
            // 9 is the first case of this so never apply this to anything under 9 pages
            return ($currentPage >= $pageCount - 5) && ($pageCount >= 9);
        }
        return false;
    }

    private function normalSpacerStartHiddenSpacerEnd(): bool
    {
        $currentPage = $this->getCurrentPage();
        $pageCount = $this->getPageCount();

        if ($currentPage == 5 && $pageCount == 8) {
            // 8-5 is a weird exception to the rule
            return false;
        }

        if ($currentPage > $pageCount - 4 && ($pageCount > 7)) {
            return ($currentPage <= $pageCount - 3) && ($pageCount >= 6);
        }
        return false;
    }

    private function hiddenSpacerAtStartOnly(): bool
    {
        // After first three pages we always want to show the first spacer
        return $this->currentPage > 3;
    }

    private function hiddenSpacerAtEndOnly(): bool
    {
        // Before last three pages we always want to show the last spacer
        if (($this->getPageCount() === 7) && ($this->getCurrentPage() <= 3)) {
            // 7-X is a weird exception to the rule
            return true;
        }
        return ($this->currentPage <= $this->getPageCount() - 3) && ($this->getPageCount() >= 6);
    }

    private function normalSpacerBothEnds(): bool
    {
        $pageCount = $this->getPageCount();

        if ($pageCount < 10) {
            // Should never have a spacer at both ends under 10
            return false;
        }

        return $this->spacerAtStart() && $this->spacerAtEnd();
    }

    private function spacerAtStart(): bool
    {
        return ($this->currentPage > 4) && ($this->getPageCount() > 7);
    }

    private function spacerAtEnd(): bool
    {
        $pageCount = $this->getPageCount();
        return ($this->currentPage <= $pageCount - 5) && ($pageCount >= 9);
    }
}
