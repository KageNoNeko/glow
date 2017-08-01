<?php

namespace Glow\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class Api extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Get the guard
     *
     * @return \Illuminate\Contracts\Auth\Guard|\Glow\Auth\TokenGuard
     */
    protected function guard() {

        return Auth::guard('api');
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    protected function user() {

        return $this->guard()->user();
    }
}
