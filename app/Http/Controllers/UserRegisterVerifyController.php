<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegisterVerifyRequest;
use App\Http\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserRegisterVerifyController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function __invoke(UserRegisterVerifyRequest $request): JsonResponse
    {
        $this->userService->userVerifyOtpRegistration($request);

        return response()->json([
            'message' => "register success"
        ], 201);
    }
}
