<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
        Vite::prefetch(concurrency: 3);

        $checks = [
            \Spatie\Health\Checks\Checks\DatabaseCheck::new(),
            \Spatie\Health\Checks\Checks\EnvironmentCheck::new()->expectEnvironment(app()->environment()),
            \Spatie\Health\Checks\Checks\DebugModeCheck::new()->expectedToBe(config('app.debug')),
        ];

        if (! app()->isLocal()) {
            $checks[] = \Spatie\Health\Checks\Checks\OptimizedAppCheck::new();
            // $checks[] = \Spatie\Health\Checks\Checks\UsedDiskSpaceCheck::new(); // Uncomment if running on Linux
        }

        \Spatie\Health\Facades\Health::checks($checks);
    }
}
