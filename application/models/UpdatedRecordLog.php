<?php
class UpdatedRecordLog extends MyAppModel
{
    public const DB_TBL = 'tbl_updated_record_log';
    public const DB_TBL_PREFIX = 'urlog_';

    public const TYPE_SHOP = 1;
    public const TYPE_USER = 2;
    public const TYPE_CATEGORY = 3;
    public const TYPE_BRAND = 4;
    public const TYPE_COUNTRY = 5;
    public const TYPE_STATE = 6;
    public const TYPE_PRODUCT = 7;
    public const TYPE_INVENTORY = 8;

    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'urlog');
        return $srch;
    }

    public static function getTypeArr()
    {
        return [
            Shop::DB_TBL_PREFIX => static::TYPE_SHOP,
            Product::DB_TBL_PREFIX =>  static::TYPE_PRODUCT,
            User::DB_TBL_PREFIX =>  static::TYPE_USER,
            ProductCategory::DB_TBL_PREFIX =>  static::TYPE_CATEGORY,
            SellerProduct::DB_TBL_PREFIX =>  static::TYPE_INVENTORY,
            Brand::DB_TBL_PREFIX =>  static::TYPE_BRAND,
            Countries::DB_TBL_PREFIX =>  static::TYPE_COUNTRY,
            States::DB_TBL_PREFIX =>  static::TYPE_STATE,
        ];
    }

    public static function getQueueRecords($pageSize)
    {
        $pageSize = Fatutility::int($pageSize);
        if (1 > $pageSize) {
            $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
        }
        
        $srch = static::getSearchObject();
        $srch->doNotCalculateRecords();
        $srch->setPageSize($pageSize);
        $srch->addCondition(static::DB_TBL_PREFIX . 'executed', '!=', applicationConstants::YES);
        $srch->addOrder(static::DB_TBL_PREFIX . 'added_on', 'asc');
        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs);
    }

    public static function setShopProducts($shopId)
    {
        $shopId = FatUtility::int($shopId);
        if (1 > $shopId) {
            return false;
        }

        $srch = new ProductSearch(0, null, null, false, false, false);
        $srch->joinSellerProducts(0, '', array(), false);
        $srch->joinSellers();
        $srch->joinShops(0, false, false, $shopId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('shop_id', '=', $shopId);
        $srch->addMultipleFields(array('distinct(product_id)'));
        $rs = $srch->getResultSet();
        while ($record = FatApp::getDb()->fetch($rs)) {
            $data = [
                'urlog_record_id' => $record['product_id'],
                'urlog_subrecord_id' => 0,
                'urlog_record_type' => static::TYPE_PRODUCT,
                'urlog_executed' => 0,
                'urlog_added_on' => date('Y-m-d H:i:s')
            ];
            if (!static::updateQueue($data)) {
                return false;
            }
        }
        return true;
    }

    public static function setUserProducts($sellerId)
    {
        $sellerId = FatUtility::int($sellerId);
        if (1 > $sellerId) {
            return false;
        }

        $srch = new ProductSearch(0, null, null, false, false, false);
        $srch->joinSellerProducts($sellerId, '', array(), false);
        $srch->joinSellers();
        $srch->joinShops(0, false, false, 0);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        //$srch->addCondition('selprod_user_id', '=', $sellerId);
        $srch->addMultipleFields(array('distinct(product_id)'));
        $rs = $srch->getResultSet();
        while ($record = FatApp::getDb()->fetch($rs)) {
            $data = [
                'urlog_record_id' => $record['product_id'],
                'urlog_subrecord_id' => 0,
                'urlog_record_type' => static::TYPE_PRODUCT,
                'urlog_executed' => 0,
                'urlog_added_on' => date('Y-m-d H:i:s')
            ];
            if (!static::updateQueue($data)) {
                return false;
            }
        }
        return true;
    }

    public static function setCategoryProducts($categoryId)
    {
        $categoryId = FatUtility::int($categoryId);
        if (1 > $categoryId) {
            return false;
        }

        $srch = new ProductSearch(0, null, null, false, false, false);
        $srch->joinProductToCategory(0, false, false, false);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('prodcat_id', '=', $categoryId);
        $srch->addMultipleFields(array('distinct(product_id)'));
        $rs = $srch->getResultSet();
        while ($record = FatApp::getDb()->fetch($rs)) {
            $data = [
                'urlog_record_id' => $record['product_id'],
                'urlog_subrecord_id' => 0,
                'urlog_record_type' => static::TYPE_PRODUCT,
                'urlog_executed' => 0,
                'urlog_added_on' => date('Y-m-d H:i:s')
            ];
            if (!static::updateQueue($data)) {
                return false;
            }
        }
        return true;
    }

    public static function setBrandProducts($brandId)
    {
        $brandId = FatUtility::int($brandId);
        if (1 > $brandId) {
            return false;
        }

        $srch = new ProductSearch(0, null, null, false, false, false);
        $srch->joinBrands(0, false, false, false);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('brand_id', '=', $brandId);
        $srch->addMultipleFields(array('distinct(product_id)'));
        $rs = $srch->getResultSet();
        while ($record = FatApp::getDb()->fetch($rs)) {
            $data = [
                'urlog_record_id' => $record['product_id'],
                'urlog_subrecord_id' => 0,
                'urlog_record_type' => static::TYPE_PRODUCT,
                'urlog_executed' => 0,
                'urlog_added_on' => date('Y-m-d H:i:s')
            ];
            if (!static::updateQueue($data)) {
                return false;
            }
        }
        return true;
    }

    public static function setCountryProducts($countryId)
    {
        $countryId = FatUtility::int($countryId);
        if (1 > $countryId) {
            return false;
        }

        $srch = new ProductSearch(0, null, null, false, false, false);
        $srch->joinSellerProducts(0, '', array(), false);
        $srch->joinSellers();
        $srch->joinShops(0, false, false, 0);
        $srch->joinShopCountry(0, false);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('country_id', '=', $countryId);
        $srch->addMultipleFields(array('distinct(product_id)'));
        $rs = $srch->getResultSet();
        while ($record = FatApp::getDb()->fetch($rs)) {
            $data = [
                'urlog_record_id' => $record['product_id'],
                'urlog_subrecord_id' => 0,
                'urlog_record_type' => static::TYPE_PRODUCT,
                'urlog_executed' => 0,
                'urlog_added_on' => date('Y-m-d H:i:s')
            ];
            if (!static::updateQueue($data)) {
                return false;
            }
        }
        return true;
    }

    public static function setStateProducts($stateId)
    {
        $stateId = FatUtility::int($stateId);
        if (1 > $stateId) {
            return false;
        }

        $srch = new ProductSearch(0, null, null, false, false, false);
        $srch->joinSellerProducts(0, '', array(), false);
        $srch->joinSellers();
        $srch->joinShops(0, false, false, 0);
        $srch->joinShopState(0, false);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('state_id', '=', $stateId);
        $srch->addMultipleFields(array('distinct(product_id)'));
        $rs = $srch->getResultSet();
        while ($record = FatApp::getDb()->fetch($rs)) {
            $data = [
                'urlog_record_id' => $record['product_id'],
                'urlog_subrecord_id' => 0,
                'urlog_record_type' => static::TYPE_PRODUCT,
                'urlog_executed' => 0,
                'urlog_added_on' => date('Y-m-d H:i:s')
            ];
            if (!static::updateQueue($data)) {
                return false;
            }
        }
        return true;
    }

    public static function markExecuted($type, $recordId, $subRecordId)
    {
        FatApp::getDb()->updateFromArray(
            static::DB_TBL,
            array(
                static::DB_TBL_PREFIX . 'executed' => 1
            ),
            array(
            'smt' => static::DB_TBL_PREFIX . 'record_type = ? and ' . static::DB_TBL_PREFIX . 'record_id = ?  and ' . static::DB_TBL_PREFIX . 'subrecord_id = ?' ,
            'vals' => array($type, $recordId, $subRecordId)
            )
        );
    }

    /**
     * Maintain records queue for Full text Search server .
     *
    */
    public static function updateQueue($data)
    {
        if (empty($data)) {
            return false;
        }

        return FatApp::getDb()->insertFromArray(UpdatedRecordLog::DB_TBL, $data, false, array(), $data);

        /* $updatedRecordLog = new UpdatedRecordLog();
        $updatedRecordLog->assignValues($data);
        if (!$updatedRecordLog->save()) {
            return false;
        }
        return true; */
    }
}
