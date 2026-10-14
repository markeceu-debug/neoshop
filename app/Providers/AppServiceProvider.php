<?php

namespace App\Providers;

use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
/**
 * Register any application services.
 */
public function register(): void
{
$this->app->singleton(StockService::class, fn () => new StockService());

}

/**
 * Bootstrap any application services.
 */

public function boot(): void
{
    // Define o locale do Carbon para português
    Carbon::setLocale(config('app.locale'));
}



}
