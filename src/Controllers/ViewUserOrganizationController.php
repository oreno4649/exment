<?php

namespace Exceedone\Exment\Controllers;

use Encore\Admin\Grid;
use Illuminate\Http\Request;
use Exceedone\Exment\Enums\SystemTableName;
use Exceedone\Exment\Model\RoleGroup;
use Exceedone\Exment\Model\ViewUserOrganization;

class ViewUserOrganizationController extends AdminControllerBase
{
    use HasResourceActions;

    public function __construct()
    {
        $role_group_id = request()->get('role_group');
        $role_group_name = null;

        if (isset($role_group_id)) {
            $role_group = RoleGroup::find($role_group_id);
            $role_group_name = isset($role_group)? $role_group->role_group_view_name: null;
        }

        $title = exmtrans('user_organization.header', $role_group_name);
        $this->setPageInfo($title, $title, exmtrans('user_organization.description'), 'fa-users');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $role_group_id = request()->get('role_group');

        $grid = new Grid(new ViewUserOrganization);
        if (isset($role_group_id)) {
            $grid->model()
                ->whereHas('role_group_user_organizations', function($query) use($role_group_id){
                    $query->where('role_group_id', $role_group_id);
                });
        }
        $grid->model()->orderBy('type_no')->orderBy('id');

        $grid->column('type', exmtrans('user_organization.type'))->sortable()->displayEscape(function ($type) {
            return exmtrans("user_organization.type_options.$type");
        });
        $grid->column('id', exmtrans('common.id'))->sortable();
        $grid->column('code', exmtrans('user_organization.code'))->sortable();
        $grid->column('name', exmtrans('user_organization.name'))->sortable();
        $grid->column('email', exmtrans('user_organization.email'))->sortable();
       
        $grid->disableCreation();
        $grid->disableExport();
        $grid->disableActions();
        
        $grid->tools(function (Grid\Tools $tools) use ($grid) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });
        
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('code', exmtrans("user_organization.code"));
            $filter->like('name', exmtrans("user_organization.name"));
            $filter->like('email', exmtrans("user_organization.email"));
            $filter->equal('type', exmtrans("user_organization.type"))
                ->radio(exmtrans("user_organization.type_options"));
        });

        return $grid;
    }

}
