<?php
namespace Exceedone\Exment\Services\FormSetting\FormColumn;

use App\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Linker;
use Encore\Admin\Layout\Content;
use Exceedone\Exment\Model\CustomForm;
use Exceedone\Exment\Model\CustomFormBlock;
use Exceedone\Exment\Model\CustomFormColumn;
use Exceedone\Exment\Model\CustomFormPriority;
use Exceedone\Exment\Model\CustomTable;
use Exceedone\Exment\Model\CustomColumn;
use Exceedone\Exment\Model\Linkage;
use Exceedone\Exment\Model\File as ExmentFile;
use Exceedone\Exment\Form\Tools;
use Exceedone\Exment\Enums\FileType;
use Exceedone\Exment\Enums\ColumnType;
use Exceedone\Exment\Enums\Permission;
use Exceedone\Exment\Enums\FormBlockType;
use Exceedone\Exment\Enums\FormColumnType;
use Exceedone\Exment\Enums\SystemColumn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 */
class OtherBase extends ColumnBase
{
    /**
     * Get column's view name
     *
     * @return string|null
     */
    public function getColumnViewName() : ?string
    {
        // get column name
        $column_form_column_name = FormColumnType::getOption(['id' => array_get($this->custom_form_column, 'form_column_target_id')])['column_name'] ?? null;
        return exmtrans("custom_form.form_column_type_other_options.$column_form_column_name");
    }

    /**
     * Whether this column is required
     *
     * @return boolean
     */
    public function isRequired() : bool
    {
        return false;
    }
}