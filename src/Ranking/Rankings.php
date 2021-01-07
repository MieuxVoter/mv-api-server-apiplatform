<?php


namespace App\Ranking;


use App\Exception\RankingNameCollisionException;


/**
 * A service to help load the appropriate ranking algorithm from the request parameters.
 *
 * This *could* be done instead with a service locator.
 * https://symfony.com/doc/current/service_container/service_subscribers_locators.html
 *
 * Class Rankings
 * @package App\Ranking
 */
class Rankings
{

    /**
     * A list of the Ranking Services defined in this project.
     * This is automatically injected by the Dependency Injection Container,
     * with all the Services tagged with 'poll_ranking',
     * ie. all Services implementing `RankingInterface`.
     *
     * @var RankingInterface[]
     */
    protected $rankings;

    /**
     * Rankings constructor.
     *
     * @param iterable $rankings
     * @throws RankingNameCollisionException
     */
    public function __construct(iterable $rankings)
    {
        $this->assertNameUniqueness($rankings);
        $this->rankings = iterator_to_array($rankings);
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
     * The `$name` probably do not need to be sanitized and may come directly from userland.
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
     * Guard against misconfiguration of Rankings.
     *
     * @param RankingInterface[] $rankings
     * @throws RankingNameCollisionException
     */
    protected function assertNameUniqueness(iterable $rankings) : void
    {
        $rankingsByName = [];
        foreach ($rankings as $ranking) {
            $name = $ranking->getName();

            if (isset($rankingsByName[$name])) {
                throw new RankingNameCollisionException("error.ranking.name_collision");
            }

            $rankingsByName[$name] = $ranking;
        }
    }

}