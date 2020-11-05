<?php

namespace Exceedone\Exment\Services\AuthUserOrg\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Exceedone\Exment\Model\CustomValue;
use Exceedone\Exment\Model\CustomRelation;
use Exceedone\Exment\Model\LoginUser;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Enums\Permission;
use Exceedone\Exment\Enums\SystemTableName;
use Exceedone\Exment\Enums\JoinedOrgFilterType;
use Exceedone\Exment\Enums\JoinedMultiUserFilterType;
use Exceedone\Exment\Services\AuthUserOrg\RolePermissionScope;

class UserScope extends RolePermissionScope
{
    use UserOrgScopeTrait;

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
        $db_table_name = getDBTableName(SystemTableName::USER);
        $this->filter($builder, $user, $db_table_name);
    }

    /**
     * Filtering user. Only join. set by filter_multi_user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param LoginUser $user
     * @param string $db_table_name
     * @return void
     */
    protected function filter(Builder $builder, LoginUser $user, $db_table_name)
    {
        // getJoinedOrgFilterType
        $joinedOrgFilterType = $this->getJoinedOrgFilterType();
        if (is_null($joinedOrgFilterType)) {
            return;
        }

        $target_users = $this->getTargetUserIds($joinedOrgFilterType);
        
        // get only login user's organization user
        $builder->whereIn("$db_table_name.id", $target_users->toArray());
    }
}
