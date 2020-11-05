<?php

namespace Exceedone\Exment\Services\AuthUserOrg;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Exceedone\Exment\Enums\SystemTableName;

class ViewUserOrgScope implements Scope
{
    use Scopes\UserOrgScopeTrait;
    

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

        // getJoinedOrgFilterType
        $joinedOrgFilterType = $this->getJoinedOrgFilterType();
        if (is_null($joinedOrgFilterType)) {
            return;
        }

        // get user and org ids
        $organizationIds = \Exment::getOrgJoinedIds($joinedOrgFilterType);
        $userIds = $this->getTargetUsers($joinedOrgFilterType, $organizationIds);

        // set whereIn query
        $whereIns = collect();

        collect($organizationIds)->each(function($organizationId) use($whereIns){
            $whereIns->push([SystemTableName::ORGANIZATION, $organizationId]);
        });
        collect($userIds)->each(function($userId) use($whereIns){
            $whereIns->push([SystemTableName::USER, $userId]);
        });

        
        $builder->whereInMultiple(
            ['type', 'id'], 
            $whereIns->toArray(),
            true
        );

    }
}
