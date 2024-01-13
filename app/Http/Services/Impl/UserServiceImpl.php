<?php

namespace App\Http\Services\Impl;

use App\Exceptions\ApiException;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserRegisterVerifyRequest;
use App\Http\Services\UserService;
use App\Jobs\SendOtpEmailJob;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class UserServiceImpl implements UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function userRegister(UserRegisterRequest $request): User
    {
        $user = User::withTrashed()->where('email', $request->email)->first() ?: new User([
            "name" => $request->name,
            "email" => $request->email,
            "password" => $request->password
        ]);

        // checking if user already verified and registered
        $userRegistered = $this->userRepository->isUserRegistered($user);
        if ($userRegistered) {
            throw new ApiException("user already exist", 400);
        }

        // checking if users already deleted 2 days
        $isUserWaitingDeletion = $this->userRepository->isUserWaitingForDeletion($user);
        if ($isUserWaitingDeletion) {
            throw new ApiException("please wait 2 days after request delete", 400);
        }

        // insert or update unverified user request to register
        $this->userRepository->registerUser($user);

        //get user detail
        $userDetail = $this->userRepository->getUserDetail($user);

        // send email job
        SendOtpEmailJob::dispatch($userDetail);

        return $userDetail;
    }

    public function userVerifyOtpRegistration(UserRegisterVerifyRequest $request): void
    {
        $user = User::where('email', $request->email)->first();

        // cek antara request dg redis
        $key = "OTP-email_" . $request->email;
        $response = Redis::get($key);

        if ($request->otp != $response) {
            throw new ApiException("otp doesn't valid", 400);
        }

        // simpen database utama
        $this->userRepository->verifyEmail($user);

        // delete data on redis
        Redis::del($key);

        $userDetail = $this->userRepository->getUserDetail($user);

        // login into system
        Auth::login($userDetail);
    }
}