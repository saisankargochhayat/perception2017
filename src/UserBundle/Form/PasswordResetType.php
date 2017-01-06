<?php
namespace UserBundle\Form;

use RecaptchaBundle\Form\RecaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PasswordResetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('new', RepeatedType::class, array(
            'type' => PasswordType::class,
            'first_options' => array(
                'label' => 'user.password.reset.new.password',
                'attr' => ['class' => 'form-control']
            ),
            'second_options' => array(
                'label' => 'user.password.reset.new.confirm_password',
                'attr' => ['class' => 'form-control']
            ),
            'invalid_message' => 'user.password.mismatch',
        ))
        ->add('recaptcha', RecaptchaType::class)
        ;


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
            'data_class' => 'UserBundle\Form\Model\ChangePassword',
            'csrf_token_id'  => 'reset_password',
            ]
        );
    }
}
