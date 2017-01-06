<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

namespace UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use UserBundle\Entity\BadUserBlock;

/**
 * BadUserBlockRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BadUserBlockRepository extends EntityRepository
{
    /**
     * @return BadUserBlock|null
     */
    public function getBlockByUsernameOrEmail($username)
    {
        //THE QUERY
        //SELECT * FROM `bad_user_block` AS `b` JOIN `user` AS `u`
        //ON (b.user_id = u.id AND (u.username = ? OR u.email = ?)) LIMIT 1

        return $this->createQueryBuilder('b')
                    ->innerJoin('b.user', 'u', 'WITH', '(u.username = :user OR u.email = :user)')
                    ->setParameter('user', $username)
                    ->getQuery()
                    ->getOneOrNullResult();
    }
}
