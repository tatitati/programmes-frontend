<?php
declare(strict_types = 1);

namespace App\Fixture\Doctrine\Entity;

use DateTime;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *  uniqueConstraints={@ORM\UniqueConstraint(name="http_fixture_unique", columns={"scenario_id", "env_agnostic_url"})},
 *  indexes={
 *   @ORM\Index(name="http_fixture_url_index", columns={"env_agnostic_url"}),
 *   @ORM\Index(name="http_fixture_select_index", columns={"scenario_id", "env_agnostic_url"}),
 *  })
 * @ORM\Entity(repositoryClass="App\Fixture\Doctrine\EntityRepository\HttpFixtureRepository")
 */
class HttpFixture
{
    use TimestampableEntity;

    /**
     * @var int|null
     *
     * @ORM\Id()
     * @ORM\Column(type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Scenario
     * @ORM\ManyToOne(targetEntity="Scenario")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $scenario;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $envAgnosticUrl;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    private $responseCode = 200;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $headers = '';

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=false)
     */
    private $body = '';

    public function __construct(string $envAgnosticUrl, Scenario $scenario)
    {
        $this->envAgnosticUrl = $envAgnosticUrl;
        $this->scenario = $scenario;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScenario(): Scenario
    {
        return $this->scenario;
    }

    public function getEnvAgnosticUrl(): string
    {
        return $this->envAgnosticUrl;
    }

    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    public function setResponseCode(int $responseCode): void
    {
        $this->responseCode = $responseCode;
    }

    public function getHeaders(): string
    {
        return $this->headers;
    }

    public function setHeaders(string $headers): void
    {
        $this->headers = $headers;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }
}
