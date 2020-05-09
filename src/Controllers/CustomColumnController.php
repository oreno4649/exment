<?php

namespace Exceedone\Exment\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;
use Exceedone\Exment\Model\Define;
use Exceedone\Exment\Model\CustomTable;
use Exceedone\Exment\Model\CustomColumn;
use Exceedone\Exment\Model\CustomForm;
use Exceedone\Exment\Model\CustomFormColumn;
use Exceedone\Exment\Model\CustomView;
use Exceedone\Exment\Model\CustomViewColumn;
use Exceedone\Exment\Form\Tools;
use Exceedone\Exment\Enums\FormBlockType;
use Exceedone\Exment\Enums\FormColumnType;
use Exceedone\Exment\Enums\ConditionType;
use Exceedone\Exment\Enums\ViewKindType;
use Exceedone\Exment\Enums\ColumnType;
use Exceedone\Exment\Enums\Permission;
use Exceedone\Exment\Enums\CurrencySymbol;
use Exceedone\Exment\Enums\SystemTableName;
use Exceedone\Exment\Enums\SystemColumn;
use Exceedone\Exment\ColumnItems\CustomItem;
use Exceedone\Exment\Validator;
use Illuminate\Validation\Rule;

class CustomColumnController extends AdminControllerTableBase
{
    use HasResourceTableActions;

    public function __construct(?CustomTable $custom_table, Request $request)
    {
        parent::__construct($custom_table, $request);
        
        $this->setPageInfo(exmtrans("custom_column.header"), exmtrans("custom_column.header"), exmtrans("custom_column.description"), 'fa-list');
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Request $request, Content $content)
    {
        //Validation table value
        if (!$this->validateTable($this->custom_table, Permission::CUSTOM_TABLE)) {
            return;
        }
        return parent::index($request, $content);
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit(Request $request, Content $content, $tableKey, $id)
    {
        //Validation table value
        if (!$this->validateTable($this->custom_table, Permission::CUSTOM_TABLE)) {
            return;
        }
        if (!$this->validateTableAndId(CustomColumn::class, $id, 'column')) {
            return;
        }
        return parent::edit($request, $content, $tableKey, $id);
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create(Request $request, Content $content)
    {
        //Validation table value
        if (!$this->validateTable($this->custom_table, Permission::CUSTOM_TABLE)) {
            return;
        }
        return parent::create($request, $content);
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CustomColumn);
        $grid->column('custom_table.table_view_name', exmtrans("custom_table.table"))->sortable();
        $grid->column('column_name', exmtrans("custom_column.column_name"))->sortable();
        $grid->column('column_view_name', exmtrans("custom_column.column_view_name"))->sortable();
        $grid->column('column_type', exmtrans("custom_column.column_type"))->sortable()->displayEscape(function ($val) {
            $class = CustomItem::findItemClass($val);
            return $class ? $class::getColumnTypeViewName() : null;
        });
        $grid->column('required', exmtrans("common.required"))->sortable()->display(function ($val) {
            return getTrueMark($val);
        });
        $grid->column('index_enabled', exmtrans("custom_column.options.index_enabled"))->sortable()->display(function ($val) {
            return getTrueMark($val);
        });
        $grid->column('unique', exmtrans("custom_column.options.unique"))->sortable()->display(function ($val) {
            return getTrueMark($val);
        });
        $grid->column('order', exmtrans("custom_column.order"))->editable('number')->sortable();

        if (isset($this->custom_table)) {
            $grid->model()->where('custom_table_id', $this->custom_table->id);
        }

        //  $grid->disableCreateButton();
        $grid->disableExport();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if ($actions->row->disabled_delete) {
                $actions->disableDelete();
            }
            $actions->disableView();
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new Tools\CustomTableMenuButton('column', $this->custom_table));
        });

        // filter
        $grid->filter(function ($filter) {
            // Remove the default id filter
            $filter->disableIdFilter();
            // Add a column filter
            $filter->equal('column_name', exmtrans("custom_column.column_name"));
            $filter->equal('column_view_name', exmtrans("custom_column.column_view_name"));
            $filter->equal('column_type', exmtrans("custom_column.column_type"))->select(function ($val) {
                $class = CustomItem::findItemClass($val);
                return $class ? $class::getColumnType() : null;
            });

            $keys = ['required' => 'common', 'index_enabled' => 'custom_column.options', 'unique' => 'custom_column.options'];
            foreach ($keys as $key => $label) {
                $filter->where(function ($query) use ($key, $label) {
                    $query->whereIn("options->$key", [1, "1"]);
                }, exmtrans("$label.$key"))->radio([
                    '' => 'All',
                    '1' => 'YES',
                ]);
            }
        });
        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($id = null)
    {
        $request = request();
        $form = new Form(new CustomColumn);

        if (!isset($id)) {
            $id = $form->model()->id;
        }

        // get custom_item for option
        $custom_column = CustomColumn::getEloquent($id);
        if(isset($custom_column)){
            $column_item = $custom_column->column_item;
        }elseif(!is_nullorempty($request->get('column_type'))){
            $column_item = $this->getCustomItem(request(), $id, $request->get('column_type'));
        }else{
            $column_item = null;
        }

        // set script
        $ver = getExmentCurrentVersion();
        if (!isset($ver)) {
            $ver = date('YmdHis');
        }
        $form->html('<script src="'.asset('vendor/exment/js/customcolumn.js?ver='.$ver).'"></script>');

        $form->hidden('custom_table_id')->default($this->custom_table->id);
        $form->display('custom_table.table_view_name', exmtrans("custom_table.table"))->default($this->custom_table->table_view_name);
        
        if (!isset($id)) {
            $classname = CustomColumn::class;
            $form->text('column_name', exmtrans("custom_column.column_name"))
                ->required()
                ->rules([
                    "max:30",
                    "regex:/".Define::RULES_REGEX_SYSTEM_NAME."/",
                    "uniqueInTable:{$classname},{$this->custom_table->id}",
                    Rule::notIn(SystemColumn::arrays()),
                ])
                ->help(sprintf(exmtrans('common.help.max_length'), 30) . exmtrans('common.help_code'));
        } else {
            $form->display('column_name', exmtrans("custom_column.column_name"));
        }

        $form->text('column_view_name', exmtrans("custom_column.column_view_name"))
            ->required()
            ->rules("max:40")
            ->help(exmtrans('common.help.view_name'));

        $form->select('column_type', exmtrans("custom_column.column_type"))
        ->options(function($option){
            return collect(CustomItem::$availableFields)->filter(function($availableField, $column_type){
                return $availableField::isUseCustomColumn(); 
            })->mapWithKeys(function($availableField, $column_type){
                return [$column_type => $availableField::getColumnTypeViewName()]; 
            })->toArray();
        })
        ->help(exmtrans("custom_column.help.column_type"))
        ->attribute([
            'data-changehtml' => admin_urls('column', $this->custom_table->table_name, $id, 'columnTypeHtml'),
            'data-changehtml_target' => '.form_dynamic_options',
            'data-changehtml_response' => '.form_dynamic_options_response',
        ])
        ->required();

        $form->embeds('options', exmtrans("custom_column.options.header"), function ($form) use ($column_item, $id) {
            $form->switchbool('required', exmtrans("common.required"))
            ->help(exmtrans("custom_column.help.required"));

            $form->switchbool('index_enabled', exmtrans("custom_column.options.index_enabled"))
                ->rules([
                    new Validator\CustomColumnIndexCountRule($this->custom_table, $id),
                    new Validator\CustomColumnUsingIndexRule($id),
                ])
                ->help(sprintf(exmtrans("custom_column.help.index_enabled"), getManualUrl('column?id='.exmtrans('custom_column.options.index_enabled'))));
            $form->switchbool('unique', exmtrans("custom_column.options.unique"))
                ->help(exmtrans("custom_column.help.unique"));

            $form->switchbool('init_only', exmtrans("custom_column.options.init_only"))
                ->help(exmtrans("custom_column.help.init_only"));
            
            $form->text('default', exmtrans("custom_column.options.default"))
                ->help(exmtrans("custom_column.help.default"));
            
            $form->text('placeholder', exmtrans("custom_column.options.placeholder"))
                ->help(exmtrans("custom_column.help.placeholder"));

            $form->text('help', exmtrans("custom_column.options.help"))->help(exmtrans("custom_column.help.help"));
            
            $form->text('min_width', exmtrans("custom_column.options.min_width"))
                ->help(exmtrans("custom_column.help.min_width"))
                ->rules(['nullable', 'integer'])
                ;
            $form->text('max_width', exmtrans("custom_column.options.max_width"))
                ->help(exmtrans("custom_column.help.max_width"))
                ->rules(['nullable', 'integer'])
                ;
            
            // setting for each settings of column_type. --------------------------------------------------

            // Form options area -- start
            $form->html('<div class="form_dynamic_options">')->plain(); 

            if(isset($column_item)){
                $column_item->setCustomColumnOptionForm($form);
            }
            
            // // image, file, select
            // // enable multiple
            // $form->switchbool('multiple_enabled', exmtrans("custom_column.options.multiple_enabled"))
            //     ->attribute(['data-filter' => json_encode(['parent' => 1, 'key' => 'column_type', 'value' => CustomItem::getColumnTypesMultipleEnabled()])]);

                
            // Form options area -- End
            $form->html('</div>')->plain(); 

        })->disableHeader();

        $form->number('order', exmtrans("custom_column.order"))->rules("integer")
            ->help(sprintf(exmtrans("common.help.order"), exmtrans('common.custom_column')));

        // if create column, add custom form and view
        if (!isset($id)) {
            $form->switchbool('add_custom_form_flg', exmtrans("custom_column.add_custom_form_flg"))->help(exmtrans("custom_column.help.add_custom_form_flg"))
                ->default("1")
                ->attribute(['data-filtertrigger' =>true])
            ;
            $form->switchbool('add_custom_view_flg', exmtrans("custom_column.add_custom_view_flg"))->help(exmtrans("custom_column.help.add_custom_view_flg"))
                ->default("0")
                ->attribute(['data-filtertrigger' =>true])
            ;
            $form->ignore('add_custom_form_flg');
            $form->ignore('add_custom_view_flg');
        }

        $form->saved(function (Form $form) {
            // create or drop index --------------------------------------------------
            $model = $form->model();
            $model->alterColumn();

            $this->addColumnAfterSaved($model);
        });

        $form->disableCreatingCheck(false);
        $form->disableEditingCheck(false);
        $custom_table = $this->custom_table;
        $form->tools(function (Form\Tools $tools) use ($id, $form, $custom_table) {
            if (isset($id) && boolval(CustomColumn::getEloquent($id)->disabled_delete)) {
                $tools->disableDelete();
            }
            $tools->add((new Tools\CustomTableMenuButton('column', $custom_table, false))->render());
        });
        return $form;
    }
    
    public function calcModal(Request $request, $tableKey, $id = null)
    {
        // get custom item
        $column_item = $this->getCustomItem($request, $id, $request->get('column_type'));

        return $column_item->calcModal($request);

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
     * add column form and view after saved
     */
    protected function addColumnAfterSaved($model)
    {
        // set custom form columns --------------------------------------------------
        $add_custom_form_flg = app('request')->input('add_custom_form_flg');
        if (boolval($add_custom_form_flg)) {
            $form = CustomForm::getDefault($this->custom_table);
            $form_block = $form->custom_form_blocks()->where('form_block_type', FormBlockType::DEFAULT)->first();
            
            // whether saved check (as index)
            $exists = $form_block->custom_form_columns()
                ->where('form_column_target_id', $model->id)
                ->where('form_column_type', FormColumnType::COLUMN)
                ->count() > 0;
                
            if (!$exists) {
                // get order
                $order = $form_block->custom_form_columns()
                    ->where('column_no', 1)
                    ->where('form_column_type', FormColumnType::COLUMN)
                    ->max('order') ?? 0;
                $order++;

                $custom_form_column = new CustomFormColumn;
                $custom_form_column->custom_form_block_id = $form_block->id;
                $custom_form_column->form_column_type = FormColumnType::COLUMN;
                $custom_form_column->form_column_target_id = $model->id;
                $custom_form_column->column_no = 1;
                $custom_form_column->order = $order;
                $custom_form_column->save();
            }
        }

        // set custom form columns --------------------------------------------------
        $add_custom_view_flg = app('request')->input('add_custom_view_flg');
        if (boolval($add_custom_view_flg)) {
            $view = CustomView::getDefault($this->custom_table, false);
            
            // get order
            if ($view->custom_view_columns()->count() == 0) {
                $order = 1;
            } else {
                // get order. ignore system column and footer
                $order = $view->custom_view_columns
                    ->filter(function ($custom_view_column) {
                        if ($custom_view_column->view_column_type != ConditionType::SYSTEM) {
                            return true;
                        }
                        $systemColumn = SystemColumn::getOption(['id' => $custom_view_column->view_column_target_id]);
                        if (!isset($systemColumn)) {
                            return false;
                        }

                        // check not footer
                        return !boolval(array_get($systemColumn, 'footer'));
                    })->max('order') ?? 1;
                $order++;
            }

            $custom_view_column = new CustomViewColumn;
            $custom_view_column->custom_view_id = $view->id;
            $custom_view_column->view_column_type = ConditionType::COLUMN;
            $custom_view_column->view_column_target = $model->id;
            $custom_view_column->order = $order;

            $custom_view_column->save();
        }
    }

    public function columnTypeHtml(Request $request){
        $val = $request->get('val');
        $id = $request->route('id');

        // get custom item
        $column_item = $this->getCustomItem($request, $id, $val);

        $form = new Form(new CustomColumn);
        $form->embeds('options', exmtrans("custom_column.options.header"), function ($form) use($column_item) {
            // Form options area -- start
            $form->html('<div class="form_dynamic_options_response">')->plain();
            if (isset($column_item)) {
                $column_item->setCustomColumnOptionForm($form);
            }
            $form->html('</div>')->plain();
        });

        $body = $form->render();
        $script = \Admin::purescript()->render();
        return [
            'body'  => $body,
            'script' => $script,
        ];
    }

    protected function getCustomItem(Request $request, $id, $column_type){
        return CustomItem::getItem(new CustomColumn([
            'custom_table_id' => $this->custom_table->id,
            'id' => $id,
            'column_type' => $column_type,
        ]));
    }
}
