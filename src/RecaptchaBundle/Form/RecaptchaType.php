<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

namespace RecaptchaBundle\Form;

use RecaptchaBundle\Validator\Constraints\Recaptcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A Recaptcha form field type
 * @package RecaptchaBundle\Form
 */
class RecaptchaType extends AbstractType
{

    const RECAPTCHA_API_URL = "https://www.google.com/recaptcha/api.js";
    private $publicKey;

    public function __construct($publicKey)
    {
        $this->publicKey = $publicKey;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars = array_replace($view->vars, [
            'url_api' => self::RECAPTCHA_API_URL,
            'public_key' => $this->publicKey,
            //'widget_options' => array_replace($view->vars['widget_options'], $options)
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'public_key' => null,
                'url_api' => null,
                'label' => '',
                'mapped' => false,
                'constraints' => new Recaptcha(),
                'attr' => [
                    'widget_options' => [
                        'theme' => 'light',
                        'size' => 'normal',
                        'expiredCallback' => null,
                        'defer' => true,
                        'async' => true,
                    ]
                ]
            ]);
    }
}
