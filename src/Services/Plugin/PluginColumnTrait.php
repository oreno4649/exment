<?php
namespace Exceedone\Exment\Services\Plugin;

use Exceedone\Exment\Model\Plugin as PluginModel;
use Exceedone\Exment\Enums\PluginType;

/**
 * Plugin (column) trait class
 */
trait PluginColumnTrait
{
    use PluginPageTrait;
    
    public function initialized($options = []){
        $this->plugin = PluginModel::getByPluginTypes(PluginType::COLUMN)->first(function($plugin){
            return $plugin->getOption('column_type') == $this->getColumnType();
        });
    }
}
