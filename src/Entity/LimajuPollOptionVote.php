<?php


namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\LimajuPollOptionVoteRepository")
 */
class LimajuPollOptionVote
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * The Majority Judgment Poll Option the author is giving a mention to.
     *
     * @Groups({ "create", "read" })
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
     * @Groups({ "read" })
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="limajuPollOptionVotes")
     * @ORM\JoinColumn(nullable=true)
     */
    private $author;


    /**
     * The name of the author of the vote, if any was specified.
     *
     * @Groups({ "create", "read", "update" })
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $author_name;


    /**
     * The mention attributed by the author to the option.
     *
     * @Groups({ "create", "read", "update" })
     * @ORM\Column(type="string", length=16)
     */
    private $mention;




    public function getId(): ?int
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
