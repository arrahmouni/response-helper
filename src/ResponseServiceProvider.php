<?php

namespace ArRahmouni\ResponseHelper;

use Illuminate\Support\ServiceProvider;

/*
|--------------------------------------------------------------------------
| Service Provider
|--------------------------------------------------------------------------
|
| Registers the response builder container binding and loads the package
| configuration/translations.
|
*/
class ResponseServiceProvider extends ServiceProvider
{
    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    |
    | Merge default configuration and register the container binding.
    |
    */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/response.php', 'response');

        $bindingKey = config('response.binding.key', 'response');

        /*
        | Primary binding for the response helper builder.
        */
        $this->app->singleton($bindingKey, function () {
            return new ResponseHelper();
        });

        // Keep `app('response')` working even if the binding key is customized.
        if ($bindingKey !== 'response') {
            $this->app->alias($bindingKey, 'response');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Boot
    |--------------------------------------------------------------------------
    |
    | Publish config/lang and register the translation namespace.
    |
    */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/response.php' => config_path('response.php'),
        ], 'response-config');

        $this->publishes([
            __DIR__ . '/../resources/lang' => lang_path('vendor/response'),
        ], 'response-lang');

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'response');
    }
}

