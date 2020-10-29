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
     * get organization ids by organization.
     * @return array
     */
    public static function getOrgJoinedIds($filterType = JoinedOrgFilterType::ALL, $targetOrgId) : array
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
            static::setTreeOrganization($results, $org, $filterType);
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
    protected static function setTreeOrganization(&$results, $org, $filterType)
    {
        $results[] = $org;
        // Set parent and child orgs.
        // *This logic is reverse isGetDowner and parents.*
        if (JoinedOrgFilterType::isGetUpper($filterType) && array_has($org, 'parents')) {
            foreach ($org['parents'] as $parent) {
                $results[] = $parent;
            }
        }
        if (JoinedOrgFilterType::isGetDowner($filterType) && array_has($org, 'children')) {
            foreach ($org['children'] as $child) {
                $results[] = $child;
            }
        }
    }
}
