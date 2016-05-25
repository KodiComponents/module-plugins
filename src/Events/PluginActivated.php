<?php

namespace KodiCMS\Plugins\Events;

class PluginActivated
{

    /**
     * @var string
     */
    private $name;

    /**
     * PluginActivated constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
