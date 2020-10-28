<?php

namespace Exceedone\Exment\Services\AuthUserOrg\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Exceedone\Exment\Model\CustomValue;
use Exceedone\Exment\Enums\Permission;
use Exceedone\Exment\Enums\SystemTableName;
use Exceedone\Exment\Enums\JoinedOrgFilterType;
use Exceedone\Exment\Services\AuthUserOrg\RolePermissionScope;
use Exceedone\Exment\Model\LoginUser;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Enums\JoinedMultiUserFilterType;

class OrganizationScope extends RolePermissionScope
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
        $db_table_name = getDBTableName(SystemTableName::ORGANIZATION);
        $this->filter($builder, $user, $db_table_name);
    }

    
    /**
     * Filtering user. Only join. set by filter_multi_user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param LoginUser $user
     * @return void
     */
    protected function filter(Builder $builder, LoginUser $user, $db_table_name)
    {
        $setting = System::filter_multi_user();
        if ($setting == JoinedMultiUserFilterType::NOT_FILTER) {
            return;
        }

        // if login user have FILTER_MULTIUSER_ALL, no filter
        if (\Exment::user()->hasPermission(Permission::FILTER_MULTIUSER_ALL)) {
            return;
        }

        $joinedOrgFilterType = JoinedOrgFilterType::getEnum($setting);

        // get only login user's organization
        $builder->whereIn("$db_table_name.id", \Exment::getOrgJoinedIds($joinedOrgFilterType));
    }

}
