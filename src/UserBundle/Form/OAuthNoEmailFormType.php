<?php
/**
 * Created by PhpStorm.
 * User: sibananda
 * Date: 13-11-2016
 * Time: 12:43
 */

namespace UserBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
class UserFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, ['label' => 'user.email.email'])
        ;
    }


}