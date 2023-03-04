<?php

class UserPermission extends MyAppModel
{
    public const DB_TBL = 'tbl_user_permissions';
    public const DB_TBL_PREFIX = 'userperm_';
    
    public function __construct(){
        
    }
    
    public static function getSearchObject($userId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'up');

        if ($userId) {
            $srch->addCondition(static::tblFld('user_id'), '=', $userId);
        }
        return $srch;
    }
    
    public static function getSellerPermissions($userId = 0)
    {
        $srch = static::getSearchObject($userId);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAll($rs, 'userperm_section_id');
        if (!empty($row)) {
            return $row;
        }
        return false;
    }

    public function updatePermissions($siteLangId, $assignValues = array(), $updateAll = false)
    {
        if ($updateAll) {
            $permissionModules = UserPrivilege::getSellerPermissionModulesArr($siteLangId);
            foreach ($permissionModules as $key => $val) {
                $assignValues['userperm_section_id'] = $key;                
                if (!FatApp::getDb()->insertFromArray(
                    static::DB_TBL,
                    $assignValues,
                    false,
                    array(),
                    $assignValues
                )) {
                    $this->error = FatApp::getDb()->getError();
                    return false;
                }
            }
        } else {
            if (!FatApp::getDb()->insertFromArray(
                static::DB_TBL,
                $assignValues,
                false,
                array(),
                $assignValues
            )) {
                $this->error = FatApp::getDb()->getError();
                return false;
            }
        }
        return true;
    }
}
