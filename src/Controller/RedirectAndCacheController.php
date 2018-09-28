<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RedirectAndCacheController extends BaseController
{
    public function redirectAction(Request $request, $route, UrlGeneratorInterface $router, $permanent = false, $ignoreAttributes = false)
    {
        if ('' == $route) {
            throw new HttpException($permanent ? 410 : 404);
        }

        $attributes = array();
        if (false === $ignoreAttributes || is_array($ignoreAttributes)) {
            $attributes = $request->attributes->get('_route_params');
            unset($attributes['route'], $attributes['permanent'], $attributes['ignoreAttributes']);
            if ($ignoreAttributes) {
                $attributes = array_diff_key($attributes, array_flip($ignoreAttributes));
            }
        }

        $response = new RedirectResponse($router->generate($route, $attributes, UrlGeneratorInterface::ABSOLUTE_URL), $permanent ? 301 : 302);
        $response->setPublic()->setMaxAge(3600);
        
        return $response;
    }
}
