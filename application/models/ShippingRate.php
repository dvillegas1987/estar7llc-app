<?php
class ShippingRate extends MyAppModel
{
    const DB_TBL = 'tbl_shipping_rates';
    const DB_TBL_PREFIX = 'shiprate_';

    const DB_TBL_LANG = 'tbl_shipping_rates_lang';
    const DB_TBL_LANG_PREFIX = 'shipratelang_';

    const CONDITION_TYPE_PRICE = 1;
    const CONDITION_TYPE_WEIGHT = 2;


    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($langId)
    {
        $srch = new SearchBase(static::DB_TBL, 'srate');
        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'ratelang.' . static::DB_TBL_LANG_PREFIX . 'shiprate_id = srate.' . static::tblFld('id') . ' and ratelang.' . static::DB_TBL_LANG_PREFIX . 'lang_id = ' . $langId,
                'ratelang'
            );
        }
        return $srch;
    }

    public static function getConditionTypes($langId)
    {
        return array(
            self::CONDITION_TYPE_WEIGHT => Labels::getLabel('LBL_Item_weight_(KG)', $langId),
            self::CONDITION_TYPE_PRICE => Labels::getLabel('LBL_Item_price', $langId),
        );
    }
}
