<?php

namespace Exceedone\Exment\Controllers;

use Encore\Admin\Grid\Linker;
use Encore\Admin\Widgets\Table as WidgetTable;
use Illuminate\Http\Request;
use Exceedone\Exment\Enums\Permission;
use Exceedone\Exment\Enums\RoleType;
use Exceedone\Exment\Enums\SystemRoleType;
use Exceedone\Exment\Model\RoleGroup;
use Exceedone\Exment\Model\RoleGroupPermission;
use Exceedone\Exment\Model\CustomTable;
use Exceedone\Exment\Model\System;

class TablePermissionController extends AdminControllerBase
{
    public function __construct()
    {
        $this->setPageInfo(trans('admin.operation_log'), trans('admin.operation_log'), exmtrans('operation_log.description'), 'fa-file-text');
    }

    /**
     * @return Grid
     */
    public function getTable(Request $request, $tableKey)
    {
        $custom_table = CustomTable::getEloquent($tableKey);

        return [
            'title' => exmtrans('custom_table.permission.title'),
            'body' => $this->body($custom_table),
            'footer' => null,
            'suuid' => $custom_table->suuid,
            'showSubmit' => false
        ];
    }
    
    /**
     * get body
     * *this function calls from non-value method. So please escape if not necessary unescape.
     */
    public function body($custom_table)
    {
        return $this->getAllUserList($custom_table) . $this->getRoleGroupList($custom_table);
    }

    protected function getAllUserList($custom_table)
    {
        $headers = [exmtrans('custom_table.permission.all_user')];
        $bodies = [];

        if (boolval($custom_table->getOption('all_user_editable_flg'))) {
            $bodies[] = [exmtrans('custom_table.all_user_editable_flg')];
        }
        if (boolval($custom_table->getOption('all_user_viewable_flg'))) {
            $bodies[] = [exmtrans('custom_table.all_user_viewable_flg')];
        }
        if (boolval($custom_table->getOption('all_user_accessable_flg'))) {
            $bodies[] = [exmtrans('custom_table.all_user_accessable_flg')];
        }
        if (count($bodies) == 0) {
            $bodies[] = [exmtrans('custom_table.permission.row_count0')];
        }

        $widgetTable = new WidgetTable($headers, $bodies);
        $widgetTable->class('table table-hover');
        return $widgetTable->render();
    }

    protected function getRoleGroupList($custom_table)
    {
        $table_id = $custom_table->id;

        $datalist = RoleGroupPermission::where(function($query){
            $query->where('role_group_permission_type', RoleType::SYSTEM)
                  ->where('role_group_target_id', SystemRoleType::SYSTEM);
        })->orWhere(function($query) use($table_id){
            $query->where('role_group_permission_type', RoleType::TABLE)
                  ->where('role_group_target_id', $table_id);
        })->get();

        // create headers
        $headers = [
            exmtrans('custom_table.permission.role_group_columns.name'), 
            exmtrans('custom_table.permission.role_group_columns.permission')
        ];

        $bodies = [];
        $role_groups = collect();
        
        if (isset($datalist)) {
            foreach ($datalist as $data) {
                $list = $this->getPermissionList($data);
                if (empty($list)) {
                    continue;
                }
                $role_group = $role_groups->get($data->role_group_id);
                if (isset($role_group)) {
                    $role_groups->put($data->role_group_id, array_merge($role_group, $list));
                } else {
                    $role_groups->put($data->role_group_id, $list);
                }
            }

            $bodies = $role_groups->map(function($item, $key) {
                $role_group = RoleGroup::find($key);
                $permission_text = implode(exmtrans('common.separate_word'), $item);
                // $link = (new Linker)
                //     ->url(admin_urls('role_group', $key, 'edit'))
                //     //->linkattributes(['style' => "margin:0 3px;"])
                //     ->icon('fa-eye')
                //     ->tooltip(trans('admin.show'))
                //     ->render();
                return [
                    $role_group->role_group_view_name,
                    $permission_text
                ];
            })->values()->toArray();
        }

        $widgetTable = new WidgetTable($headers, $bodies);
        $widgetTable->class('table table-hover');
        return $widgetTable->render();
    }

    protected function getPermissionList($data)
    {
        $permission_type = $data->role_group_permission_type;
        $permissions = $data->permissions;

        return collect($permissions)->filter(function($permission) {
            if (isset($permission) && in_array($permission, Permission::TABLE_ROLE_PERMISSION)) {
                return true;
            }
        })->map(function($permission) use($permission_type) {
            if ($permission_type == RoleType::SYSTEM) {
                return exmtrans("role_group.role_type_option_system.$permission.label");
            } else {
                return exmtrans("role_group.role_type_option_table.$permission.label");
            }
        })->toArray();
    }

    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
    public function getData(Request $request, $tableKey, $id)
    {
        return null;
    }
}
