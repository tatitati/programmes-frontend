<?php

namespace App\DsShared\Helpers;

use App\Translate\TranslatableTrait;
use App\Translate\TranslateProvider;

class ProseToParagraphsHelper
{
    use TranslatableTrait;

    public function __construct(TranslateProvider $translateProvider)
    {
        $this->translateProvider = $translateProvider;
    }

    public function proseToParagraphs(string $text, int $max = null, string $id = null): string
    {
        $html = strip_tags($text);
        $html = htmlspecialchars($html, ENT_COMPAT, 'UTF-8');
        $html = nl2br($html);
        $html = trim(preg_replace('/\s+/', ' ', $html)); // remove left over new lines
        $html = str_replace('/> ', '/>', $html); // remove space between tags
        $html = preg_replace('/(<br \/>){2,}/', '</p><p>', $html); // replace multi <br /> with <p>

        if (!$max || $max > $this->countWithoutTags($html)) {
            return '<p>' . $html . '</p>';
        }

        $paras = [];
        $split = explode('</p><p>', $html); // split up the paragraphs
        $count = 0;
        $max = 0.7 * $max; // go back 30% (so truncation is worth it)
        foreach ($split as $para) {
            $paras[] = $this->buildParagraphHtml($count, $max, $para);
        }
        // join it all back together
        return $this->addMoreLessMoreToggle(implode('', $paras), $id);
    }

    private function addMoreLessMoreToggle($html, string $id = null): string
    {
        $contentId = "ml-" . (($id) ? $id : time());
        $more = $this->tr('show_more');
        $less = $this->tr('show_less');
        $content = <<<"MORELESS"
<div class="ml">
    <input class="ml__status" id="$contentId" type="checkbox" checked />
    <div class="ml__content prose text--prose">
        $html
    </div>
    <label class="ml__button br-pseudolink" for="$contentId">
        <span class="ml__label--more">$more</span> <span class="ml__label--sep"> / </span> <span class="ml__label--less">$less</span>
    </label>
</div>
MORELESS;

        return $content;
    }

    private function buildParagraphHtml(int &$count, float $max, string $para): string
    {
        $charactersInPara = $this->countWithoutTags($para);
        if ($count >= $max) {
            return '<p class="ml__hidden">' . $para . '</p>';
        }

        $prev = $count;
        $count += $charactersInPara;
        if ($count > $max) {
            $startSpanAt = $max - $prev;

            // remove the space from <br /> (so we don't truncate in the middle of it)
            $para = str_replace('<br />', '<br-/>', $para);

            $nearestSpace = strrpos($para, ' ', -(strlen($para) - $startSpanAt));

            // restore space to <br />
            $para = str_replace('<br-/>', '<br />', $para);

            // split the string in the right place
            $first = substr($para, 0, $nearestSpace);
            $rest = substr($para, $nearestSpace);

            $para = $first . '<span class="ml__ellipsis"><span class="ml__hidden">' . $rest . '</span></span>';
        }

        return '<p>' . $para . '</p>';
    }

    private function countWithoutTags(string $text): int
    {
        return strlen(strip_tags($text));
    }
}
