<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\RmsPodcast\Mapper;

use App\ExternalApi\Exception\ParseException;
use App\ExternalApi\RmsPodcast\Domain\RmsPodcast;
use App\ExternalApi\RmsPodcast\Mapper\RmsPodcastMapper;
use Tests\App\ExternalApi\BaseServiceTestCase;

class RmsPodcastMapperTest extends BaseServiceTestCase
{
    public function testJsonCanBeTranslatedToApiResult()
    {
        $jsonResponse = $this->givenServerApiRespondsWIthJson('200_response_b006qykl.json');

        $outputMapped = (new RmsPodcastMapper())->mapItem($jsonResponse);

        $this->thenOutputIsOneDomainObject($outputMapped);
    }

    /**
     * @dataProvider wrongJsonProvider
     */
    public function testDoesntExplodeWhenJsonIsNotAPodcast(string $wrongJsonProvided)
    {
        $this->expectException(ParseException::class);

        (new RmsPodcastMapper())->mapItem($wrongJsonProvided);
    }

    public function wrongJsonProvider()
    {
        return [
            [
                json_encode(['anyfield' => 'any json wrong']),
            ], [
                json_encode(['results' => ['wrong_field']]),
            ],
        ];
    }

    /**
     * helpers
     */
    private function givenServerApiRespondsWIthJson(string $filename)
    {
        return file_get_contents(dirname(dirname(__DIR__)) . '/RmsPodcast/' . $filename);
    }

    private function thenOutputIsOneDomainObject(RmsPodcast $outputMapper)
    {
        $this->assertInstanceOf(RmsPodcast::class, $outputMapper);
        $this->assertEquals('b006qykl', (string) $outputMapper->getPid());
    }
}
