<?php

namespace KodiCMS\Plugins\Providers;

use KodiCMS\Plugins\Events\PluginActivated;
use KodiCMS\Plugins\Events\PluginDeactivated;
use KodiCMS\Support\ServiceProvider;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        PluginActivated::class => [],
        PluginDeactivated::class => []
    ];

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
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
            ],
        ]);
    }
}
