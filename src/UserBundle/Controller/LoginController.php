<?php
namespace UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LoginController
 *
 * @package UserBundle\Controller
 */
class LoginController extends Controller
{
    /**
     * @Route("login", name="login")
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        
        //implement throttling / blocking here

        $redirect_url = $request->get('redirect_url', false);

        if ($redirect_url) {

            //do not process redirects for /login and
            if ($redirect_url == $this->generateUrl('login')) {
                $redirect_url = false;
            }
        }

        return $this->render('login/login.html.twig',
                             [
                                 'last_username' => $lastUsername,
                                 'error'         => $error,
                                 'redirect_url'  => $redirect_url
                             ]);
    }
}
