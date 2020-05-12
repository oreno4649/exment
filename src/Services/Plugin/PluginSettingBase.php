<?php
namespace Exceedone\Exment\Services\Plugin;

/**
 * PluginSettingBase.
 * Please extends if plugin_type is multiple, and want to add custom setting.
 */
abstract class PluginSettingBase
{
    use PluginTrait;
    
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
    }
}
