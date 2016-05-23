<?php

namespace KodiCMS\Plugins\Providers;

use KodiCMS\Plugins\Model\Plugin;
use KodiCMS\Support\ServiceProvider;
use KodiCMS\Plugins\Loader\PluginLoader;
use KodiCMS\Plugins\Loader\PluginInstaller;
use KodiCMS\Plugins\Console\Commands\PluginsListCommand;
use KodiCMS\Plugins\Console\Commands\PluginActivateCommand;
use KodiCMS\Plugins\Console\Commands\PluginDeactivateCommand;
use KodiCMS\Plugins\Facades\PluginLoader as PluginLoaderFacade;

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
            'PluginLoader' => PluginLoaderFacade::class,
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
            PluginsListCommand::class,
            PluginActivateCommand::class,
            PluginDeactivateCommand::class,
        ]);
    }

    public function boot()
    {
        \Event::listen('config.loaded', function () {
            $this->registerNavigation();
        }, 999);
    }

    protected function registerNavigation()
    {
        \Navigation::setFromArray([
            [
                'id' => 'plugins',
                'title' => 'plugins::core.title',
                'icon' => 'puzzle-piece',
                'url' => route('backend.plugins.list'),
                'priority' => 9999999,
            ],
        ]);
    }
}
