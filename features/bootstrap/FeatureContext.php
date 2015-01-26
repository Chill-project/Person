<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;

use Behat\MinkExtension\Context\MinkContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context, SnippetAcceptingContext
{
    use Behat\Symfony2Extension\Context\KernelDictionary;
    
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }
    
    /**
     * @Given I am logged as :username with password :password
     */
    public function iAmLoggedAsWithPassword($username, $password)
    {
        $this->getSession()->setBasicAuth($username,$password);
    }
    
    /**
     *
     * @var Chill\PersonBundle\Entity\Person[]
     */
    private $allPeople;
    
    /**
     * @var Chill\PersonBundle\Entity\Person
     */
    private $person = NULL;
    
    /**
     * @Given a random person is selected
     */
    public function aRandomPersonIsSelected()
    {
        if ($this->allPeople === NULL) {
            $this->allPeople = $this->getContainer()->get('doctrine.orm.entity_manager')
                  ->getRepository('ChillPersonBundle:Person')
                  ->findAll();
        }
        
        $this->person = $this->allPeople[rand(0, count($this->allPeople) -1 )];
    }
    
    /**
     * @Given I am on landing person page for the selected person
     */
    public function iAmOnLandingPersonPageForTheSelectedPerson()
    {
        $this->getSession()->visit(sprintf('/en/person/%s/general', $this->person->getId()));
    }

}
