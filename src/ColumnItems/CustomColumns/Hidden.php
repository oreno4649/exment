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
    protected $column_type = 'hidden';

    protected function getAdminFieldClass()
    {
        return Field\Hidden::class;
    }
}
