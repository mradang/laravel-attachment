<?php

namespace mradang\LumenAttachment;

use Illuminate\Support\ServiceProvider;

class LumenAttachmentServiceProvider extends ServiceProvider {

    public function boot() {
        $this->configure();
        $this->registerMigrations();
    }

    protected function configure() {
        $this->app->configure('attachment');

        $this->mergeConfigFrom(
            __DIR__.'/../config/attachment.php', 'attachment'
        );
    }

    protected function registerMigrations() {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/migrations');
        }
    }

}