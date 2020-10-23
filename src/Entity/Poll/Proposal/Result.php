<?php


namespace App\Entity\Poll\Proposal;


use App\Entity\Poll\Proposal;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 *
 *
 * Class Result
 * @package App\Entity\Poll\Proposal
 */
class Result
{
    /**
     * @var Proposal
     * @Groups({"read"})
     */
    private $proposal;

    /**
     * Rank starts at 1 and goes upwards.
     * Two proposals may have the same rank.
     *
     * @var int Rank of the proposal in the poll.
     * @Groups({"read"})
     */
    private $rank;
}