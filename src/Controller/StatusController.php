<?php
declare(strict_types = 1);
namespace App\Controller;

use BBC\ProgrammesPagesService\Cache\CacheInterface;
use BBC\ProgrammesPagesService\Domain\ValueObject\Pid;
use BBC\ProgrammesPagesService\Domain\ValueObject\Sid;
use BBC\ProgrammesPagesService\Service\BroadcastsService;
use BBC\ProgrammesPagesService\Service\ProgrammesService;
use BBC\ProgrammesPagesService\Service\SegmentEventsService;
use BBC\ProgrammesPagesService\Service\VersionsService;
use DateTimeImmutable;
use Doctrine\DBAL\ConnectionException as ConnectionExceptionDBAL;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\DriverException;
use ErrorException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StatusController extends Controller
{
    public function __invoke(
        Request $request,
        Connection $dbalConnection,
        ProgrammesService $programmesService,
        BroadcastsService $broadcastsService,
        VersionsService $versionsService,
        SegmentEventsService $segmentEventsService
    ): Response {
        // If the load balancer is pinging us then give them a plain OK
        $dbCacheIsOk = $this->verifyNoDatabaseCacheIssues($programmesService, $broadcastsService, $versionsService, $segmentEventsService);
        if ($request->headers->get('User-Agent') == 'ELB-HealthChecker/1.0') {
            if (!$dbCacheIsOk) {
                return new Response('ERROR', Response::HTTP_INTERNAL_SERVER_ERROR, ['content-type' => 'text/plain']);
            }
            return new Response('OK', Response::HTTP_OK, ['content-type' => 'text/plain']);
        }

        // Other people get a better info screen
        return $this->render('status/status.html.twig', [
            'now' => new DateTimeImmutable(),
            'db_connectivity' => $dbalConnection->isConnected(),
            'dbCacheIsOk' => $dbCacheIsOk,
        ]);
    }

    /**
     * This profanity is copy/pasted from Faucet. It detects whether an Exception indicates that the
     * database is down. No, there is no single exception for that.
     * When the database is down we return a 200 status. Other DB exceptions return a 500 status.
     * See programmes ticket https://jira.dev.bbc.co.uk/browse/PROGRAMMES-5534
     *
     * Please remove this kludge once the underlying issues in APCU are fixed
     *
     * returns TRUE if there are no issues or if the database is down
     * returns FALSE if there are non-connection related database issues (e.g. APCU problems)
     */
    private function verifyNoDatabaseCacheIssues(
        ProgrammesService $programmesService,
        BroadcastsService $broadcastsService,
        VersionsService $versionsService,
        SegmentEventsService $segmentEventsService
    ): bool {
        try {
            // Eastenders clip
            $clipPid = new Pid('p04r0jcv');
            $programmesService->findByPidFull($clipPid, 'Programme', CacheInterface::NONE);

            // Broadcast
            $fromDateTime = new DateTimeImmutable('2010-01-15 06:00:00');
            $toDatetime = new DateTimeImmutable('2017-10-16 06:00:00');
            $sid = new Sid('bbc_radio_two');
            $broadcastsService->findByServiceAndDateRange($sid, $fromDateTime, $toDatetime, 1, 1, CacheInterface::NONE);

            // Version
            $versionPid = new Pid('b00000p6');
            $versionsService->findByPidFull($versionPid, CacheInterface::NONE);

            // Segment event
            $segmentPid = new Pid('p002d80x');
            $segmentEventsService->findByPidFull($segmentPid, CacheInterface::NONE);
        } catch (ConnectionExceptionDBAL | ConnectionException $e) {
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() === 0 && stristr($e->getMessage(), 'There is no active transaction')) {
                // I am aware of how horrible this is. PDOExcetion is very generic. The only
                // way I can see to be specific to the case of "DB server went down"
                // is to do a string compare on the error message.
                return true;
            } elseif ($e->getCode() == 2002) {
                // Connection timeout
                return true;
            }
            return false;
        } catch (DriverException $e) {
            if ($e->getErrorCode() == 1213 || $e->getErrorCode() == 1205) {
                // This is thrown on a MySQL deadlock error 1213 or 1205 lock wait timeout. We catch it
                // and exit with a zero exit status allowing the processor
                // to restart
                return true;
            } elseif ($e->getErrorCode() == 2006) {
                // General error: 2006 MySQL server has gone away
                return true;
            }
            return false;
        } catch (DBALException | ErrorException $e) {
            $msg = $e->getMessage();
            if ($e->getCode() === 0 &&
                (stristr($msg, 'server has gone away') || stristr($msg, 'There is no active transaction.'))
            ) {
                // This is what happens when the SQL server goes away while the process is active
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}
