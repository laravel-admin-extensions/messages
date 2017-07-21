<?php

namespace Encore\Admin\Message;

use Encore\Admin\Auth\Database\Menu;
use Encore\Admin\Auth\Database\Permission;
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

    public static function import()
    {
        $lastOrder = Menu::max('order');

        Menu::create([
            'parent_id' => 0,
            'order'     => $lastOrder + 1,
            'title'     => 'Messages',
            'icon'      => 'fa-paper-plane',
            'uri'       => 'messages',
        ]);

        // Add a permission.
        Permission::create([
            'name'          => 'Admin messages',
            'slug'          => 'ext.messages',
            'http_path'     => admin_base_path('messages*'),
        ]);
    }
}