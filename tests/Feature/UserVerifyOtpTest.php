<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Sleep;
use Tests\TestCase;

class UserVerifyOtpTest extends TestCase
{
    public function testUserInputValidOtp(): void
    {
        // setup
        $email = "test@gmail.com";
        $otp = "123456";
        $key = "OTP-email_" . $email;
        $expTime = 300;
        DB::insert("INSERT INTO users (name, email, password, created_at, updated_at)
                VALUES(?, ?, ?, ?, ?)", [
            "test",
            $email,
            "password",
            Carbon::now(),
            Carbon::now()
        ]);

        Redis::setex($key, $expTime, $otp);

        // action
        $response = $this->post("/api/otp/verify", [
            "email" => "test@gmail.com",
            "otp" => "123456"
        ]);

        // assertion
        $response->assertStatus(201)
            ->assertJson([
                'message' => "register success"
            ]);
        self::assertNull(
            Redis::get($key)
        );
        self::assertNotNull(
            Auth::user()
        );
        self::assertSame(
            Auth::user()->email,
            $email
        );
        self::assertNotNull(
            Auth::user()->email_verified_at
        );
        self::assertNotNull(
            Auth::user()->remember_token
        );
    }

    public function testUserInputInvalidOtp(): void
    {
        // setup
        $email = "test@gmail.com";
        $otp = "123456";
        $key = "OTP-email_" . $email;
        $expTime = 300;
        DB::insert("INSERT INTO users (name, email, password, created_at, updated_at)
                VALUES(?, ?, ?, ?, ?)", [
            "test",
            $email,
            "password",
            Carbon::now(),
            Carbon::now()
        ]);

        Redis::setex($key, $expTime, $otp);

        // action
        $response = $this->post("/api/otp/verify", [
            "email" => "test@gmail.com",
            "otp" => "123457"
        ]);

        // assertion
        $response->assertStatus(400)
            ->assertJson([
                'errors' => [
                    "otp doesn't valid"
                ]
            ]);
        self::assertNotNull(
            Redis::get($key)
        );
        self::assertNull(
            Auth::user()
        );
    }

    public function testUserInputExpiredOtp(): void
    {
        // setup
        $email = "test@gmail.com";
        $otp = "123456";
        $key = "OTP-email_" . $email;
        $expTime = 1;
        DB::insert("INSERT INTO users (name, email, password, created_at, updated_at)
                VALUES(?, ?, ?, ?, ?)", [
            "test",
            $email,
            "password",
            Carbon::now(),
            Carbon::now()
        ]);

        Redis::setex($key, $expTime, $otp);

        // action
        Sleep::for(2)->second();
        $response = $this->post("/api/otp/verify", [
            "email" => "test@gmail.com",
            "otp" => "123456"
        ]);

        // assertion
        $response->assertStatus(400)
            ->assertJson([
                'errors' => [
                    "otp doesn't valid"
                ]
            ]);
        self::assertNull(
            Redis::get($key)
        );
        self::assertNull(
            Auth::user()
        );
    }
}
