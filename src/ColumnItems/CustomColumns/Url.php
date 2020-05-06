<?php

namespace Exceedone\Exment\ColumnItems\CustomColumns;

use Exceedone\Exment\ColumnItems\CustomItem;
use Encore\Admin\Form\Field;

class Url extends CustomItem
{
    /**
     * Set column type
     *
     * @var string
     */
    protected $column_type = 'url';

    /**
     * get html(for display)
     * *this function calls from non-escaping value method. So please escape if not necessary unescape.
     */
    public function html()
    {
        $value = $this->value();
        $url = $this->value();

        $value = boolval(array_get($this->options, 'grid_column')) ? get_omitted_string($value) : $value;
     
        return "<a href='{$url}' target='_blank'>$value</a>";
    }
    
    protected function getAdminFieldClass()
    {
        return Field\Url::class;
    }
    
    public function isUrl()
    {
        return true;
    }
}
