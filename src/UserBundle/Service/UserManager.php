<?php
/**
 * Created by PhpStorm.
 * User: amitosh
 * Date: 6/1/17
 * Time: 8:12 PM
 */

namespace UserBundle\Service;


use Doctrine\ORM\EntityManagerInterface;
use UserBundle\Entity\User;

class UserManager
{
    /** @var  EntityManagerInterface */
    private $em;

    /** @var  UserMailer */
    private $mailer;

    public function __construct(EntityManagerInterface $em, UserMailer $mailer)
    {
        $this->em = $em;
        $this->mailer = $mailer;
    }

    public function resetPassword(User $user) {
        $verification_token = bin2hex(random_bytes(32));
        $user->setVerificationToken($verification_token);
        $em->persist($user);
        $em->flush();
        $this->mailer->sendPasswordResetRequestEmail($user);

    }

}