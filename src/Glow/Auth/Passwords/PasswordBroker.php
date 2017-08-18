<?php

namespace Glow\Auth\Passwords;

use Illuminate\Auth\Passwords\PasswordBroker as IlluminatePasswordBroker;

class PasswordBroker extends IlluminatePasswordBroker
{

    /**
     * The password token repository.
     *
     * @var \Glow\Auth\Passwords\DatabaseTokenRepository
     */
    protected $tokens;

    /**
     * Validate a password reset for the given credentials.
     *
     * @param  array $credentials
     *
     * @return \Illuminate\Contracts\Auth\CanResetPassword|string
     */
    protected function validateReset(array $credentials) {

        // INVALID_TOKEN for both cases

        // at first retrieve token
        if (!$token = $this->tokens->retrieve($credentials[ 'token' ])) {

            return static::INVALID_TOKEN;
        }

        // at second retrieve user
        $credentials[ 'email' ] = $token[ 'email' ];
        if (is_null($user = $this->getUser($credentials))) {

            return static::INVALID_TOKEN;
        }

        // password validation should not be here at all

        return $user;
    }
}
