<?php

namespace Exceedone\Exment\ColumnItems\CustomColumns;

use Exceedone\Exment\ColumnItems\CustomItem;
use Encore\Admin\Form\Field;

class Hidden extends CustomItem
{
    /**
     * Set column type
     *
     * @var string
     */
    protected static $column_type = 'hidden';

    protected function getAdminFieldClassName()
    {
        return Field\Hidden::class;
    }

    /**
     * Whether is use custom column. If false, not show column column list.
     *
     * @return boolean
     */
    public static function isUseCustomColumn(){
        return false;
    }
}
