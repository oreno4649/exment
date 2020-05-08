<?php

namespace Exceedone\Exment\ColumnItems\CustomColumns;

use Exceedone\Exment\Enums\CurrencySymbol;

class Currency extends Decimal
{
    /**
     * Set column type
     *
     * @var string
     */
    protected static $column_type = 'currency';

    public function text()
    {
        list($symbol, $value) = $this->getSymbolAndValue();
        if (!isset($symbol)) {
            return $value;
        }

        return getCurrencySymbolLabel($symbol, false, $value);
    }

    public function html()
    {
        list($symbol, $value) = $this->getSymbolAndValue();
        if (!isset($symbol)) {
            return $value;
        }

        return getCurrencySymbolLabel($symbol, true, $value);
    }

    protected function getSymbolAndValue()
    {
        if (is_null($this->value())) {
            return [null, null];
        }

        if (boolval(array_get($this->custom_column, 'options.number_format'))
        && is_numeric($this->value())
        && !boolval(array_get($this->options, 'disable_number_format'))) {
            if (array_has($this->custom_column, 'options.decimal_digit')) {
                $digit = intval(array_get($this->custom_column, 'options.decimal_digit'));
                $value = number_format($this->value(), $digit);
            //$value = preg_replace("/\.?0+$/",'', $value);
            } else {
                $value = number_format($this->value());
            }
        } else {
            $value = $this->value();
        }

        if (boolval(array_get($this->options, 'disable_currency_symbol'))) {
            return [null, $value];
        }
        // get symbol
        $symbol = array_get($this->custom_column, 'options.currency_symbol');
        return [$symbol, $value];
    }

    protected function setAdminOptions(&$field, $form_column_options)
    {
        parent::setAdminOptions($field, $form_column_options);
        
        $options = $this->custom_column->options;
        
        // get symbol
        $symbol = CurrencySymbol::getEnum(array_get($options, 'currency_symbol'));
        if (isset($symbol)) {
            $field->prepend(array_get($symbol->getOption(), 'html'));
        }
        $field->attribute(['style' => 'max-width: 200px']);
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
        $this->setCustomColumnOptionFormNumber($form);

        $form->select('currency_symbol', exmtrans("custom_column.options.currency_symbol"))
            ->help(exmtrans("custom_column.help.currency_symbol"))
            ->required()
            ->options(function ($option) {
                // create options
                $options = [];
                $currencies = CurrencySymbol::values();
                foreach ($currencies as $currency) {
                    // make text
                    $options[$currency->getValue()] = getCurrencySymbolLabel($currency, true, '123,456.00');
                }
                return $options;
            });
            
        $form->number('decimal_digit', exmtrans("custom_column.options.decimal_digit"))
            ->default(2)
            ->min(0)
            ->max(8);
        
    }
}
