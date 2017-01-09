<?php
namespace UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * User
 * @ORM\Table(name="user_data")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Email Already Registered")
 */
class User implements UserInterface, \Serializable, EquatableInterface
{
    const USER        = 1;
    const COORDINATOR = 2;
    const ADMIN       = 32767;
    const SUPER_ADMIN = 65535;

    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=false)
     */
    public $firstName;
    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    public $lastName;
    /**
     * @var string
     * @Assert\Email(message="Invalid email address")
     * @ORM\Column(name="email", type="string", length=255, unique=true, options={"collation":"utf8mb4_bin"})
     */
    private $email;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", unique=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=64, nullable=true, options={"fixed":true})
     */
    private $password;
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min=6, max=4096, minMessage="password.too_short")
     */
    private $plainPassword;
    /**
     * @var \DateTime
     * @Assert\DateTime()
     * @ORM\Column(name="register_date", type="datetime", options={"default":0})
     */
    private $registerDate;
    /**
     * @var int
     *
     * @ORM\Column(name="role", type="integer", options={"default":1})
     */
    private $role = User::USER;
    /**
     * @var bool
     *
     * @ORM\Column(name="verified", type="boolean", options={"default":false})
     */
    private $verified = false;
    /**
     * @var bool
     *
     * @ORM\Column(name="locked", type="boolean", options={"default":false})
     */
    private $locked = false;
    /**
     * @var string
     *
     * @ORM\Column(name="verification_token", type="string", length=64, nullable=true, unique=true, options={"fixed":true})
     */
    private $verificationToken;
    /**
     * @var \DateTime
     * @Assert\DateTime()
     * @ORM\Column(name="last_seen", type="datetime", nullable=true)
     */
    private $lastSeen;
    /**
     * @var int
     * @ORM\Column(name="type", type="smallint")
     */
    private $type = self::USER;

    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->registerDate = new \DateTime();
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    /**
     * Get registerDate
     *
     * @return \DateTime
     */
    public function getRegisterDate()
    {
        return $this->registerDate;
    }

    /**
     * Set registerDate
     *
     * @param \DateTime $registerDate
     *
     * @return User
     */
    public function setRegisterDate($registerDate)
    {
        $this->registerDate = $registerDate;

        return $this;
    }

    /**
     * String representation of object
     *
     * @link  http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize([$this->id, $this->email, $this->password, $this->verified, $this->locked]);
    }

    /**
     * Constructs the object
     *
     * @link  http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized <p>
     *                           The string representation of the object.
     *                           </p>
     *
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        list($this->id, $this->email, $this->password, $this->verified, $this->locked) = unserialize($serialized);
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return array (Role|string)[] The user roles
     */
    public function getRoles()
    {
        $roles = ['ROLE_USER'];
        $role = $this->getRole();

        if ($this->isVerified()) {
            $roles[] = 'ROLE_VERIFIED_USER';
        }

        switch ($role) {
            case self::COORDINATOR:
                $roles[] = 'ROLE_COORDINATOR';
                break;
            case self::ADMIN:
                $roles[] = 'ROLE_ADMIN';
                break;
            case self::SUPER_ADMIN:
                $roles[] = 'ROLE_SUPER_ADMIN';
                break;

        }

        return $roles;
    }

    /**
     * Get role
     *
     * @return integer
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set role
     *
     * @param integer $role
     *
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    public function isVerified()
    {
        return $this->verified;
    }

    /**
     * Get verified
     *
     * @return boolean
     */
    public function getVerified()
    {
        return $this->verified;
    }

    /**
     * Set verified
     *
     * @param boolean $verified
     *
     * @return User
     */
    public function setVerified($verified)
    {
        $this->verified = $verified;

        return $this;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        unset($this->plainPassword);
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->verified;
    }

    /**
     * Get verificationToken
     *
     * @return string
     */
    public function getVerificationToken()
    {
        return $this->verificationToken;
    }

    /**
     * Set verificationToken
     *
     * @param string $verificationToken
     *
     * @return User
     */
    public function setVerificationToken($verificationToken)
    {
        $this->verificationToken = $verificationToken;

        return $this;
    }

    /**
     * Get realName
     *
     * @return string
     */
    public function getRealName()
    {
        return trim("$this->firstName $this->lastName");
    }

    /**
     * The equality comparison should neither be done by referential equality
     * nor by comparing identities (i.e. getId() === getId()).
     *
     * However, you do not need to compare every attribute, but only those that
     * are relevant for assessing whether re-authentication is required.
     *
     * Also implementation should consider that $user instance may implement
     * the extended user interface `AdvancedUserInterface`.
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        // TODO: Implement isEqualTo() method.
        if (!$user instanceof User) {
            return false;
        }

        return $user->getId() == $this->getId()
               && $user->getVerified() == $this->verified
               && $user->getLocked() == $this->locked
               && $user->getPassword() == $this->password;
        //&& $user->getPassword() == $this->getPassword();
    }

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
     * Get locked
     *
     * @return boolean
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * Set locked
     *
     * @param boolean $locked
     *
     * @return User
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastSeen
     *
     * @return \DateTime
     */
    public function getLastSeen()
    {
        return $this->lastSeen;
    }

    /**
     * Set lastSeen
     *
     * @param \DateTime $lastSeen
     *
     * @return User
     */
    public function setLastSeen($lastSeen)
    {
        $this->lastSeen = $lastSeen;

        return $this;
    }

    /**
     * @return int
     */
    public function getType() : int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function __toString() : string
    {
        return $this->email;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername() : string
    {
        return $this->email;
    }

    public function getName() : string {
        return $this->firstName . ' ' . $this->lastName;
    }
}
