<?php
declare(strict_types = 1);

namespace App\Fixture\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use DateTimeInterface;

/**
 * @ORM\Table(
 *  indexes={
 *    @ORM\Index(name="scenario_name_index", columns={"name"}),
 *  })
 * @ORM\Entity(repositoryClass="App\Fixture\Doctrine\EntityRepository\ScenarioRepository")
 */
class Scenario
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
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=false, unique=true)
     */
    private $name;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $applicationTime;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $originalUrl;

    public function __construct(string $name, DateTimeInterface $applicationTime, string $originalUrl)
    {
        $this->name = $name;
        $this->applicationTime = $applicationTime;
        $this->originalUrl = $originalUrl;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOriginalUrl(): string
    {
        return $this->originalUrl;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getApplicationTime(): DateTimeInterface
    {
        return $this->applicationTime;
    }

    public function setApplicationTime(DateTimeInterface $applicationTime): void
    {
        $this->applicationTime = $applicationTime;
    }
}
