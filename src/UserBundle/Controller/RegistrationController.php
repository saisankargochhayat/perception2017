<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

namespace UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use UserBundle\Entity\User;
use UserBundle\Form\RegistrationType;
use UserBundle\Security\Token\GuardedUsernamePasswordToken;

/**
 * Class RegistrationController
 *
 * @package UserBundle\Controller
 *
 * @Route("/account/register")
 */
class RegistrationController extends Controller
{
    /**
     * @Route("/", name="account_register")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request)
    {
        $user = new User();

        // get data from previous attempt from OAuth
        $user->setFirstName($request->getSession()
                                    ->get('prefill_firstname'));
        $user->setLastName($request->getSession()
                                   ->get('prefill_lastname'));

        //clear previous oauth data
        $request->getSession()->remove('oauth_data');
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->get('security.password_encoder')
                             ->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $this->getDoctrine()
                 ->getRepository('UserBundle:User')
                 ->createUser($user);

            $this->get('user_mailer')->sendVerificationEmail($user);

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user
            $this->addFlash('success', $this->get('translator')->trans('user.register.success'));

            //create a profile for the user
            $this->getDoctrine()
                 ->getManager()
                 ->getRepository('UserBundle:Profile')
                 ->createProfile($user);

            $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
            $token->setUser($user);
            $this->get("security.token_storage")
                 ->setToken($token); //now the user is logged in

            //now dispatch the login event
            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")
                 ->dispatch("security.interactive_login", $event);

            // remove prefill data
            $request->getSession()
                    ->remove('prefill_firstname');
            $request->getSession()
                    ->remove('prefill_lastname');

            $response = $this->redirect($this->getRedirect($request));
            $response->headers->setCookie(new Cookie('register_success', true, 0, '/', null, false, false));

            return $response;
        }

        return $this->render(
            'registration/register.html.twig',
            ['form' => $form->createView()]
        );
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
            $redirect = '/';
        }

        return $redirect;
    }

    /**
     * @Route("/verify/{key}", name="account_registration_verify")
     * @param $key
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function verifyAction($key)
    {
        $user = $this->getDoctrine()
                     ->getRepository('UserBundle:User')
                     ->loadUserByVerificationKey($key);

        if (!is_null($user)) {
            if ($user->isVerified()) {
                $this->addFlash('info', 'Your account has already been verified.');

                return $this->redirectToRoute('login');
            } else {
                $user->setVerified(true);
                $em = $this->getDoctrine()
                           ->getManager();
                $em->persist($user);
                $em->flush();
                $this->addFlash('info', 'Your account has been verified.');
                $this->get('user_mailer')->sendWelcomeEmail($user);
                return $this->redirectToRoute('login', [], 302);
            }
        } else {
            $this->addFlash('info', 'Verification token has expired or is invalid.');
            return $this->redirectToRoute('login', [], 302);
        }
    }
}
