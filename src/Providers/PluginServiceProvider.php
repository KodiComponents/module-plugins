<?php

namespace KodiCMS\Plugins\Providers;

use KodiCMS\Plugins\Model\Plugin;
use KodiCMS\Support\ServiceProvider;
use KodiCMS\Plugins\Loader\PluginLoader;
use KodiCMS\Plugins\Loader\PluginInstaller;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct($app)
    {
        parent::__construct($app);

        $this->app->singleton('plugins.loader', function ($app) {
            return new PluginLoader($app['files'], base_path('plugins'));
        });
    }

    public function register()
    {
        $this->registerAliases([
            'PluginLoader' => \KodiCMS\Plugins\Facades\PluginLoader::class,
        ]);

        $this->app->singleton('plugin.installer', function ($app) {
            return new PluginInstaller($app['db'], $app['files']);
        });

        try {
            Plugin::setConnectionResolver($this->app['db']);
            Plugin::setEventDispatcher($this->app['events']);

            $this->app['plugins.loader']->init();
        } catch (\Exception $e) {
        }

        $this->registerConsoleCommand([
            \KodiCMS\Plugins\Console\Commands\PluginsListCommand::class,
            \KodiCMS\Plugins\Console\Commands\PluginActivateCommand::class,
            \KodiCMS\Plugins\Console\Commands\PluginDeactivateCommand::class,
        ]);
    }
}
