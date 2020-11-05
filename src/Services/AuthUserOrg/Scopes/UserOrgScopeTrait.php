<?php

namespace Exceedone\Exment\Services\AuthUserOrg\Scopes;

use Exceedone\Exment\Model\CustomRelation;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Enums\SystemTableName;
use Exceedone\Exment\Enums\Permission;
use Exceedone\Exment\Enums\JoinedOrgFilterType;
use Exceedone\Exment\Enums\JoinedMultiUserFilterType;

trait UserOrgScopeTrait
{
    /**
     * Get joined org filter type
     *
     * @return ?string If null, not filter.
     */
    public function getJoinedOrgFilterType() : ?string
    {
        $setting = System::filter_multi_user();
        if ($setting == JoinedMultiUserFilterType::NOT_FILTER) {
            return null;
        }

        // if login user have FILTER_MULTIUSER_ALL, no filter
        if (\Exment::user()->hasPermission(Permission::FILTER_MULTIUSER_ALL)) {
            return null;
        }

        return JoinedOrgFilterType::getEnum($setting);
    }


    /**
     * Get target users loginuser joind
     *
     * @param string $joinedOrgFilterType
     * @param array|null $organizationIds
     * @return \Illuminate\Support\Collection
     */
    protected function getTargetUserIds(string $joinedOrgFilterType, ?array $organizationIds = null){
        // if first get, return $organizationIds
        if(is_null($organizationIds)){
            $organizationIds = \Exment::getOrgJoinedIds($joinedOrgFilterType);
        }
        
        // First, get users org joined
        $db_table_name_pivot = CustomRelation::getRelationNameByTables(SystemTableName::ORGANIZATION, SystemTableName::USER);
        $target_users = \DB::table($db_table_name_pivot)
            ->whereIn('parent_id', $organizationIds)
            ->get(['child_id'])
            ->pluck('child_id');

        $target_users = $target_users->merge($user->getUserId())->unique();
        
        return $target_users;
    }
}
