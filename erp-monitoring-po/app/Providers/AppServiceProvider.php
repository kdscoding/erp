<?php

namespace App\Providers;

use App\Support\LabelRegistry;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LabelRegistry::class, function () {
            LabelRegistry::init();
            return new LabelRegistry();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $view->with('labelRegistry', app(LabelRegistry::class));
        });

        View::composer('components.*', function ($view) {
            $view->with('labels', app(LabelRegistry::class));
        });
    }
}
