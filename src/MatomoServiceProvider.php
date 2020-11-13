<?php

namespace Haxibiao\Matomo;

use Illuminate\Support\ServiceProvider;
use Haxibiao\Matomo\Console\InstallCommand;

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
            Console\InstallCommand::class,
            Console\PublishCommand::class,
            Console\MatomoProxy::class,
            Console\MatomoClient::class,
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
