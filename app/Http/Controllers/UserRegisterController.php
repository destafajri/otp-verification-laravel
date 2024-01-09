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
                SELECT 1 FROM users WHERE email = ? AND email_verified_at is not null AND deleted_at is null limit 1
            ", [
            $request->email
        ]);

        if (count($user) == 1) {
            return response()->json([
                'error' => 'user already exist'
            ], 400);
        }

        // checking if users already deleted 2 days
        $user = DB::select("
                SELECT 1
                FROM users
                WHERE email = ?
                AND deleted_at IS NOT NULL
                AND deleted_at >= DATE_SUB(?, INTERVAL 2 DAY)
                LIMIT 1;
            ", [
            $request->email,
            Carbon::now()
        ]);

        if (count($user) == 1) {
            return response()->json([
                'error' => 'please wait 2 days after request delete'
            ], 400);
        }

        // insert or update unverified user request to register
        DB::statement("
                INSERT INTO users (name, email, password, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                email = VALUES(email),
                password = VALUES(password),
                updated_at = VALUES(updated_at),
                deleted_at = null,
                email_verified_at = null
            ", [
            // payload request is register new user
            $request->name,
            $request->email,
            Hash::make($request->password),
            Carbon::now(),
            Carbon::now(),
        ]);

        $data = DB::select('
                SELECT * FROM users WHERE email = ? limit 1
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
