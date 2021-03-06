<?php

namespace KodiCMS\Plugins\Http\Controllers\API;

use KodiCMS\API\Exceptions\PermissionException;
use KodiCMS\API\Http\Controllers\System\Controller;
use KodiCMS\Plugins\Exceptions\PluginContainerException;
use PluginLoader;

class PluginController extends Controller
{
    public function getList()
    {
        if (! acl_check('plugins::list')) {
            throw new PermissionException('backend.plugins.list');
        }

        $this->setContent(PluginLoader::getPlugins()->toArray());
    }

    /**
     * @throws PluginContainerException
     */
    public function changeStatus()
    {
        if (! acl_check('plugins::change_status')) {
            throw new PermissionException('plugins::change_status');
        }

        $name = $this->getRequiredParameter('name');
        $removeTable = $this->getParameter('remove_data');

        if (is_null($plugin = PluginLoader::getPluginContainer($name))) {
            throw new PluginContainerException("Plugin [{$name}] not found");
        }

        if (PluginLoader::isActivated($name)) {
            PluginLoader::deactivatePlugin($name, (bool) $removeTable);
        } else {
            PluginLoader::activatePlugin($name);
        }

        $this->setContent($plugin);
    }
}
