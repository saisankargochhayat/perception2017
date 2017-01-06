<?php
namespace UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UserBundle\Form\Model\ChangePassword;
use UserBundle\Form\Model\PasswordResetRequest;
use UserBundle\Form\PasswordResetRequestType;
use UserBundle\Form\PasswordResetType;

/**
 * Class ResetPasswordController
 * @package UserBundle\Controller
 *
 * @Route("/account/reset-password")
 */
class ResetPasswordController extends Controller
{

    /**
     * @Route("/", name="account_request_password_reset")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function requestPasswordRequestAction(Request $request)
    {
        $passwordResetReq = new PasswordResetRequest();

        $form = $this->createForm(PasswordResetRequestType::class, $passwordResetReq);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getDoctrine()->getRepository("UserBundle:User")->loadUserByUsername(
                $passwordResetReq->getEmail()
            );

            if (!is_null($user)) {
                $verification_token = bin2hex(random_bytes(32));
                $user->setVerificationToken($verification_token);
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->get('user_mailer')->sendPasswordResetRequestEmail($user);

                $this->addFlash('success', 'user.password.reset.form.acknowledge');
                return $this->redirectToRoute('login');
            }else{
                $this->addFlash('danger', 'Email not registered with us.');
            }
        }
        return $this->render(
            'UserBundle:ResetPassword:request_password_request.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{key}", name="account_reset_password")
     * @param Request $request
     * @param         $key
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resetPasswordAction(Request $request, $key)
    {
        $user = $this->getDoctrine()->getRepository('UserBundle:User')->loadUserByVerificationKey($key);

        //show that the reset password link is invalid and ask him to try again
        if (is_null($user)) {
            $this->addFlash('error', 'user.password.reset.link.failure');
            return $this->redirectToRoute('account_request_password_reset');
        }

        $data = new ChangePassword();
        $form = $this->createForm(PasswordResetType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //todo:move this to user manager
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $data->getNew());
            $user->setPassword($password);

            //remove the old token to prevent reuse
            $user->setVerificationToken(null);

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

            //create additional actions such as send mails
//            $message = \Swift_Message::newInstance('user.password.changed')
//                ->addFrom($this->getParameter('mail_security_events_sender'))
//                ->addTo($user->getEmail())
//                ->setBody(
//                    $this->renderView(
//                        'UserBundle:ChangePassword:change_password_email.html.twig',
//                        [
//                            'real_name' => $user->getRealName(),
//                            'email' => $user->getEmail(),
//                            'username' => $user->getUsername(),
//                            'user_id' => $user->getUserHash(),
//                        ]
//                    ),
//                    'text/html'
//                );
//            $this->get('mailer')->send($message);

            $this->addFlash('info', 'user.account.password.change.success');

            return $this->redirectToRoute('login');
        }


//        $this->get('user.manager')->createPasswordResetToken($user) {
//
//        }

        return $this->render(
            'UserBundle:ResetPassword:reset_password.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }
}
