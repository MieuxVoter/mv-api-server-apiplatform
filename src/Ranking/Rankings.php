<?php


namespace App\Ranking;


use App\Exception\RankingNameCollisionException;


/**
 * A service to help load the appropriate ranking algorithm from the request parameters.
 *
 * Class Rankings
 * @package App\Ranking
 */
class Rankings
{

    /**
     * A list of the Ranking Services defined in this project.
     * This is auto-loaded by the Dependency Injection Container,
     * from all the Services tagged with 'poll_ranking',
     * ie. all Services implementing `RankingInterface`.
     *
     * @var RankingInterface[]
     */
    protected $rankings;

    /**
     * Rankings constructor.
     */
    public function __construct(iterable $rankings)
    {
        $this->assertNameUniqueness($rankings);
        $this->rankings = $rankings;
    }

    /**
     * @return RankingInterface[]
     */
    public function getRankings(): array
    {
        return $this->rankings;
    }

    /**
     * Returns a Ranking matching the provided name, if any.
     *
     * @param string $name
     * @return RankingInterface|null
     */
    public function findByName(string $name) : ?RankingInterface
    {
        foreach ($this->getRankings() as $ranking) {
            if ($ranking->getName() === $name) {
                return $ranking;
            }
        }

        return null;
    }

    /**
     * @param RankingInterface[] $rankings
     * @throws RankingNameCollisionException
     */
    protected function assertNameUniqueness(iterable $rankings) : void
    {
        $uniqueNames = [];
        foreach ($rankings as $ranking) {
            $name = $ranking->getName();

            if (isset($uniqueNames[$name])) {
                throw new RankingNameCollisionException("error.ranking.name_collision");
            }

            $uniqueNames[$name] = $ranking;
        }
    }

}