<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Profile
 *
 * @ORM\Table(name="user_profile")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\ProfileRepository")
 */
class Profile
{
    const GENDER_UNSPECIFIED = 0;
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;
    const GENDER_OTHERS = 3;
    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="UserBundle\Entity\User")
     */
    private $user;

    /**
     * @var string
     * @ORM\Column(name="avatar", type="string", length=255, nullable=true)
     */
    private $avatar;

    /**
     * @var int
     * @ORM\Column(name="gender", type="smallint")
     */
    private $gender = self::GENDER_UNSPECIFIED;

    /**
     * @var string
     * @ORM\Column(name="phone", type="bigint", length=15, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=32, nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="institute", type="string")
     */
    private $institute;

    /**
     * @var string
     *
     *
     * @ORM\Column(name="stream", type="string")
     */
    private $stream;

    /**
     * @var string
     *
     * @ORM\Column(name="specialization", type="string")
     */
    private $specialization;

    /**
     * @var int
     *
     * @ORM\Column(name="year_of_study", type="smallint", length=4)
     */
    private $yearOfStudy;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     *
     * @return Profile
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set gender
     *
     * @param integer $gender
     *
     * @return Profile
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return integer
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set phone
     *
     * @param integer $phone
     *
     * @return Profile
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return integer
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Profile
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set institute
     *
     * @param string $institute
     *
     * @return Profile
     */
    public function setInstitute($institute)
    {
        $this->institute = $institute;

        return $this;
    }

    /**
     * Get institute
     *
     * @return string
     */
    public function getInstitute()
    {
        return $this->institute;
    }

    /**
     * Set stream
     *
     * @param string $stream
     *
     * @return Profile
     */
    public function setStream($stream)
    {
        $this->stream = $stream;

        return $this;
    }

    /**
     * Get stream
     *
     * @return string
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Set specialization
     *
     * @param string $specialization
     *
     * @return Profile
     */
    public function setSpecialization($specialization)
    {
        $this->specialization = $specialization;

        return $this;
    }

    /**
     * Get specialization
     *
     * @return string
     */
    public function getSpecialization()
    {
        return $this->specialization;
    }

    /**
     * Set yearOfStudy
     *
     * @param integer $yearOfStudy
     *
     * @return Profile
     */
    public function setYearOfStudy($yearOfStudy)
    {
        $this->yearOfStudy = $yearOfStudy;

        return $this;
    }

    /**
     * Get yearOfStudy
     *
     * @return integer
     */
    public function getYearOfStudy()
    {
        return $this->yearOfStudy;
    }

    /**
     * Set user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return Profile
     */
    public function setUser(\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
