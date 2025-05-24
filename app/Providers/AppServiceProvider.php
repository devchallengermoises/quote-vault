<?php

namespace App\Providers;

use App\ApiClients\FavQsClient;
use App\Repositories\QuoteRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FavQsClient::class);
        $this->app->singleton(QuoteRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
