<?php


namespace App\Ranking;


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
        // fixme: assert unicity of names
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

}