<?php

namespace Exceedone\Exment\Services\AuthUserOrg\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Exceedone\Exment\Model\LoginUser;
use Exceedone\Exment\Model\CustomValue;
use Exceedone\Exment\Services\AuthUserOrg\RolePermissionScope;

class NoScope extends RolePermissionScope
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
        return;
    }
}
