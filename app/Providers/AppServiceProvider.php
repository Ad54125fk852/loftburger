<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Blade::directive('target', function () {
            // Read the value from your .env file, default to '_blank'
            $target = env('LINK_TARGET', '_blank');
            return "<?php echo 'target=\"$target\"'; ?>";
        });
    }
}
