<?php

namespace KodiCMS\Plugins\Loader;

use Artisan;
use ModulesLoader;
use KodiCMS\Plugins\Model\Plugin;
use Illuminate\Filesystem\Filesystem;

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
     * @var array
     */
    protected $activated = [];

    /**
     * @var array
     */
    protected $foundPlugins = [];

    /**
     * @param Filesystem $files
     * @param string     $path
     */
    public function __construct(Filesystem $files, $path)
    {
        $this->path = $path;
        $this->files = $files;
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

        $this->loadActivated();
        $this->init = true;
    }

    /**
     * @return array
     */
    public function getActivated()
    {
        return $this->activated;
    }

    /**
     * @return array
     */
    public function findPlugins()
    {
        foreach ($this->files->directories($this->getPath()) as $directory) {
            foreach ($this->files->directories($directory) as $plugin) {
                if (is_null($class = $this->initPlugin($plugin))) {
                    continue;
                }

                $this->foundPlugins[] = $class;
            }
        }

        return $this->foundPlugins;
    }

    /**
     * @param $name
     *
     * @return null|BasePluginContainer
     */
    public function getPluginContainer($name)
    {
        foreach ($this->findPlugins() as $plugin) {
            if ($plugin->getName() == $name) {
                return $plugin;
            }
        }

        return;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isActivated($name)
    {
        foreach ($this->getActivated() as $plugin) {
            if ($plugin->getName() == $name) {
                return true;
            }
        }

        return false;
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
            $this->activated[get_class($plugin)] = $plugin;
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
            unset($this->activated[get_class($plugin)]);
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
     * @return mixed
     */
    protected function loadActivated()
    {
        $activated = Plugin::get();

        foreach ($activated as $model) {
            if ($this->files->isDirectory($model->path) and ! is_null($pluginContainer = $this->initPlugin($model->path))) {
                $this->activated[get_class($pluginContainer)] = $pluginContainer;
                ModulesLoader::registerModule($pluginContainer);

                $pluginContainer->checkActivation();
                $pluginContainer->setSettings($model->settings);
            }
        }
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
