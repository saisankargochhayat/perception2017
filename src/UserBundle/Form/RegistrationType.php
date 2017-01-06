<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

namespace UserBundle\Form;

use RecaptchaBundle\Form\RecaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, ['label' => 'user.first_name'])
            ->add('lastName', TextType::class, [
                'label'    => 'user.last_name',
                'required' => false
            ])
            ->add('email', EmailType::class, ['label' => 'user.email.email'])
            ->add('plainPassword', RepeatedType::class, [
                'type'           => PasswordType::class,
                'first_options'  => ['label' => 'user.password.new'],
                'second_options' => ['label'           => 'user.password.confirm',
                                     'invalid_message' => 'user.password.confirm.mismatch'],
            ])
            ->add('recaptcha', RecaptchaType::class)
        ;

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
//        $resolver->setDefaults(array(
//            'data_class' => 'UserBundle\Entity\User'
//        ));
    }
}
