<?php
class ShippingZone extends MyAppModel
{
    const DB_TBL = 'tbl_shipping_zone';
    const DB_TBL_PREFIX = 'shipzone_';

    const DB_SHIP_LOC_TBL = 'tbl_shipping_locations';
    const DB_SHIP_LOC_TBL_PREFIX = 'shiploc_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($isActive = false)
    {
        $srch = new SearchBase(static::DB_TBL, 'szone');
        if ($isActive == true) {
            $srch->addCondition('szone.' . static::DB_TBL_PREFIX . 'active', '=', applicationConstants::ACTIVE);
        }
        return $srch;
    }

    public static function getZoneLocationSearchObject($langId = 0)
    {
        $langId = FatUtility::int($langId);
        $srch = new SearchBase(static::DB_SHIP_LOC_TBL, 'sloc');
        $srch->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'sc.country_id = sloc.shiploc_country_id', 'sc');

        $fields = ['sloc.shiploc_shipzone_id', 'sloc.shiploc_zone_id', 'sloc.shiploc_country_id', 'sloc.shiploc_state_id', 'sc.country_id'];
        if (0 < $langId) {
            $srch->joinTable(Countries::DB_TBL_LANG, 'LEFT OUTER JOIN', 'c_l.' . Countries::DB_TBL_LANG_PREFIX . 'country_id = sc.' . Countries::tblFld('id') . ' and c_l.' . Countries::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId, 'c_l');
            $fields[] = 'if(c_l.country_name is null, sc.country_code, c_l.country_name) as country_name';
        } else {
            $fields[] = 'sc.country_code as country_name';
        }

        $srch->addMultipleFields($fields);
        return $srch;
    }

    public function updateLocations($data)
    {
        if (!FatApp::getDb()->insertFromArray(self::DB_SHIP_LOC_TBL, $data, true, array(), $data)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public function deleteData($zoneId)
    {
        if (!$this->deleteLocations($zoneId)) {
            //$this->error = FatApp::getDb()->getError();
            return false;
        }
        if (!$this->deleteRates($zoneId)) {
            //$this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public function deleteLocations($zoneId)
    {
        if (!FatApp::getDb()->deleteRecords(
            self::DB_SHIP_LOC_TBL,
            array(
                'smt' => self::DB_SHIP_LOC_TBL_PREFIX . 'shipzone_id=? ',
                'vals' => array($zoneId)
            )
        )) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public function deleteRates($shipprozoneId)
    {
        if (!FatApp::getDb()->query('DELETE rates, rateLang FROM ' . ShippingRate::DB_TBL . ' rates LEFT JOIN ' . ShippingRate::DB_TBL_LANG . ' rateLang ON rateLang.shipratelang_shiprate_id  = rates.shiprate_id  WHERE rates.shiprate_shipprozone_id = ' . $shipprozoneId)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        };
        return true;
    }
}
