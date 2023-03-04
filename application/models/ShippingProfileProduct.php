<?php
class ShippingProfileProduct extends MyAppModel
{
    const DB_TBL = 'tbl_shipping_profile_products';
    const DB_TBL_PREFIX = 'shippro_';

    public function __construct()
    {
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'sppro');
        $srch->joinTable(Product::DB_TBL, 'LEFT OUTER JOIN', 'pro.product_id = sppro.shippro_product_id', 'pro');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'pro.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . CommonHelper::getLangId(), 'p_l');
        $srch->addMultipleFields(array('product_id', 'IFNULL(product_name, product_identifier) as product_name', 'shippro_shipprofile_id as profile_id'));
        $srch->addCondition('product_deleted', '=', applicationConstants::NO);
        return $srch;
    }

    public function addProduct($data)
    {
        if (!FatApp::getDb()->insertFromArray(self::DB_TBL, $data, true, array(), $data)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public static function getUserSearchObject($userId = 0, $userInnerJoin = false)
    {
        $srch = new SearchBase(static::DB_TBL, 'sppro');
        $fields = array('sppro.shippro_product_id', 'if(spprot.shippro_user_id > 0, spprot.shippro_user_id, sppro.shippro_user_id) as shippro_user_id', 'if(spprot.shippro_user_id > 0, spprot.shippro_shipprofile_id, sppro.shippro_shipprofile_id) as shippro_shipprofile_id');

        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
            $cond = ' and spprot.shippro_user_id = 0';
        } else {
            if ($userId) {
                $cond = ' and spprot.shippro_user_id = ' . $userId;
            } else {
                $cond = ' and spprot.shippro_user_id = tp.product_seller_id';
            }
        }

        $join = (true == $userInnerJoin) ? 'INNER JOIN' : 'LEFT OUTER JOIN';

        $srch->joinTable(Product::DB_TBL, $join, 'tp.product_id = sppro.shippro_product_id', 'tp');
        $srch->joinTable(static::DB_TBL, 'LEFT OUTER JOIN', 'spprot.shippro_product_id = sppro.shippro_product_id ' . $cond, 'spprot');
        $srch->addMultipleFields($fields);
        $srch->addGroupBy('sppro.shippro_product_id');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        return $srch;
    }

    public static function isShippingProfileLinked($productId, $userId = 0)
    {
        $productId = FatUtility::int($productId);
        $userId = FatUtility::int($userId);

        $srch = new SearchBase(static::DB_TBL, 'sppro');
        $srch->addCondition('shippro_product_id', '=', $productId);
        $srch->addCondition('shippro_user_id', '=', $userId);
        $srch->setPageSize(1);
        $srch->doNotCalculateRecords();
        $res = FatApp::getDb()->fetch($srch->getResultSet());
        if (!empty($res)) {
            return true;
        }
        return false;
    }
}
