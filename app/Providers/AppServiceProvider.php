<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'Category' => 'App\Models\Category',
        ]);
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'Technology' => 'App\Models\Technology',
        ]);
        Str::macro('properSlug', function ($str) {
            return Str::slug(preg_replace('/[^\w\d]+/', '-', $str));
        });
    }
}
