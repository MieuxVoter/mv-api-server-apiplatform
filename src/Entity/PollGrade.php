<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\PollGradeRepository")
 */
class PollGrade
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"Poll:create"})
     */
    private $name;

    /**
     * Used to compare grades procedurally.
     * Usually starts at zero (0) and ends at <MAXIMUM_GRADES>-1.
     * Grades of the same poll MUST have unique levels between themselves.
     *
     * @ORM\Column(type="integer")
     * @Groups({"Poll:create"})
     */
    private $level;

    /**
     * The poll this grade is attached to.
     *
     * Groups({"PollProposal:create"})
     * Groups({"Poll:create"})
     * @ORM\ManyToOne(
     *     targetEntity="Poll",
     *     inversedBy="grades"
     * )
     * @ORM\JoinColumn(nullable=false)
     */
    private $poll;

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
