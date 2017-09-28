<?php

namespace Encore\Admin\Message;

use Encore\Admin\Extension;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Message\Widgets\NavbarMenu;
use Illuminate\Support\Facades\Route;

class Messages extends Extension
{
    public static function boot()
    {
        static::registerRoutes();

        Admin::navbar()->add(new NavbarMenu());

        Admin::extend('messages', __CLASS__);
    }

    /**
     * Register routes for laravel-admin.
     *
     * @return void
     */
    public static function registerRoutes()
    {
        /* @var \Illuminate\Routing\Router $router */
        Route::group(['prefix' => config('admin.route.prefix')], function ($router) {
            $attributes = array_merge([
                'middleware' => config('admin.route.middleware'),
            ], static::config('route', []));

            Route::group($attributes, function ($router) {

                /* @var \Illuminate\Routing\Router $router */
                $router->resource('messages', 'Encore\Admin\Message\MessageController');
            });
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function import()
    {
        parent::createMenu('Messages', 'messages', 'fa-paper-plane');

        parent::createPermission('Admin messages', 'ext.messages', 'messages*');
    }
}
