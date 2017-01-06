<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

namespace UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('current_password', PasswordType::class, array(
            'label' => 'user.password.current',
            'mapped' => false,
            'constraints' => new UserPassword(),
            'attr' => ['class' => 'form-control'],
        ));
        $builder->add('new', RepeatedType::class, array(
            'type' => PasswordType::class,
            'first_options' => array(
                'label' => 'user.password.new',
                'attr' => ['class' => 'form-control']
            ),
            'second_options' => array(
                'label' => 'user.password.confirm_password',
                'attr' => ['class' => 'form-control']
            ),
            'invalid_message' => 'user.password.mismatch',
        ));
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
            'data_class' => 'UserBundle\Form\Model\ChangePassword',
            'csrf_token_id'  => 'change_password',
            ]
        );
    }
}
