<?php

namespace CL\Chill\PersonBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PersonControllerTest extends WebTestCase
{
    public function testSee()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/see');
    }

}
