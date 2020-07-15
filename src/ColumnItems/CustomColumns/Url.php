<?php

namespace Exceedone\Exment\ColumnItems\CustomColumns;

use Exceedone\Exment\ColumnItems\CustomItem;
use Exceedone\Exment\Enums\UrlTagType;
use Encore\Admin\Form\Field;

class Url extends CustomItem
{
    /**
     * Set column type
     *
     * @var string
     */
    protected static $column_type = 'url';

    /**
     * get html(for display)
     * *this function calls from non-escaping value method. So please escape if not necessary unescape.
     */
    public function html()
    {
        $value = $this->value();
        $url = $this->value();

        $value = boolval(array_get($this->options, 'grid_column')) ? get_omitted_string($value) : $value;
        
        return \Exment::getUrlTag($url, $value, UrlTagType::BLANK);
    }
    
    protected function getAdminFieldClassName()
    {
        return Field\Url::class;
    }
    
    public static function isUrl()
    {
        return true;
    }
}
