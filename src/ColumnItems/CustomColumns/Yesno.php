<?php

namespace Exceedone\Exment\ColumnItems\CustomColumns;

use Exceedone\Exment\ColumnItems\CustomItem;
use Exceedone\Exment\Form\Field;
use Exceedone\Exment\Model\Define;
use Encore\Admin\Grid\Filter;

class Yesno extends CustomItem
{
    use ImportValueTrait;
    
    /**
     * Set column type
     *
     * @var string
     */
    protected static $column_type = 'yesno';

    /**
     * laravel-admin set required. if false, always not-set required
     */
    protected $required = false;

    public function text()
    {
        return boolval($this->value()) ? 'YES' : 'NO';
    }

    public function saving()
    {
        $value = $this->value();
        if (is_null($value)) {
            return 0;
        }
        if (strtolower($value) === 'yes') {
            return 1;
        }
        if (strtolower($value) === 'no') {
            return 0;
        }
        return boolval($value) ? 1 : 0;
    }

    protected function getAdminFieldClassName()
    {
        return Field\SwitchBoolField::class;
    }
    
    protected function getAdminFilterClassName()
    {
        return Filter\Equal::class;
    }

    protected function setAdminFilterOptions(&$filter)
    {
        $filter->radio(Define::YESNO_RADIO);
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
        $form->text('true_value', exmtrans("custom_column.options.true_value"))
            ->help(exmtrans("custom_column.help.true_value"))
            ->required();

        $form->text('true_label', exmtrans("custom_column.options.true_label"))
            ->help(exmtrans("custom_column.help.true_label"))
            ->required()
            ->default(exmtrans("custom_column.options.true_label_default"));
        
        $form->text('false_value', exmtrans("custom_column.options.false_value"))
            ->help(exmtrans("custom_column.help.false_value"))
            ->required();

        $form->text('false_label', exmtrans("custom_column.options.false_label"))
            ->help(exmtrans("custom_column.help.false_label"))
            ->required()
            ->default(exmtrans("custom_column.options.false_label_default"));
    }

    /**
     * replace value for import
     *
     * @param mixed $value
     * @param array $setting
     * @return void
     */
    public function getImportValueOption()
    {
        return [
            0    => 'NO',
            1    => 'YES',
        ];
    }

    /**
     * Get pure value. If you want to change the search value, change it with this function.
     *
     * @param [type] $value
     * @return ?string string:matched, null:not matched
     */
    public function getValFromLabel($label)
    {
        $option = $this->getImportValueOption();

        foreach ($option as $value => $l) {
            if (strtolower($label) == strtolower($l)) {
                return $value;
            }
        }
        return null;
    }
    
    /**
     * Get default value
     *
     * @return mixed
     */
    public function default(){
        if(!is_null($default = parent::default())){
            return $default;
        }

        return 0;
    }
}
