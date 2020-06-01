<?php

namespace haxibiao\matomo;

use Illuminate\Support\ServiceProvider;

class MatomoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //注册一些helpers 函数
        $src_path = __DIR__;
        foreach (glob($src_path . '/helpers/*.php') as $filename) {
            require_once $filename;
        }

        // Register Commands
        $this->commands([
            InstallCommand::class,
            PublishCommand::class,
            MatomoProxy::class,
            MatomoClient::class,
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../config/matomo.php' => config_path('matomo.php'),
            ], 'matomo-config');

        }

    }
}
