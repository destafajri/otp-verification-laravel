<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegisterRequest;
use App\Jobs\SendOtpEmailJob;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserRegisterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UserRegisterRequest $request)
    {
        // checking if user already verified and registered
        $user = DB::select("
                SELECT 1 FROM users WHERE email = ? AND email_verified_at is not null
            ", [
            $request->email
        ]);

        if (count($user) == 1) {
            return response()->json([
                'error' => 'user already exist'
            ], 400);
        }

        // insert or update unverified user request to register
        DB::statement("
                INSERT INTO users (name, email, password, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?)
                ON CONFLICT (email) DO UPDATE
                SET name = ?, email = ?, password = ?, updated_at = ?
            ", [
            // payload if request is register new user
            $request->name,
            $request->email,
            Hash::make($request->password),
            Carbon::now(),
            Carbon::now(),

            // payload if request is update un verified user
            $request->name,
            $request->email,
            Hash::make($request->password),
            Carbon::now(),
        ]);

        $data = DB::select('
                SELECT * FROM users WHERE email = ?
            ', [
            $request->email
        ]);

        // Convert the raw result into a collection of User models
        $users = User::hydrate($data);
        $user = $users->first();

        // send email job
        SendOtpEmailJob::dispatch($user);

        return response()->json($user);
    }
}
