<?php


namespace App\Entity;


use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
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
 * @ORM\Entity(repositoryClass="App\Repository\LimajuPollOptionVoteRepository")
 */
class LimajuPollOptionVote
{
    /**
     * @var UuidInterface
     *
     * @Groups({ "read" })
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;


    /**
     * The Majority Judgment Poll Option the author is giving a mention to.
     *
     * @Groups({ "vote:create", "vote:read" })
     * @ORM\ManyToOne(targetEntity="App\Entity\LimajuPollOption", inversedBy="votes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $option;


    /**
     * The author of the vote, the one that chooses the given mention.
     * (if there is such a thing as choice)
     * This is optional, and some polls may require all participants to be logged in.
     * For anonymous votes, this may be null.
     *
     * @Groups({ "none" })
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="limajuPollOptionVotes")
     * @ORM\JoinColumn(nullable=true)
     */
    private $author;


    /**
     * The name of the author of the vote, if any was specified.
     *
     * @Groups({ "vote:create", "vote:read", "vote:update" })
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $author_name;


    /**
     * The mention attributed by the author to the option.
     *
     * @Groups({ "vote:create", "vote:read", "vote:update" })
     * @ORM\Column(type="string", length=16)
     */
    private $mention;




    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getOption(): ?LimajuPollOption
    {
        return $this->option;
    }

    public function setOption(?LimajuPollOption $option): self
    {
        $this->option = $option;

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
