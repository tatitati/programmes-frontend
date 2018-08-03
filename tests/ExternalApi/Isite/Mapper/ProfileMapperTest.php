<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Isite\Mapper;

use App\Controller\Helpers\IsiteKeyHelper;
use App\ExternalApi\Isite\Domain\Profile;
use App\ExternalApi\Isite\Mapper\MapperFactory;
use App\ExternalApi\Isite\Mapper\ProfileMapper;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

/**
 * @group profiles
 */
class ProfileMapperTest extends TestCase
{
    /** @var ProfileMapper */
    private $mapper;

    public function setUp()
    {
        $keyHelper = new IsiteKeyHelper();
        $this->mapper = new ProfileMapper(new MapperFactory($keyHelper), $keyHelper);
    }

    public function testCanMappXmlWithSomeEmptyValues()
    {
        // This xml is interesting because provide a real Isite response with No parent pid
        $xmlIsiteProfileResponse = new SimpleXMLElement(file_get_contents(__DIR__ . '/isite_profile_response_200.xml'));

        $profileMapped = $this->mapper->getDomainModel($xmlIsiteProfileResponse);

        $this->assertInstanceOf(Profile::class, $profileMapped);
        $this->assertEquals('', $profileMapped->getParentPid(), 'Fields with no value set an empty string into the domain');
        $this->assertEquals('progs-radio4and4extra', $profileMapped->getProjectSpace());
        $this->assertEquals('dr who', $profileMapped->getBrandingId());
    }
}
