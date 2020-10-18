<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource()
 * @ORM\Entity(
 *     repositoryClass="App\Repository\PollGradeRepository",
 * )
 */
class Grade
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @ApiProperty(identifier=false)
     */
    private $id;

    /**
     * Universally Unique IDentifier, something like this: 10e3c5e8-4a7d-4d23-a20a-8c175bf45a92
     *
     * @var UuidInterface|null
     * @ORM\Column(type="uuid", unique=true)
     * @ApiProperty(identifier=true)
     * @Groups({"Grade:read"})
     */
    public $uuid;

    /**
     * @ORM\Column(type="string", length=32)
     * @Groups({"Grade:read", "Poll:create"})
     */
    private $name;

    /**
     * Used to compare grades procedurally.
     * Usually starts at zero (0) and ends at <MAXIMUM_GRADES>-1.
     * Grades of the same poll MUST have unique levels between themselves.
     *
     * @ORM\Column(type="integer")
     * @Groups({"Grade:read", "Poll:create"})
     */
    private $level;

    /**
     * The poll this grade is attached to.
     *
     * Groups({"Proposal:create"})
     * Groups({"Poll:create"})
     * @ORM\ManyToOne(
     *     targetEntity="Poll",
     *     inversedBy="grades"
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $poll;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPoll()
    {
        return $this->poll;
    }

    /**
     * @param mixed $poll
     */
    public function setPoll($poll): void
    {
        $this->poll = $poll;
    }
}
