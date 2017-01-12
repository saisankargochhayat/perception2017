<?php

namespace AppBundle\Form;

use AppBundle\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('slug')
            ->add('description')
            ->add('image')
            ->add('content')
            ->add('attachments')
            ->add('time')
            ->add('venue')
            ->add('teamEvent')
            ->add('price')
            ->add('type', ChoiceType::class, [
                'choices'           => [
                    'event.type.flagship_event' => Event::TYPE_FLAGSHIP,
                    'event.type.event' => Event::TYPE_EVENT,
                    'event.type.workshop' => Event::TYPE_WORKSHOP,
                    'event.type.guest_lecture' => Event::TYPE_GUEST_LECTURE,
                    'event.type.celebrity_appearance' => Event::TYPE_CELEBRITY_APPEARANCE
                ],
                'choices_as_values' => true
            ])
            ->add('category');
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Event'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbundle_event';
    }


}
