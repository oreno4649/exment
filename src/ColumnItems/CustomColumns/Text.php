<?php

namespace Exceedone\Exment\ColumnItems\CustomColumns;

use Exceedone\Exment\Model\CustomColumn;
use Exceedone\Exment\ColumnItems\CustomItem;
use Exceedone\Exment\Form\Field;

class Text extends CustomItem
{
    /**
     * Set column type
     *
     * @var string
     */
    protected static $column_type = 'text';

    protected function getAdminFieldClass()
    {
        return Field\Text::class;
    }
    
    protected function setValidates(&$validates, $form_column_options)
    {
        $options = $this->custom_column->options;
        
        // value size
        if (array_get($options, 'string_length')) {
            $validates[] = 'max:'.array_get($options, 'string_length');
        }
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
            ->help(exmtrans("custom_column.help.string_length"))
        ;

        $form->checkbox('available_characters', exmtrans("custom_column.options.available_characters"))
            ->options(CustomColumn::getAvailableCharacters()->pluck('label', 'key'))
            ->help(exmtrans("custom_column.help.available_characters"))
            ;

        $form->switchbool('suggest_input', exmtrans("custom_column.options.suggest_input"))
            ->help(exmtrans("custom_column.help.suggest_input"));

        if (boolval(config('exment.expart_mode', false))) {
            $manual_url = getManualUrl('column#'.exmtrans('custom_column.options.regex_validate'));
            $form->text('regex_validate', exmtrans("custom_column.options.regex_validate"))
                ->rules('regularExpression')
                ->help(sprintf(exmtrans("custom_column.help.regex_validate"), $manual_url));
        }
    }
}
