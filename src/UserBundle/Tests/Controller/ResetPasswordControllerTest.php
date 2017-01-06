<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

namespace UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ResetPasswordControllerTest extends WebTestCase
{
    public function testResetpassword()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/account/reset_password');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

    }

    public function testVerification()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/account/reset_password/notakey');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

    }
}
