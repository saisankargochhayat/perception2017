<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

/**
 * Created by PhpStorm.
 * User: amitosh
 * Date: 3/6/16
 * Time: 2:44 PM
 */
namespace RecaptchaBundle\Validator\Constraints;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use RecaptchaBundle\Services\RecaptchaVerifier;

class RecaptchaValidator extends ConstraintValidator
{

    private $requestStack;

    private $verifier;


    /**
     * RecaptchaValidator constructor.
     * @param RecaptchaVerifier $verifier
     * @param RequestStack $requestStack
     */
    public function __construct(RecaptchaVerifier $verifier, RequestStack $requestStack)
    {
        $this->verifier = $verifier;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $request = $this->requestStack->getMasterRequest();
        try {
            $result = $this->verifier->validate(
                $request->get(RecaptchaVerifier::RECAPTCHA_RESPONSE_FIELD),
                $request->server->get('REMOTE_ADDR')
            );

            if (!$result) {
                $this->context->addViolation('captcha.invalid');
            }
        } catch (\Exception $e) {
            $this->context->addViolation($e->getMessage());
//            $this->context->addViolation('captcha.server_error');

        }
    }
}
