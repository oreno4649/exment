<?php

namespace Exceedone\Exment\ColumnItems\CustomColumns;

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
use Exceedone\Exment\Enums\DatabaseDataType;
use Exceedone\Exment\Enums\FilterSearchType;
use Exceedone\Exment\Enums\FilterType;
use Exceedone\Exment\Enums\FormBlockType;
use Exceedone\Exment\Enums\FormColumnType;
use Exceedone\Exment\Enums\Permission;
use Exceedone\Exment\Enums\SearchType;
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
use Exceedone\Exment\Model\Linkage;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Model\Traits\ColumnOptionQueryTrait;
use Exceedone\Exment\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class SelectTable extends CustomItem
{
    use SelectTrait;

    /**
     * Set column type
     *
     * @var string
     */
    protected static $column_type = 'select_table';

    protected $target_table;
    protected $target_view;
    
    public function __construct($custom_column, $custom_value)
    {
        parent::__construct($custom_column, $custom_value);

        $this->target_table = CustomTable::getEloquent(array_get($custom_column, 'options.select_target_table'));
        $this->target_view = CustomView::getEloquent(array_get($custom_column, 'options.select_target_view'));
    }

    /**
     * sortable for grid
     */
    public function sortable()
    {
        if (boolval(array_get($this->custom_column, 'options.multiple_enabled'))) {
            return false;
        }
        return parent::sortable();
    }

    /**
     * get cast name for sort
     */
    public function getCastName()
    {
        $grammar = \DB::getQueryGrammar();
        return $grammar->getCastString(DatabaseDataType::TYPE_INTEGER, true);
    }

    public function getSelectTable()
    {
        return $this->target_table;
    }

    public function value()
    {
        return $this->getValue(false, false);
    }

    public function text()
    {
        return $this->getValue(true, false);
    }

    public function html()
    {
        return $this->getValue(true, true);
    }

    protected function getValue($text, $html)
    {
        if (!isset($this->target_table)) {
            return;
        }
        
        if (!is_array($this->pureValue()) && preg_match('/\[.+\]/i', $this->pureValue())) {
            $this->value = json_decode($this->pureValue());
        }

        // set custom value cache
        $value = toArray($this->pureValue());
        $this->target_table->setCustomValueModels($value);

        $result = [];

        foreach ($value as $v) {
            if (!isset($v)) {
                continue;
            }
            
            $model = $this->target_table->getValueModel($v);
            if (is_null($model)) {
                continue;
            }
            
            // if $model is array multiple, set as array
            if (!($model instanceof Collection)) {
                $model = [$model];
            }
    
            foreach ($model as $m) {
                if (is_null($m)) {
                    continue;
                }
                
                $result[] = $this->getResult($m, $text, $html);
            }
        }

        if ($text === false) {
            return count($result) > 0 && !$isArray ? $result[0] : $result;
        } else {
            return implode(exmtrans('common.separate_word'), $result);
        }
    }

    protected function getResult($model, $text, $html)
    {
        if ($text === false) {
            return $model;
        // get text column
        } elseif ($html) {
            return $model->getUrl(true);
        } else {
            return $model->getLabel();
        }
    }
    
    protected function getAdminFieldClassName()
    {
        if (boolval(array_get($this->custom_column, 'options.multiple_enabled'))) {
            return Field\MultipleSelect::class;
        } else {
            return Field\Select::class;
        }
    }
    
    protected function getAdminFilterClassName()
    {
        if (boolval($this->custom_column->getOption('multiple_enabled'))) {
            return Filter\Where::class;
        }
        return Filter\Equal::class;
    }

    protected function setAdminOptions(&$field, $form_column_options)
    {
        if (!isset($this->target_table)) {
            return;
        }
        if ($field instanceof ExmentField\Display) {
            return;
        }

        // if this method calls for only validate, return
        if (boolval(array_get($this->options, 'forValidate'))) {
            return;
        }

        // add table info
        $field->attribute(['data-target_table_name' => array_get($this->target_table, 'table_name')]);

        // add view info
        $linkage = $this->getLinkage($form_column_options);
        $callback = $this->getRelationFilterCallback($linkage);

        $selectOption = [
            'custom_column' => $this->custom_column,
            'display_table' => $this->custom_column->custom_table_cache,
            'filterCallback' => $callback,
            'target_view' => $this->target_view,
            'target_id' => isset($this->custom_value) ? $this->custom_value->id : null,
        ];

        $field->options(function ($value) use ($selectOption) {
            $selectOption['selected_value'] = $value;

            // get DB option value
            return $this->target_table->getSelectOptions($selectOption);
        });

        $ajax = $this->target_table->getOptionAjaxUrl($selectOption);
        if (isset($ajax)) {

            // set select2_expand data
            $select2_expand = [];
            if (isset($this->target_view)) {
                $select2_expand['target_view_id'] = array_get($this->target_view, 'id');
            }
            if (isset($linkage)) {
                $select2_expand['linkage_column_id'] = $linkage->parent_column->id;
                $select2_expand['column_id'] = $linkage->child_column->id;
                $select2_expand['linkage_value_id'] = $linkage->getParentValueId($this->custom_value);
            }

            $field->attribute([
                'data-add-select2' => $this->label(),
                'data-add-select2-ajax' => $ajax,
                'data-add-select2-expand' => json_encode($select2_expand),
            ]);
        }
    }

    /**
     * Get relation filter object
     *
     * @param ?array $form_column_options
     * @return void
     */
    protected function getLinkage($form_column_options)
    {
        // if config "select_relation_linkage_disabled" is true, not callback
        if (boolval(config('exment.select_relation_linkage_disabled', false))) {
            return;
        }

        $relation_filter_target_column_id = array_get($form_column_options, 'relation_filter_target_column_id');
        if (!isset($relation_filter_target_column_id)) {
            return;
        }

        return Linkage::getLinkage($relation_filter_target_column_id, $this->custom_column);
    }

    /**
     * get relation filter callback
     *
     * @return void
     */
    protected function getRelationFilterCallback($linkage)
    {
        if (!isset($linkage)) {
            return;
        }

        // get callback
        $callback = function (&$query) use ($linkage) {
            return $linkage->setQueryFilter($query, $linkage->getParentValueId($this->custom_value));
        };
        
        return $callback;
    }
    
    public function getAdminFilterWhereQuery($query, $input)
    {
        $this->getSelectFilterQuery($query, $input);
    }

    protected function setAdminFilterOptions(&$filter)
    {
        if (isset($this->target_table)) {
            $options = $this->target_table->getSelectOptions();
            $ajax = $this->target_table->getOptionAjaxUrl();
    
            if (isset($ajax)) {
                $filter->select([])->ajax($ajax);
            } else {
                $filter->select($options);
            }
        }
    }
    
    protected function setValidates(&$validates, $form_column_options)
    {
        $validates[] = new Validator\SelectTableNumericRule($this->target_table);
        $validates[] = new Validator\CustomValueRule($this->target_table);
    }
    
    /**
     * replace value for import
     *
     * @param mixed $value
     * @param array $setting
     * @return void
     */
    public function getImportValue($value, $setting = [])
    {
        $result = true;
        $message = null;

        $isSingle = false;
        if (!is_array($value)) {
            $isSingle = true;
            $value = [$value];
        }

        foreach ($value as &$v) {
            // get id from datalist
            if (array_has($setting, 'datalist') && !is_null($target_column_name = array_get($setting, 'target_column_name'))) {
                $target_value = array_get($setting['datalist'], $v);

                if (!isset($target_value)) {
                    $result = false;
                } else {
                    $v = $target_value;
                }
            } elseif (!isset($this->target_table)) {
                $result = false;
            } elseif (is_null($target_column_name = array_get($setting, 'target_column_name'))) {
                // if get as id and not numeric, set error
                if (!is_numeric($v)) {
                    $result = false;
                    $message = trans('validation.integer', ['attribute' => $this->label()]);
                }
            } else {
                // get target value
                $target_custom_column = CustomColumn::getEloquent($target_column_name, $this->target_table);
                if (isset($target_custom_column)) {
                    $target_value = $this->target_table->getValueModel()->where($target_custom_column->getQueryKey(), $v)->select(['id'])->first();
                }

                if (!isset($target_value)) {
                    $result = false;
                } else {
                    $v = $target_value->id;
                }
            }
        }

        if ($isSingle && count($value) == 1) {
            $value = $value[0];
        }

        return [
            'result' => $result,
            'value' => $value,
            'message' => $message,
        ];
    }

    /**
     * Get Key and Id List
     *
     * @param [type] $datalist
     * @param [type] $key
     * @return void
     */
    public function getKeyAndIdList($datalist, $key)
    {
        if (is_nullorempty($datalist) || is_nullorempty($key)) {
            return [];
        }

        // if has request session
        $sessionkey = sprintf(Define::SYSTEM_KEY_SESSION_IMPORT_KEY_VALUE, $this->custom_table, $this->custom_column->column_name, $key);
        return System::requestSession($sessionkey, function () use ($datalist, $key) {
            // get key and value list
            $keyValueList = collect($datalist)->filter()->map(function ($d) {
                return $d->pureValue($this->custom_column);
            })->flatten()->filter()->toArray();

            $target_custom_column = CustomColumn::getEloquent($key, $this->target_table);
            $indexName = $target_custom_column ? $target_custom_column->getQueryKey() : "value->$key";
            $values = $this->target_table->getValueModel()->whereIn($indexName, $keyValueList)->select(['value', 'id'])
                ->get()->filter()->mapWithKeys(function ($v) use ($key) {
                    return [array_get($v, "value.$key") => $v->id];
                });

            return $values;
        });
    }
    
    /**
     * Get pure value. If you want to change the search value, change it with this function.
     *
     * @param [type] $value
     * @return ?string string:matched, null:not matched
     */
    public function getValFromLabel($label)
    {
        $select_table = $this->custom_column->select_target_table ?? null;
        if (!isset($select_table)) {
            return null;
        }

        // get label columns
        $labelColumns = $select_table->getLabelColumns();

        // not support table_label_format
        if (is_string($labelColumns)) {
            return null;
        }

        $use_table_label_id = boolval($select_table->getOption('use_label_id_flg', false));

        // searching select table query
        $query = $select_table->getValueModel()::query();
        $executeSearch = false;

        // only single search column, search
        if (!$use_table_label_id && count($labelColumns) == 1) {
            if ($this->setSelectTableQuery($query, array_get($labelColumns[0], 'table_label_id'), $label)) {
                $executeSearch = true;
            }
        } else {
            // split $label space. zen-han
            $label = str_replace('　', ' ', $label);
            $label = preg_replace('/\s+/', ' ', $label);
            $items = preg_split('/[\s|\x{3000}]+/u', $label);

            $searchAsId = $use_table_label_id && substr($items[0], 0, 1) == '#';

            if ($searchAsId) {
                $searchId = substr($items[0], 1);
                $query->where('id', $searchId);

                $executeSearch = true;
            }

            $labelColumnIndex = 0;
            for ($i = ($searchAsId ? 1 : 0); $i < count($items); $i++) {
                if (count($labelColumns) <= $labelColumnIndex) {
                    return null;
                }

                if ($this->setSelectTableQuery($query, array_get($labelColumns[$labelColumnIndex++], 'table_label_id'), $items[$i])) {
                    $executeSearch = true;
                }
            }
        }

        if (!$executeSearch) {
            return null;
        }

        // get value
        $ids = $query->pluck('id');
        return is_nullorempty($ids) ? null : $ids;
    }

    protected function setSelectTableQuery($query, $custom_column_id, $value)
    {
        $custom_column = CustomColumn::getEloquent($custom_column_id);
        if (!isset($custom_column)) {
            return false;
        }

        $column_item = $custom_column->column_item;
        if (!isset($column_item)) {
            return false;
        }

        $searchValue = $column_item->getValFromLabel($value);
        if (!isset($searchValue)) {
            $searchValue = $value;
        }

        if (System::filter_search_type() == FilterSearchType::ALL) {
            $searchValue = '%' . $searchValue . '%';
        } else {
            $searchValue = $searchValue . '%';
        }

        $name = $custom_column->getQueryKey();
        $query->where($name, 'LIKE', $searchValue);
        
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
        $custom_column = $this->custom_column;
        $id = $this->custom_column->id;

        // define select-target table
        if(!is_subclass_of($this, \Exceedone\Exment\ColumnItems\CustomColumns\SelectTable::class)){
            $form->select('select_target_table', exmtrans("custom_column.options.select_target_table"))
            ->help(exmtrans("custom_column.help.select_target_table"))
            ->required()
            ->options(function ($select_table) {
                $options = CustomTable::filterList()->whereNotIn('table_name', [SystemTableName::USER, SystemTableName::ORGANIZATION])->pluck('table_view_name', 'id')->toArray();
                return $options;
            })
            ->attribute([
                'data-linkage' => json_encode([
                    'options_select_import_column_id' => [
                        'url' => admin_url('webapi/table/indexcolumns'),
                        'text' => 'column_view_name',
                    ],
                    'options_select_export_column_id' => [
                        'url' => admin_url('webapi/table/columns'),
                        'text' => 'column_view_name',
                    ],
                    'options_select_target_view' => [
                        'url' => admin_url('webapi/table/filterviews'),
                        'text' => 'view_view_name',
                    ]
                ]),
            ]);

        }

        // define select-target table view
        $form->select('select_target_view', exmtrans("custom_column.options.select_target_view"))
            ->help(exmtrans("custom_column.help.select_target_view"))
            ->options(function ($select_view, $form) use ($custom_column) {
                $data = $form->data();
                if (!isset($data)) {
                    return [];
                }

                // select_table
                $select_target_table = array_get($data, 'select_target_table');
                if (!isset($select_target_table)) {
                    if (!isset($custom_column) || !$custom_column->isUserOrganization()) {
                        return [];
                    }
                    $select_target_table = CustomTable::getEloquent($custom_column->custom_table_id);
                }

                return CustomTable::getEloquent($select_target_table)->custom_views
                    ->filter(function ($value) {
                        return array_get($value, 'view_kind_type') == ViewKindType::FILTER;
                    })->pluck('view_view_name', 'id');
            });
        
        $custom_item = $this;
        $manual_url = getManualUrl('data_import_export#'.exmtrans('custom_column.help.select_import_column_id_key'));
        $form->select('select_import_column_id', exmtrans("custom_column.options.select_import_column_id"))
            ->help(exmtrans("custom_column.help.select_import_column_id", $manual_url))
            ->options(function ($select_table, $form) use ($id, $custom_item) {
                return $custom_item->getImportExportColumnSelect($select_table, $form, $id);
            });

        $form->select('select_export_column_id', exmtrans("custom_column.options.select_export_column_id"))
            ->help(exmtrans("custom_column.help.select_export_column_id"))
            ->options(function ($select_table, $form) use ($id, $custom_item) {
                return $custom_item->getImportExportColumnSelect($select_table, $form, $id, false);
            });

        $form->switchbool('select_load_ajax', exmtrans("custom_column.options.select_load_ajax"))
            ->help(exmtrans("custom_column.help.select_load_ajax", config('exment.select_table_limit_count', 100)))
            ->default("0");

        // enable multiple
        $form->switchbool('multiple_enabled', exmtrans("custom_column.options.multiple_enabled"));
    }
    
    /**
     * Get import export select list
     *
     * @return void
     */
    protected function getImportExportColumnSelect($select_table, $form, $id, $isImport = true)
    {
        if ($this->isUserOrganization()) {
            return CustomTable::getEloquent($this->isUser() ? SystemTableName::USER : SystemTableName::ORGANIZATION)->getColumnsSelectOptions([
                'index_enabled_only' => $isImport,
                'include_system' => false,
            ]) ?? [];
        }

        // select_table
        if (is_null($select_target_table = $this->custom_column->select_target_table)) {
            return [];
        }
        return CustomTable::getEloquent($select_target_table)->getColumnsSelectOptions([
            'index_enabled_only' => $isImport,
            'include_system' => false,
        ]) ?? [];
    }

    public static function isUrl()
    {
        return true;
    }
    
    public static function isSelectTable()
    {
        return true;
    }
    
    public static function isMultipleEnabled()
    {
        return true;
    }
    
    public static function isSelect()
    {
        return true;
    }
    
    /**
     * Get Search queries for free text search
     *
     * @param [type] $mark
     * @param [type] $value
     * @param [type] $takeCount
     * @return void
     */
    public function getSearchQueries($mark, $value, $takeCount, $q, $options = [])
    {
        if (!boolval($this->custom_column->getOption('multiple_enabled'))) {
            return parent::getSearchQueries($mark, $value, $takeCount, $q, $options);
        }

        $query = $this->custom_table->getValueModel()->query();
        $this->getAdminFilterWhereQuery($query, $value);

        $query->take($takeCount)->select('id');

        return [$query];
    }
}
