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
    protected static $column_type = 'user';

    public function __construct($custom_column, $custom_value)
    {
        parent::__construct($custom_column, $custom_value);

        $this->target_table = CustomTable::getEloquent(SystemTableName::USER);
    }
        
    public static function isUser()
    {
        return true;
    }

    /**
     * Get default value. Only avaiable form input.
     *
     * @return mixed
     */
    public function defaultForm(){
        if(!is_null($default = parent::defaultForm())){
            return $default;
        }
        if(!is_null($default = $this->custom_column->getOption('login_user_default'))){
            return $default;
        }

        return null;
    }
    
    /**
     * Set Custom Column Option Form. Using laravel-admin form option
     * https://laravel-admin.org/docs/#/en/model-form-fields
     *
     * @param Form $form
     * @return void
     */
    public function setCustomColumnOptionForm(&$form)
    {
        parent::setCustomColumnOptionForm($form);
        
        $form->switchbool('login_user_default', exmtrans("custom_column.options.login_user_default"))
        ->help(exmtrans("custom_column.help.login_user_default"));
    }
}
