<?php

namespace mradang\LaravelAttachment;

use Illuminate\Support\ServiceProvider;

class LaravelAttachmentServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->mergeConfigFrom(
            realpath(__DIR__ . '/../config/config.php'),
            'attachment'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                realpath(__DIR__ . '/../config/config.php') => config_path('attachment.php')
            ], 'config');

            $this->loadMigrationsFrom(realpath(__DIR__ . '/../migrations/'));
        }
    }
}
