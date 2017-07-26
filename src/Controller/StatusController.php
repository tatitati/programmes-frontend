<?php
declare(strict_types = 1);
namespace App\Controller;

use DateTimeImmutable;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StatusController extends Controller
{
    public function __invoke(Request $request, Connection $dbalConnection): Response
    {
        // If the load balancer is pinging us then give them a plain OK
        if ($request->headers->get('User-Agent') == 'ELB-HealthChecker/1.0') {
            return new Response('OK', Response::HTTP_OK, ['content-type' => 'text/plain']);
        }

        // Other people get a better info screen
        return $this->render('status/status.html.twig', [
            'now' => new DateTimeImmutable(),
            'db_connectivity' => $dbalConnection->isConnected() || $dbalConnection->connect(),
        ]);
    }
}
