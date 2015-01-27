<?php

namespace Chill\PersonBundle\Tests\Behat\Context;

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
    use PersonTrait;
    //use \Behat\Symfony2Extension\Context\KernelDictionary;
    
    /**
     * @Given I am logged as :username with password :password
     */
    public function iAmLoggedAsWithPassword($username, $password)
    {
        $this->getSession()->setBasicAuth($username,$password);
    }

}
