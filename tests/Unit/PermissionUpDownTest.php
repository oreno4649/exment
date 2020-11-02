<?php

namespace Exceedone\Exment\Tests\Unit;

use Exceedone\Exment\Model\LoginUser;
use Exceedone\Exment\Model\CustomTable;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Enums\JoinedOrgFilterType;

class PermissionUpDownTest extends UnitTestBase
{
    protected function init(){
        System::clearCache();
        \Exceedone\Exment\Middleware\Morph::defineMorphMap();
    }

    // User - Organization -------------------------------------------
    public function testOrganizationCompany()
    {
        $this->executeAuthTestOrganizationUser(5, JoinedOrgFilterType::ONLY_JOIN, [1], true);
    }

    public function testOrganizationDev()
    {
        $this->executeAuthTestOrganizationUser(6, JoinedOrgFilterType::ONLY_JOIN, [2], true);
    }

    public function testOrganizationDev1()
    {
        $this->executeAuthTestOrganizationUser(7, JoinedOrgFilterType::ONLY_JOIN, [4], true);
    }

    public function testOrganizationCompanyUpper()
    {
        $this->executeAuthTestOrganizationUser(5, JoinedOrgFilterType::ONLY_UPPER, [1, 2, 3, 4, 5], true);
    }

    public function testOrganizationDevUpper()
    {
        $this->executeAuthTestOrganizationUser(6, JoinedOrgFilterType::ONLY_UPPER, [2, 4, 5], true);
    }

    public function testOrganizationDev1Upper()
    {
        $this->executeAuthTestOrganizationUser(7, JoinedOrgFilterType::ONLY_UPPER, [4], true);
    }

    public function testOrganizationCompanyDowner()
    {
        $this->executeAuthTestOrganizationUser(5, JoinedOrgFilterType::ONLY_DOWNER, [1], true);
    }

    public function testOrganizationDevDowner()
    {
        $this->executeAuthTestOrganizationUser(6, JoinedOrgFilterType::ONLY_DOWNER, [1, 2], true);
    }

    public function testOrganizationDev1Downer()
    {
        $this->executeAuthTestOrganizationUser(7, JoinedOrgFilterType::ONLY_DOWNER, [1, 2, 4], true);
    }

    public function testOrganizationCompanyAll()
    {
        $this->executeAuthTestOrganizationUser(5, JoinedOrgFilterType::ALL, [1, 2, 3, 4, 5], true);
    }

    public function testOrganizationDevAll()
    {
        $this->executeAuthTestOrganizationUser(6, JoinedOrgFilterType::ALL, [1, 2, 4, 5], true);
    }

    public function testOrganizationDev1All()
    {
        $this->executeAuthTestOrganizationUser(7, JoinedOrgFilterType::ALL, [1, 2, 4], true);
    }

    
    // Organization - Organization -------------------------------------------
    public function testOrganizationOrgCompany()
    {
        $this->executeAuthTestOrganizationOrg(1, JoinedOrgFilterType::ONLY_JOIN, [1], true);
    }

    public function testOrganizationOrgDev()
    {
        $this->executeAuthTestOrganizationOrg(2, JoinedOrgFilterType::ONLY_JOIN, [2], true);
    }

    public function testOrganizationOrgDev1()
    {
        $this->executeAuthTestOrganizationOrg(3, JoinedOrgFilterType::ONLY_JOIN, [3], true);
    }

    public function testOrganizationOrgCompanyUpper()
    {
        $this->executeAuthTestOrganizationOrg(1, JoinedOrgFilterType::ONLY_UPPER, [1, 2, 3, 4, 5], true);
    }

    public function testOrganizationOrgDevUpper()
    {
        $this->executeAuthTestOrganizationOrg(2, JoinedOrgFilterType::ONLY_UPPER, [2, 4, 5], true);
    }

    public function testOrganizationOrgDev1Upper()
    {
        $this->executeAuthTestOrganizationOrg(4, JoinedOrgFilterType::ONLY_UPPER, [4], true);
    }

    public function testOrganizationOrgCompanyDowner()
    {
        $this->executeAuthTestOrganizationOrg(1, JoinedOrgFilterType::ONLY_DOWNER, [1], true);
    }

    public function testOrganizationOrgDevDowner()
    {
        $this->executeAuthTestOrganizationOrg(2, JoinedOrgFilterType::ONLY_DOWNER, [1, 2], true);
    }

    public function testOrganizationOrgDev1Downer()
    {
        $this->executeAuthTestOrganizationOrg(4, JoinedOrgFilterType::ONLY_DOWNER, [1, 2, 4], true);
    }

    public function testOrganizationOrgCompanyAll()
    {
        $this->executeAuthTestOrganizationOrg(1, JoinedOrgFilterType::ALL, [1, 2, 3, 4, 5], true);
    }

    public function testOrganizationOrgDevAll()
    {
        $this->executeAuthTestOrganizationOrg(2, JoinedOrgFilterType::ALL, [1, 2, 4, 5], true);
    }

    public function testOrganizationOrgDev1All()
    {
        $this->executeAuthTestOrganizationOrg(4, JoinedOrgFilterType::ALL, [1, 2, 4], true);
    }


    // Role Group -------------------------------------------
    public function testRoleGroupAdmin()
    {
        $this->executeTestRoleGroup(1, JoinedOrgFilterType::ALL, true);
    }

    public function testRoleGroupDirect()
    {
        $this->executeTestRoleGroup(6, JoinedOrgFilterType::ALL, true);
    }

    public function testRoleGroupUpper()
    {
        $this->executeTestRoleGroup(5, JoinedOrgFilterType::ALL, true);
    }

    public function testRoleGroupDowner()
    {
        $this->executeTestRoleGroup(7, JoinedOrgFilterType::ALL, true);
    }

    public function testRoleGroupOtherOrg()
    {
        $this->executeTestRoleGroup(10, JoinedOrgFilterType::ALL, false);
    }

    public function testRoleGroupDirectOnlyUpper()
    {
        $this->executeTestRoleGroup(6, JoinedOrgFilterType::ONLY_UPPER, true);
    }

    public function testRoleGroupUpperOnlyUpper()
    {
        $this->executeTestRoleGroup(5, JoinedOrgFilterType::ONLY_UPPER, true);
    }

    public function testRoleGroupDownerOnlyUpper()
    {
        $this->executeTestRoleGroup(7, JoinedOrgFilterType::ONLY_UPPER, false);
    }

    public function testRoleGroupOtherOrgOnlyUpper()
    {
        $this->executeTestRoleGroup(10, JoinedOrgFilterType::ONLY_UPPER, false);
    }
    
    public function testRoleGroupDirectOnlyDowner()
    {
        $this->executeTestRoleGroup(6, JoinedOrgFilterType::ONLY_DOWNER, true);
    }

    public function testRoleGroupUpperOnlyDowner()
    {
        $this->executeTestRoleGroup(5, JoinedOrgFilterType::ONLY_DOWNER, false);
    }

    public function testRoleGroupDownerOnlyDowner()
    {
        $this->executeTestRoleGroup(7, JoinedOrgFilterType::ONLY_DOWNER, true);
    }

    public function testRoleGroupOtherOrgOnlyDowner()
    {
        $this->executeTestRoleGroup(10, JoinedOrgFilterType::ONLY_DOWNER, false);
    }
    
    public function testRoleGroupDirectOnlyJoin()
    {
        $this->executeTestRoleGroup(6, JoinedOrgFilterType::ONLY_JOIN, true);
    }

    public function testRoleGroupUpperOnlyJoin()
    {
        $this->executeTestRoleGroup(5, JoinedOrgFilterType::ONLY_JOIN, false);
    }

    public function testRoleGroupDownerOnlyJoin()
    {
        $this->executeTestRoleGroup(7, JoinedOrgFilterType::ONLY_JOIN, false);
    }

    public function testRoleGroupOtherOrgOnlyJoin()
    {
        $this->executeTestRoleGroup(10, JoinedOrgFilterType::ONLY_JOIN, false);
    }


    // Custom Value -------------------------------------------
    public function testCustomValueAdmin()
    {
        $this->executeTestCustomValue(1, JoinedOrgFilterType::ONLY_JOIN, true);
    }

    public function testCustomValueDirect()
    {
        $this->executeTestCustomValue(6, JoinedOrgFilterType::ONLY_JOIN, true);
    }

    public function testCustomValueUpper()
    {
        $this->executeTestCustomValue(5, JoinedOrgFilterType::ONLY_JOIN, false);
    }

    public function testCustomValueDowner()
    {
        $this->executeTestCustomValue(7, JoinedOrgFilterType::ONLY_JOIN, false);
    }

    public function testCustomValueOtherOrg()
    {
        $this->executeTestCustomValue(10, JoinedOrgFilterType::ONLY_JOIN, false);
    }


    public function testCustomValueDirectOnlyUpper()
    {
        $this->executeTestCustomValue(6, JoinedOrgFilterType::ONLY_UPPER, true);
    }

    public function testCustomValueUpperOnlyUpper()
    {
        $this->executeTestCustomValue(5, JoinedOrgFilterType::ONLY_UPPER, true);
    }

    public function testCustomValueDownerOnlyUpper()
    {
        $this->executeTestCustomValue(7, JoinedOrgFilterType::ONLY_UPPER, false);
    }

    public function testCustomValueOtherOrgOnlyUpper()
    {
        $this->executeTestCustomValue(10, JoinedOrgFilterType::ONLY_UPPER, false);
    }
    
    
    public function testCustomValueDirectOnlyDowner()
    {
        $this->executeTestCustomValue(6, JoinedOrgFilterType::ONLY_DOWNER, true);
    }

    public function testCustomValueUpperOnlyDowner()
    {
        $this->executeTestCustomValue(5, JoinedOrgFilterType::ONLY_DOWNER, false);
    }

    public function testCustomValueDownerOnlyDowner()
    {
        $this->executeTestCustomValue(7, JoinedOrgFilterType::ONLY_DOWNER, true);
    }

    public function testCustomValueOtherOrgOnlyDowner()
    {
        $this->executeTestCustomValue(10, JoinedOrgFilterType::ONLY_DOWNER, false);
    }
    
    
    public function testCustomValueDirectAll()
    {
        $this->executeTestCustomValue(6, JoinedOrgFilterType::ALL, true);
    }

    public function testCustomValueUpperAll()
    {
        $this->executeTestCustomValue(5, JoinedOrgFilterType::ALL, true);
    }

    public function testCustomValueDownerAll()
    {
        $this->executeTestCustomValue(7, JoinedOrgFilterType::ALL, true);
    }

    public function testCustomValueOtherOrgAll()
    {
        $this->executeTestCustomValue(10, JoinedOrgFilterType::ALL, false);
    }



    // User - Organization real join -------------------------------------------
    public function testOrganizationCompanyJoin()
    {
        $this->executeJoinTestOrganizationUser(5, JoinedOrgFilterType::ONLY_JOIN, [1], true);
    }

    public function testOrganizationDevJoin()
    {
        $this->executeJoinTestOrganizationUser(6, JoinedOrgFilterType::ONLY_JOIN, [2], true);
    }

    public function testOrganizationDev1Join()
    {
        $this->executeJoinTestOrganizationUser(7, JoinedOrgFilterType::ONLY_JOIN, [4], true);
    }

    public function testOrganizationCompanyUpperJoin()
    {
        $this->executeJoinTestOrganizationUser(5, JoinedOrgFilterType::ONLY_UPPER, [1], true);
    }

    public function testOrganizationDevUpperJoin()
    {
        $this->executeJoinTestOrganizationUser(6, JoinedOrgFilterType::ONLY_UPPER, [1, 2], true);
    }

    public function testOrganizationDev1UpperJoin()
    {
        $this->executeJoinTestOrganizationUser(7, JoinedOrgFilterType::ONLY_UPPER, [1, 2, 4], true);
    }

    public function testOrganizationCompanyDownerJoin()
    {
        $this->executeJoinTestOrganizationUser(5, JoinedOrgFilterType::ONLY_DOWNER, [1, 2, 3, 4, 5], true);
    }

    public function testOrganizationDevDownerJoin()
    {
        $this->executeJoinTestOrganizationUser(6, JoinedOrgFilterType::ONLY_DOWNER, [2, 4, 5], true);
    }

    public function testOrganizationDev1DownerJoin()
    {
        $this->executeJoinTestOrganizationUser(7, JoinedOrgFilterType::ONLY_DOWNER, [4], true);
    }

    public function testOrganizationCompanyAllJoin()
    {
        $this->executeJoinTestOrganizationUser(5, JoinedOrgFilterType::ALL, [1, 2, 3, 4, 5], true);
    }

    public function testOrganizationDevAllJoin()
    {
        $this->executeJoinTestOrganizationUser(6, JoinedOrgFilterType::ALL, [1, 2, 4, 5], true);
    }

    public function testOrganizationDev1AllJoin()
    {
        $this->executeJoinTestOrganizationUser(7, JoinedOrgFilterType::ALL, [1, 2, 4], true);
    }

    
    // Organization - Organization -------------------------------------------
    public function testOrganizationOrgCompanyJoin()
    {
        $this->executeJoinTestOrganizationOrg(1, JoinedOrgFilterType::ONLY_JOIN, [1], true);
    }

    public function testOrganizationOrgDevJoin()
    {
        $this->executeJoinTestOrganizationOrg(2, JoinedOrgFilterType::ONLY_JOIN, [2], true);
    }

    public function testOrganizationOrgDev1Join()
    {
        $this->executeJoinTestOrganizationOrg(3, JoinedOrgFilterType::ONLY_JOIN, [3], true);
    }

    public function testOrganizationOrgCompanyUpperJoin()
    {
        $this->executeJoinTestOrganizationOrg(1, JoinedOrgFilterType::ONLY_UPPER, [1], true);
    }

    public function testOrganizationOrgDevUpperJoin()
    {
        $this->executeJoinTestOrganizationOrg(2, JoinedOrgFilterType::ONLY_UPPER, [1, 2], true);
    }

    public function testOrganizationOrgDev1UpperJoin()
    {
        $this->executeJoinTestOrganizationOrg(4, JoinedOrgFilterType::ONLY_UPPER, [1, 2, 4], true);
    }

    public function testOrganizationOrgCompanyDownerJoin()
    {
        $this->executeJoinTestOrganizationOrg(1, JoinedOrgFilterType::ONLY_DOWNER, [1, 2, 3, 4, 5], true);
    }

    public function testOrganizationOrgDevDownerJoin()
    {
        $this->executeJoinTestOrganizationOrg(2, JoinedOrgFilterType::ONLY_DOWNER, [2, 4, 5], true);
    }

    public function testOrganizationOrgDev1DownerJoin()
    {
        $this->executeJoinTestOrganizationOrg(4, JoinedOrgFilterType::ONLY_DOWNER, [4], true);
    }

    public function testOrganizationOrgCompanyAllJoin()
    {
        $this->executeJoinTestOrganizationOrg(1, JoinedOrgFilterType::ALL, [1, 2, 3, 4, 5], true);
    }

    public function testOrganizationOrgDevAllJoin()
    {
        $this->executeJoinTestOrganizationOrg(2, JoinedOrgFilterType::ALL, [1, 2, 4, 5], true);
    }

    public function testOrganizationOrgDev1AllJoin()
    {
        $this->executeJoinTestOrganizationOrg(4, JoinedOrgFilterType::ALL, [1, 2, 4], true);
    }



    protected function executeAuthTestOrganizationUser($loginId, $joinedOrgFilterType, $antiOrganizations, bool $antiResult){
        $this->_executeAuthTestOrganizationUser(true, $loginId, $joinedOrgFilterType, $antiOrganizations, $antiResult);
    }
    
    protected function executeJoinTestOrganizationUser($loginId, $joinedOrgFilterType, $antiOrganizations, bool $antiResult){
        $this->_executeAuthTestOrganizationUser(false, $loginId, $joinedOrgFilterType, $antiOrganizations, $antiResult);
    }
    
    protected function _executeAuthTestOrganizationUser(bool $reverse, $loginId, $joinedOrgFilterType, $antiOrganizations, bool $antiResult){
        $this->init();

        $user = CustomTable::getEloquent('user')->getValueModel($loginId);
        $organizations = $reverse ? $user->getOrgAuthoritableIds($joinedOrgFilterType) : $user->getOrgJoinedIds($joinedOrgFilterType);
        
        sort($organizations);
        sort($antiOrganizations);
        
        $result = true;
        if(count($organizations) != count($antiOrganizations)){
            $result = false;
        }
        else{

            for($i = 0; $i < count($organizations); $i++){
                if($organizations[$i] != $antiOrganizations[$i]){
                    $result = false;
                    break;
                }
            }
        }

        $func = $antiResult ? 'assertTrue' : 'assertFalse';
        $this->{$func}(
            $result
        );
    }

    
    protected function executeAuthTestOrganizationOrg($loginId, $joinedOrgFilterType, $antiOrganizations, bool $antiResult){
        $this->_executeTestOrganizationOrg(true, $loginId, $joinedOrgFilterType, $antiOrganizations, $antiResult);
    }
    
    protected function executeJoinTestOrganizationOrg($loginId, $joinedOrgFilterType, $antiOrganizations, bool $antiResult){
        $this->_executeTestOrganizationOrg(false, $loginId, $joinedOrgFilterType, $antiOrganizations, $antiResult);
    }
    
    protected function _executeTestOrganizationOrg(bool $reverse, $id, $joinedOrgFilterType, $antiOrganizations, bool $antiResult){
        $this->init();

        $organization = CustomTable::getEloquent('organization')->getValueModel($id);
        $organizations = $reverse ? $organization->getOrgAuthoritableIds($joinedOrgFilterType) : $organization->getOrgJoinedIds($joinedOrgFilterType);

        sort($organizations);
        sort($antiOrganizations);
        
        $result = true;
        if(count($organizations) != count($antiOrganizations)){
            $result = false;
        }
        else{
            for($i = 0; $i < count($organizations); $i++){
                if($organizations[$i] != $antiOrganizations[$i]){
                    $result = false;
                    break;
                }
            }
        }

        $func = $antiResult ? 'assertTrue' : 'assertFalse';
        $this->{$func}(
            $result
        );
    }

    protected function executeTestRoleGroup($loginId, $joinedOrgFilterType, bool $result){
        $this->init();
        $this->be(LoginUser::find($loginId));
        System::org_joined_type_role_group($joinedOrgFilterType);
        
        $func = $result ? 'assertTrue' : 'assertFalse';
        $this->{$func}(CustomTable::getEloquent('custom_value_edit')->hasPermission());
    }

    protected function executeTestCustomValue($loginId, $joinedOrgFilterType, bool $result){
        $this->init();
        $this->be(LoginUser::find($loginId));
        System::org_joined_type_role_group($joinedOrgFilterType);
        System::org_joined_type_custom_value($joinedOrgFilterType);
        
        $func = $result ? 'assertTrue' : 'assertFalse';
        $custom_value = CustomTable::getEloquent('custom_value_edit')->getValueModel()->find(51); // 51 --- created by dev user
        $this->{$func}(isset($custom_value));
    }
}
