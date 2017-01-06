<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

namespace UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class OAuthLoginControllerTest extends WebTestCase
{
    public function testRender()
    {
        $client = static::createClient();

        $client->request('GET', '/account/oauth_login/facebook_login');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        
        $client->request('GET', '/account/oauth_login/google_login');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

    }

}
