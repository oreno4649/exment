<?php

namespace Exceedone\Exment\ColumnItems\CustomColumns;

use Exceedone\Exment\ColumnItems\CustomItem;
use Encore\Admin\Form\Field;

class Textarea extends CustomItem
{
    /**
     * Set column type
     *
     * @var string
     */
    protected static $column_type = 'textarea';

    public function html()
    {
        $text = $this->text();
        $text = boolval(array_get($this->options, 'grid_column')) ? get_omitted_string($text) : $text;
        
        return  replaceBreak($text);
    }
    protected function getAdminFieldClass()
    {
        return Field\Textarea::class;
    }
    
    protected function setAdminOptions(&$field, $form_column_options)
    {
        $options = $this->custom_column->options;
        $field->rows(array_get($options, 'rows', 6));
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
        // text
        // string length
        $form->number('string_length', exmtrans("custom_column.options.string_length"))
            ->default(256)
            ->help(exmtrans("custom_column.help.string_length"));

        $form->number('rows', exmtrans("custom_column.options.rows"))
            ->default(6)
            ->min(1)
            ->max(30)
            ->help(exmtrans("custom_column.help.rows"));
    }
}
