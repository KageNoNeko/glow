<?php

namespace Glow\Auth;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Auth\AuthenticationException;

class TokenGuard implements Guard
{

    use GuardHelpers;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * The tokens repository instance.
     *
     * @var \Glow\Auth\TokenRepository
     */
    protected $tokens;

    /**
     * The name of the field on the request containing the API token.
     *
     * @var string
     */
    protected $inputKey;

    /**
     * The currently authenticated user token.
     *
     * @var array
     * @todo maybe make a model
     */
    protected $token;

    /**
     * Get the token for the current request.
     *
     * @return string
     */
    protected function getTokenForRequest() {

        $token = $this->request->input($this->inputKey);

        if (empty($token)) {
            $token = $this->request->bearerToken();
        }

        return $token;
    }

    /**
     * Determine if the user matches the credentials.
     *
     * @param mixed $user
     * @param array $credentials
     *
     * @return bool
     */
    protected function hasValidCredentials($user, array $credentials) {

        return !is_null($user) && $this->provider->validateCredentials($user, $credentials);
    }

    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider $provider
     * @param  \Illuminate\Http\Request                $request
     * @param  \Glow\Auth\TokenRepository              $tokens
     */
    public function __construct(UserProvider $provider, Request $request, TokenRepository $tokens) {

        $this->request = $request;
        $this->provider = $provider;
        $this->inputKey = 'api_token';
        $this->tokens = $tokens;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user() {

        if (!is_null($this->user)) {

            return $this->user;
        }

        $this->user = null;
        $this->token = null;

        $token = $this->getTokenForRequest();

        if (!empty($token)) {

            if ($this->token = $this->tokens->findValid($token)) {

                $this->user = $this->provider->retrieveById($this->token[ 'user_id' ]);
            }
        }

        return $this->user;
    }

    /**
     * Get currently authenticated user's token
     *
     * @return array
     */
    public function token() {

        return $this->token;
    }

    /**
     * Get combined array of user & token
     *
     * @return array
     */
    public function combined() {

        return [
            'token' => $this->token(),
            'user' => $this->user(),
        ];
    }

    /**
     * Validate a user's credentials.
     *
     * @param array $credentials
     *
     * @return bool
     */
    public function validate(array $credentials = []) {

        $user = $this->provider->retrieveByCredentials($credentials);

        return $this->hasValidCredentials($user, $credentials);
    }

    /**
     * Attempt to authenticate a user using the given credentials.
     *
     * @param array $credentials
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function attempt(array $credentials = []) {

        $user = $this->provider->retrieveByCredentials($credentials);

        if (!$this->hasValidCredentials($user, $credentials)) {

            throw new AuthenticationException;
        }

        $this->create($user);
    }

    /**
     * Create access for user.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     */
    public function create(Authenticatable $user) {

        $this->token = $this->tokens->create($user);

        $this->setUser($user);
    }

    /**
     * Revoke user acess.
     *
     * @param array|string $token
     *
     * @return void
     */
    public function revoke($token = null) {

        $user = $this->user();

        if (!is_null($user)) {

            $token = is_array($token) ? $token[ 'token' ] : $token;

            $this->tokens->deleteForUser($user, $token);
        }
    }

    /**
     * Get the user provider used by the guard.
     *
     * @return \Illuminate\Contracts\Auth\UserProvider
     */
    public function getProvider() {

        return $this->provider;
    }
}
