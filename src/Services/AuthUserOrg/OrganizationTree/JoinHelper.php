<?php
namespace Exceedone\Exment\Services\AuthUserOrg\OrganizationTree;

use Exceedone\Exment\Model\Define;
use Exceedone\Exment\Model\CustomTable;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Enums\SystemTableName;
use Exceedone\Exment\Enums\JoinedOrgFilterType;
use Exceedone\Exment\Form\Widgets\ModalForm;
use Exceedone\Exment\Services\AuthUserOrg\RolePermissionScope;

/**
 * user and organization for real join helper.
 */
class JoinHelper extends HelperBase
{
    /**
     * get organization ids by org
     * @return mixed
     * get organization ids by organization ids
     * @return array
     */
    public static function getOrgJoinedIds($filterType = JoinedOrgFilterType::ALL, $targetUserId = null) : array
    {
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
            static::setJoinedOrganization($results, $org, $filterType, $targetUserId, false);
        }

        return collect($results)->pluck('id')->toArray();
    }


}
