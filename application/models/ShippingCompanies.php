<?php

class ShippingCompanies extends MyAppModel
{
    public const DB_TBL = 'tbl_shipping_company';
    public const DB_TBL_LANG = 'tbl_shipping_company_lang';
    public const DB_TBL_PREFIX = 'scompany_';
    public const DB_TBL_LANG_PREFIX = 'scompanylang_';


    public const MANUAL_SHIPPING = 1;
    public const SHIPPING_SERVICES = 2;

    private $db;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
        $this->db = FatApp::getDb();
    }

    public static function getSearchObject($isActive = true, $langId = 0)
    {
        $langId = FatUtility::int($langId);

        $srch = new SearchBase(static::DB_TBL, 'sc');
        if ($isActive == true) {
            $srch->addCondition('sc.' . static::DB_TBL_PREFIX . 'active', '=', applicationConstants::ACTIVE);
        }

        if ($langId > 0) {
            $srch->joinTable(
                static::DB_TBL_LANG,
                'LEFT OUTER JOIN',
                'sc_l.scompanylang_' . static::DB_TBL_PREFIX . 'id = sc.' . static::DB_TBL_PREFIX . 'id and sc_l.scompanylang_lang_id = ' . $langId,
                'sc_l'
            );
        }

        $srch->addOrder('sc.' . static::DB_TBL_PREFIX . 'display_order', 'ASC');
        return $srch;
    }

    public static function getListingObj($langId, $attr = null)
    {
        $srch = self::getSearchObject(true, $langId);

        if (null != $attr) {
            if (is_array($attr)) {
                $srch->addMultipleFields($attr);
            } elseif (is_string($attr)) {
                $srch->addFld($attr);
            }
        }

        $srch->addMultipleFields(
            array(
            'IFNULL(sc_l.scompany_name,sc.scompany_identifier) as scompany_name'
            )
        );

        return $srch;
    }
}
