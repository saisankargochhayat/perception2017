<?php
namespace UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use UserBundle\Entity\User;

/**
 * UserRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserRepository extends EntityRepository implements UserLoaderInterface
{
    /**
     * Loads the user for the given username.
     *
     * This method must return null if the user is not found.
     *
     * @param string $username The username
     *
     * @return User|null
     */
    public function loadUserByUsername($username)
    {
        if (is_null($username)) {
            return null;
        }
        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery()
            //->useQueryCache(true)
           // ->useResultCache(true)
            //->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * @param $key
     *
     * @return User
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function loadUserByVerificationKey($key)
    {

        if (is_null($key)) {
            return null;
        }

        return $this->createQueryBuilder('u')
            ->where('u.verificationToken = :token')
            ->setParameter('token', $key)
            ->getQuery()
            ->useQueryCache(true)
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    public function createUser(User $user, $verified = false, $role = User::USER)
    {

        $user->setRole($role);
        $user->setRegisterDate(new \DateTime());

        $user->setVerified($verified);

        //todo: need more entropy here ??
        $verification_token = bin2hex(random_bytes(32));
        $user->setVerificationToken($verification_token);

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Searches a user by email, creates if user does not exist and updates with new data if user exists.
     *
     * @param $data array user info
     *
     * @return array
     */
    public function createOrUpdateUser(array $data)
    {

        //do we create a new user ?

        $created = false;
        $user = $this->findOneBy(['email' => $data['email']]);

        if ($user == null) {
            $user = new User();
            $user->setEmail($data['email']);
            $created = true;
        }

        if (key_exists('username', $data) && is_null($user->getUsername()))
            $user->setUsername($data['username']);

//        if (key_exists('password', $data))
//            $user->setPassword($data['password']);

        if (key_exists('first_name', $data) && is_null($user->getFirstName()))
            $user->setFirstName($data['first_name']);

        if (key_exists('last_name', $data) && is_null($user->getLastName()))
            $user->setLastName($data['last_name']);

        if (key_exists('verified', $data))
            $user->setVerified($data['verified']);

        if (key_exists('locked', $data))
            $user->setLocked($data['locked']);
        
        if($created)
            $user->setRegisterDate(new \DateTime());

        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();

        return [
            'user' => $user,
            'created' => $created
        ];
    }

    /**
     * @return array
     *
     * Load all user email.
     */
    public function loadAllUserEmail(){
        return $this->getEntityManager()->createQuery(
            'SELECT u.email FROM UserBundle:User as u'
        )
            ->getScalarResult();

    }

    /**
     * @return array
     * 
     * Load emails of all faculty or verified faculty.
     */
    public function loadAllFacultyEmail(){
        return $this->getEntityManager()->createQuery(
            'SELECT u.email 
              FROM UserBundle:User as u 
              WHERE u.role = 7 OR u.role = 31'
        )
            ->getScalarResult();
    }

    /**
     * @param $institute
     * @return array
     * 
     * Load all faculty emails from a specified college
     */
    public function loadFacultyEmailByInstitution($institute){
        return $this->getEntityManager()->createQuery(
            'SELECT u.email
            FROM UserBundle:User as u
            JOIN UserBundle:Profile as p WITH p.user = u.id
            WHERE p.presentInstitution=:institute AND u.role = 7 OR u.role = 31'
        )
            ->setParameter('institute', $institute)
            ->getScalarResult();
    }

    public function loadStudentsEmailByInstitution($institute){
        return $this->getEntityManager()->createQuery(
            'SELECT u.email
            FROM UserBundle:User as u
            JOIN UserBundle:Profile as p
            WITH p.user = u.id
            WHERE p.presentInstitution=:institute AND u.role = 1'
        )
            ->setParameter('institute', $institute)
            ->getScalarResult();
    }
    
}
