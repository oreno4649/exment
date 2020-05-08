<?php

namespace Exceedone\Exment\ColumnItems\CustomColumns;

use Exceedone\Exment\Enums\SystemTableName;
use Exceedone\Exment\Model\CustomTable;

class Organization extends SelectTable
{
    /**
     * Set column type
     *
     * @var string
     */
    protected static $column_type = 'organization';

    public function __construct($custom_column, $custom_value)
    {
        parent::__construct($custom_column, $custom_value);

        $this->target_table = CustomTable::getEloquent(SystemTableName::ORGANIZATION);
    }
    
    public static function isOrganization()
    {
        return true;
    }

}
