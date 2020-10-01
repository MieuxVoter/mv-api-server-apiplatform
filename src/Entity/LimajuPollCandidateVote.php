<?php


namespace App\Entity;


use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * A Vote on a Candidate of a Majority Judgment Poll.
 * A Vote is immutable.
 * A Vote cannot be deleted.
 *
 * @ApiResource(
 *     itemOperations={
 *         "get"={
 *             "normalization_context"={"groups"={"vote:read"}},
 *         },
 *         "delete"={
 *             "access_control"="is_granted('can_delete', object)",
 *         },
 *     },
 *     collectionOperations = {
 *         "post"={
 *             "denormalization_context"={"groups"={"vote:create"}},
 *             "normalization_context"={"groups"={"vote:read"}},
 *         },
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\LimajuPollCandidateVoteRepository")
 */
class LimajuPollCandidateVote
{
    /**
     * @var UuidInterface
     *
     * @Groups({ "read", "vote:read" })
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;


    /**
     * The Majority Judgment Poll Candidate the author is giving a mention to.
     *
     * @Groups({ "vote:create", "vote:read" })
     * @ORM\ManyToOne(targetEntity="PollCandidate", inversedBy="votes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $candidate;


    /**
     * The elector of the vote, the one that chooses the given mention.
     * (if there is such a thing as choice)
     * This is optional, and some polls may require all participants to be logged in.
     * For anonymous votes, this may be null.
     *
     * @Groups({ "none" })
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="limajuPollCandidateVotes")
     * @ORM\JoinColumn(nullable=true)
     */
    private $elector;


    /**
     * The name of the author of the vote, if any was specified.
     *
     * @Groups({ "vote:create", "vote:read" })
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $author_name;


    /**
     * The mention attributed by the author to the candidate.
     *
     * @Groups({ "vote:create", "vote:read" })
     * @ORM\Column(type="string", length=16)
     */
    private $mention;




    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getCandidate(): ?PollCandidate
    {
        return $this->candidate;
    }

    public function setCandidate(?PollCandidate $candidate): self
    {
        $this->candidate = $candidate;

        return $this;
    }

    public function getElector(): ?User
    {
        return $this->elector;
    }

    public function setElector(?User $elector): self
    {
        $this->elector = $elector;

        return $this;
    }

    public function getMention(): ?string
    {
        return $this->mention;
    }

    public function setMention(string $mention): self
    {
        $this->mention = $mention;

        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->author_name;
    }

    public function setAuthorName(?string $author_name): self
    {
        $this->author_name = $author_name;

        return $this;
    }

}
