<?php
declare(strict_types = 1);
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;

class StatusController extends Controller
{
    public function __invoke(Request $request, Connection $dbalConnection): Response
    {
        // TODO: Name this method __invoke rather than statusAction if
        // "controller.service_arguments" becomes supported on invokable controllers
        // https://github.com/symfony/symfony/issues/22202

        // If the load balancer is pinging us then give them a plain OK
        if ($request->headers->get('User-Agent') == 'ELB-HealthChecker/1.0') {
            return new Response('OK', Response::HTTP_OK, ['content-type' => 'text/plain']);
        }

        // Other people get a better info screen
        return $this->render('status/status.html.twig', [
            'now' => new DateTimeImmutable(),
            'dbConnectivity' => $dbalConnection->isConnected() || $dbalConnection->connect(),
        ]);
    }
}
