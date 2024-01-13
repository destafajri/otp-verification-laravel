<?php

namespace App\Repositories;

use App\Models\User;

interface UserRepository
{
    public function isUserRegistered(User $user): bool;
    public function isUserWaitingForDeletion(User $user): bool;
    public function registerUser(User $user): void;
    public function getUserDetail(User $user): User;
    public function verifyEmail(User $user): void;
}