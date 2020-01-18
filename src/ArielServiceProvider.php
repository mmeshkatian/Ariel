<?php

namespace Mmeshkatian\Ariel;

use Illuminate\Support\ServiceProvider;

class ArielServiceProvider extends ServiceProvider
{

    public function register()
    {
//        $this->app->make('Mmeshkatian\Ariel\BaseController');
        $this->app->singleton('ariel', function () {
            return new ArielService();
        });
    }

    public function boot()
    {
        include __DIR__.'/routes.php';
        include __DIR__.'/directives.php';

        $this->publishes([
            __DIR__ . '/config.php' => config_path('ariel.php'),
            __DIR__.'/views' => resource_path('views/vendor/ariel'),
            __DIR__.'/translations' => resource_path('lang/vendor/ariel'),

        ]);
        $this->loadTranslationsFrom(__DIR__.'/translations', 'ariel');
        $this->loadViewsFrom(__DIR__.'/views', 'ariel');

    }
}
