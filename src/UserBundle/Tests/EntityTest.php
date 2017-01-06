<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

/**
 * Created by PhpStorm.
 * User: amitosh
 * Date: 30/6/16
 * Time: 7:12 AM
 */

namespace UserBundle\Tests;


use UserBundle\Entity\BadUserBlock;
use UserBundle\Entity\Profile;
use UserBundle\Entity\User;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    public function testUserBundleEntities() {
        $a = new BadUserBlock();
        $b = new User();
        $c = new Profile();

        $this->assertTrue(is_object($a));
        $this->assertTrue(is_object($b));
        $this->assertTrue(is_object($c));
    }
}
