<?php

class Admin extends MyAppModel
{
    public static $admin_dashboard_layouts = array(0 => 'default', 1 => 'switch_layout');

    public const SUPER = 1;
    public const DB_TBL = 'tbl_admin';
    public const DB_TBL_PREFIX = 'admin_';
    public function __construct($userId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $userId);
        $this->objMainTableRecord->setSensitiveFields(array());
    }

    public static function getAddress($langId){
        global $adminAddress;
        if (!empty($adminAddress) && $adminAddress['line1'] != '') {
            return $adminAddress;
        }

        $countryId = FatApp::getConfig('CONF_COUNTRY', FatUtility::VAR_INT, 0);
        $countryCode = Countries::getAttributesById($countryId, 'country_code');
        $countryName = Countries::getAttributesByLangId($langId, $countryId, 'country_name');

        $stateId = FatApp::getConfig('CONF_STATE', FatUtility::VAR_INT, 0);
        $stateName = States::getAttributesByLangId($langId, $stateId, 'state_name');
        $stateCode = States::getAttributesById($stateId, 'state_code');
        
       return $adminAddress = [
            'line1' => FatApp::getConfig('CONF_ADDRESS_' . $langId, FatUtility::VAR_STRING, ''),
            'line2' => '',
            'city' =>FatApp::getConfig('CONF_CITY_' . $langId, FatUtility::VAR_STRING, ''),
            'state' => $stateName,
            'stateCode' => $stateCode,
            'postalCode' => FatApp::getConfig('CONF_ZIP_CODE', FatUtility::VAR_STRING, ''),
            'country' => $countryName,
            'countryCode' => $countryCode,
        ];
    }
}
