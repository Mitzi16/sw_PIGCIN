<?php

namespace App\Providers;

use App\Models\ArticleType;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inertia::share([
            'articleTypes' => fn() => ArticleType::select('id', 'name')->get(),
        ]);
    }
}
