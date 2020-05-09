<?php

namespace Exceedone\Exment\Form\Field;

use Encore\Admin\Form\Field;

class NumberRange extends Field
{
    protected $view = 'exment::form.field.numberrange';

    /**
     * Column name.
     *
     * @var array
     */
    protected $baseColumn = '';

    /**
     * Column name.
     *
     * @var array
     */
    protected $column = [];

    public function __construct($column, $arguments)
    {
        $this->column['start'] = $column . '_start';
        $this->column['end'] = $column . '_end';

        $this->label = $this->formatLabel($arguments);
        $this->id = $this->formatId($column);
        
        $this->baseColumn = $column;
    }

    /**
     * {@inheritdoc}
     */
    public function value($value = null)
    {
        if (is_null($value)) {
            if (is_null($this->value['start']) && is_null($this->value['end'])) {
                return $this->getDefault();
            }

            return $this->value;
        }

        $this->value = $value;

        return $this;
    }
}
