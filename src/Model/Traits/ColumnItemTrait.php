<?php

namespace Exceedone\Exment\Model\Traits;

use Exceedone\Exment\Model\System;

/**
 * Column Item trait. for Custom Column.
 */
trait ColumnItemTrait
{
    public function isCalc()
    {
        return $this->column_item->isCalc();
    }

    public function isDate()
    {
        return $this->column_item->isDate();
    }

    public function isDateTime()
    {
        return $this->column_item->isDateTime();
    }
    
    public function isUrl()
    {
        return $this->column_item->isUrl();
    }
    
    public function isAttachment()
    {
        return $this->column_item->isAttachment();
    }
    
    public function isUserOrganization()
    {
        return $this->column_item->isUserOrganization();
    }

    public function isUser()
    {
        return $this->column_item->isUser();
    }

    public function isOrganization()
    {
        return $this->column_item->isOrganization();
    }

    public function isSelectTable()
    {
        return $this->column_item->isSelectTable();
    }

    public function isMultipleEnabled()
    {
        return $this->column_item->isMultipleEnabled();
    }

    public function isNotEscape()
    {
        return $this->column_item->isNotEscape();
    }
    
    public function isSelectForm()
    {
        return $this->column_item->isSelectForm();
    }
}
