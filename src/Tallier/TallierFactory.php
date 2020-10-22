<?php


namespace App\Tallier;


use Symfony\Component\DependencyInjection\ContainerInterface;


/**
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
     * /!. NAIVE = does not check $tallyName sanity
     * @param string $tallyName
     * @return TallierInterface
     */
    public function findByName(string $tallyName) : TallierInterface
    {
        $tallyFileName = ucwords($tallyName);
        /** @noinspection MissingService */
        /** @noinspection CaseSensitivityServiceInspection */
        return $this->container->get("App\\Tallier\\${tallyFileName}Tallier");
    }
}