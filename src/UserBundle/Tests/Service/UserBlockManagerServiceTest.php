<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

/**
 * Created by PhpStorm.
 * User: amitosh
 * Date: 19/6/16
 * Time: 6:18 PM
 */

namespace UserBundle\Tests\Service;


use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Monolog\Logger;
use UserBundle\Entity\BadUserBlock;
use UserBundle\Entity\User;
use UserBundle\Repository\BadUserBlockRepository;
use UserBundle\Service\UserBlockManager;

class UserBlockManagerSeviceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * A good user should be allowed to login
     */
    public function testGoodUser() {

        $user = new BadUserBlock();
        $user->setUser($this->getMock(User::class));

        $user->setLoginAttempts(0);
        $user->addLoginAttempt(); //last login time is now
        $user->setLastLoginAttempt(new \DateTime());

        $repository = $this
            ->getMockBuilder(BadUserBlockRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->any())
                   ->method('getBlockByUsernameOrEmail')
                   ->will($this->returnValue($user));

        $em = $this->getMockBuilder(EntityManagerInterface::class)
                   ->disableOriginalConstructor()
                   ->getMock();

        $em->expects($this->any())
           ->method('getRepository')
           ->will($this->returnValue($repository));

        $manager = new UserBlockManager($em,null,3,10);

        //$this->assertTrue(true);
        //return true;
        $this->assertFalse($manager->isEmailOrUsernameThrottled('gooduser'));
        $this->assertFalse($manager->isEmailOrUsernameBlocked('gooduser'));
        
    }

    /**
     * This user is trying to login to an ID but is failing password verification
     * should be throttled. (Shown a captcha)
     */
    public function testDubiousUser() {

        $user = new BadUserBlock();
        $user->setUser($this->getMock(User::class));

        $user->setLoginAttempts(3);
        $user->addLoginAttempt(); //last login time is now
        $user->setLastLoginAttempt(new \DateTime());

        $repository = $this
            ->getMockBuilder(BadUserBlockRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->any())
                   ->method('getBlockByUsernameOrEmail')
                   ->will($this->returnValue($user));

        $em = $this->getMockBuilder(EntityManagerInterface::class)
                   ->disableOriginalConstructor()
                   ->getMock();

        $em->expects($this->any())
           ->method('getRepository')
           ->will($this->returnValue($repository));

        $manager = new UserBlockManager($em,null,3,10);

        //user should be throttled
        $this->assertTrue($manager->isEmailOrUsernameThrottled('dubious_user'));

        //user should not be blocked
        $this->assertFalse($manager->isEmailOrUsernameBlocked('dubious_user'));

    }

    /**
     * A bad user should be blocked
     */
    public function testBadUser() {

        $user = new BadUserBlock();
        $user->setUser($this->getMock(User::class));

        $user->setLoginAttempts(10);
        $user->addLoginAttempt(); //last login time is now
        $user->setLastLoginAttempt(new \DateTime());

        $repository = $this
            ->getMockBuilder(BadUserBlockRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->any())
                   ->method('getBlockByUsernameOrEmail')
                   ->will($this->returnValue($user));

        $em = $this->getMockBuilder(EntityManagerInterface::class)
                   ->disableOriginalConstructor()
                   ->getMock();

        $em->expects($this->any())
           ->method('getRepository')
           ->will($this->returnValue($repository));

        $manager = new UserBlockManager($em,null,3,10);

        //user should be throttled
        $this->assertTrue($manager->isEmailOrUsernameThrottled('baduser'));

        //user should be blocked
        $this->assertTrue($manager->isEmailOrUsernameBlocked('baduser'));

    }

    /**
     * An innocent user was targeted by a bad user.
     * Now when re returns he should be able to login provided it's 30 mins
     * since the bad user last attempted login.
     */
    public function testInnocentUser() {

        $user = new BadUserBlock();
        $user->setUser("baduser");

        $user->setLoginAttempts(10);
        $user->addLoginAttempt(); //last login time is now
        $user->setLastLoginAttempt(new \DateTime());

        $repository = $this
            ->getMockBuilder(BadUserBlockRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repository->expects($this->any())
                   ->method('getBlockByUsernameOrEmail')
                   ->will($this->returnValue($user));

        $em = $this->getMockBuilder(EntityManagerInterface::class)
                   ->disableOriginalConstructor()
                   ->getMock();

        $em->expects($this->any())
           ->method('getRepository')
           ->will($this->returnValue($repository));

        $manager = new UserBlockManager($em,null,3,10);

        //user should be throttled
        $this->assertTrue($manager->isEmailOrUsernameThrottled('baduser'));

        //user should be blocked
        $this->assertTrue($manager->isEmailOrUsernameBlocked('baduser'));


        //thirty minutes have passed
        $now  = new \DateTime();
        //$interval 
        $aHourAgo = $now->sub(new \DateInterval('PT1H'));
        
        $user->setLastLoginAttempt($aHourAgo);

        $this->assertTrue($manager->isEmailOrUsernameThrottled('baduser'));
        $this->assertFalse($manager->isEmailOrUsernameBlocked('baduser'));

    }
}
