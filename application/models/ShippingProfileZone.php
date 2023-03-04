<?php
class ShippingProfileZone extends MyAppModel
{
    const DB_TBL = 'tbl_shipping_profile_zones';
    const DB_TBL_PREFIX = 'shipprozone_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }
    
    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'spzone');
        $srch->joinTable(ShippingZone::DB_TBL, 'LEFT OUTER JOIN', 'szone.shipzone_id = spzone.shipprozone_shipzone_id', 'szone');
        return $srch;
    }

    public static function getAttributesByProfileId($recordId, $attr = null, $multiRows = false)
    {
        $recordId = FatUtility::convertToType($recordId, FatUtility::VAR_INT);
        $db = FatApp::getDb();

        $srch = new SearchBase(static::DB_TBL);
        $srch->doNotCalculateRecords();
        if (false === $multiRows) {
            $srch->setPageSize(1);
        }
        $srch->addCondition(static::tblFld('shipprofile_id'), '=', $recordId);

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $rs = $srch->getResultSet();
        if (false === $multiRows) {
            $row = $db->fetch($rs);
        } else {
            return $db->fetchAll($rs);
        }

        if (!is_array($row)) {
            return false;
        }

        if (is_string($attr)) {
            return $row[$attr];
        }

        return $row;
    }

    /* public function addZone($data)
    {
        if (!FatApp::getDb()->insertFromArray(self::DB_TBL, $data, true, array(), $data)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    } */
}
