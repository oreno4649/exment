<?php

namespace Exceedone\Exment\ColumnItems;

trait ItemTrait
{
    /**
     * this column's target custom_table
     */
    protected $value;

    protected $label;

    protected $id;

    protected $options;

    /**
     * get pure value. (In database value)
     * Don't call $this->value(). otherwise, aborting.
     */
    public function pureValue()
    {
        // not $this->value();
        return $this->value;
    }

    /**
     * get value
     */
    public function value()
    {
        return $this->pureValue();
    }

    /**
     * Get default value
     *
     * @return mixed
     */
    public function default(){
        return null;
    }

    /**
     * get or set option for convert
     */
    public function options($options = null)
    {
        if (!func_num_args()) {
            return $this->options ?? [];
        }

        $this->options = array_merge(
            $this->options ?? [],
            $options
        );

        return $this;
    }

    /**
     * get label. (user theader, form label etc...)
     */
    public function label($label = null)
    {
        if (!func_num_args()) {
            return $this->label;
        }
        if (isset($label)) {
            $this->label = $label;
        }
        return $this;
    }

    /**
     * get value's id.
     */
    public function id($id = null)
    {
        if (!func_num_args()) {
            return $this->id;
        }
        $this->id = $id;
        return $this;
    }

    public function prepare()
    {
    }
    
    /**
     * whether column is enabled index.
     *
     */
    public function indexEnabled()
    {
        return true;
    }

    /**
     * get cast name for sort
     */
    public function getCastName()
    {
        return null;
    }

    /**
     * Get API column name
     *
     * @return string
     */
    public function apiName()
    {
        return $this->name();
    }

    /**
     * Get API column definition
     *
     * @return array
     */
    public function apiDefinitions()
    {
        $items = [];
        $items['table_name'] = $this->custom_table->table_name;
        $items['column_name'] = $this->name();
        $items['label'] = $this->label();
        
        if (method_exists($this, 'getSummaryConditionName')) {
            $summary_condition = $this->getSummaryConditionName();
            if (isset($summary_condition)) {
                $items['summary_condition'] = $summary_condition;
            }
        }

        return $items;
    }

    /**
     * get sort column name as SQL
     */
    public function getSortColumn()
    {
        $cast = $this->getCastName();
        $index = $this->index();
        
        if (!isset($cast)) {
            return $index;
        }

        return "CAST($index AS $cast)";
    }

    /**
     * get style string from key-values
     *
     * @param array $array
     * @return string
     */
    public function getStyleString(array $array = [])
    {
        $array['word-wrap'] = 'break-word';
        $array['white-space'] = 'normal';
        return implode('; ', collect($array)->map(function ($value, $key) {
            return "$key:$value";
        })->toArray());
    }
    
    /**
     * Get Search queries for free text search
     *
     * @param string $mark
     * @param string $value
     * @param int $takeCount
     * @param string|null $q
     * @return array
     */
    public function getSearchQueries($mark, $value, $takeCount, $q, $options = [])
    {
        list($mark, $pureValue) = $this->getQueryMarkAndValue($mark, $value, $q, $options);

        $query = $this->custom_table->getValueModel()->query();
        
        $query->whereOrIn($this->custom_column->getIndexColumnName(), $mark, $pureValue)->select('id');
        
        $query->take($takeCount);

        return [$query];
    }

    /**
     * Set Search orWhere for free text search
     *
     * @param Builder $mark
     * @param string $mark
     * @param string $value
     * @param string|null $q
     * @return void
     */
    public function setSearchOrWhere(&$query, $mark, $value, $q)
    {
        list($mark, $pureValue) = $this->getQueryMarkAndValue($mark, $value, $q);

        if (is_list($pureValue)) {
            $query->orWhereIn($this->custom_column->getIndexColumnName(), toArray($pureValue));
        } else {
            $query->orWhere($this->custom_column->getIndexColumnName(), $mark, $pureValue);
        }

        return $this;
    }

    /**
     * Get pure value. If you want to change the search value, change it with this function.
     *
     * @param string $label
     * @return ?string string:matched, null:not matched
     */
    public function getValFromLabel($label)
    {
        return null;
    }

    protected function getQueryMarkAndValue($mark, $value, $q, $options = [])
    {
        $options = array_merge([
            'relation' => false,
        ], $options);

        if (is_nullorempty($q)) {
            return [$mark, $value];
        }

        // if not relation search, get pure value
        if (!boolval($options['relation'])) {
            $pureValue = $this->getValFromLabel($q);
        } else {
            $pureValue = $value;
        }

        if (is_null($pureValue)) {
            return [$mark, $value];
        }

        return ['=', $pureValue];
    }





    /**
     * Disabled form. If true, not showing.
     *
     */
    public static function disabledForm()
    {
        return false;
    }

    /**
     * whether column is Virtual. Not save in form "value" field.
     *
     */
    public static function disableSave()
    {
        return true;
    }
    
    /**
     * whether column is date (contains datetime)
     *
     */
    public static function isDate()
    {
        return false;
    }

    public static function isDateTime()
    {
        return false;
    }
    
    /**
     * whether column is Number
     *
     */
    public static function isNumber()
    {
        return false;
    }

    /**
     * whether column is text
     *
     */
    public static function isText()
    {
        return false;
    }

    /**
     * whether column is calc
     *
     */
    public static function isCalc()
    {
        return false;
    }

    public static function isUrl()
    {
        return false;
    }
    
    public static function isAttachment()
    {
        return false;
    }
    
    public static function isUser()
    {
        return false;
    }

    public static function isOrganization()
    {
        return false;
    }

    public static function isUserOrganization()
    {
        return static::isUser() || static::isOrganization();
    }

    public static function isSelectTable()
    {
        return false;
    }

    public static function isMultipleEnabled()
    {
        return false;
    }

    public static function isShowNotEscape()
    {
        return false;
    }
    
    public static function isSelect()
    {
        return false;
    }

    public static function isEmail()
    {
        return false;
    }
    
}
