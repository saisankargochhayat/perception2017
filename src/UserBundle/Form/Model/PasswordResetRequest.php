<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

/**
 * Created by PhpStorm.
 * User: amitosh
 * Date: 5/6/16
 * Time: 11:07 AM
 */

namespace UserBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class PasswordResetRequest
{
    /**
     * @var string
     * @Assert\Email()
     */
    private $email;
    
    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }
}
