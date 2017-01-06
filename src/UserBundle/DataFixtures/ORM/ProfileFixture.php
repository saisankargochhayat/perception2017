<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

namespace UserBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use UserBundle\Entity\Profile;

class ProfileFixture extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1000;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        // TODO: Implement load() method.

        //var_dump($manager->getRepository('UserBundle:User')->loadUserByUsername('admin'));

        $manager->getRepository('UserBundle:Profile')->createProfile($this->getReference('super_admin'));
        $manager->getRepository('UserBundle:Profile')->createProfile($this->getReference('admin'),[
            'institute' => $this->getReference('cet'),
            'course' => $this->getReference('btech')
        ]);
        $manager->getRepository('UserBundle:Profile')->createProfile($this->getReference('user'),[
            'institute' => $this->getReference('sit'),
            'course' => $this->getReference('btech')
        ]);


    }
}