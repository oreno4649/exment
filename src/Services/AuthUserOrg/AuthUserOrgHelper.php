<?php
namespace Exceedone\Exment\Services\AuthUserOrg;

use Exceedone\Exment\Model\Define;
use Exceedone\Exment\Model\CustomTable;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Enums\SystemTableName;
use Exceedone\Exment\Enums\JoinedOrgFilterType;
use Exceedone\Exment\Form\Widgets\ModalForm;
use Exceedone\Exment\Services\AuthUserOrg\RolePermissionScope;

/**
 * Role, user , organization helper
 */
class AuthUserOrgHelper
{
    public static function getRealUserOrOrgs($related_type, $ids){
        return getModelName($related_type)::query()
            ->withoutGlobalScope(RolePermissionScope::class)
            ->whereIn('id', $ids)
            ->get()
            ->unique();
    }

    
    /**
     * get organiztions who has roles.
     * this function is called from custom value role
     */
    // getRoleUserOrgQuery
    public static function getRoleOrganizationQueryTable($target_table, $tablePermission = null, $builder = null)
    {
        if (!System::organization_available()) {
            return [];
        }

        if (is_null($target_table)) {
            return [];
        }

        $target_table = CustomTable::getEloquent($target_table);
        if (is_null($target_table)) {
            return [];
        }

        $key = sprintf(Define::SYSTEM_KEY_SESSION_TABLE_ACCRSSIBLE_ORGS, $target_table->id);
        return static::_getRoleUserOrOrgQueryTable(SystemTableName::ORGANIZATION, $key, $target_table, $tablePermission, $builder);
    }

    

    /**
     * get users who has roles for target table.
     * and get users joined parent or children organizations
     * this function is called from custom value display's role
     */
    // getRoleUserOrgQuery
    public static function getRoleUserAndOrgBelongsUserQueryTable($target_table, $tablePermission = null, $builder = null)
    {
        if (is_null($target_table)) {
            return [];
        }
        $target_table = CustomTable::getEloquent($target_table);
        $key = sprintf(Define::SYSTEM_KEY_SESSION_TABLE_ACCRSSIBLE_USERS_ORGS, $target_table->id);
        
        return static::_getRoleUserOrOrgQueryTable(SystemTableName::USER, $key, $target_table, $tablePermission, $builder, function ($target_ids, $target_table) {
            // joined organization belongs user ----------------------------------------------------
            if (!System::organization_available()) {
                return $target_ids;
            }

            // and get authoritiable organization
            $organizations = static::getRoleOrganizationQueryTable($target_table)
                ->get() ?? [];
            foreach ($organizations as $organization) {
                // get JoinedOrgFilterType. this method is for org_joined_type_role_group. get users for has role groups.
                $enum = JoinedOrgFilterType::getEnum(System::org_joined_type_role_group(), JoinedOrgFilterType::ONLY_JOIN);
                $relatedOrgs = CustomTable::getEloquent(SystemTableName::ORGANIZATION)->getValueModel()->with('users')->find($organization->getOrganizationIds($enum));

                foreach ($relatedOrgs as $related_organization) {
                    foreach ($related_organization->users as $user) {
                        $target_ids[] = $user->getUserId();
                    }
                }
            }

            return $target_ids;
        });
    }


    /**
     * Get user or organization query table.
     * this method called getRoleOrganizationQueryTable or getRoleUserAndOrgBelongsUserQueryTable.
     *
     * @param string $related_type
     * @param string $key
     * @param CustomTable|string $target_table
     * @param string|array $tablePermission
     * @param [type] $builder
     * @param \Closure|null $target_ids_callback
     * @return array
     */
    protected static function _getRoleUserOrOrgQueryTable($related_type, $key, $target_table, $tablePermission = null, $builder = null, ?\Closure $target_ids_callback = null)
    {
        if (is_null($target_table)) {
            return [];
        }
        $target_table = CustomTable::getEloquent($target_table);
        
        // get custom_value's users
        $target_ids = [];
        $all = false;
        
        if ($target_table->allUserAccessable()) {
            $all = true;
        } else {
            // if set $tablePermission, always call
            if (isset($tablePermission) || is_null($target_ids = System::requestSession($key))) {
                // get user ids
                $target_ids = $target_table->getRoleUserOrgId($related_type, $tablePermission);

                if ($target_ids_callback) {
                    $target_ids = $target_ids_callback($target_ids, $target_table);
                }

                if (!isset($tablePermission)) {
                    System::requestSession($key, $target_ids);
                }
            }
        }
    
        $target_ids = array_unique($target_ids);
        // return target values
        if (!isset($builder)) {
            $builder = getModelName($related_type)::query();
        }
        if (!$all) {
            $builder->whereIn('id', $target_ids);
        }

        return $builder;
    }


    /**
     * get organization ids
     * @return mixed
     * get organization ids by organization ids
     * @return array
     */
    public static function getTreeOrganizationIds($filterType = JoinedOrgFilterType::ALL, $targetOrgId) : array
    {
        if (!System::organization_available()) {
            return [];
        }
        // get organization and ids. only match $targetOrgId.
        $orgsArray = collect(static::getOrganizationTreeArray())->filter(function($org) use($targetOrgId){
            return isMatchString($targetOrgId, array_get($org, 'id'));
        });
        $results = [];
        foreach ($orgsArray as $org) {
            static::setJoinedOrganization($results, $org, $filterType, null);
        }

        return collect($results)->pluck('id')->toArray();
    }

    
    /**
     * get organization ids
     * @return mixed
     */
    public static function getOrganizationIds($filterType = JoinedOrgFilterType::ALL, $targetUserId = null)
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
            static::setJoinedOrganization($results, $org, $filterType, $targetUserId);
        }

        return collect($results)->pluck('id')->toArray();
    }

    /**
     * Get all organization tree array
     *
     * @return array
     */
    protected static function getOrganizationTreeArray() : array
    {
        return System::requestSession(Define::SYSTEM_KEY_SESSION_ORGANIZATION_TREE, function () {
            $modelname = getModelName(SystemTableName::ORGANIZATION);
            $indexName = $modelname::getParentOrgIndexName();

            // get query
            $orgs = $modelname::with([
                    'users' => function ($query) {
                        // pass aborting
                        return $query->withoutGlobalScope(RolePermissionScope::class);
                    }
                ])
                // pass aborting
                ->withoutGlobalScopes([RolePermissionScope::class])
                ->get(['id', $indexName])->toArray();

            $baseOrgs = $orgs;

            if (is_nullorempty($orgs)) {
                return [];
            }

            foreach ($orgs as &$org) {
                static::parents($org, $baseOrgs, $org, $indexName);
                static::children($org, $orgs, $org, $indexName);
            }

            return $orgs;
        });
    }

    protected static function parents(&$org, $orgs, $target, $indexName)
    {
        if (!isset($target[$indexName])) {
            return;
        }

        // if same id, return
        if ($org['id'] == $target[$indexName]) {
            return;
        }

        $newTarget = collect($orgs)->first(function ($o) use ($target, $indexName) {
            return $target[$indexName] == $o['id'];
        });
        if (!isset($newTarget)) {
            return;
        }

        // set parent
        $org['parents'][] = $newTarget;
        static::parents($org, $orgs, $newTarget, $indexName);
    }

    protected static function children(&$org, $orgs, $target, $indexName)
    {
        $children = collect($orgs)->filter(function ($o) use ($target, $indexName) {
            if (!isset($o[$indexName])) {
                return;
            }

            return $o[$indexName] == $target['id'];
        });

        foreach ($children as $child) {
            if ($org['id'] == $child['id']) {
                continue;
            }
            // set children
            $org['children'][] = $child;
            static::children($org, $orgs, $child, $indexName);
        }
    }

    protected static function setJoinedOrganization(&$results, $org, $filterType, $targetUserId)
    {
        // set $org id only $targetUserId
        if (!array_has($org, 'users') || !collect($org['users'])->contains(function ($user) use ($targetUserId) {
            return $user['id'] == $targetUserId;
        })) {
            return;
        }

        $results[] = $org;
        if (JoinedOrgFilterType::isGetDowner($filterType) && array_has($org, 'parents')) {
            foreach ($org['parents'] as $parent) {
                $results[] = $parent;
            }
        }

        if (JoinedOrgFilterType::isGetUpper($filterType) && array_has($org, 'children')) {
            foreach ($org['children'] as $child) {
                $results[] = $child;
            }
        }
    }

    /**
     * Get User, org, role group form
     *
     * @return ModalForm
     */
    public static function getUserOrgModalForm($custom_table = null, $value = [], $options = [])
    {
        $options = array_merge([
            'prependCallback' => null
        ], $options);
        
        $form = new ModalForm();

        if (isset($options['prependCallback'])) {
            $options['prependCallback']($form);
        }

        list($users, $ajax) = CustomTable::getEloquent(SystemTableName::USER)->getSelectOptionsAndAjaxUrl([
            'display_table' => $custom_table,
            'selected_value' => array_get($value, SystemTableName::USER),
        ]);

        // select target users
        $form->multipleSelect('modal_' . SystemTableName::USER, exmtrans('menu.system_definitions.user'))
            ->options($users)
            ->ajax($ajax)
            ->attribute(['data-filter' => json_encode(['key' => 'work_target_type', 'value' => 'fix'])])
            ->default(array_get($value, SystemTableName::USER));

        if (System::organization_available()) {
            list($organizations, $ajax) = CustomTable::getEloquent(SystemTableName::ORGANIZATION)->getSelectOptionsAndAjaxUrl([
                'display_table' => $custom_table,
                'selected_value' => array_get($value, SystemTableName::ORGANIZATION),
            ]);
                
            $form->multipleSelect('modal_' . SystemTableName::ORGANIZATION, exmtrans('menu.system_definitions.organization'))
                ->options($organizations)
                ->ajax($ajax)
                ->attribute(['data-filter' => json_encode(['key' => 'work_target_type', 'value' => 'fix'])])
                ->default(array_get($value, SystemTableName::ORGANIZATION));
        }

        return $form;
    }
}
