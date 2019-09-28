<?php


namespace App\Entity;


use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\LimajuPollOptionRepository")
 */
class LimajuPollOption
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     *
     * @Groups({ "read" })
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @Groups({ "create", "read", "update" })
     * @ORM\Column(type="string", length=142)
     */
    private $title;

    /**
     * The poll this option is attached to.
     *
     * @Groups({ "create" })
     * @ORM\ManyToOne(targetEntity="App\Entity\LimajuPoll", inversedBy="options")
     * @ORM\JoinColumn(nullable=false)
     */
    private $poll;

//    /**
//     * @ORM\OneToMany(targetEntity="App\Entity\LimajuOptionVote", mappedBy="option", orphanRemoval=true)
//     */
//    private $votes;

    public function __construct()
    {
        $this->votes = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPoll(): ?LimajuPoll
    {
        return $this->poll;
    }

    public function setPoll(?LimajuPoll $poll): self
    {
        $this->poll = $poll;

        return $this;
    }

    /**
     * @return Collection|LimajuPollOptionVote[]
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(LimajuPollOptionVote $vote): self
    {
        if (!$this->votes->contains($vote)) {
            $this->votes[] = $vote;
            $vote->setOption($this);
        }

        return $this;
    }

    public function removeVote(LimajuPollOptionVote $vote): self
    {
        if ($this->votes->contains($vote)) {
            $this->votes->removeElement($vote);
            // set the owning side to null (unless already changed)
            if ($vote->getOption() === $this) {
                $vote->setOption(null);
            }
        }

        return $this;
    }
}
