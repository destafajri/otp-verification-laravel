<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Http\Services\UserService::class,
            \App\Http\Services\Impl\UserServiceImpl::class
        );

        $this->app->bind(
            \App\Repositories\UserRepository::class,
            \App\Repositories\Impl\UserRepositoryImpl::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Debug query sql
        DB::listen(function (QueryExecuted $query) {
            Log::info($query->sql);
        });
    }
}
