<?php
namespace UserBundle\Form;

use RecaptchaBundle\Form\RecaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PasswordResetRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'email',
            EmailType::class, [
                'label' => 'user.password.reset.form.email_address',
                'attr'  => [
                    'class' => 'form-control'
                ]
            ])
            ->add(
            'recaptcha',
            RecaptchaType::class,
            ['invalid_message' => 'user.captcha.failed']
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'UserBundle\Form\Model\PasswordResetRequest',
                'csrf_token_id' => 'request_password_reset',
            ]
        );
    }
}
