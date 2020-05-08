<?php

namespace Exceedone\Exment\ColumnItems\CustomColumns;

class SelectValtext extends Select
{
    use ImportValueTrait;
    
    /**
     * Set column type
     *
     * @var string
     */
    protected static $column_type = 'select_valtext';

    protected function getReturnsValue($select_options, $val, $label)
    {
        // switch column_type and get return value
        $returns = [];
        // loop keyvalue
        foreach ($val as $v) {
            // set whether $label
            if (is_null($v)) {
                $returns[] = null;
            } else {
                $returns[] = $label ? array_get($select_options, $v) : $v;
            }
        }
        return $returns;
    }
    
    protected function getImportValueOption()
    {
        return $this->custom_column->createSelectOptions();
    }

    /**
     * Get pure value. If you want to change the search value, change it with this function.
     *
     * @param [type] $value
     * @return ?string string:matched, null:not matched
     */
    public function getValFromLabel($label)
    {
        foreach ($this->custom_column->createSelectOptions() as $key => $q) {
            if ($label == $q) {
                return $key;
            }
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
        // define select-item
        $form->textarea('select_item_valtext', exmtrans("custom_column.options.select_item"))
            ->required()
            ->help(exmtrans("custom_column.help.select_item_valtext"));

        // enable multiple
        $form->switchbool('multiple_enabled', exmtrans("custom_column.options.multiple_enabled"));

    }
}
