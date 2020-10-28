<?php
namespace Exceedone\Exment\Services\AuthUserOrg;

use Exceedone\Exment\Model\Define;
use Exceedone\Exment\Model\CustomTable;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Enums\SystemTableName;
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
            ->whereOrIn('id', $ids)
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
            $organizations->load('users');
            foreach ($organizations as $organization) {
                foreach ($organization->users as $user) {
                    $target_ids[] = $user->getUserId();
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
                $target_ids = $target_table->getRoleUserOrgIds($related_type, $tablePermission);

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
