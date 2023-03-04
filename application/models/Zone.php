<?php
class Zone extends MyAppModel
{
    const DB_TBL = 'tbl_zones';
    const DB_TBL_PREFIX = 'zone_';
    const DB_TBL_LANG = 'tbl_zones_lang';
    const DB_TBL_LANG_PREFIX = 'zonelang_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($isActive = true, $langId = 0)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_TBL, 'zone');

        if ($isActive == true) {
            $srch->addCondition('zone.' . static::DB_TBL_PREFIX . 'active', '=', applicationConstants::ACTIVE);
        }

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'z_l.' . static::DB_TBL_LANG_PREFIX . 'zone_id = zone.' . static::tblFld('id') . ' and z_l.' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId,
                'z_l'
            );
        }
        return $srch;
    }

    public static function getAllZones($langId, $isActive = true)
    {
        $langId = FatUtility::int($langId);
        $srch = static::getSearchObject($isActive, $langId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('zone_name', 'ASC');
        $srch->addMultipleFields(
            array(
                'zone_id',
                'if(zone_name is null, zone_identifier, zone_name) as zone_name'
            )
        );
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetchAllAssoc($rs);
        if (!is_array($row)) {
            return false;
        }
        return $row;
    }

    public static function getZoneWithCountries($langId, $isActive = true)
    {
        $srch = static::getSearchObject($isActive, $langId);
        $srch->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'c.country_zone_id = zone.zone_id', 'c');

        $srch->joinTable(Countries::DB_TBL_LANG, 'INNER JOIN', 'c_l.' . Countries::DB_TBL_LANG_PREFIX . 'country_id = c.' . Countries::tblFld('id') . ' and c_l.' . Countries::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId, 'c_l');

        $srch->addMultipleFields(
            array(
                'zone_id',
                'if(zone_name is null, zone_identifier, zone_name) as zone_name', 'country_id', 'if(country_name is null, country_code, country_name) as  country_identifier', '(select count(*) from ' . States::DB_TBL . ' where state_country_id = c.country_id) as state_count'
            )
        );

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('zone_name', 'ASC');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $zoneContryArray = [];
        $zoneCountryStateList = array();
        if (!empty($records)) {
            foreach ($records as $record) {
                $key = $record['zone_id'];
                $zoneContryArray[$key]['zone_name'] = $record['zone_name'];
                $zoneContryArray[$key]['zone_id'] = $record['zone_id'];
                $country_key = $record['country_id'];
                unset($record['zone_name']);
                unset($record['zone_id']);
                if ($country_key != '') {
                    $zoneContryArray[$key]['countries'][] = $record;
                }
            }
        }
        return $zoneContryArray;
    }
    
    public static function getZoneWithCountriesStates($langId, $isActive = true)
    {
        $srch = static::getSearchObject($isActive, $langId);
        $srch->joinTable(Countries::DB_TBL, 'INNER  JOIN', 'c.country_zone_id = zone.zone_id and c.' . Countries::tblFld('active') . ' = '.applicationConstants::ACTIVE, 'c');

        $srch->joinTable(Countries::DB_TBL_LANG, 'LEFT OUTER JOIN', 'c_l.' . Countries::DB_TBL_LANG_PREFIX . 'country_id = c.' . Countries::tblFld('id') . ' and c_l.' . Countries::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId, 'c_l');
        $srch->joinTable(States::DB_TBL, 'INNER JOIN', 's.state_country_id = c.country_id  and s.' . States::tblFld('active') . ' = '.applicationConstants::ACTIVE, 's');
        $srch->joinTable(States::DB_TBL_LANG, 'LEFT OUTER JOIN', 's.state_id = s_l.statelang_state_id AND s_l.statelang_lang_id = ' . $langId, 's_l');        
        $srch->addMultipleFields(
            array(
                'zone_id',
                'if(zone_name is null, zone_identifier, zone_name) as zone_name', 'country_id', 'if(country_name is null, country_code, country_name) as country_name','if(state_name is null, state_identifier, state_name) as  state_name,state_id'
            )
        );

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('zone_name', 'ASC');
        $srch->addOrder('country_name', 'ASC');
        $srch->addOrder('state_name', 'ASC');
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);

        $zoneCountryStateArray = []; 
        if (!empty($records)) {
            foreach ($records as $record) {
                $zone_id = $record['zone_id'];                
                $zoneCountryStateArray[$zone_id]['zone_name'] = $record['zone_name'];
                $zoneCountryStateArray[$zone_id]['zone_id'] = $record['zone_id'];

                $countryId = $record['country_id'];
                $zoneCountryStateArray[$zone_id]['countries'][$countryId]['country_name'] = $record['country_name'];
                $zoneCountryStateArray[$zone_id]['countries'][$countryId]['country_id'] = $record['country_id'];

                $state_id = $record['state_id'];
                $zoneCountryStateArray[$zone_id]['countries'][$countryId]['states'][$state_id]['state_name'] = $record['state_name'];
                $zoneCountryStateArray[$zone_id]['countries'][$countryId]['states'][$state_id]['state_id'] = $record['state_id'];
            }
        }      
        return $zoneCountryStateArray;
    }
    
    public static function requiredFields()
    {
        return array(
            ImportexportCommon::VALIDATE_POSITIVE_INT => array(
                'zone_id',
            ),
            ImportexportCommon::VALIDATE_NOT_NULL => array(
                'zone_identifier',
                'zone_name',
            ),
        );
    }

    public static function validateFields($columnIndex, $columnTitle, $columnValue, $langId)
    {
        $requiredFields = static::requiredFields();
        return ImportexportCommon::validateFields($requiredFields, $columnIndex, $columnTitle, $columnValue, $langId);
    }

}
