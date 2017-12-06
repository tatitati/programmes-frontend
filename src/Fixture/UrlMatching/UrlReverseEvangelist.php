<?php
declare(strict_types = 1);

namespace App\Fixture\UrlMatching;

/**
 * Class UrlReverseEvangelist
 *
 * Makes URLs agnostic (to their environment)
 */
class UrlReverseEvangelist
{
    /**
     * This is also the list of URLs that can be fixtured
     */
    private static $replacements = array(
        '/www(?:\.(?:live|stage|test|int))?\.bbc\.co\.uk(?::80|:443)?/' => 'www.bbc.co.uk',
        '/branding(?:\.test)?\.files\.bbci\.co\.uk(?::80|:443)?\/branding\/(?:int|test|live)\//' => 'branding.files.bbci.co.uk/branding/live/', // Branding
        '/api\.(?:live|stage|test|int)\.bbc.co.uk\/food\/recipes\//' => 'api.live.bbc.co.uk/food/recipes/', // Recipes
        '/api\.(?:live|stage|test|int)\.bbc.co.uk\/electron\//' => 'api.live.bbc.co.uk/electron/', // Electron
        '/open\.(?:live|stage|test|int)\.bbc.co.uk\/recommend\//' => 'open.live.bbc.co.uk/recommend/', // Electron
        // @TODO do we want to fixture the ORB?
        '/navigation\.(int\.|test\.|stage\.)?api\.bbci\.co\.uk(?::80|:443)?/' => 'navigation.api.bbci.co.uk',
    );

    public function isFixturable(string $url): bool
    {
        foreach (array_keys(self::$replacements) as $regex) {
            if (preg_match($regex, $url)) {
                return true;
            }
        }
        return false;
    }

    public function makeEnvAgnostic(string $url)
    {
        return preg_replace(array_keys(self::$replacements), array_values(self::$replacements), $url);
    }
}
