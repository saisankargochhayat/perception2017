<?php

namespace UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UserBundle\Entity\Profile;

class ProfileType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('gender', ChoiceType::class, [

            'choices'           => [
                'user.gender.unspecified' => Profile::GENDER_UNSPECIFIED,
                'user.gender.male'        => Profile::GENDER_MALE,
                'user.gender.female'      => Profile::GENDER_FEMALE,
            ],
            'choices_as_values' => true

        ])
            ->add('phone')
            ->add('institute')
            ->add('stream')
            ->add('specialization')
            ->add('yearOfStudy');
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'UserBundle\Entity\Profile'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'userbundle_profile';
    }


}
