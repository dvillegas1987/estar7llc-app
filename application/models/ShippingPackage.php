<?php
class ShippingPackage extends MyAppModel
{
    const DB_TBL = 'tbl_shipping_packages';
    const DB_TBL_PREFIX = 'shippack_';
    const UNIT_TYPE_CM = 1;
    const UNIT_TYPE_METER = 2;
    const UNIT_TYPE_INCH = 3;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'spack');
        return $srch;
    }
    
    public static function getPackageIdByName($packageName)
    {
        $srch = self::getSearchObject();
        $srch->addCondition('shippack_name', '=', trim($packageName));
        $srch->addFld('shippack_id');
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!empty($row)) {
            return $row['shippack_id'];
        }
        return 0;
    }
    
    public static function getUnitTypes($langId)
    {
        return applicationConstants::getLengthUnitsArr($langId);
    }
}
