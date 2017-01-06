<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

namespace UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testR()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/account/register/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
