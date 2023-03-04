<?php

class UserWishList extends MyAppModel
{
    public const DB_TBL = 'tbl_user_wish_lists';
    public const DB_TBL_PREFIX = 'uwlist_';

    public const DB_TBL_LIST_PRODUCTS = 'tbl_user_wish_list_products';
    public const DB_TBL_LIST_PRODUCTS_PREFFIX = 'uwlp_';

    public const TYPE_WISHLIST = 0;
    public const TYPE_FAVOURITE = 1;
    public const TYPE_SAVE_FOR_LATER = 2;
    public const TYPE_DEFAULT_WISHLIST = 3;
    public function __construct($uwlistId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $uwlistId);
        $this->objMainTableRecord->setSensitiveFields(array());
    }

    public static function getTypeArr(int $langId): array
    {
        return [
            self::TYPE_FAVOURITE => Labels::getLabel('LBL_Favorite', $langId),
            self::TYPE_SAVE_FOR_LATER => Labels::getLabel('LBL_Save_For_Later', $langId),
            self::TYPE_DEFAULT_WISHLIST => Labels::getLabel('LBL_Default_list', $langId)
        ];
    }

    public static function wishlistOrFavtArr($langId)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return [
            applicationConstants::NO => Labels::getLabel("LBL_FAVORITE", $langId),
            applicationConstants::YES => Labels::getLabel("LBL_WISHLIST", $langId)
        ];
    }

    public static function getSearchObject($userId = 0)
    {
        $srch = new SearchBase(static::DB_TBL, 'uwl');

        if ($userId) {
            $srch->addCondition(static::tblFld('user_id'), '=', $userId);
        }

        return $srch;
    }

    public function joinWishListProducts($srchObj)
    {
        if (!is_object($srchObj)) {
            trigger_error(Labels::getLabel('MSG_Invalid_Join_Request!', $this->commonLangId), E_USER_ERROR);
        }
        $srchObj->joinTable(UserWishListProducts::DB_TBL, 'LEFT OUTER JOIN', 'uwlist_id = uwlp_uwlist_id');
    }

    public function addUpdateListProducts(int $uwlp_uwlist_id, int $selprod_id): bool
    {
        $uwlp_uwlist_id = FatUtility::int($uwlp_uwlist_id);
        $selprod_id = FatUtility::int($selprod_id);
        $data_to_save = array('uwlp_uwlist_id' => $uwlp_uwlist_id, 'uwlp_selprod_id' => $selprod_id, 'uwlp_added_on' => date('Y-m-d H:i:s'));
        $data_to_save_on_duplicate = array('uwlp_selprod_id' => $selprod_id);
        if (!FatApp::getDb()->insertFromArray(UserWishListProducts::DB_TBL, $data_to_save, false, array(), $data_to_save_on_duplicate)) {
            $this->error = FatApp::getDb()->getError();
            return false;
        }
        return true;
    }

    public function deleteWishList(int $uwlistId): bool
    {
        $uwlistId = FatUtility::int($uwlistId);
        $db = FatApp::getDb();
        $db->deleteRecords(UserWishListProducts::DB_TBL, array('smt' => 'uwlp_uwlist_id = ?', 'vals' => array($uwlistId)));
        $db->deleteRecords(static::DB_TBL, array('smt' => 'uwlist_id = ?', 'vals' => array($uwlistId)));
        return true;
    }

    public static function getUserWishLists($userId = 0, $fetchProducts = false, $excludeWishList = 0, $type = -1)
    {
        $excludeWishList = FatUtility::int($excludeWishList);
        $userId = FatUtility::int($userId);
        if (1 > $userId) {
            trigger_error(Labels::getLabel('MSG_Invalid_Argument_Passed!', CommonHelper::getLangId()), E_USER_ERROR);
        }

        /* This function return default wish list id and also create default wishlist if not created. */
        $wishList = new UserWishList();
        $wishList->getWishListId($userId, UserWishList::TYPE_DEFAULT_WISHLIST);
        /* This function return default wish list id and also create default wishlist if not created. */

        $srchWishlist = new UserWishListProductSearch();
        $srchWishlist->joinSellerProducts();
        $srchWishlist->joinProducts();
        $srchWishlist->joinSellers();
        $srchWishlist->joinShops();
        $srchWishlist->joinProductToCategory();
        $srchWishlist->joinSellerSubscription(0, true);
        $srchWishlist->addSubscriptionValidCondition();
        $srchWishlist->addMultipleFields(array('uwlp_uwlist_id', "count(selprod_id) as WishlistItemsProductCnt"));
        $srchWishlist->doNotCalculateRecords();
        $srchWishlist->doNotLimitRecords();
        //$srch->addMultipleFields( array( 'selprod_id', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'product_id', 'IFNULL(product_name, product_identifier) as product_name', 'IF(selprod_stock > 0, 1, 0) AS in_stock') );
        $srchWishlist->addGroupBy('uwlp_uwlist_id');
        $selWishlistProductSubQuery = $srchWishlist->getQuery();


        $srch = static::getSearchObject($userId);
        $srch->joinTable('(' . $selWishlistProductSubQuery . ')', 'LEFT OUTER JOIN', 'uwlist_id = uw_items.uwlp_uwlist_id', 'uw_items');
        if (0 < $excludeWishList) {
            $srch->addCondition('uwlist_id', '!=', $excludeWishList);
        }
        if ($type == self::TYPE_SAVE_FOR_LATER) {
            $srch->addCondition('uwlist_type', '=', self::TYPE_SAVE_FOR_LATER);
        } else {
            $srch->addCondition('uwlist_type', '!=', self::TYPE_SAVE_FOR_LATER);
        }
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addOrder('uwlist_title');

        $rs = $srch->getResultSet();

        $wishLists = array();
        if ($fetchProducts) {
            while ($row = FatApp::getDb()->fetch($rs)) {
                $wishLists[$row['uwlist_id']] = $row;
                $wishLists[$row['uwlist_id']]['products'] = static::getListProductsByListId($row['uwlist_id']);
            }
            return $wishLists;
        }
        return FatApp::getDb()->fetchAll($rs);
    }

    public function getWishListId(int $userId, int $type): int
    {
        $srch = static::getSearchObject($userId, true);
        $srch->addCondition('uwlist_type', '=', $type);
        $srch->addMultipleFields(array('uwlist_id'));
        $srch->setPageSize(1);
        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);

        if (!empty($row)) {
            return $row['uwlist_id'];
        }

        $typeArr = static::getTypeArr(CommonHelper::getLangId());
        switch ($type) {
            case self::TYPE_DEFAULT_WISHLIST:
                $title = $typeArr[self::TYPE_DEFAULT_WISHLIST];
                break;
            case self::TYPE_SAVE_FOR_LATER:
                $title = $typeArr[self::TYPE_SAVE_FOR_LATER];
                break;
        }
        $data = [
            'uwlist_type' => $type,
            'uwlist_user_id' => $userId,
            'uwlist_title' => $title,
            'uwlist_added_on' => date('Y-m-d H:i:s')
        ];
        $this->assignValues($data);
        if (!$this->save()) {
            return 0;
        }
        return $this->getMainTableRecordId();
    }

    public static function getListProductsByListId($uwlp_uwlist_id = 0, $selprod_id = 0)
    {
        $uwlp_uwlist_id = FatUtility::int($uwlp_uwlist_id);
        if (!$uwlp_uwlist_id) {
            trigger_error(Labels::getLabel('MSG_Invalid_Argument_Passed!', CommonHelper::getLangId()), E_USER_ERROR);
        }
        $srch = new SearchBase(UserWishListProducts::DB_TBL);
        $srch->addCondition('uwlp_uwlist_id', '=', $uwlp_uwlist_id);

        if ($selprod_id) {
            $srch->addCondition('uwlp_selprod_id', '=', $selprod_id);
        }

        $rs = $srch->getResultSet();
        return FatApp::getDb()->fetchAll($rs, 'uwlp_selprod_id');
    }

    public static function getUserWishlistItemCount($userId = 0)
    {
        $srch = new UserWishListProductSearch();
        $srch->joinSellerProducts();
        $srch->joinProducts();
        $srch->joinBrands();
        $srch->joinSellers();
        $srch->joinShops();
        $srch->joinProductToCategory();
        $srch->joinSellerSubscription(0, true);
        $srch->addSubscriptionValidCondition();
        $srch->joinSellerProductSpecialPrice();
        $srch->joinFavouriteProducts($userId);
        $srch->addCondition('selprod_deleted', '=', applicationConstants::NO);
        $srch->addCondition('selprod_active', '=', applicationConstants::YES);
        $srch->addGroupBy('selprod_id');
        $srch->addFld('selprod_id');
        $srch->getResultSet();
        return $totalWishlistItems['totalWishlistItems'] = $srch->recordCount();
    }
}
