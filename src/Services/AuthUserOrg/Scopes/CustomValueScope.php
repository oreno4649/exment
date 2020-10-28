<?php

namespace Exceedone\Exment\Services\AuthUserOrg\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Exceedone\Exment\Model\LoginUser;
use Exceedone\Exment\Model\CustomValue;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Enums\Permission;
use Exceedone\Exment\Enums\JoinedOrgFilterType;
use Exceedone\Exment\Services\AuthUserOrg\RolePermissionScope;

class CustomValueScope extends RolePermissionScope
{
    /**
     * is Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  LoginUser  $user
     * @param  CustomValue  $custom_value
     * @return void
     */
    public function callApply(Builder $builder, LoginUser $user, CustomValue $custom_value)
    {
        $table_name = $custom_value->custom_table->table_name;
        $db_table_name = getDBTableName($table_name);

        if ($custom_value->custom_table->hasPermission(Permission::AVAILABLE_ALL_CUSTOM_VALUE)) {
            return;
        }
        
        if ($custom_value->custom_table->hasPermission(Permission::AVAILABLE_ACCESS_CUSTOM_VALUE)) {
            $builder->whereHas('custom_value_authoritables', function ($builder) use ($user) {
                // get only has role
                $enum = JoinedOrgFilterType::getEnum(System::org_joined_type_custom_value(), JoinedOrgFilterType::ONLY_JOIN);
                $builder->whereInMultiple(
                    ['authoritable_user_org_type', 'authoritable_target_id'],
                    $user->getUserAndOrganizationIds($enum),
                    true
                );
            });
        }
        // if not role, set always false result.
        else {
            $builder->where('id', '<', 0);
        }
    }
}
