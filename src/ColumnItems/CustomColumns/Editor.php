<?php

namespace Exceedone\Exment\ColumnItems\CustomColumns;

use Exceedone\Exment\ColumnItems\CustomItem;
use Exceedone\Exment\Form\Field;

class Editor extends CustomItem
{
    /**
     * Set column type
     *
     * @var string
     */
    protected static $column_type = 'editor';

    public function html()
    {
        $text = $this->text();
        if (is_null($text)) {
            return null;
        }

        if (boolval(array_get($this->options, 'grid_column'))) {
            // if grid, remove tag and omit string
            $text = get_omitted_string(strip_tags($text));
        }
        
        return  '<div class="show-tinymce">'.replaceBreak(esc_script_tag($text), false).'</div>';
    }
    
    protected function getAdminFieldClassName()
    {
        return Field\Tinymce::class;
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
        $form->number('rows', exmtrans("custom_column.options.rows"))
            ->default(6)
            ->min(1)
            ->max(30)
            ->help(exmtrans("custom_column.help.rows"));
    }
}
