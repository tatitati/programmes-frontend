<?php
declare(strict_types = 1);

namespace App\Controller\Profiles;

use App\Controller\BaseController;
use App\Controller\Helpers\IsiteKeyHelper;
use Symfony\Component\HttpFoundation\Request;

class ShowController extends BaseController
{
    public function __invoke(string $key, string $slug, Request $request, IsiteKeyHelper $isiteKeyHelper)
    {
        if (strpos($key, '-') !== false) { // if contains dashes it is a guid
            $key = $isiteKeyHelper->convertGuidToKey($key);

            $params = ['key' => $key, 'slug' => $slug];
            if ($request->query->has('preview') && $request->query->get('preview')) {
                $params['preview'] = 'true';
            }
            return $this->cachedRedirectToRoute('programme_profile', $params, 301);
        }

        return $this->renderWithChrome('profiles/show.html.twig', []);
    }
}
