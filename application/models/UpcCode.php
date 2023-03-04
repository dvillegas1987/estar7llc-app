<?php

class UpcCode extends MyAppModel
{
    public const DB_TBL = 'tbl_upc_codes';
    public const DB_TBL_PREFIX = 'upc_';

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'code_id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'upc_code');
        return $srch;
    }

    public static function getUpcCode($product_id, $optionvalue_id)
    {
        $product_id = FatUtility::int($product_id);
        $optionvalue_id = FatUtility::int($optionvalue_id);
        $db = FatApp::getDb();
        if (!$product_id || !$optionvalue_id) {
            trigger_error(Labels::getLabel('ERR_Invalid_Arguments', CommonHelper::getLangId()), E_USER_ERROR);
        }

        $srch = self::getSearchObject();

        $srch->addCondition(self::DB_TBL_PREFIX . 'product_id', '=', $product_id);
        $srch->addCondition(self::DB_TBL_PREFIX . 'options', '=', $optionvalue_id);
        $srch->addFld('upc_code');
        $rs = $srch->getResultSet();
        $code = $db->fetch($rs);
        if (empty($code)) {
            return '';
        }
        return $code['upc_code'];
    }

    public static function remove(int $product_id)
    {
        if (!FatApp::getDb()->deleteRecords(self::DB_TBL, array('smt' => self::DB_TBL_PREFIX . 'product_id = ?', 'vals' => array($product_id)))) {
            return false;
        }
        return true;
    }
}
