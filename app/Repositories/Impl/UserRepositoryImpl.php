<?php

namespace App\Repositories\Impl;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserRepositoryImpl implements UserRepository
{

    public function isUserRegistered(User $user): bool
    {
        // checking if user already verified and registered
        $user = DB::select("
                SELECT 1 FROM users
                WHERE email = ? 
                AND email_verified_at is not null
                AND deleted_at is null
                LIMIT 1
            ", [
            $user->email
        ]);

        return count($user) == 1 ? true : false;
    }

    public function isUserWaitingForDeletion(User $user): bool
    {
        // checking if users already deleted 2 days
        $user = DB::select("
            SELECT 1 FROM users
            WHERE email = ?
            AND deleted_at IS NOT NULL
            AND deleted_at >= DATE_SUB(?, INTERVAL 2 DAY)
            LIMIT 1
            ", [
            $user->email,
            Carbon::now()
        ]);

        return count($user) == 1 ? true : false;
    }

    public function registerUser(User $user): void
    {
        DB::insert("
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
            $user->name,
            $user->email,
            Hash::make($user->password),
            Carbon::now(),
            Carbon::now(),
        ]);
    }

    public function getUserDetail(User $user): User
    {
        $data = DB::select('
                SELECT * FROM users
                WHERE email = ?
                AND deleted_at is null
                LIMIT 1
            ', [
            $user->email
        ]);
        // Convert the raw result into a collection of User models
        $users = User::hydrate($data);

        return $users->first();
    }

    public function verifyEmail(User $user): void
    {
        DB::statement("
                        UPDATE users
                        SET email_verified_at = ?,
                            deleted_at = null
                        WHERE email = ?
                    ", [
            Carbon::now(),
            $user->email
        ]);
    }
}