<?php
declare(strict_types=1);

namespace App\ExternalApi\Morph\Service;

use BBC\ProgrammesMorphLibrary\MorphClient;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use BBC\ProgrammesPagesService\Domain\Exception\DataNotFetchedException;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Log\LoggerInterface;

class LxPromoService
{
    /** @var string */
    private $lxPromoEnv;

    /** @var MorphClient */
    private $client;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        LoggerInterface $logger,
        MorphClient $client,
        string $lxPromoEnv
    ) {
        $this->lxPromoEnv = $lxPromoEnv;
        $this->client = $client;
        $this->logger = $logger;
    }

    public function fetchByProgrammeContainer(ProgrammeContainer $programme): PromiseInterface
    {
        $url = $this->getUrl($programme);
        $show = $this->getShow($programme);

        if (!$url || !$show) {
            $this->logger->error('[MORPH] Malformed livepromo_block for PID ' . (string) $programme->getPid());
            return new FulfilledPromise(null);
        }

        return $this->client->makeCachedViewPromise(
            'bbc-morph-lx-promo',
            'bbc-morph-lx-promo',
            [
                'env' => $this->lxPromoEnv,
                'section' => $url,
                'show' => $show,
                'isUK' => 'true',
                'brandingTool' => 'true',
            ],
            []
        );
    }

    /** @throws DataNotFetchedException */
    private function getUrl(ProgrammeContainer $programme): ?string
    {
        $lx = $programme->getOption('livepromo_block');

        if ($lx && isset($lx['content']) && isset($lx['content']['url'])) {
            return $lx['content']['url'];
        }

        return null;
    }

    /** @throws DataNotFetchedException */
    private function getShow(ProgrammeContainer $programme): ?string
    {
        $lx = $programme->getOption('livepromo_block');

        if ($lx && isset($lx['content']) && isset($lx['content']['show'])) {
            return $lx['content']['show'];
        }

        return null;
    }
}
