<?php
namespace UserBundle\Service;

use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use UserBundle\Entity\User;

/**
 * Created by PhpStorm.
 * User: amitosh
 * Date: 6/1/17
 * Time: 4:46 PM
 */
class UserMailer
{
    /** @var  \Swift_Mailer */
    private $mailer;

    /** @var  TranslatorInterface */
    private $translator;

    /** @var  TwigEngine */
    private $renderer;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;
    /** @var string */
    private $mailerDomain;

    /** @var string */
    private $mailerName;

    public function __construct(\Swift_Mailer $mailer, TranslatorInterface $translator,
                                UrlGeneratorInterface $urlGenerator, TwigEngine $renderer,
                                string $mailerDomain, string $mailerName)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
        $this->renderer = $renderer;
        $this->mailerDomain = $mailerDomain;
        $this->mailerName = $mailerName;
    }

    public function sendVerificationEmail(User $user) {
        $message = \Swift_Message::newInstance($this->translator->trans('user.registration.email.verify.subject'))
            ->setFrom([ "noreply@$this->mailerDomain" => $this->mailerName ])
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderer->render(
                    'registration/registration_verify_email.html.twig',
                    [
                        'firstName' => $user->getFirstName(),
                        'email'     => $user->getEmail(),
                        'username'  => $user->getUsername(),
                        'url'       => $this->urlGenerator->generate(
                            'account_registration_verify',
                            ['key' => $user->getVerificationToken()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        ),
                    ]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }

    public function sendWelcomeEmail(User $user) {
        $message = \Swift_Message::newInstance($this->translator
            ->trans('user.registration.email.welcome.subject'))
            ->setFrom([ "welcome@$this->mailerDomain" => $this->mailerName ])
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderer->render(
                    'registration/registration_welcome.html.twig',
                    [
                        'firstName' => $user->getRealName(),
                        'email'     => $user->getEmail(),
                        'username'  => $user->getUsername(),
                        'url'       => $this->urlGenerator->generate(
                            'my_study_table',
                            [],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        ),
                    ]
                ),
                'text/html'
            );
        $this->mailer->send($message);
    }

    public function sendPasswordResetRequestEmail(User $user) {
        $message = \Swift_Message::newInstance($this->get('translator')
            ->trans('user.password.reset.email.subject'))
            ->setFrom([ "accounts-noreply@$this->mailerDomain" => $this->mailerName ])
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderer->render(
                    'resetpassword/reset_password_verification_email.html.twig',
                    [
                        'firstName' => $user->getFirstName(),
                        'email' => $user->getEmail(),
                        'username' => $user->getUsername(),
                        'url' => $this->urlGenerator->generate(
                            'account_reset_password',
                            ['key' => $user->getVerificationToken()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        )
                    ]
                ),
                'text/html'
            );
        $this->get('mailer')->send($message);
    }
}