<?php

namespace KodiCMS\Plugins\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use KodiCMS\Support\ServiceProvider;
use KodiCMS\Users\Model\Permission;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \KodiCMS\Plugins\Events\PluginActivated::class => [],
        \KodiCMS\Plugins\Events\PluginDeactivated::class => [],
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Permission::register('plugins', 'plugin', [
            'change_status',
            'list',
            'view_settings',
        ]);
    }

    /**
     * @param DispatcherContract $events
     */
    public function boot(DispatcherContract $events)
    {
        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }
    }

    public function contextBackend()
    {
        \Navigation::setFromArray([
            [
                'id' => 'plugins',
                'title' => 'plugins::core.title',
                'icon' => 'puzzle-piece',
                'url' => route('backend.plugins.list'),
                'priority' => 9999999,
                'permissions' => 'plugin::list'
            ],
        ]);
    }
}
