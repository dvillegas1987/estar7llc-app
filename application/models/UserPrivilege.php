<?php
class UserPrivilege
{
    public const SECTION_SHOP = 1;
    public const SECTION_PRODUCTS = 2;
    public const SECTION_PRODUCT_TAGS = 3;
    public const SECTION_IMPORT_EXPORT = 4;
    public const SECTION_META_TAGS = 5;
    public const SECTION_URL_REWRITING = 6;
    public const SECTION_SPECIAL_PRICE = 7;
    public const SECTION_VOLUME_DISCOUNT = 8;
    public const SECTION_BUY_TOGETHER_PRODUCTS = 9;
    public const SECTION_RELATED_PRODUCTS = 10;
    public const SECTION_SALES = 11;
    public const SECTION_CANCELLATION_REQUESTS = 12;
    public const SECTION_RETURN_REQUESTS = 14;
    public const SECTION_TAX_CATEGORY = 15;
    public const SECTION_PRODUCT_OPTIONS = 16;
    public const SECTION_SOCIAL_PLATFORMS = 17;
    public const SECTION_MESSAGES = 18;
    public const SECTION_CREDITS = 19;
    public const SECTION_SALES_REPORT = 20;
    public const SECTION_PERFORMANCE_REPORT = 21;
    public const SECTION_INVENTORY_REPORT = 22;
    public const SECTION_SELLER_DASHBOARD = 23;
    public const SECTION_SELLER_PERMISSIONS = 24;
    public const SECTION_UPLOAD_BULK_IMAGES = 25;
    public const SECTION_PROMOTIONS = 26;
    public const SECTION_PROMOTION_CHARGES = 27;
    public const SECTION_SUBSCRIPTION = 28;
    public const SECTION_SHIPPING_PROFILE = 28;
    public const SECTION_SHIPPING_PACKAGES = 29;
    public const SECTION_SELLER_REQUESTS = 30;
    public const SECTION_ADVERTISEMENT_FEED = 31;



    public const MODULE_SHOP = 1;
    public const MODULE_ORDERS = 2;
    public const MODULE_PROMOTIONS = 3;
    public const MODULE_SEO = 4;
    public const MODULE_REPORTS = 5;
    public const MODULE_ACCOUNT = 6;
    public const MODULE_ADVERTISEMENT = 7;
    public const MODULE_IMPORT_EXPORT = 8;
    public const MODULE_SHIPPING = 9;

    public const PRIVILEGE_NONE = 0;
    public const PRIVILEGE_READ = 1;
    public const PRIVILEGE_WRITE = 2;

    private static $instance = null;
    private $loadedPermissions = array();

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function isUserSuperSeller($sellerId)
    {
        $user = new User($sellerId);
        $srch = $user->getUserSearchObj();
        $rs = $srch->getResultSet();
        $userData = FatApp::getDb()->fetch($rs);
        return ($userData['user_parent'] == 0) ? true : false;
    }

    public static function getSellerModulesArr($langId)
    {
        $arr = array(
            static::MODULE_SHOP => Labels::getLabel('LBL_Shop', $langId),
            static::MODULE_ORDERS => Labels::getLabel('LBL_Orders', $langId),
            static::MODULE_PROMOTIONS => Labels::getLabel('LBL_Promotions', $langId),
            static::MODULE_SEO => Labels::getLabel('LBL_SEO', $langId),
            static::MODULE_REPORTS => Labels::getLabel('LBL_Reports', $langId),
            static::MODULE_ACCOUNT => Labels::getLabel('LBL_Account', $langId),
            static::MODULE_ADVERTISEMENT => Labels::getLabel('LBL_Advertisement', $langId),
            static::MODULE_IMPORT_EXPORT => Labels::getLabel('LBL_Import_Export', $langId),
            static::MODULE_SHIPPING => Labels::getLabel('LBL_Shipping', $langId),
        );
        return $arr;
    }

    public static function getPermissionArr($langId)
    {
        $arr = array(
            static::PRIVILEGE_NONE => Labels::getLabel('LBL_None', $langId),
            static::PRIVILEGE_READ => Labels::getLabel('LBL_Read_Only', $langId),
            static::PRIVILEGE_WRITE => Labels::getLabel('LBL_Read_and_Write', $langId)
        );
        return $arr;
    }

    public static function getSellerPermissionModulesArr($langId)
    {
        $arr = array(
            static::SECTION_SELLER_DASHBOARD => Labels::getLabel('LBL_Seller_Dashboard', $langId),
            static::SECTION_SHOP => Labels::getLabel('LBL_Shop', $langId),
            static::SECTION_PRODUCTS => Labels::getLabel('LBL_Products', $langId),
            static::SECTION_PRODUCT_TAGS => Labels::getLabel('LBL_Product_Tags', $langId),
            static::SECTION_IMPORT_EXPORT => Labels::getLabel('LBL_Import_Export', $langId),
            static::SECTION_META_TAGS => Labels::getLabel('LBL_Meta_Tags', $langId),
            static::SECTION_URL_REWRITING => Labels::getLabel('LBL_Url_Rewriting', $langId),
            static::SECTION_SPECIAL_PRICE => Labels::getLabel('LBL_Special_Price', $langId),
            static::SECTION_VOLUME_DISCOUNT => Labels::getLabel('LBL_Volume_Discount', $langId),
            static::SECTION_BUY_TOGETHER_PRODUCTS => Labels::getLabel('LBL_Buy_Together_Products', $langId),
            static::SECTION_RELATED_PRODUCTS => Labels::getLabel('LBL_Related_Products', $langId),
            static::SECTION_SALES => Labels::getLabel('LBL_Sales', $langId),
            static::SECTION_CANCELLATION_REQUESTS => Labels::getLabel('LBL_Cancellation_Requests', $langId),
            static::SECTION_RETURN_REQUESTS => Labels::getLabel('LBL_Return_Requests', $langId),
            static::SECTION_TAX_CATEGORY => Labels::getLabel('LBL_Tax_Category', $langId),
            static::SECTION_SELLER_REQUESTS => Labels::getLabel('LBL_Tax_Category', $langId),
            static::SECTION_PRODUCT_OPTIONS => Labels::getLabel('LBL_Product_Options', $langId),
            static::SECTION_SOCIAL_PLATFORMS => Labels::getLabel('LBL_Manage_Social_Platforms', $langId),
            static::SECTION_MESSAGES => Labels::getLabel('LBL_Messages', $langId),
            static::SECTION_SALES_REPORT => Labels::getLabel('LBL_Sales_Report', $langId),
            static::SECTION_PERFORMANCE_REPORT => Labels::getLabel('LBL_Product_Performance_Report', $langId),
            static::SECTION_INVENTORY_REPORT => Labels::getLabel('LBL_Inventory_Report', $langId),
            static::SECTION_UPLOAD_BULK_IMAGES => Labels::getLabel('LBL_Upload_Bulk_Images', $langId),
            static::SECTION_PROMOTIONS => Labels::getLabel('LBL_Promotions', $langId),
            static::SECTION_SUBSCRIPTION => Labels::getLabel('LBL_Subscription', $langId),
            static::SECTION_SHIPPING_PROFILE => Labels::getLabel('LBL_Shipping_Profiles', $langId),
            static::SECTION_SHIPPING_PACKAGES => Labels::getLabel('LBL_Shipping_Packages', $langId),
            static::SECTION_ADVERTISEMENT_FEED => Labels::getLabel('LBL_Advertisement_Feed', $langId),
        );
        return $arr;
    }

    public static function getModuleSpecificPermissionArr($langId)
    {
        $arr = array(
            static::MODULE_SHOP =>
            array(
                static::SECTION_SHOP => Labels::getLabel('LBL_Shop', $langId),
                static::SECTION_PRODUCTS => Labels::getLabel('LBL_Products', $langId),
                static::SECTION_PRODUCT_TAGS => Labels::getLabel('LBL_Product_Tags', $langId),
                static::SECTION_PRODUCT_OPTIONS => Labels::getLabel('LBL_Product_Options', $langId),
                static::SECTION_TAX_CATEGORY => Labels::getLabel('LBL_Tax_Categories', $langId),
                static::SECTION_SELLER_REQUESTS => Labels::getLabel('LBL_Seller_Requests', $langId),
            ),
            static::MODULE_PROMOTIONS =>
            array(
                static::SECTION_SPECIAL_PRICE => Labels::getLabel('LBL_Special_Price', $langId),
                static::SECTION_VOLUME_DISCOUNT => Labels::getLabel('LBL_Volume_Discount', $langId),
                static::SECTION_BUY_TOGETHER_PRODUCTS => Labels::getLabel('LBL_Buy_Together_Products', $langId),
                static::SECTION_RELATED_PRODUCTS => Labels::getLabel('LBL_Related_Products', $langId)
            ),
            static::MODULE_ORDERS =>
            array(
                static::SECTION_SALES => Labels::getLabel('LBL_Sales', $langId),
                static::SECTION_CANCELLATION_REQUESTS => Labels::getLabel('LBL_Cancellation_Requests', $langId),
                static::SECTION_RETURN_REQUESTS => Labels::getLabel('LBL_Return_Requests', $langId),
            ),
            static::MODULE_SHIPPING =>
            array(
                static::SECTION_SHIPPING_PROFILE => Labels::getLabel('LBL_Shipping_Profiles', $langId),
                static::SECTION_SHIPPING_PACKAGES => Labels::getLabel('LBL_Shipping_Packages', $langId),
            ),
            static::MODULE_SEO =>
            array(
                static::SECTION_META_TAGS => Labels::getLabel('LBL_Meta_Tags', $langId),
                static::SECTION_URL_REWRITING => Labels::getLabel('LBL_Url_Rewriting', $langId)
            ),
            static::MODULE_REPORTS =>
            array(
                static::SECTION_SALES_REPORT => Labels::getLabel('LBL_Sales_Report', $langId),
                static::SECTION_PERFORMANCE_REPORT => Labels::getLabel('LBL_Product_Performance_Report', $langId),
                static::SECTION_INVENTORY_REPORT => Labels::getLabel('LBL_Inventory_Report', $langId),
            ),
            static::MODULE_ADVERTISEMENT =>
            array(
                // static::SECTION_PROMOTIONS => Labels::getLabel('LBL_Promotions', $langId),
                static::SECTION_ADVERTISEMENT_FEED => Labels::getLabel('LBL_Advertisement_Feed', $langId),
            ),
            static::MODULE_ACCOUNT =>
            array(
                static::SECTION_MESSAGES => Labels::getLabel('LBL_Messages', $langId),
                static::SECTION_SUBSCRIPTION => Labels::getLabel('LBL_Seller_Subscription', $langId),
            ),
            static::MODULE_IMPORT_EXPORT =>
            array(
                static::SECTION_IMPORT_EXPORT => Labels::getLabel('LBL_Import_Export', $langId),
                static::SECTION_UPLOAD_BULK_IMAGES => Labels::getLabel('LBL_Upload_Bulk_Images', $langId)
            ),
        );
        return $arr;
    }

    public static function getWriteOnlyPermissionModulesArr()
    {
        return array(
            static::SECTION_UPLOAD_BULK_IMAGES,
        );
    }

    public static function getSellerPermissionLevel($sellerId, $sectionId = 0)
    {
        $db = FatApp::getDb();
        $sellerId = FatUtility::int($sellerId);

        /* Are you looking for permissions of seller [ */
        if (static::isUserSuperSeller($sellerId)) {
            $arrLevels = array();
            if ($sectionId > 0) {
                $arrLevels[$sectionId] = static::PRIVILEGE_WRITE;
            } else {
                for ($i = 0; $i <= 2; $i++) {
                    $arrLevels[$i] = static::PRIVILEGE_WRITE;
                }
            }
            return $arrLevels;
        }
        /* ] */

        $srch = UserPermission::getSearchObject();
        $srch->addCondition('userperm_user_id', '=', $sellerId);
        if (0 < $sectionId) {
            $srch->addCondition('userperm_section_id', '=', $sectionId);
        }

        $srch->addMultipleFields(array('userperm_section_id', 'userperm_value'));
        $rs = $srch->getResultSet();
        $arr = $db->fetchAllAssoc($rs);

        return $arr;
    }

    private function cacheLoadedPermission($sellerId, $secId, $level)
    {
        if (!isset($this->loadedPermissions[$sellerId])) {
            $this->loadedPermissions[$sellerId] = array();
        }
        $this->loadedPermissions[$sellerId][$secId] = $level;
    }

    private function checkPermission($sellerId, $secId, $level, $returnResult = false)
    {
        $db = FatApp::getDb();

        if (!in_array($level, array(1, 2))) {
            trigger_error(Labels::getLabel('LBL_Invalid_permission_level_checked', CommonHelper::getLangId()) . ' ' . $level, E_USER_ERROR);
        }

        $sellerId = FatUtility::convertToType($sellerId, FatUtility::VAR_INT);
        if (0 == $sellerId) {
            $sellerId = UserAuthentication::getLoggedUserId();
        }

        if (static::isUserSuperSeller($sellerId)) {
            return true;
        }

        if (isset($this->loadedPermissions[$sellerId][$secId])) {
            if ($level <= $this->loadedPermissions[$sellerId][$secId]) {
                return true;
            }
            return $this->returnFalseOrDie($returnResult);
        }

        $user = new User($sellerId);
        $srch = $user->getUserSearchObj();
        $srch->addCondition('credential_active', '=', applicationConstants::ACTIVE);
        $rs = $srch->getResultSet();
        $userData = FatApp::getDb()->fetch($rs);
        if (empty($userData)) {
            return $this->returnFalseOrDie($returnResult);
        }

        $srch = UserPermission::getSearchObject();
        $srch->addCondition('userperm_user_id', '=', $sellerId);
        $srch->addCondition('userperm_section_id', '=', $secId);

        $srch->addFld('userperm_value');
        $rs = $srch->getResultSet();

        if (!$row = $db->fetch($rs)) {
            $this->cacheLoadedPermission($sellerId, $secId, static::PRIVILEGE_NONE);
            return $this->returnFalseOrDie($returnResult);
        }

        $permissionLevel = $row['userperm_value'];

        $this->cacheLoadedPermission($sellerId, $secId, $permissionLevel);

        if ($level > $permissionLevel) {
            return $this->returnFalseOrDie($returnResult);
        }

        return (true);
    }

    private function returnFalseOrDie($returnResult, $msg = '')
    {
        if ($returnResult) {
            return (false);
        }
        Message::addErrorMessage(Labels::getLabel('LBL_Unauthorized_Access!', CommonHelper::getLangId()));
        if ($msg == '') {
            $msg = Message::getHtml();
        }
        LibHelper::exitWithError($msg);
    }

    public function clearPermissionCache($sellerId)
    {
        if (isset($this->loadedPermissions[$sellerId])) {
            unset($this->loadedPermissions[$sellerId]);
        }
    }

    public static function canSellerEditOption($userId, $optionId, $langId)
    {
        $userId = FatUtility::int($userId);
        if (0 == $userId) {
            $userId = UserAuthentication::getLoggedUserId();
        }

        $option = new Option();
        if (!$row = $option->getOption($optionId, $userId)) {
            return false;
        }
        return true;
    }

    /* public static function canSellerEditOptionValue($userId, $optionValueId, $langId)
    {
        $userId = FatUtility::int($userId);
        if (0 == $userId) {
            $userId = UserAuthentication::getLoggedUserId();
        }
        $optionValue = new OptionValue($optionValueId);
        if (!$row = $optionValue->getOptionValue($optionId)) {
            return false;
        }
        return true;
    } */

    public static function canEditSellerProductSpecification($specificationId, $productId)
    {
        $prodSpecObj = new ProdSpecification();
        if (!$row = $prodSpecObj->getProdSpecification($specificationId, $productId, '', false)) {
            return false;
        }
        return true;
    }

    public static function canSellerEditCustomProduct($userId, $productId = -1)
    {
        $userId = FatUtility::int($userId);
        if (0 == $userId) {
            $userId = UserAuthentication::getLoggedUserId();
        }

        if ($productId < 0) {
            return false;
        }

        /* Validate product belongs to current logged seller[ */
        $productRow = Product::getAttributesById($productId, array('product_seller_id'));
        if (!$productRow || $productRow['product_seller_id'] != $userId) {
            return false;
        }
        return true;
        /* ] */
    }

    public static function canEditSellerProduct($userId, $productId = -1)
    {
        $userId = FatUtility::int($userId);
        if (0 == $userId) {
            $userId = UserAuthentication::getLoggedUserId();
        }

        if ($productId < 0) {
            return false;
        }

        /* Validate product belongs to current logged seller[ */
        $sellerProductRow = SellerProduct::getAttributesById($productId, array('selprod_user_id'));

        if (!$sellerProductRow || $sellerProductRow['selprod_user_id'] != $userId) {
            return false;
        }
        return true;
        /* ] */
    }

    public static function canEditMetaTag($metaId = 0, $metaRecordId = 0)
    {
        /* if ($metaId == 0 && !self::canEditSellerProduct($metaRecordId)) {
            return false;
        } */
        if ($metaId > 0 && !$data = MetaTag::getAttributesById($metaId, array('meta_record_id'))) {
            return false;
        }

        return true;
    }

    public static function canSellerUpdateTag($userId, $tagId)
    {
        $userId = FatUtility::int($userId);
        if (0 == $userId) {
            $userId = UserAuthentication::getLoggedUserId();
        }

        if (!$data = Tag::getAttributesById($tagId, array('tag_user_id'))) {
            return false;
        } else {
            if ($data['tag_user_id'] != $userId) {
                return false;
            }
        }
        return true;
    }

    public static function canSellerUpdateBrandRequest($userId, $brandId)
    {
        $userId = FatUtility::int($userId);
        if (1 > $userId) {
            $userId = UserAuthentication::getLoggedUserId();
        }

        if (!$data = Brand::getAttributesById($brandId, array('brand_seller_id'))) {
            return false;
        }

        $parentId = User::getAttributesById($userId, 'user_parent');
        $allowedUsers = [$userId];

        if (1 > $parentId) {
            $subusers = User::getSubUsers($userId, array('user_id'));
            $allowedUsers = array_merge($allowedUsers, array_column($subusers, 'user_id'));
        }

        if (in_array($data['brand_seller_id'], $allowedUsers)) {
            return true;
        }

        return false;
    }

    public static function canSellerUpdateCategoryRequest($userId, $prodCatId)
    {
        $userId = FatUtility::int($userId);
        if (1 > $userId) {
            $userId = UserAuthentication::getLoggedUserId();
        }

        if (!$data = ProductCategory::getAttributesById($prodCatId, array('prodcat_seller_id'))) {
            return false;
        }

        $parentId = User::getAttributesById($userId, 'user_parent');
        $allowedUsers = [$userId];

        if (1 > $parentId) {
            $subusers = User::getSubUsers($userId, array('user_id'));
            $allowedUsers = array_merge($allowedUsers, array_column($subusers, 'user_id'));
        }

        if (in_array($data['prodcat_seller_id'], $allowedUsers)) {
            return true;
        }

        return false;
    }

    public static function canSellerAddNewProduct($userId)
    {
        $userId = FatUtility::int($userId);
        if (0 == $userId) {
            $userId = UserAuthentication::getLoggedUserId();
        }

        if (!self::isUserHasValidSubsription($userId)) {
            return false;
        }

        if (!FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0)) {
            return true;
        }
        /* if(FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE',FatUtility::VAR_INT,0)){

        if(!self::isUserHasValidSubsription($userId)){
        return false;
        }
        } */
        $products = new Product();
        $currentPlanData = OrderSubscription::getUserCurrentActivePlanDetails(CommonHelper::getLangId(), $userId, array('ossubs_products_allowed'));
        $productsAllowed = $currentPlanData['ossubs_products_allowed'];

        $totalProducts = $products->getTotalProductsAddedByUser($userId);
        if ($totalProducts >= $productsAllowed) {
            return false;
        }
        return true;
    }

    public static function isUserHasValidSubsription($userId = 0)
    {
        if ($userId < 1) {
            return false;
        }

        if (!FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE', FatUtility::VAR_INT, 0)) {
            return true;
        }

        $latestOrder = OrderSubscription::getUserCurrentActivePlanDetails(CommonHelper::getLangId(), $userId, array('ossubs_till_date', 'ossubs_id'));
        if (empty($latestOrder)) {
            return false;
        } elseif ($latestOrder['ossubs_till_date'] < date("Y-m-d")) {
            return false;
        }

        return true;
    }

    /* Subscription privildges */

    /**
     * canSellerUpgradeOrDowngradePlan
     *
     * @param  int $userId
     * @param  int $spPlanId
     * @param  int $langId
     * @return bool
     */
    public static function canSellerUpgradeOrDowngradePlan(int $userId, int $spPlanId = 0, int $langId = 0): bool
    {
        if (1 > $userId || $spPlanId < 1) {
            return false;
        }
        $currentPlanData = OrderSubscription::getUserCurrentActivePlanDetails($langId, $userId, array(OrderSubscription::DB_TBL_PREFIX . 'id'));
        $currentActivePlanId = is_array($currentPlanData) && isset($currentPlanData[OrderSubscription::DB_TBL_PREFIX . 'id']) ? $currentPlanData[OrderSubscription::DB_TBL_PREFIX . 'id'] : 0;

        if (!$currentActivePlanId) {
            return true;
        } else {
            $totalActiveProducts = Product::getActiveCount($userId);
            $allowedLimit = SellerPackagePlans::getSubscriptionPlanDataByPlanId($spPlanId, $langId);

            if ($totalActiveProducts > $allowedLimit['spackage_products_allowed']) {
                Message::addErrorMessage(sprintf(Labels::getLabel('M_YOU_ARE_DOWNGRADING_YOUR_PACKAGE', $langId), $allowedLimit['spackage_products_allowed'], $totalActiveProducts));
                return false;
            }

            $totalActiveInventories = SellerProduct::getActiveCount($userId);
            if ($totalActiveInventories > $allowedLimit['spackage_inventory_allowed']) {
                Message::addErrorMessage(sprintf(Labels::getLabel('M_YOU_ARE_DOWNGRADING_YOUR_PACKAGE', $langId), $allowedLimit['spackage_inventory_allowed'], $totalActiveInventories));
                return false;
            }
        }
        return true;
    }

    public static function canEditSellerCollection($userId = 0)
    {
        //Pending
        return true;
    }

    public static function canSellerAddProductInCatalog($productId = 0, $userId = 0)
    {
        $product = Product::getAttributesById($productId);

        if ($userId != $product['product_seller_id'] && $product['product_seller_id'] != 0) {
            return false;
        }
        return true;
    }

    public function canViewShop($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_SHOP, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditShop($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_SHOP, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewProducts($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_PRODUCTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditProducts($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_PRODUCTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewProductTags($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_PRODUCT_TAGS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditProductTags($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_PRODUCT_TAGS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewImportExport($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_IMPORT_EXPORT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditImportExport($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_IMPORT_EXPORT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewMetaTags($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_META_TAGS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditMetaTags($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_META_TAGS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewUrlRewriting($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_URL_REWRITING, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditUrlRewriting($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_URL_REWRITING, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSpecialPrice($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_SPECIAL_PRICE, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSpecialPrice($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_SPECIAL_PRICE, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewVolumeDiscount($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_VOLUME_DISCOUNT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditVolumeDiscount($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_VOLUME_DISCOUNT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewBuyTogetherProducts($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_BUY_TOGETHER_PRODUCTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditBuyTogetherProducts($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_BUY_TOGETHER_PRODUCTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewRelatedProducts($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_RELATED_PRODUCTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditRelatedProducts($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_RELATED_PRODUCTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSales($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_SALES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSales($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_SALES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewCancellationRequests($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_CANCELLATION_REQUESTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditCancellationRequests($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_CANCELLATION_REQUESTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewReturnRequests($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_RETURN_REQUESTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditReturnRequests($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_RETURN_REQUESTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewTaxCategory($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_TAX_CATEGORY, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditTaxCategory($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_TAX_CATEGORY, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewShippingProfiles($sellerId = 0, $returnResult = false)
    {
        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0) == applicationConstants::YES) {
            return $this->returnFalseOrDie($returnResult);
        }
        return $this->checkPermission($sellerId, static::SECTION_SHIPPING_PROFILE, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditShippingProfiles($sellerId = 0, $returnResult = false)
    {
        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0) == applicationConstants::YES) {
            return $this->returnFalseOrDie($returnResult);
        }
        return $this->checkPermission($sellerId, static::SECTION_SHIPPING_PROFILE, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewShippingPackages($sellerId = 0, $returnResult = false)
    {
        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0) == applicationConstants::YES) {
            return $this->returnFalseOrDie($returnResult);
        }
        return $this->checkPermission($sellerId, static::SECTION_SHIPPING_PACKAGES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditShippingPackages($sellerId = 0, $returnResult = false)
    {
        if (FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0) == applicationConstants::YES) {
            return $this->returnFalseOrDie($returnResult);
        }
        return $this->checkPermission($sellerId, static::SECTION_SHIPPING_PACKAGES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewProductOptions($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_PRODUCT_OPTIONS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditProductOptions($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_PRODUCT_OPTIONS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSocialPlatforms($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_SOCIAL_PLATFORMS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSocialPlatforms($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_SOCIAL_PLATFORMS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewMessages($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_MESSAGES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditMessages($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_MESSAGES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSubscription($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_SUBSCRIPTION, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSubscription($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_SUBSCRIPTION, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewCredits($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_CREDITS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditCredits($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_CREDITS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSalesReport($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_SALES_REPORT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSalesReport($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_SALES_REPORT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewPerformanceReport($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_PERFORMANCE_REPORT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditPerformanceReport($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_PERFORMANCE_REPORT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewInventoryReport($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_INVENTORY_REPORT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditInventoryReport($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_INVENTORY_REPORT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSellerDashboard($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_SELLER_DASHBOARD, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSellerDashboard($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_SELLER_DASHBOARD, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSellerPermissions($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_SELLER_PERMISSIONS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSellerPermissions($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_SELLER_PERMISSIONS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewPromotions($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_PROMOTIONS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditPromotions($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_PROMOTIONS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canUploadBulkImages($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_UPLOAD_BULK_IMAGES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSellerRequests($sellerId = 0, $returnResult = false)
    {
        if (!((FatApp::getConfig('CONF_SELLER_CAN_REQUEST_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0)) || (FatApp::getConfig('CONF_BRAND_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)) || (FatApp::getConfig('CONF_PRODUCT_CATEGORY_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)))) {
            return $this->returnFalseOrDie($returnResult);
        }
        return $this->checkPermission($sellerId, static::SECTION_SELLER_REQUESTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSellerRequests($sellerId = 0, $returnResult = false)
    {
        if (!((FatApp::getConfig('CONF_SELLER_CAN_REQUEST_CUSTOM_PRODUCT', FatUtility::VAR_INT, 0)) || (FatApp::getConfig('CONF_BRAND_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)) || (FatApp::getConfig('CONF_PRODUCT_CATEGORY_REQUEST_APPROVAL', FatUtility::VAR_INT, 0)))) {
            return $this->returnFalseOrDie($returnResult);
        }
        return $this->checkPermission($sellerId, static::SECTION_SELLER_REQUESTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewAdvertisementFeed($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_ADVERTISEMENT_FEED, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditAdvertisementFeed($sellerId = 0, $returnResult = false)
    {
        return $this->checkPermission($sellerId, static::SECTION_ADVERTISEMENT_FEED, static::PRIVILEGE_WRITE, $returnResult);
    }
}
