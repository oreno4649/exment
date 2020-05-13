<?php

namespace Exceedone\Exment\ColumnItems;

use Encore\Admin\Form;
use Encore\Admin\Form\Field;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Filter\Where;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Table;
use Exceedone\Exment\ColumnItems\CustomColumns\AutoNumber;
use Exceedone\Exment\ColumnItems\CustomItem;
use Exceedone\Exment\Enums\ColumnType;
use Exceedone\Exment\Enums\ConditionType;
use Exceedone\Exment\Enums\CurrencySymbol;
use Exceedone\Exment\Enums\FilterSearchType;
use Exceedone\Exment\Enums\FilterType;
use Exceedone\Exment\Enums\FormBlockType;
use Exceedone\Exment\Enums\FormColumnType;
use Exceedone\Exment\Enums\Permission;
use Exceedone\Exment\Enums\SystemColumn;
use Exceedone\Exment\Enums\SystemTableName;
use Exceedone\Exment\Enums\ViewKindType;
use Exceedone\Exment\Form\Field as ExmentField;
use Exceedone\Exment\Form\Tools;
use Exceedone\Exment\Grid\Filter as ExmentFilter;
use Exceedone\Exment\Model\CustomColumn;
use Exceedone\Exment\Model\CustomColumnMulti;
use Exceedone\Exment\Model\CustomForm;
use Exceedone\Exment\Model\CustomFormColumn;
use Exceedone\Exment\Model\CustomTable;
use Exceedone\Exment\Model\CustomView;
use Exceedone\Exment\Model\CustomViewColumn;
use Exceedone\Exment\Model\Define;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Model\Traits\ColumnOptionQueryTrait;
use Exceedone\Exment\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

abstract class CustomItem implements ItemInterface
{
    use ItemTrait, SummaryItemTrait, ColumnOptionQueryTrait, CustomItemEngineTrait;
    
    protected $custom_column;
    
    protected $custom_table;
    
    protected $custom_value;
    
    /**
     * Set column type
     *
     * @var string
     */
    protected static $column_type = '';

    /**
     * laravel-admin set required. if false, always not-set required
     */
    protected $required = true;

    
    public function __construct($custom_column, $custom_value, $view_column_target = null)
    {
        $this->custom_column = $custom_column;
        $this->custom_table = CustomTable::getEloquent($custom_column);
        $this->setCustomValue($custom_value);
        $this->options = [];

        $params = static::getOptionParams($view_column_target, $this->custom_table);
        // get label. check not match $this->custom_table and pivot table
        if (array_key_value_exists('view_pivot_table_id', $params) && $this->custom_table->id != $params['view_pivot_table_id']) {
            $this->label = static::getViewColumnLabel($this->custom_column->column_view_name, $this->custom_table->table_view_name);
        } else {
            $this->label = $this->custom_column->column_view_name;
        }

        if(method_exists($this, 'initialized')){
            $this->initialized([]);
        }
    }

    /**
     * get column type
     *
     * @var string
     */
    public static function getColumnType()
    {
        return static::$column_type;
    }

    /**
     * Get column type's view name.
     *
     * @return void
     */
    public static function getColumnTypeViewName()
    {
        return array_get(ColumnType::transArray("custom_column.column_type_options"), static::getColumnType());
    }

    /**
     * Get custom laravel admin's Fields class name
     *
     * @return void
     */
    public static function getCustomAdminExtend(){
        return null;
    }

    /**
     * get column name
     */
    public function name()
    {
        return $this->custom_column->column_name;
    }

    /**
     * sqlname
     */
    public function sqlname()
    {
        if (boolval(array_get($this->options, 'summary'))) {
            return $this->getSummarySqlName();
        }
        if (boolval(array_get($this->options, 'groupby'))) {
            return $this->getGroupBySqlName();
        }

        return $this->custom_column->getQueryKey();
    }

    /**
     * get index name
     */
    public function index()
    {
        return $this->custom_column->getIndexColumnName();
    }

    /**
     * Get default value. If value is null, return this result.
     *
     * @return mixed
     */
    public function default(){
        return null;
    }
    
    /**
     * Get default value. Only avaiable form input.
     *
     * @return mixed
     */
    public function defaultForm(){
        // custom column's option "default" is for only create. 
        $default = $this->custom_column->getOption('default');
        if(!is_null($default)){
            return $default;
        }

        return $this->default();
    }
    
    /**
     * get value
     * Don't call $this->value(). otherwise, aborting.
     */
    public function pureValue()
    {
        if(!is_nullorempty($this->value)){
            return $this->value;
        }

        return $this->default();
    }

    /**
     * get Text(for display)
     */
    public function text()
    {
        return $this->value();
    }

    /**
     * get html(for display)
     */
    public function html()
    {
        // default escapes text
        $text = boolval(array_get($this->options, 'grid_column')) ? get_omitted_string($this->text()) : $this->text();
        return esc_html($text);
    }

    /**
     * whether column is enabled index.
     *
     */
    public function indexEnabled()
    {
        return $this->custom_column->index_enabled;
    }

    /**
     * get view filter type
     */
    public function getViewFilterType()
    {
        return FilterType::DEFAULT;
    }

    /**
     * get value before saving
     *
     * @return void
     */
    public function saving()
    {
    }

    /**
     * get value after saving
     *
     * @return void
     */
    public function saved()
    {
    }

    /**
     * replace value for import
     *
     * @param mixed $value
     * @param array $options
     * @return array
     *     result : import result is true or false.
     *     message : If error, showing error message
     *     skip :Iif true, skip import this column.
     *     value : Replaced value.
     */
    public function getImportValue($value, $options = [])
    {
        return [
            'result' => true,
            'value' => $value,
        ];
    }

    protected function getAdminFieldClassName(){
        return null;
    }

    protected function getAdminFilterClassName()
    {
        if (System::filter_search_type() == FilterSearchType::ALL) {
            return Filter\Like::class;
        }

        return ExmentFilter\StartsWith::class;
    }

    protected function setAdminOptions(&$field, $form_column_options)
    {
    }

    protected function setAdminFilterOptions(&$filter)
    {
    }
    
    protected function setValidates(&$validates, $form_column_options)
    {
    }

    /**
     * Compare two values.
     */
    public function compareTwoValues(CustomColumnMulti $compare_column, $this_value, $target_value)
    {
        return true;
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
    }

    /**
     * whether column is Virtual. Not save in form "value" field.
     *
     */
    public static function isVirtual()
    {
        return false;
    }

    /**
     * Whether is use custom column. If false, not show column column list.
     *
     * @return boolean
     */
    public static function isUseCustomColumn(){
        return true;
    }

}
