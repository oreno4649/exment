<?php

/**
 * Execute Batch
 */
namespace Exceedone\Exment\Services\Plugin;

use Exceedone\Exment\Controllers\ApiTrait;

/**
 * Plugin (API) base class
 */
class PluginApiBase
{
    use ApiTrait;
    use PluginTrait;
    
    public function _plugin()
    {
        return $this->plugin;
    }
    
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Get route uri for page
     *
     * @return void
     */
    public function getRouteUri($endpoint = null)
    {
        if (!isset($this->plugin)) {
            return null;
        }

        return $this->plugin->getRouteUri($endpoint);
    }
}
