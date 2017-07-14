<?php
declare(strict_types = 1);
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class CloudLabsController extends BaseController
{
    public function __invoke(string $action)
    {
        // if no action, assume show
        if ($action == '') {
            $action = 'show';
        }

        $methodName = $action . 'Action';

        if (!method_exists($this, $methodName)) {
            // Valid actions are based upon the methods in this class that end
            // with 'Action'
            $validActions = [];

            foreach (get_class_methods($this) as $method) {
                if (substr($method, -6) == 'Action') {
                    $validActions[] = substr($method, 0, -6);
                }
            }

            throw $this->createNotFoundException(sprintf(
                'CloudLabs Action not found. Expected one of %s but got "%s"',
                '"' . implode('", "', $validActions) . '"',
                $action
            ));
        }

        return $this->{$methodName}();
    }

    public function showAction()
    {
        return $this->render('cloud_labs/show.html.twig');
    }

    public function analyticsAction()
    {
        return new Response('hai');
    }
}
