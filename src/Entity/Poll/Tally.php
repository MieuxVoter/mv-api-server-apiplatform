<?php


namespace App\Entity\Poll;


use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\GetTallyController;
use App\Tallier\Output\PollTally as TallyOutput;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * A tally of a liquid majority judgment poll.
 * There may (will!) be additional tallies in the future,
 * and we'd love to make adding new tally algorithms easy.
 *
 * @ApiResource(
 *     shortName="Tally",
 *     itemOperations={
 *         "get_for_poll"={
 *             "method"="GET",
 *             "controller"=GetTallyController::class,
 *             "path"="/polls/{id}/tally.{_format}",
 *             "read"=false,
 *         },
 *     },
 *     collectionOperations={},
 * )
 */
class Tally
{
    /**
     * @var TallyOutput Standard tally output.
     * @Groups({"read"})
     */
    public $standard;
}