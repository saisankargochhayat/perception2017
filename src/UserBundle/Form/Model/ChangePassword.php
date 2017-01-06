<?php
/**
 * Created by PhpStorm.
 * User: amitosh
 * Date: 4/6/16
 * Time: 6:49 PM
 */

namespace UserBundle\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ChangePassword
{

    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $new;

    /**
     * @return string
     */
    public function getNew()
    {
        return $this->new;
    }

    /**
     * @param string $new
     */
    public function setNew($new)
    {
        $this->new = $new;
    }
}
