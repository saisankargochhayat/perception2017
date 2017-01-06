<?php
/**
 * Created by PhpStorm.
 * User: amitosh
 * Date: 14/6/16
 * Time: 11:17 PM
 */

namespace UserBundle\Security\Token;


use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Represents a OAuth User Token
 *
 * @package UserBundle\Security\Token
 */
class OAuthToken extends AbstractToken
{

    /**
     * @var string
     */
    private $provider;

    /**
     * @var string
     */
    private $oAuthId;

    public function __construct(UserInterface $user,$oauthid, $provider, array $roles = [])
    {
        parent::__construct($roles);
        $this->provider = $provider;

        parent::setUser($user);
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    public function getOAuthId()
    {
        return $this->oAuthId;
    }

    /**
     * @param string $oAuthId
     */
    public function setOAuthId($oAuthId)
    {
        $this->oAuthId = $oAuthId;
    }
    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        return $this->provider . '_' . $this->oAuthId;
    }
}