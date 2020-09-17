<?php

namespace Lostmilky\Snowflake;

use Illuminate\Support\ServiceProvider;

class SnowflakeProvider extends ServiceProvider
{
    protected $defer = true; // 延迟加载服务

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('snowflake', function ($app) {
            return new Snowflake($app['config']);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        $this->publishes(
            [__DIR__.'/config/snowflake.php' => config_path('snowflake.php')]
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['snowflake'];
    }
}
