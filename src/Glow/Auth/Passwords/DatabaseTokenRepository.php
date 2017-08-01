<?php

namespace Glow\Auth\Passwords;

use Carbon\Carbon;
use Illuminate\Auth\Passwords\DatabaseTokenRepository as IlluminateDatabaseTokenRepository;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class DatabaseTokenRepository extends IlluminateDatabaseTokenRepository
{

    /**
     * Build the record payload for the table.
     *
     * @param  string $email
     * @param  string $token
     *
     * @return array
     */
    protected function getPayload($email, $token) {

        return ['email' => $email, 'token' => $token, 'created_at' => new Carbon];
    }

    /**
     * Retrieve a token record if exists and is valid.
     *
     * @param  string $token
     *
     * @return array|null
     */
    public function retrieve($token) {

        $record = (array)$this->getTable()->where('token', $token)->first();

        return ($record
                && !$this->tokenExpired($record[ 'created_at' ]) ? $record : null);
    }
}
