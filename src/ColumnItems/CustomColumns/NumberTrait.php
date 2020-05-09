<?php

namespace Exceedone\Exment\ColumnItems\CustomColumns;

use Exceedone\Exment\Model\CustomColumnMulti;
use Exceedone\Exment\Enums\FilterOption;
use Exceedone\Exment\Enums;

/**
 * Intefer, decimal, currency common logic
 */
trait NumberTrait
{
    /**
     * whether column is Numeric
     *
     */
    public static function isNumber()
    {
        return true;
    }
    
    /**
     * whether column is calc
     *
     */
    public static function isCalc()
    {
        return true;
    }

    /**
     * get view filter type
     */
    public function getViewFilterType()
    {
        return Enums\FilterType::NUMBER;
    }
    
    /**
     * Compare two values.
     */
    public function compareTwoValues(CustomColumnMulti $compare_column, $this_value, $target_value)
    {
        switch ($compare_column->compare_type) {
            case FilterOption::COMPARE_GT:
                if ($this_value > $target_value) {
                    return true;
                }

                return $compare_column->getCompareErrorMessage('validation.not_gt', $compare_column->compare_column1, $compare_column->compare_column2);
                
            case FilterOption::COMPARE_GTE:
                if ($this_value >= $target_value) {
                    return true;
                }

                return $compare_column->getCompareErrorMessage('validation.not_gte', $compare_column->compare_column1, $compare_column->compare_column2);
                
            case FilterOption::COMPARE_LT:
                if ($this_value < $target_value) {
                    return true;
                }

                return $compare_column->getCompareErrorMessage('validation.not_lt', $compare_column->compare_column1, $compare_column->compare_column2);
                
            case FilterOption::COMPARE_LTE:
                if ($this_value <= $target_value) {
                    return true;
                }

                return $compare_column->getCompareErrorMessage('validation.not_lte', $compare_column->compare_column1, $compare_column->compare_column2);
        }

        return true;
    }
    
    /**
     * Set Custom Column Option Form. Using laravel-admin form option
     * https://laravel-admin.org/docs/#/en/model-form-fields
     *
     * @param Form $form
     * @return void
     */
    public function setCustomColumnOptionFormNumber(&$form)
    {
        // calc
        $custom_table = $this->custom_table;
        $id = $this->custom_column->id;
        $self = $this;
        
        $form->number('number_min', exmtrans("custom_column.options.number_min"))
            ->disableUpdown()
            ->defaultEmpty();
            
        $form->number('number_max', exmtrans("custom_column.options.number_max"))
            ->disableUpdown()
            ->defaultEmpty();
        
        $form->switchbool('number_format', exmtrans("custom_column.options.number_format"))
            ->help(exmtrans("custom_column.help.number_format"))
            ;
        
        $form->valueModal('calc_formula', exmtrans("custom_column.options.calc_formula"))
            ->help(exmtrans("custom_column.help.calc_formula"))
            ->ajax(admin_urls('column', $custom_table->table_name, $id, 'calcModal?column_type=' . $this->column_type))
            ->modalContentname('options_calc_formula')
            ->valueTextScript('Exment.CustomColumnEvent.GetSettingValText();')
            ->text(function ($value) use ($id, $custom_table, $self) {
                /////TODO:copy and paste
                if (!isset($value)) {
                    return null;
                }
                // convert json to array
                if (!is_array($value) && is_json($value)) {
                    $value = json_decode($value, true);
                }

                $custom_column_options = $self->getCalcCustomColumnOptions($id, $custom_table);
                ///// get text
                $texts = [];
                foreach ($value as &$v) {
                    $texts[] = $self->getCalcDisplayText($v, $custom_column_options);
                }
                return implode(" ", $texts);
            })
        ;
    }
    
    public function calcModal($request)
    {
        $id = $this->custom_column->id;
        
        // get other columns
        // return $id is null(calling create fuction) or not match $id and row id.
        $custom_column_options = $this->getCalcCustomColumnOptions($id, $this->custom_table);
        
        // get value
        $value = $request->get('options_calc_formula');

        if (!isset($value)) {
            $value = [];
        }
        $value = jsonToArray($value);

        ///// get text
        foreach ($value as &$v) {
            $v['text'] = $this->getCalcDisplayText($v, $custom_column_options);
        }
        
        $render = view('exment::custom-column.calc_formula_modal', [
            'custom_columns' => $custom_column_options,
            'value' => $value,
            'symbols' => exmtrans('custom_column.symbols'),
        ]);
        return getAjaxResponse([
            'body'  => $render->render(),
            'showReset' => true,
            'title' => exmtrans("custom_column.options.calc_formula"),
            'contentname' => 'options_calc_formula',
            'submitlabel' => trans('admin.setting'),
        ]);
    }

    /**
     * Get column options for calc
     *
     * @param [type] $id
     * @param [type] $custom_table
     * @return void
     */
    protected function getCalcCustomColumnOptions($id, $custom_table)
    {
        $options = [];

        // get calc options
        $custom_table->custom_columns_cache->filter(function ($custom_column) use ($id) {
            if (isset($id) && $id == array_get($custom_column, 'id')) {
                return false;
            }
            if (!$custom_column->isCalc()) {
                return false;
            }

            return true;
        })->each(function ($custom_column) use (&$options) {
            $options[] = [
                'val' => $custom_column->id,
                'type' => 'dynamic',
                'text' => $custom_column->column_view_name,
            ];
        });
        
        // get select table custom columns
        $select_table_custom_columns = [];
        $custom_table->custom_columns->each(function ($custom_column) use ($id, &$options) {
            if (isset($id) && $id == array_get($custom_column, 'id')) {
                return;
            }
            if (!$custom_column->isSelectTable()) {
                return;
            }

            // get select table's calc column
            $custom_column->select_target_table->custom_columns_cache->filter(function ($select_target_column) use ($id, $custom_column, &$options) {
                if (isset($id) && $id == array_get($select_target_column, 'id')) {
                    return false;
                }
                if (!$select_target_column->isCalc()) {
                    return false;
                }
    
                return true;
            })->each(function ($select_target_column) use ($custom_column, &$options) {
                $options[] = [
                    'val' => $custom_column->id,
                    'type' => 'select_table',
                    'from' => $select_target_column->id,
                    'text' => $custom_column->column_view_name . '/' . $select_target_column->column_view_name,
                ];
            });
        });

        // add child columns
        $child_relations = $custom_table->custom_relations;
        if (isset($child_relations)) {
            foreach ($child_relations as $child_relation) {
                $child_table = $child_relation->child_custom_table;
                $child_table_name = array_get($child_table, 'table_view_name');
                $options[] = [
                    'type' => 'count',
                    'text' => exmtrans('custom_column.child_count_text', $child_table_name),
                    'custom_table_id' => $child_table->id
                ];

                $child_columns = $child_table->custom_columns_cache->filter(function ($custom_column) {
                    return $custom_column->isCalc();
                })->map(function ($custom_column) use ($child_table_name) {
                    return [
                        'type' => 'summary',
                        'val' => $custom_column->id,
                        'text' => exmtrans('custom_column.child_sum_text', $child_table_name, $custom_column->column_view_name),
                        'custom_table_id' => $custom_column->custom_table_id
                    ];
                })->toArray();
                $options = array_merge($options, $child_columns);
            }
        }
        
        return $options;
    }

    protected function getCalcDisplayText($v, $custom_column_options)
    {
        $val = array_get($v, 'val');
        $table = array_get($v, 'table');
        $text = null;
        switch (array_get($v, 'type')) {
            case 'dynamic':
            case 'select_table':
                $target_column = collect($custom_column_options)->first(function ($custom_column_option) use ($v) {
                    return array_get($v, 'val') == array_get($custom_column_option, 'val') && array_get($v, 'type') == array_get($custom_column_option, 'type');
                });
                $text = array_get($target_column, 'text');
                break;
            case 'count':
                if (isset($table)) {
                    $child_table = CustomTable::getEloquent($table);
                    if (isset($child_table)) {
                        $text = exmtrans('custom_column.child_count_text', $child_table->table_view_name);
                    }
                }
                break;
            case 'summary':
                $column = CustomColumn::getEloquent($val);
                if (isset($column)) {
                    $text = exmtrans('custom_column.child_sum_text', $column->custom_table->table_view_name, $column->column_view_name);
                }
                break;
            case 'symbol':
                $symbols = exmtrans('custom_column.symbols');
                $text = array_get($symbols, $val);
                break;
            case 'fixed':
                $text = $val;
                break;
        }
        return $text;
    }

}
