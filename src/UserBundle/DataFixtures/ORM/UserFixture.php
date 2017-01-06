<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

namespace UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UserBundle\Entity\Profile;
use UserBundle\Entity\User;

class UserFixture extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function load(ObjectManager $manager)
    {
        //SUPER ADMIN USER
        
        $user = new User();
        $user->setUsername('admin')
             ->setFirstName('Admin')
             ->setLastName('Lecturenotes')
             ->setEmail('admin@lecturenotes.in')
            ->setRole(User::SUPER_ADMIN)
             ->setPlainPassword('secret');

        $password = $this->container->get('security.password_encoder')
                         ->encodePassword($user, $user->getPlainPassword());

        $user->setPassword($password);
        $user->setRegisterDate(new \DateTime());

        $user->setVerified(true);

        //todo: need more entropy here ??
        $verification_token = bin2hex(random_bytes(32));
        $user->setVerificationToken($verification_token);

        $manager->persist($user);

        //ADMIN
        
        $user2 = new User();
        $user2->setUsername('amitosh')
             ->setFirstName('Amitosh')
             ->setLastName('Swain')
             ->setEmail('amitosh@lecturenotes.in')
             ->setRole(User::ADMIN)
        ->setPlainPassword('skynet');

        $password = $this->container->get('security.password_encoder')
                                    ->encodePassword($user2, $user2->getPlainPassword());

        $user2->setPassword($password);

        $user2->setRegisterDate(new \DateTime());

        $user2->setVerified(true);

        //todo: need more entropy here ??
        $verification_token = bin2hex(random_bytes(32));
        $user2->setVerificationToken($verification_token);

        $manager->persist($user2);


        //USER
        
        $user3 = new User();
        $user3->setUsername('user')
             ->setFirstName('User')
             ->setLastName('Lecturenotes')
             ->setEmail('user@lecturenotes.in')
             ->setPlainPassword('password');

        $password = $this->container->get('security.password_encoder')
                                    ->encodePassword($user3, $user3->getPlainPassword());

        $user3->setPassword($password);

        $user3->setRegisterDate(new \DateTime());

        $user3->setVerified(true);

        //todo: need more entropy here ??
        $verification_token = bin2hex(random_bytes(32));
        $user3->setVerificationToken($verification_token);

        $manager->persist($user3);

        $manager->flush();

        $this->setReference('super_admin',$user);
        $this->setReference('admin',$user2);
        $this->setReference('user',$user3);

    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        //This is the first class that should run
        return 1;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}