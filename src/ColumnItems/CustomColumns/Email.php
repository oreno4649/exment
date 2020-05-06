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
    protected $column_type = 'email';

    protected function getAdminFieldClass()
    {
        return Field\Email::class;
    }

    public function isEmail()
    {
        return true;
    }
}
