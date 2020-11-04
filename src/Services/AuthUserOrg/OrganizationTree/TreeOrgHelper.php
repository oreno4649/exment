<?php
namespace Exceedone\Exment\Services\AuthUserOrg\OrganizationTree;

use Exceedone\Exment\Model\System;
use Exceedone\Exment\Enums\JoinedOrgFilterType;

/**
 * organization join helper.
 */
class TreeOrgHelper extends HelperBase
{
    /**
     * get organization ids for authoritable.
     * *This organization is NOT org joined org.*
     * It's reverse upper and downer.
     * @return array
     */
    public static function getOrgAuthoritableIds($filterType = JoinedOrgFilterType::ALL, $targetOrgId) : array
    {
        return static::_getOrgJoinedIds($filterType, $targetOrgId, true);
    }

    /**
     * get organization ids by organization.
     * @return array
     */
    public static function getOrgJoinedIds($filterType = JoinedOrgFilterType::ALL, $targetOrgId) : array
    {
        return static::_getOrgJoinedIds($filterType, $targetOrgId, false);
    }

    /**
     * get organization ids by organization.
     * @return array
     */
    protected static function _getOrgJoinedIds($filterType = JoinedOrgFilterType::ALL, $targetOrgId, bool $reverse) : array
    {
        if (!System::organization_available()) {
            return [];
        }
        // get organization and ids. only match $targetOrgId.
        $orgsArray = collect(static::getOrganizationTreeArray())->filter(function ($org) use ($targetOrgId) {
            return isMatchString($targetOrgId, array_get($org, 'id'));
        });
        $results = [];
        foreach ($orgsArray as $org) {
            static::setTreeOrganization($results, $org, $filterType, $reverse);
        }

        return collect($results)->pluck('id')->toArray();
    }

    /**
     * Set joined organization.
     * Set tree joined organization.
     *
     * @param array $results organization array.
     * @param [type] $org
     * @param string $filterType filter type. is upper or downer
     * @return void
     */
    protected static function setTreeOrganization(&$results, $org, $filterType, bool $reverse)
    {
        $results[] = $org;
        // Set parent and child orgs.

        $isParent = $reverse ? JoinedOrgFilterType::isGetDowner($filterType) : JoinedOrgFilterType::isGetUpper($filterType);
        $isChild = $reverse ? JoinedOrgFilterType::isGetUpper($filterType) : JoinedOrgFilterType::isGetDowner($filterType);

        if ($isParent && array_has($org, 'parents')) {
            foreach ($org['parents'] as $parent) {
                $results[] = $parent;
            }
        }
        if ($isChild && array_has($org, 'children')) {
            foreach ($org['children'] as $child) {
                $results[] = $child;
            }
        }
    }
}
