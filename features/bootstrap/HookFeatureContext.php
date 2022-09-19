<?php

namespace Features;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Testwork\Hook\Scope\AfterSuiteScope;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;


/**
 * Where we define our feature-suite lifecycle hooks.
 *
 * We're using this for:
 * - Fixtures
 * - Fortune
 */
class HookFeatureContext extends BaseFeatureContext
{
    /**
     * Prepare the system for the test suite before it runs,
     * by booting the kernel (in test mode, apparently)
     * and loading fresh fixtures into an empty db.
     *
     * This is run before each new Scenario.
     *
     * @BeforeScenario
     */
    public function prepare(BeforeScenarioScope $scope)
    {
        // (Re)Boot the kernel
        static::bootKernel();

        // Loading an empty array still truncates all tables.
        $this->loadFixtures(array());
    }


//    /**
//     * Not 100% sure we need to reboot the kernel on each step. Perhaps we do.
//     * @BeforeStep
//     */
//    public function prepareStep(\Behat\Behat\Hook\Scope\BeforeStepScope $scope)
//    {
//        // (Re)Boot the kernel
//        static::bootKernel();
//    }


    /**
     * Train our inner pigeon into enjoying Feature-Driven Development…
     *
     * @AfterSuite
     */
    public static function nomnomCookieCat(AfterSuiteScope $scope)
    {
        if ($scope->getTestResult()->isPassed()) {
            try { print(shell_exec('fortune -a -n 222 | cowsay -f dragon -W 60')); } catch (\Exception $e) {}
        }
    }

}
