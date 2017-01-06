<?php
namespace UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Form\ChangePasswordType;
use UserBundle\Form\Model\ChangePassword;

/**
 * Class MeController
 * @package UserBundle\Controller
 *
 * @Security("has_role('ROLE_USER')")
 * @Route("/account")
 */
class AccountController extends Controller
{
//    /**
//     * Display own profile
//     * @Route("/my-profile", name="account_user_profile")
//     * @Method("GET")
//     */
//    public function myProfileAction()
//    {
//        if (!$this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
//            throw $this->createAccessDeniedException();
//        }
//
//        $id = $this->getUser()->getUsername();
//
//        if (!$id) {
//            $id = $this->get('global.hash_generator')->encode($this->getUser()->getId());
//        }
//
//        return $this->redirectToRoute('user_profile_show', ['id' => $id]);
//    }

    /**
     * @Route("/edit_profile", name="account_edit_user_profile")
     * @Method("GET")
     */
    public function editProfileAction()
    {
        $id = $this->getUser()->getUsername();

        if (!$id) {
            $id = $this->get('global.hash_generator')->encode($this->getUser()->getId());
        }

        return $this->redirectToRoute('user_profile_edit', ['id' => $id]);
    }

    /**
     * @Route("/settings", name="account_settings")
     * @Method("GET")
     */
    public function settingsAction()
    {
        return $this->render('UserBundle:Me:settings.html.twig', array(
            // ...
        ));
    }

    /**
     *
     * @Route("/settings/change_username", name="account_setings_create_username")
     *
     */
    public function changeUsernameAction(Request $request)
    {
        //make sure that the user is fully authenticated
        //do not allow remember me sessions here
        //return new JsonResponse(['data' => var_dump($request->request)]);
        //todo:bug:not working!
//        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
//            $this->createAccessDeniedException();
//        }
        $user = $this->get('security.token_storage')
            ->getToken()
            ->getUser();
        //var_dump($user->getUsername());
        $form = $this->createFormBuilder()
                    ->add('username', TextType::class,[
                            'required'=>false,
                            'mapped'=>false,
                            'data' => $user->getUsername(),
                            'attr' => ['class' => 'form-control'],
                    ])
//                    ->add('submit', SubmitType::class, [
//                        'attr' => ['class' => 'username-submit btn btn-primary']
//                    ])
                    ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

           $username = $form['username']->getData();
            //var_dump($username);
            if($user->getUsername()==$username){
                //echo 1;
                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Same Username as before.'
                ]);
            }
            //check if username already taken
            $check = $this->getDoctrine()->getRepository('UserBundle:User')
                ->findBy(['username'=> $username]);

            if(!empty($check)){
                $html = $this->renderView('@User/Me/change_username.html.twig',
                    [
                        'form' => $form->createView(),
                    ]);
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Username already taken, try something else',
                    'html'  => $html
                ]);
            }

            //check if username has any unwanted characters including space and . only allow alpha numeric
            if(!preg_match("/^[a-zA-Z0-9_]+$/", $username)){
                $html = $this->renderView('@User/Me/change_username.html.twig',
                    [
                        'form' => $form->createView(),
                    ]);
                return new JsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid Characters encountered in the string.',
                    'html'  => $html
                ]);
            }

            $user->setUsername($username);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Username Changed'
            ]);
        }
        if($request->isXmlHttpRequest()){
            $html = $this->renderView('@User/Me/change_username.html.twig',
                [
                    'form' => $form->createView(),
                ]);
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid Details',
                'html' => $html,
            ]);
        }else{
            return $this->render(
                'UserBundle:Me:change_username.html.twig',
                [
                    'form' => $form->createView(),
                    'user' => $user
                ]
            );
        }

//        return $this->render('UserBundle:Me:create_username.html.twig', array(
//            // ...
//        ));
    }
    
    /**
     * Change user password
     *
     *
     *
     * @Route("/settings/change_password", name="account_change_password")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changePasswordAction(Request $request)
    {
        //make sure that the user is fully authenticated
        //do not allow remember me sessions here
        //return new JsonResponse(['data' => var_dump($request->request)]);
        //todo:bug:not working!
//        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
//            $this->createAccessDeniedException();
//        }
        $data = new ChangePassword();
        $form = $this->createForm(ChangePasswordType::class, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //todo:move this to user manager
            $user = $this->getUser();
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $data->getNew());
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            //create additional actions such as send mails
            $message = \Swift_Message::newInstance($this->get('translator')
                                                        ->trans('user.account.password.change.subject'))
                                     ->setFrom([$this->getParameter('mail_security_events_sender') => $this->getParameter('mail_security_events_sender_name')])
                                     ->setTo($user->getEmail())
                                     ->setBody(
                    $this->renderView(
                        'UserBundle:Me:change_password_email.html.twig',
                        [
                            'firstName' => $user->getFirstName(),
                            'email' => $user->getEmail(),
                            'url' => $this->generateUrl('login'),
                        ]
                    ),
                    'text/html'
                );
            $this->get('mailer')->send($message);


            if($request->isXmlHttpRequest()){
                $html = $this->renderView('@User/Me/change_password.html.twig',
                    [
                        'form' => $form->createView(),
                    ]);
                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Password Changed'
                ]);
            }else{
                $this->addFlash('info', 'user.password.change.success');
                return $this->redirect($this->getRedirectUrl());
            }
        }
        if($request->isXmlHttpRequest()){
            $html = $this->renderView('@User/Me/change_password.html.twig',
                [
                    'form' => $form->createView(),
                ]);
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid Details',
                'html' => $html,
            ]);
        }else{
            return $this->render(
                'UserBundle:Me:change_password.html.twig',
                [
                    'form' => $form->createView(),
                ]
            );
        }
    }
    
    private function getRedirectUrl()
    {
        $id = $this->getUser()->getUsername();

        if (!$id) {
            $id = $this->get('global.hash_generator')->encode($this->getUser()->getId());
        }

        return $this->generateUrl('user_profile_edit', ['id' => $id]);

    }
}
