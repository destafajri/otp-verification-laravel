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
        DB::insert("INSERT INTO users (name, email, password)
        VALUES(?, ?, ?)", [
            "test",
            $email,
            "password"
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
    }

    public function testUserInputInvalidOtp(): void
    {
        // setup
        $email = "test@gmail.com";
        $otp = "123456";
        $key = "OTP-email_" . $email;
        $expTime = 300;
        DB::insert("INSERT INTO users (name, email, password)
        VALUES(?, ?, ?)", [
            "test",
            $email,
            "password"
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
                'message' => "otp doesn't valid"
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
        DB::insert("INSERT INTO users (name, email, password)
        VALUES(?, ?, ?)", [
            "test",
            $email,
            "password"
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
                'message' => "otp doesn't valid"
            ]);
        self::assertNull(
            Redis::get($key)
        );
        self::assertNull(
            Auth::user()
        );
    }
}
