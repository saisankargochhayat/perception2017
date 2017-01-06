<?php
/**
 * Created by PhpStorm.
 * User: amitosh
 * Date: 6/1/17
 * Time: 10:06 PM
 */

namespace AppBundle\Service;


use AppBundle\Entity\Event;
use AppBundle\Entity\UserEventParticipation;
use Doctrine\ORM\EntityManagerInterface;
use UserBundle\Entity\User;

class EventManager
{
    /** @var  EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function registerUser(Event $event, User $user) {
        if($event->isTeamEvent())
            return false;
        $record = new UserEventParticipation();
        $record->setEvent($event)
            ->setUser($user);
        $this->em->persist($event);
        $this->em->flush();
    }

    public function isRegistered(Event $event, User $user) {
        $expr = $this->em->getExpressionBuilder();
        $q = $this->em->createQueryBuilder()
            ->select($expr->count(1))
            ->from('AppBundle:UserEventParticipation','p')
            ->where($expr->eq('p.user',$user))
            ->andWhere($expr->eq('p.event',$event))
            ->getQuery()
            ->setParameters([$user,$event])
            ->getSingleScalarResult();
        return $q > 0;
    }
}