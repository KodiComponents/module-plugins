<?php

namespace KodiCMS\Plugins\Loader;

use CMS;
use KodiCMS\CMS\Contracts\SettingsInterface;
use KodiCMS\Plugins\Events\PluginActivated;
use KodiCMS\Plugins\Events\PluginDeactivated;
use KodiCMS\Plugins\Model\Plugin;
use KodiCMS\Support\Traits\Settings;
use KodiCMS\Support\Loader\ModuleContainer;
use KodiCMS\Plugins\Exceptions\PluginContainerException;

abstract class BasePluginContainer extends ModuleContainer implements SettingsInterface
{
    use Settings;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var bool
     */
    protected $isInstallable = true;

    /**
     * @var bool
     */
    protected $isActivated = false;

    /**
     * @var bool
     */
    protected $isPublishable = false;

    /**
     * @var array
     */
    protected $details = [];

    /**
     * @param string      $moduleName
     * @param null|string $modulePath
     * @param null|string $namespace
     *
     * @throws PluginContainerException
     */
    public function __construct($moduleName, $modulePath = null, $namespace = null)
    {
        parent::__construct($moduleName, $modulePath, $namespace);

        $this->name = strtolower($moduleName);
        $this->details = array_merge($this->defaultDetails(), $this->details());

        if (! isset($this->details['title'])) {
            throw new PluginContainerException("Plugin title for plugin {$moduleName} not set");
        }

        $this->isInstallable = $this->checkPluginVersion();

        $this->setSettings($this->defaultSettings());
    }

    /**
     * @return array
     */
    public function defaultSettings()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getInfo()
    {
        return [
            'version' => $this->getVersion(),
            'description' => $this->getDescription(),
            'authors' => [[
                'name' => $this->getAuthor()
            ]]
        ];
    }


    /**
     * @return string
     */
    public function getTitle()
    {
        return array_get($this->details, 'title');
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return array_get($this->details, 'author');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return array_get($this->details, 'description');
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return array_get($this->details, 'icon');
    }

    /**
     * @return bool
     */
    public function isInstallable()
    {
        return $this->isInstallable;
    }

    /**
     * @return \Illuminate\View\View|null
     */
    public function getSettingsTemplate()
    {
        if ($this->hasSettingsPage()) {
            return view($this->details['settings_template'], [
                'plugin' => $this,
            ]);
        }

        return;
    }

    /**
     * @return bool
     */
    public function hasSettingsPage()
    {
        return (bool) array_get($this->details, 'settings_template', false);
    }

    /**
     * @return bool
     */
    public function isActivated()
    {
        $this->checkActivation();
        return $this->isActivated;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return array_get($this->details, 'version', '0.0.0');
    }

    /**
     * @return string
     */
    public function getRequiredVersion()
    {
        return array_get($this->details, 'required_cms_version', '0.0.0');
    }

    /**
     * @return string
     */
    public function getSchemasPath()
    {
        return $this->getPath(['database', 'schemas']);
    }

    /**
     * @return string
     */
    public function getAssetsPublicPath()
    {
        return public_path('cms'.DIRECTORY_SEPARATOR.'plugins'.DIRECTORY_SEPARATOR.$this->getName());
    }

    /**
     * @return bool
     */
    public function checkActivation()
    {
        return $this->isActivated = \PluginLoader::isActivated($this->getName());
    }

    /**
     * @return bool
     * @throws PluginContainerException
     */
    public function activate()
    {
        if ($this->isActivated()) {
            throw new PluginContainerException('Plugin is activated');
        }

        if (! $this->isInstallable()) {
            $CmsVersion = CMS::VERSION;
            throw new PluginContainerException("
				Plugin can`t be installed.
				Required CMS version is: [{$this->getRequiredVersion()}]
				Version of your CMS is: [{$CmsVersion}].
			");
        }

        Plugin::create([
            'name'     => $this->getName(),
            'path'     => $this->getPath(),
            'settings' => $this->getSettings(),
        ]);

        app('plugin.installer')->installSchemas($this->getSchemasPath());

        event(new PluginActivated($this->getName()));
        
        $this->isActivated = true;

        return true;
    }

    /**
     * @param bool $removeTable
     *
     * @return bool
     * @throws PluginContainerException
     */
    public function deactivate($removeTable = false)
    {
        if (! $this->isActivated()) {
            throw new PluginContainerException('Plugin is not activated');
        }

        if (is_null($plugin = Plugin::where('name', $this->getName()))) {
            throw new PluginContainerException('Plugin not found');
        }

        $plugin->delete();

        if ($removeTable) {
            app('plugin.installer')->dropSchemas($this->getSchemasPath());
        }

        event(new PluginDeactivated($this->getName()));

        $this->isActivated = false;

        return true;
    }

    /**
     * @param array $settings
     */
    public function saveSettings(array $settings)
    {
        $this->setSettings($settings);

        $model = Plugin::where('name', $this->getName())->first();

        if (! is_null($model)) {
            $model->update(['settings' => $this->getSettings()]);
        }
    }

    /**
     * @return bool
     */
    protected function checkPluginVersion()
    {
        return version_compare(CMS::getVersion(), $this->getRequiredVersion(), '>=');
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $details = $this->details;

        $details['isInstallable'] = $this->isInstallable();
        $details['isActivated'] = $this->isActivated();
        $details['settings'] = $this->getSettings();
        $details['settingsUrl'] = route('backend.plugins.settings.get', [$this->getName()]);

        return array_merge(parent::toArray(), $details);
    }

    /**
     * @param string $moduleName
     *
     * @return string
     */
    protected function getDefaultModulePath($moduleName)
    {
        return base_path('plugins/'.$moduleName);
    }

    /**
     * @return string
     */
    protected function publishViewPath()
    {
        return base_path("/resources/views/plugins/{$this->getName()}");
    }

    protected function loadViews()
    {
        if (is_dir($appPath = $this->publishViewPath())) {
            app('view')->addNamespace($this->getKey(), $appPath);
        } else {
            app('view')->addNamespace($this->getKey(), $this->getViewsPath());
        }
    }

    /**
     * @return array
     */
    protected function defaultDetails()
    {
        return [
            'title'                => null,
            'description'          => null,
            'author'               => null,
            'icon'                 => 'puzzle-piece',
            'version'              => '0.0.0',
            'required_cms_version' => '0.0.0',
            'settings_template'    => false,
        ];
    }

    /**
     * @return array
     *
     * [
     *        'title'                => '...',
     *        'description'            => '...',
     *        'author'                => '...',
     *        'icon'                    => '...',
     *        'version'                => '...',
     *        'required_cms_version'  => '...'
     * ]
     */
    abstract public function details();
}
