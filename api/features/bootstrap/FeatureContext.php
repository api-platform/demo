<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\MinkContext;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /**
     * @var ManagerRegistry
     */
    private $doctrine;

    /**
     * @var SchemaTool
     */
    private $schemaTool;

    /**
     * @var array
     */
    private $classes;

    /**
     * @var MinkContext
     */
    private $minkContext;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
        $manager = $doctrine->getManager();
        $this->schemaTool = new SchemaTool($manager);
        $this->classes = $manager->getMetadataFactory()->getAllMetadata();
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->minkContext = $scope->getEnvironment()->getContext(MinkContext::class);
        $this->minkContext->getSession()->getDriver()->getClient()->disableReboot();
    }

    /**
     * @BeforeScenario
     */
    public function createTransaction()
    {
        $this->doctrine->getConnection()->beginTransaction();
    }

    /**
     * @AfterScenario
     */
    public function rollbackTransaction()
    {
        $this->doctrine->getConnection()->rollback();
    }
}
