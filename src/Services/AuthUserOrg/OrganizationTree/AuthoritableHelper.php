<?php
namespace Exceedone\Exment\Services\AuthUserOrg\OrganizationTree;

use Exceedone\Exment\Model\System;
use Exceedone\Exment\Enums\JoinedOrgFilterType;

/**
 * user and organization for authoritable helper.
 * *This organization is NOT real user joined org.*
 *  It's reverse upper and downer.
 *
 */
class AuthoritableHelper extends HelperBase
{
    /**
     * get organization ids for authoritable.
     * *This organization is NOT user joined org.*
     * It's reverse upper and downer.
     * @return array
     */
    public static function getOrgAuthoritableIds($filterType = JoinedOrgFilterType::ALL, $targetUserId = null)
    {
        // if system doesn't use organization, return empty array.
        if (!System::organization_available()) {
            return [];
        }
        
        // get organization and ids
        $orgsArray = static::getOrganizationTreeArray();
                
        if (!isset($targetUserId)) {
            $targetUserId = \Exment::getUserId();
        }

        $results = [];
        foreach ($orgsArray as $org) {
            static::setJoinedOrganization($results, $org, $filterType, $targetUserId, true);
        }

        return collect($results)->pluck('id')->toArray();
    }
}
