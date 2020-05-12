<?php

namespace Exceedone\Exment\Services\Plugin;

/**
 * Plugin (batch) base class
 */
class PluginBatchBase
{
    use PluginTrait;
    
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Processing during batch execution.
     *
     * @return void
     */
    public function execute()
    {
    }
}
