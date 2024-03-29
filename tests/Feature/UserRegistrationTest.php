<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserRegistrationTest extends TestCase
{
    public function testUserSuccessRegister(): void
    {
        //action
        $response = $this->post('/api/register', [
            "name" => "desta",
            "email" => "test@gmail.com",
            "password" => "hehehehe123"
        ]);

        //expected
        $response->assertStatus(200);
    }

    public function testUserFailedRegisterWrongEmailFormat(): void
    {
        //action
        $response = $this->post('/api/register', [
            "name" => "desta",
            "email" => "testgmail.com",
            "password" => "hehehehe123"
        ]);

        //expected
        $response->assertStatus(422)
            ->assertJson([
                "errors" => [
                    "email" => [
                        "The email field must be a valid email address."
                    ]
                ]
            ]);
    }

    public function testUserFailedRegisterUserAlreadyExist(): void
    {
        //setup
        DB::insert("INSERT INTO users (name, email, password, email_verified_at)
                    VALUES(?, ?, ?, ?)", [
            "test",
            "test@gmail.com",
            "password",
            Carbon::now()
        ]);

        //action
        $response = $this->post('/api/register', [
            "name" => "desta",
            "email" => "test@gmail.com",
            "password" => "hehehehe123"
        ]);

        //expected
        $response->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "user already exist"
                ]
            ]);
    }

    public function testUserFailedRegisterWaitingForTwoDays(): void
    {
        //setup
        DB::insert("INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at, deleted_at)
                VALUES(?, ?, ?, ?, ?, ?, ?)", [
            "test",
            "test@gmail.com",
            "password",
            Carbon::now(),
            Carbon::now(),
            Carbon::now(),
            Carbon::now()->subDay(2)
        ]);


        //action
        $response = $this->post('/api/register', [
            "name" => "desta",
            "email" => "test@gmail.com",
            "password" => "hehehehe123"
        ]);

        //expected
        $response->assertStatus(400)
            ->assertJson([
                "errors" => [
                    "please wait 2 days after request delete"
                ]
            ]);
    }

    public function testUserSuccessRegisterAfterWaitingForThreeDays(): void
    {
        //setup
        DB::insert("INSERT INTO users (name, email, password, email_verified_at, created_at, updated_at, deleted_at)
                    VALUES(?, ?, ?, ?, ?, ?, ?)", [
            "test",
            "test@gmail.com",
            "password",
            Carbon::now(),
            Carbon::now(),
            Carbon::now(),
            Carbon::now()->subDay(3)
        ]);

        //action
        $response = $this->post('/api/register', [
            "name" => "desta",
            "email" => "test@gmail.com",
            "password" => "hehehehe123"
        ]);

        //expected
        $response->assertStatus(200);
    }
}
