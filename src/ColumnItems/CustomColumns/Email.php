<?php

namespace Exceedone\Exment\ColumnItems\CustomColumns;

use Exceedone\Exment\ColumnItems\CustomItem;
use Encore\Admin\Form\Field;

class Email extends CustomItem
{
    /**
     * Set column type
     *
     * @var string
     */
    protected static $column_type = 'email';

    protected function getAdminFieldClassName()
    {
        return Field\Email::class;
    }

    public static function isEmail()
    {
        return true;
    }
}
