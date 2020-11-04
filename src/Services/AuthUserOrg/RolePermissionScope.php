<?php

namespace Exceedone\Exment\Services\AuthUserOrg;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Exceedone\Exment\Model\CustomValue;

use Exceedone\Exment\Enums\SystemTableName;

class RolePermissionScope implements Scope
{
    protected function getScopeClassName(CustomValue $custom_value)
    {
        $table_name = $custom_value->custom_table->table_name;
        if ($table_name == SystemTableName::USER) {
            return Scopes\UserScope::class;
        }

        // organization
        elseif ($table_name == SystemTableName::ORGANIZATION) {
            return Scopes\OrganizationScope::class;
        }
        
        // Add document skip logic
        elseif ($table_name == SystemTableName::DOCUMENT) {
            return Scopes\NoScope::class;
        }

        return Scopes\CustomValueScope::class;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param Model $custom_value
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // get user info
        $user = \Exment::user();
        // if not have, check as login
        if (!isset($user)) {
            // no access role
            //throw new \Exception;
            
            // set no filter. Because when this function called, almost after login or pass oauth authonize.
            // if throw exception, Cannot execute batch.
            return;
        }

        // if system administrator user, return
        if ($user->isAdministrator()) {
            return;
            // if user can access list, return
        }

        $scope = $this->getScopeClassName($model);
        (new $scope())->callApply($builder, $user, $model);
    }
}
