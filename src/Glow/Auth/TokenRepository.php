<?php

namespace Glow\Auth;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Contracts\Auth\Authenticatable;

class TokenRepository
{
    /**
     * The database connection instance.
     *
     * @var \Illuminate\Database\ConnectionInterface
     */
    protected $connection;

    /**
     * The token database table.
     *
     * @var string
     */
    protected $table;

    /**
     * The hashing key.
     *
     * @var string
     */
    protected $hashKey;

    /**
     * The number of seconds a token should last.
     *
     * @var int
     */
    protected $expires;

    /**
     * Multiple tokens support
     *
     * @var boolean
     */
    protected $multiple;

    /**
     * Create a new token for the user.
     *
     * @return string
     */
    protected function createNewToken() {

        return hash_hmac('sha256', Str::random(40), $this->hashKey);
    }

    /**
     * Begin a new database query against the table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getTable() {

        return $this->connection->table($this->table);
    }

    /**
     * Get the database connection instance.
     *
     * @return \Illuminate\Database\ConnectionInterface
     */
    protected function getConnection() {

        return $this->connection;
    }

    /**
     * Build the record payload for the table.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param string                                     $token
     *
     * @return array
     */
    protected function getPayload(Authenticatable $user, $token) {

        return ['user_id' => $user->getAuthIdentifier(), 'token' => $token, 'expires_at' => (new Carbon)->addSeconds($this->expires)];
    }

    /**
     * Determine if the token has expired.
     *
     * @param array $token
     *
     * @return bool
     */
    protected function tokenExpired(array $token) {

        return strtotime($token[ 'expires_at' ]) < $this->getCurrentTime();
    }

    /**
     * Get the current UNIX timestamp.
     *
     * @return int
     */
    protected function getCurrentTime() {

        return time();
    }

    /**
     * Create a new token repository instance.
     *
     * @param \Illuminate\Database\ConnectionInterface $connection
     * @param string                                   $table
     * @param string                                   $hashKey
     * @param int                                      $expires
     * @param boolean                                  $multiple
     */
    public function __construct(ConnectionInterface $connection, $table, $hashKey, $expires = 60, $multiple = false) {

        $this->table = $table;
        $this->hashKey = $hashKey;
        $this->setExpires($expires);
        $this->connection = $connection;
        $this->multiple = $multiple;
    }

    /**
     * Delete all existing tokens for user.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param string                                     $token
     *
     * @return int
     */
    public function deleteForUser(Authenticatable $user, $token = null) {

        $query = $this->getTable()
                      ->where('user_id', $user->getAuthIdentifier());

        if ($token) {

            $query->where('token', $token);
        }

        return $query->delete();
    }

    /**
     * Create a new token record.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     *
     * @return array
     */
    public function create(Authenticatable $user) {

        if (!$this->multiple) {

            $this->deleteForUser($user);
        }

        $token = $this->createNewToken();

        $payload = $this->getPayload($user, $token);

        $expires_at = $payload[ 'expires_at' ];

        $this->getTable()->insert($payload);

        return compact('token', 'expires_at');
    }

    /**
     * Find token record
     *
     * @param string $token
     *
     * @return array|null
     */
    public function find($token) {

        $token = (array)$this->getTable()->where('token', $token)->first();

        return $token ?: null;
    }

    /**
     * Find valid token record.
     *
     * @param string $token
     *
     * @return array|null
     */
    public function findValid($token) {

        $token = $this->find($token);

        return $token && !$this->tokenExpired($token) ? $token : null;
    }

    /**
     * Delete expired tokens.
     *
     * @return void
     */
    public function deleteExpired() {

        $this->getTable()->where('expires_at', '<', Carbon::now())->delete();
    }

    /**
     * @param int $expires
     */
    public function setExpires($expires) {

        $this->expires = $expires * 60;
    }
}
