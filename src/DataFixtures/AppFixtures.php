<?php


namespace App\DataFixtures;


use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;


/**
 * This application needs no fixtures for the moment.
 * This file allows us to leverage the database reset utilities of fixtures in the gherkin suite.
 *
 * We might add some preset mentions.  Might not.  Clients ought to do it?
 *
 * To set up a database with specific fixtures, use a Scenario.
 * The test database is cleared before each scenario, not after.
 *
 * Class AppFixtures
 * @package App\DataFixtures
 */
class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
