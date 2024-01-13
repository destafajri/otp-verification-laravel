<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegisterRequest;
use App\Http\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserRegisterController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function __invoke(UserRegisterRequest $request): JsonResponse
    {
        $user = $this->userService->userRegister($request);

        return response()->json($user);
    }
}
