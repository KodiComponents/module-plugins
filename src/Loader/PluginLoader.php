<?php

namespace KodiCMS\Plugins\Loader;

use Artisan;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use KodiCMS\Plugins\Model\Plugin;
use ModulesLoader;

class PluginLoader
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var bool
     */
    protected $init = false;

    /**
     * @var BasePluginContainer[]|Collection
     */
    protected $activated;

    /**
     * @var BasePluginContainer[]|Collection
     */
    protected $plugins;

    /**
     * @param Filesystem $files
     * @param string     $path
     */
    public function __construct(Filesystem $files, $path)
    {
        $this->path = $path;
        $this->files = $files;
        $this->plugins = new Collection();
        $this->activated = new Collection();
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    public function init()
    {
        if ($this->init) {
            return;
        }

        $this->findPlugins();
        $this->loadActivated();

        $this->init = true;
    }

    /**
     * @return Collection|BasePluginContainer[]
     */
    public function getActivated()
    {
        return $this->activated;
    }

    /**
     * @return Collection|BasePluginContainer[]
     */
    public function findPlugins()
    {
        foreach ($this->files->directories($this->getPath()) as $directory) {
            foreach ($this->files->directories($directory) as $plugin) {
                if (is_null($class = $this->initPlugin($plugin))) {
                    continue;
                }

                $this->plugins->push($class);
            }
        }

        return $this->plugins;
    }

    /**
     * @param string $name
     *
     * @return null|BasePluginContainer
     */
    public function getPluginContainer($name)
    {
        return $this->plugins->filter(function (BasePluginContainer $plugin) use ($name) {
            return $plugin->getName() == $name;
        })->first();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isActivated($name)
    {
        return ! $this->getActivated()->filter(function (BasePluginContainer $plugin) use ($name) {
            return $plugin->getName() == $name;
        })->isEmpty();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function activatePlugin($name)
    {
        $status = false;
        if (! $this->isActivated($name) and ! is_null($plugin = $this->getPluginContainer($name))) {
            $status = $plugin->activate();

            if (app()->routesAreCached()) {
                Artisan::call('route:cache');
            }

            $plugin->checkActivation();
            $this->activated->put(get_class($plugin), $plugin);
        }

        return $status;
    }

    /**
     * @param string $name
     * @param bool   $removeTable
     *
     * @return bool
     */
    public function deactivatePlugin($name, $removeTable = false)
    {
        $status = false;
        if ($this->isActivated($name) and ! is_null($plugin = $this->getPluginContainer($name))) {
            $status = $plugin->deactivate($removeTable);

            if (app()->routesAreCached()) {
                Artisan::call('route:cache');
            }

            $plugin->checkActivation();
            $this->activated->offsetUnset(get_class($plugin));
        }

        return $status;
    }

    /**
     * @param string $directory
     *
     * @return BasePluginContainer|null
     */
    protected function initPlugin($directory)
    {
        $pluginName = pathinfo($directory, PATHINFO_BASENAME);
        $vendorName = pathinfo(pathinfo($directory, PATHINFO_DIRNAME), PATHINFO_BASENAME);

        $namespace = "Plugins\\{$vendorName}\\{$pluginName}";
        $class = "{$namespace}\\PluginContainer";

        if (! class_exists($class)) {
            return;
        }

        if (isset($this->activated[$class])) {
            return $this->activated[$class];
        }

        return new $class($vendorName.':'.$pluginName, $directory, $namespace);
    }

    /**
     * @return Collection|BasePluginContainer[]
     */
    protected function loadActivated()
    {
        Plugin::get()->filter(function (Plugin $model) {
            return $this->files->isDirectory($model->path);
        })->each(function (Plugin $model) {
            /** @var BasePluginContainer $pluginContainer */
            if (is_null($pluginContainer = $this->initPlugin($model->path))) {
                return;
            }

            $this->activated->put(get_class($pluginContainer), $pluginContainer);

            ModulesLoader::registerModule($pluginContainer);

            $pluginContainer->checkActivation();
            $pluginContainer->setSettings($model->settings);
        });

        return $this->activated;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    protected function pluginExists($key)
    {
        return $this->files->isDirectory($this->path.DIRECTORY_SEPARATOR.$key);
    }
}
