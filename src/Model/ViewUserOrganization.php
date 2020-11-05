<?php

namespace Exceedone\Exment\Model;

use Exceedone\Exment\Services\AuthUserOrg\ViewUserOrgScope;

class ViewUserOrganization extends ModelBase
{

    public function role_group_user_organizations()
    {
        return $this->hasMany(RoleGroupUserOrganization::class, 'role_group_target_id')
            ->whereColumn('view_user_organizations.type', 'role_group_user_organizations.role_group_user_org_type');
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ViewUserOrgScope);
    }

}
