<?php

namespace Exceedone\Exment\ColumnItems\CustomColumns;

use Exceedone\Exment\Enums\SystemTableName;
use Exceedone\Exment\Model\CustomTable;

class User extends SelectTable
{
    /**
     * Set column type
     *
     * @var string
     */
    protected $column_type = 'user';

    public function __construct($custom_column, $custom_value)
    {
        parent::__construct($custom_column, $custom_value);

        $this->target_table = CustomTable::getEloquent(SystemTableName::USER);
    }
        
    public function isUser()
    {
        return true;
    }

    /**
     * Get default value. Only avaiable form input.
     *
     * @return mixed
     */
    public function defaultForm(){
        if(!is_null($default = parent::default())){
            return $default;
        }
        if(!is_null($default = $this->custom_column->getOption('login_user_default'))){
            return $default;
        }

        return null;
    }
}
