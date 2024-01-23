<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Services\User\CreateUserService;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;

class UserController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    public function registerUser(Request $request)
    {
        $service = app()->make(CreateUserService::class);
        $service->handle($request->json()->all());
        if (!$service->getProcessResult()) {
            return $this->apiResponse('', 500, (object) []);
        }
        return $this->apiResponse('', 0, $service->getData());
    }

    public function login(Request $request)
    {
        $credentials = request(['email', 'password']);

        if (!$token = Auth::attempt($credentials)) {
            return $this->apiResponse('帳號密碼錯誤', 1040, (object) []);
        }

        $token = JWTAuth::fromUser(auth()->user());

        return $this->apiResponse('', 0, ['email' => auth()->user()->email, 'token' => $token]);
    }
}
