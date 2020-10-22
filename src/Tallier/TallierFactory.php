<?php


namespace App\Tallier;


use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Helps fetching the appropriate tallier algorithm.
 *
 * Perhaps not a Factory, since it does not instantiate anything.
 * TallierPool ?
 *
 * Class TallierFactory
 * @package App\Tallier
 */
class TallierFactory
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $tallierName
     * @return TallierInterface
     */
    public function findByName(string $tallierName) : TallierInterface
    {
        $tallierFilename = ucwords($tallierName);
        $tallierFilename = $this->sanitizeTallierName($tallierFilename);
        /** @noinspection MissingService */
        /** @noinspection CaseSensitivityServiceInspection */
        return $this->container->get("App\\Tallier\\${tallierFilename}Tallier");
    }

    protected function sanitizeTallierName($tallierName)
    {
        $numbers = [
            'Zero', 'One', 'Two', 'Three', 'Four',
            'Five', 'Six', 'Seven', 'Eight', 'Nine',
        ];
        foreach($numbers as $k => $s) {
            $tallierName = str_replace("$k", "$s", $tallierName);
        }
        return preg_replace("[^a-zA-Z]", "", $tallierName);
    }
}