<?php
namespace UserBundle\Controller;

use Curl\Curl;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use UserBundle\Entity\Profile;
use UserBundle\Entity\User;
use UserBundle\Security\Token\OAuthToken;

/**
 * Manages OAuth logins such as Google+ login and facebook login
 *
 * @package UserBundle\Controller
 *
 * @Route("/account/login")
 */
class OAuthLoginController extends Controller
{

    //THIS VALUES ARE ALWAYS SUBJECT TO CHANGE
    //TODO: READ VALUES FROM JSON CONFIG AND USE CRON TO UPDATE THEM
    const FACEBOOK_AUTHORIZATION_ENDPOINT = "https://www.facebook.com/dialog/oauth";

    const FACEBOOK_TOKEN_ENDPOINT    = "https://graph.facebook.com/v2.3/oauth/access_token";
    const FACEBOOK_USERINFO_ENDPOINT = "https://graph.facebook.com/v2.8/me";

    const FACEBOOK_LOGIN_TOKEN = 'facebook_login';
    const GOOGLE_LOGIN_TOKEN   = 'google_login';

    const GOOGLE_AUTHORIZATION_ENDPOINT = 'https://accounts.google.com/o/oauth2/v2/auth';
    const GOOGLE_TOKEN_ENDPOINT         = 'https://www.googleapis.com/oauth2/v4/token';
    const GOOGLE_USERINFO_ENDPOINT      = 'https://www.googleapis.com/oauth2/v3/userinfo';

    /**
     * @Route("/facebook_login", name="login_facebook")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function facebookLoginAction(Request $request)
    {
        $redirect_url = $request->get('redirect_url', false);

        if ($redirect_url) {

            //do not process redirects for /login and /home
            if ($redirect_url == $this->generateUrl('home') || $redirect_url == $this->generateUrl('login')) {
                $redirect_url = false;
            }
        }

        if ($redirect_url) {
            $request->getSession()
                    ->set('_security.main.target_path', $redirect_url);
        }

        $token = urlencode($this->get('security.csrf.token_manager')
                                ->getToken(static::FACEBOOK_LOGIN_TOKEN));
        $url = static::FACEBOOK_AUTHORIZATION_ENDPOINT . '?client_id=' . $this->getParameter('facebook_app_id') . '&redirect_uri='
               . urlencode($this->generateUrl('login_facebook_callback', [],
                                              UrlGeneratorInterface::ABSOLUTE_URL)) . "&state=$token"
               . '&response_type=code&scope=public_profile,email';

        return $this->redirect($url);
    }

    /**
     * @Route("/facebook_login_callback", name="login_facebook_callback")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function facebookLoginCallbackAction(Request $request)
    {
        $code = $request->get('code');
        $token = $request->get('state');

        if ($this->get('security.csrf.token_manager')
                 ->isTokenValid(new CsrfToken(static::FACEBOOK_LOGIN_TOKEN, $token))
        ) {

            $curl = new Curl();
            $response = $curl->get(static::FACEBOOK_TOKEN_ENDPOINT, [
                'client_id'     => $this->getParameter('facebook_app_id'),
                'redirect_uri'  => $this->generateUrl('login_facebook_callback', [],
                                                      UrlGeneratorInterface::ABSOLUTE_URL),
                'client_secret' => $this->getParameter('facebook_app_secret'),
                'code'          => $code
            ]);

            $this->checkResponse($response);

            if (!isset($response->access_token)) {
                throw new \LogicException("Expected a access token got none");
            }

            $data = $curl->get(static::FACEBOOK_USERINFO_ENDPOINT, [
                'fields'       => 'id,email,first_name,gender,last_name,link',
                'access_token' => $response->access_token
            ]);

            if (!is_object($data)) {
                throw new \Exception('object expected');
            }

            $oauth_data = [
                'provider' => 'facebook',
                'id'       => $data->id
            ];

            $request->getSession()->set('oauth_data',$oauth_data);

            if (!isset($data->first_name)) {
                $data->first_name = null;
            }

            if (!isset($data->last_name)) {
                $data->last_name = null;
            }

            if (!isset($data->email)) {
                $this->addFlash('error', 'A email ID was not provided. Please sign-up using an email');
                $request->getSession()
                        ->set('prefill_firstname', $data->first_name);
                $request->getSession()
                        ->set('prefill_lastname', $data->last_name);

                return $this->redirectToRoute('account_register');

                //prefetch the photo too!

            }

            $userData = [
                'first_name' => $data->first_name,
                'last_name'  => $data->last_name,
                'email'      => $data->email,
                'verified'   => true,
            ];
            
            if(!isset($data->gender))
                $data->gender = Profile::GENDER_UNSPECIFIED;
            if(!isset($data->link))
                $data->link = null;

            $profileData = [
                'gender' => $data->gender,
                'fb_link' => $data->link
            ];

            $val = $this->getDoctrine()
                        ->getRepository('UserBundle:User')
                        ->createOrUpdateUser($userData);

            $user = $val['user'];
            $created = $val['created'];

            if (!($user instanceof User)) {
                throw new \LogicException('Must be user but got ' . get_class($user));
            }

            $this->getDoctrine()
                    ->getRepository('UserBundle:Profile')
                    ->createOrUpdateProfileFromData($user, $profileData);

            if ($created) {
                $this->get('user_mailer')->sendWelcomeEmail($user);
            }

            return $this->oAuthLogin($request,'facebook',$user);

        } else {
            throw $this->createAccessDeniedException();
        }
    }

    private function getRedirect(Request $request)
    {
        $redirect = '';
        if ($request->hasPreviousSession() && $request->getSession()
                                                      ->has('_security.main.target_path')
        ) {
            $redirect = $request->getSession()
                                ->get('_security.main.target_path');
        }

        $request->getSession()
                ->set('_security.main.target_path', '');

        if (empty($redirect)) {
            $redirect = '/me';
        }

        return $redirect;
    }

    /**
     * @Route("/google_login", name="login_google")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function googleLoginAction(Request $request)
    {
        $redirect_url = $request->get('redirect_url', false);

        if ($redirect_url) {

            //do not process redirects for /login and /home
            if ($redirect_url == $this->generateUrl('homepage') || $redirect_url == $this->generateUrl('login')) {
                $redirect_url = false;
            }
        }

        if ($redirect_url) {
            $request->getSession()
                    ->set('_security.main.target_path', $redirect_url);
        }

        $token = urlencode($this->get('security.csrf.token_manager')
                                ->getToken(static::GOOGLE_LOGIN_TOKEN));
        $url = static::GOOGLE_AUTHORIZATION_ENDPOINT . '?client_id=' . $this->getParameter('google_app_id') . '&redirect_uri='
               . urlencode($this->generateUrl('login_google_callback', [],
                                              UrlGeneratorInterface::ABSOLUTE_URL)) . "&state=$token"
               . '&response_type=code' . '&scope=' . urlencode('openid profile email');

        return $this->redirect($url);
    }

    /**
     * @Route("/google_login_callback", name="login_google_callback")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function googleLoginCallbackAction(Request $request)
    {
        $code = $request->get('code');
        $token = $request->get('state');

        if ($this->get('security.csrf.token_manager')
                 ->isTokenValid(new CsrfToken(static::GOOGLE_LOGIN_TOKEN, $token))
        ) {

            $curl = new Curl();
            $response = $curl->post(static::GOOGLE_TOKEN_ENDPOINT, [
                'client_id'     => $this->getParameter('google_app_id'),
                'redirect_uri'  => $this->generateUrl('login_google_callback', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'client_secret' => $this->getParameter('google_app_secret'),
                'code'          => $code,
                'grant_type'    => 'authorization_code'
            ]);
            $this->checkResponse($response);
            if (!isset($response->access_token)) {
                throw new \LogicException("Expected a access token got none");
            }

            $token_type = 'Bearer';
            if (isset($response->token_type)) {
                $token_type = $response->token_type;
            }

            $access_token = $response->access_token;

            //set authorization header
            $curl->setHeader('Authorization', "$token_type $access_token");
            $data = $curl->get(static::GOOGLE_USERINFO_ENDPOINT);


            $oauth_data = [
                'provider' => 'google',
                //'id'       => $data->id
            ];

            $request->getSession()->set('oauth_data',$oauth_data);

            //$user = JSON::parse($data);
            if (!isset($data->given_name)) {
                $data->given_name = null;
            }

            if (!isset($data->family_name))
                $data->family_name = null;

            if (!is_object($data) || !isset($data->email)) {
                $request->getSession()
                        ->set('prefill_firstname', $data->given_name);
                $request->getSession()
                        ->set('prefill_lastname', $data->family_name);

                return $this->redirectToRoute('account_register');
            }


            $userData = [
                'first_name' => $data->given_name,
                'last_name'  => $data->family_name,
                'email'      => $data->email,
                'verified'   => true,
            ];

            if (!isset($data->gender))
                $data->gender = Profile::GENDER_UNSPECIFIED;

            if (!isset($data->profile))
                $data->profile = null;
            
            $profileData = [
                'gender'    => $data->gender,
                'google_link' => $data->profile
            ];

            $val = $this->getDoctrine()
                        ->getRepository('UserBundle:User')
                        ->createOrUpdateUser($userData);

            $user = $val['user'];
            $created = $val['created'];

            if (!($user instanceof User)) {
                throw new \LogicException('Must be user but got ' . get_class($user));
            }

            $this->getDoctrine()
                 ->getRepository('UserBundle:Profile')
                 ->createOrUpdateProfileFromData($user, $profileData);
            
            if ($created) {
                $this->get('user_mailer')->sendWelcomeEmail($user);
            }

            return $this->oAuthLogin($request,'google', $user);

        } else {
            throw $this->createAccessDeniedException();
        }
    }

    private function oAuthLogin(Request $request, string $provider, User $user) {
        $token = new OAuthToken($user, $provider, 'main', $user->getRoles());
        $token->setAuthenticated(true);
        $this->get("security.token_storage")
            ->setToken($token); //now the user is logged in

        //now dispatch the login event
        $event = new InteractiveLoginEvent($request, $token);
        $this->get("event_dispatcher")
            ->dispatch("security.interactive_login", $event);

        $request->getSession()->remove('oauth_data');

        return $this->redirect($this->getRedirect($request));

    }

    private function checkResponse($response) {
        if (isset($response->error_description)) {
            throw new BadRequestHttpException($response->error_description);
        }
        if (isset($response->error)) {
            $error = isset($response->error->message) ? $response->error->message : $response->error;
            throw new BadRequestHttpException($error);
        }
    }
}
