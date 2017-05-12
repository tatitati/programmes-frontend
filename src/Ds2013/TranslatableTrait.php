<?php
declare(strict_types = 1);
namespace App\Ds2013;

use RMP\Translate\Translate;

trait TranslatableTrait
{
    /** @var Translate */
    protected $translate;

    protected function tr(
        string $key,
        $substitutions = [],
        $numPlurals = null,
        ?string $domain = null
    ): string {
        if (is_int($substitutions) && is_null($numPlurals)) {
            $numPlurals = $substitutions;
            $substitutions = array('%count%' => $numPlurals);
        }

        if (is_int($numPlurals) && !isset($substitutions['%count%'])) {
            $substitutions['%count%'] = $numPlurals;
        }

        return $this->translate->translate($key, $substitutions, $numPlurals, $domain);
    }
}
