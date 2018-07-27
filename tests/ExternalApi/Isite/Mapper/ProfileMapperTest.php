<?php
declare(strict_types = 1);

namespace Tests\App\ExternalApi\Isite\Mapper;

use App\ExternalApi\Isite\Domain\Profile;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;
use App\ExternalApi\Isite\Mapper\ProfileMapper;
use App\Controller\Helpers\IsiteKeyHelper;

/**
 * @group profiles
 */
class ProfileMapperTest extends TestCase
{
    /** @var ProfileMapper */
    private $mapper;

    public function setUp()
    {
        $this->mapper = new ProfileMapper(new IsiteKeyHelper());
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
