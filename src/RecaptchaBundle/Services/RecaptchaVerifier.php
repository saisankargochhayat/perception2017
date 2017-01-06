<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

/**
 * Created by PhpStorm.
 * User: Amitosh
 * Date: 3/6/16
 * Time: 1:32 PM
 */

namespace RecaptchaBundle\Services;

use Curl\Curl;
use OOJson\JSON;

/**
 * Class RecaptchaVerifier
 *
 * Verifies a Google reCAPTCHA challenge
 *
 * @package UserBundle\Service
 */

class RecaptchaVerifier
{
    /**
     * reCAPTCHA verify server URL
     */
    const RECAPTCHA_SERVER = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * reCAPTCHA challenge response field of reCAPTCHA v2
     */
    const RECAPTCHA_RESPONSE_FIELD = 'g-recaptcha-response';

    /**
     * @var string reCAPTCHA secret key
     */
    private $recaptchaSecret;

    /**
     * RecaptchaVerifier constructor.
     *
     * @param string $secret key used for verifying the reCAPTCHA response
     */
    public function __construct($secret)
    {
        $this->recaptchaSecret = $secret;
    }

    /**
     * Validates a reCAPTCHA response against Google's reCAPTCHA service
     *
     * @param mixed  $challenge the client's response
     * @param string $ip        [optional] the ip of the client responding to the challenge
     *
     * @return bool
     */
    public function validate($challenge, $ip = null)
    {
        $response = $this->getResponse($challenge, $ip);

        if (is_string($response)) {
            $response = JSON::parse($response);
        }

        return isset($response->success) && $response->success;
    }

    /**
     * @param $response
     * @param $ip
     *
     * @return mixed
     * @throws RecaptchaException
     */
    public function getResponse($response, $ip)
    {
        if (strlen($response) == 0) {
            throw new RecaptchaException("Invalid captcha response");
        }

        $data = [
            'secret'   => $this->recaptchaSecret,
            'response' => $response,
            //we should use remote address while validation ?
            //'remoteip' => $_SERVER['REMOTE_ADDR'],
        ];

        if (!is_null($ip)) {
            $data['remoteip'] = $ip;
        }

        $curl = new Curl();

        $response = $curl->post(static::RECAPTCHA_SERVER, $data);

        if (is_string($response)) {
            $response = JSON::parse($response);
        }

        return $response;
    }
}
