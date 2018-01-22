<?php
declare(strict_types=1);

namespace Tests\App\ExternalApi\Morph\Service;

use App\ExternalApi\Morph\Service\LxPromoService;
use BBC\ProgrammesMorphLibrary\MorphClient;
use BBC\ProgrammesPagesService\Domain\Entity\ProgrammeContainer;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RMP\Translate\TranslateFactory;

class LxPromoServiceTest extends TestCase
{
    private $logger;
    private $translateFactory;
    private $client;
    private $env;

    public function setUp()
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->translateFactory = $this->createMock(TranslateFactory::class);
        $this->client = $this->createMock(MorphClient::class);
        $this->env = 'unit-test';
    }

    /** @dataProvider malformedLivePromoBlockOptionProvider */
    public function testMalformedLivePromoBlockOptionReturnsNull(array $options)
    {
        $programme = $this->createConfiguredMock(
            ProgrammeContainer::class,
            ['getOption' => ['livepromo_block' => $options]]
        );

        $this->client->expects($this->never())->method('makeCachedViewPromise');
        $service = new LxPromoService($this->logger, $this->client, $this->env);
        $this->assertNull($service->fetchByProgrammeContainer($programme)->wait());
    }

    public function malformedLivePromoBlockOptionProvider(): array
    {
        return [
            'no url' => [['show' => 3]],
            'no show' => [['url' => '/sport/']],
        ];
    }
}
