<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegisteVerifyrRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class UserRegisterVerifyController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UserRegisteVerifyrRequest $request)
    {
        // cek antara request dg redis
        $key = "OTP-email_" . $request->email;
        $response = Redis::get($key);

        if ($request->otp != $response) {
            return response()->json([
                'message' => "otp doesn't valid"
            ], 400);
        }

        // simpen database utama
        DB::statement("
                UPDATE users
                SET email_verified_at = ?
                WHERE email = ?
            ", [
            Carbon::now(),
            $request->email
        ]);

        // delete data on redis
        Redis::del($key);

        $data = DB::select('
            SELECT * FROM users WHERE email = ?
            ', [
            $request->email
        ]);
        // Convert the raw result into a collection of User models
        $users = User::hydrate($data);
        $user = $users->first();

        // login into system
        auth::login($user);

        return response()->json([
            'message' => "register success"
        ], 201);
    }
}
