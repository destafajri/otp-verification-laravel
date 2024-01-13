<?php

namespace App\Http\Services;

use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserRegisterVerifyRequest;
use App\Models\User;

interface UserService
{
    public function userRegister(UserRegisterRequest $request): User;
    public function userVerifyOtpRegistration(UserRegisterVerifyRequest $userRegisterVerifyRequest): void;
}