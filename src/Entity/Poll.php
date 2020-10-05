<?php


namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * A Liquid Majority Judgment Poll.
 * A poll is not editable after creation. (or perhaps after the first judgment has been cast)
 * The includes the poll's grades.
 * New proposals MAY be added during the poll, but the poll's settings should govern this.
 * A poll cannot be deleted without privileges.
 *
 * @ApiResource(
 *     normalizationContext={"groups"={"Poll:read"}},
 *     itemOperations={
 *         "get"={
 *             "normalization_context"={"groups"={"Poll:read"}},
 *         },
 *         "delete"={
 *             "access_control"="is_granted('can_delete', object)",
 *         },
 *     },
 *     collectionOperations={
 *         "post"={
 *             "denormalization_context"={"groups"={"Poll:create"}},
 *         },
 *     },
 * )
 * @ORM\Entity(
 *     repositoryClass="App\Repository\PollRepository",
 * )
 */
class Poll
{

    /** @deprecated */
    const MENTION_EXCELLENT  = 'bonega';
    /** @deprecated */
    const MENTION_VERY_GOOD  = 'trebona';
    /** @deprecated */
    const MENTION_GOOD       = 'bona';
    /** @deprecated */
    const MENTION_PASSABLE   = 'trairebla';
    /** @deprecated */
    const MENTION_INADEQUATE = 'neadekvata';
    /** @deprecated */
    const MENTION_MEDIOCRE   = 'malboneta';
    /** @deprecated */
    const MENTION_TO_REJECT  = 'malakcepti';

    /**
     * @var int|null
     * @ApiProperty(identifier=false)
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var UuidInterface|null
     * @ApiProperty(identifier=true)
     * @ORM\Column(type="uuid", unique=true)
     * @Groups({"Poll:read"})
     */
    public $uuid;

    /**
     * @var string
     * @Groups({"Poll:create", "Poll:read", "Poll:update"})
     * @ORM\Column(type="string", length=142)
     */
    private $subject;

    /**
     * @var ArrayCollection
     * @Groups({"Poll:create", "Poll:read", "Poll:update"})
     * @ApiSubresource()
     * @ORM\OneToMany(
     *     targetEntity="PollProposal",
     *     mappedBy="poll",
     *     cascade={"persist"},
     *     orphanRemoval=true,
     * )
     */
    private $proposals;

    /**
     * @var ArrayCollection
     * @Groups({"Poll:create", "Poll:read", "Poll:update"})
     * @ApiSubresource()
     * @ORM\OneToMany(
     *     targetEntity="PollGrade",
     *     mappedBy="poll",
     *     cascade={"persist"},
     *     orphanRemoval=true,
     * )
     */
    private $grades;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="polls")
     */
    private $author;
    
    public function __construct()
    {
        $this->proposals = new ArrayCollection();
        $this->grades = new ArrayCollection();
        $this->uuid = Uuid::uuid4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?UuidInterface
    {
        return $this->uuid;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }



    /**
     * @return Collection|PollProposal[]
     */
    public function getProposals(): Collection
    {
        return $this->proposals;
    }

    public function addProposal(PollProposal $proposal): self
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals[] = $proposal;
            $proposal->setPoll($this);
        }

        return $this;
    }

    public function removeProposal(PollProposal $proposal): self
    {
        if ($this->proposals->contains($proposal)) {
            $this->proposals->removeElement($proposal);
            // set the owning side to null (unless already changed)
            if ($proposal->getPoll() === $this) {
                $proposal->setPoll(null);
            }
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|PollGrade[]
     */
    public function getGrades(): Collection
    {
        return $this->grades;
    }

    public function addGrade(PollGrade $grade): self
    {
        if ( ! $this->grades->contains($grade)) {
            $this->grades[] = $grade;
            $grade->setPoll($this);
        }

        return $this;
    }

    public function removeGrade(PollGrade $grade): self
    {
        if ($this->grades->contains($grade)) {
            $this->grades->removeElement($grade);
            // set the owning side to null (unless already changed)
            if ($grade->getPoll() === $this) {
                $grade->setPoll(null);
            }
        }

        return $this;
    }

    public function getGradesInOrder() : array
    {
        $grades = $this->getGrades();
//        dump($grades);
        $grades = $this->getGrades()->toArray();
        dump($grades);
        usort($grades, function (PollGrade $a, PollGrade $b) {
            return $a->getLevel() - $b->getLevel();
        });
//        dump($grades);
        return $grades;
    }

    public function getLevelsOfGrades() : array
    {
        $levels = [];
        foreach ($this->getGrades() as $grade) {
            $levels[$grade->getName()] = $grade->getLevel();
        }
        return array_flip($levels);
    }

    public function getGradesNames() : array
    {
        $names = [];
        foreach ($this->getGradesInOrder() as $grade) {
            $names[] = $grade->getName();
        }

        return $names;

    }
}
