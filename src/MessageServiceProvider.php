<?php

namespace Encore\Admin\Message;

use Illuminate\Support\ServiceProvider;

class MessageServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-admin-message');

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        Messages::boot();
    }
}
