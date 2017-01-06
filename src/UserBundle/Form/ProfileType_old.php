<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

namespace UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UserBundle\Entity\Profile;
use UserBundle\Entity\User;

class ProfileType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('avatar', FileType::class, [
                'required' => false,
                'mapped'   => false
            ])
//            ->add('firstName', TextType::class, [
//                'mapped' => false
//            ])
//            ->add('lastName', TextType::class, [
//                'mapped' => false
//            ])
            ->add('dateOfBirth', BirthdayType::class)
            ->add('gender', ChoiceType::class, [

                'choices'           => [
                    'user.gender.unspecified' => Profile::GENDER_UNSPECIFIED,
                    'user.gender.male'        => Profile::GENDER_MALE,
                    'user.gender.female'      => Profile::GENDER_FEMALE,
                ],
                'mapped'            => false,
                'choices_as_values' => true

            ])
            ->add('phone')
            ->add('city')
            ->add('state')
            ->add('country', CountryType::class)
            ->add('presentInstitution')
            ->add('specialization')
            ->add('batch');

        if ($options['role'] >= User::ADMIN) {

            $choices = [
                'user.role.user'           => User::USER,
                'user.role.ambassador'     => User::AMBASSADOR,
                'user.role.faculty'        => User::FACULTY,
                'user.role.content_editor' => User::CONTENT_EDITOR
            ];

            if ($options['role'] == User::SUPER_ADMIN) {
                $choices['user.role.admin'] = User::ADMIN;
            }

            $builder->add('role', ChoiceType::class, [
                'choices'           => $choices,
                'mapped'            => false,
                'choices_as_values' => true
            ]);

            //$builder->get('role')->addModelTransformer( new CallbackTransformer())
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                                   'data_class' => 'UserBundle\Entity\Profile',
                                   'role'       => User::USER
                               ]);
    }
}
