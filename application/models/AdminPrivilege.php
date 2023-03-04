<?php

class AdminPrivilege
{
    public const SECTION_PRODUCT_CATEGORIES = 1;
    public const SECTION_PRODUCTS = 2;
    public const SECTION_BRANDS = 3;
    public const SECTION_FILTER_GROUPS = 4;
    public const SECTION_FILTERS = 5;
    public const SECTION_ATTRIBUTES = 6;
    public const SECTION_TAGS = 7;
    public const SECTION_OPTIONS = 8;
    public const SECTION_EXTRA_ATTRIBUTES = 9;
    public const SECTION_GENERAL_SETTINGS = 10;
    public const SECTION_USERS = 11;
    public const SECTION_SUPPLIER_APPROVAL_FORM = 12;
    public const SECTION_SUPPLIER_APPROVAL_REQUESTS = 13;
    public const SECTION_SHOPS = 14;
    public const SECTION_PAYMENT_METHODS = 15;
    public const SECTION_CONTENT_BLOCKS = 16;
    public const SECTION_SHIPPING_DURATIONS = 17;
    public const SECTION_MANUAL_SHIPPING_API = 18;
    public const SECTION_LANGUAGE_LABELS = 19;
    public const SECTION_CURRENCY_MANAGEMENT = 20;
    public const SECTION_CONTENT_PAGES = 21;
    public const SECTION_EMPTY_CART_ITEMS_MANAGEMENT = 22;
    public const SECTION_NAVIGATION_MANAGEMENT = 23;
    public const SECTION_CATALOG_REQUESTS = 24;
    public const SECTION_SHIPPING_APIS = 25;
    public const SECTION_COMMISSION = 26;
    public const SECTION_ORDERS = 27;
    public const SECTION_VENDOR_ORDERS = 28;
    public const SECTION_WITHDRAW_REQUESTS = 29;
    public const SECTION_ORDER_CANCELLATION_REQUESTS = 30;
    public const SECTION_ORDER_RETURN_REQUESTS = 31;
    public const SECTION_TAX = 32;
    public const SECTION_SLIDES = 33;
    public const SECTION_COUNTRIES = 34;
    public const SECTION_ZONES = 35;
    public const SECTION_STATES = 36;
    public const SECTION_EMAIL_TEMPLATES = 37;
    public const SECTION_ADMIN_USERS = 38;
    public const SECTION_BANNERS = 39;
    public const SECTION_SOCIALPLATFORM = 40;
    public const SECTION_COLLECTIONS = 41;
    public const SECTION_HOME_PAGE_ELEMENTS = 42;
    public const SECTION_SHOP_REPORT_REASONS = 43;
    public const SECTION_SHOP_REPORTS = 44;
    public const SECTION_ORDER_CANCEL_REASONS = 45;
    public const SECTION_ORDER_RETURN_REASONS = 46;
    public const SECTION_META_TAGS = 47;
    public const SECTION_ADMIN_DASHBOARD = 48;
    public const SECTION_FAQ_CATEGORY = 49;
    public const SECTION_FAQ = 50;
    public const SECTION_URL_REWRITE = 51;
    public const SECTION_TESTIMONIAL = 52;
    public const SECTION_SUCCESS_STORIES = 53;
    public const SMART_RECOMENDED_WEIGHTAGES = 54;
    public const SMART_PRODUCT_TAG_PRODUCTS = 55;
    public const SECTION_ADMIN_PERMISSIONS = 56;
    public const SECTION_BLOG_POST_CATEGORIES = 57;
    public const SECTION_BLOG_POSTS = 58;
    public const SECTION_DISCOUNT_COUPONS = 59;
    public const SECTION_BLOG_CONTRIBUTIONS = 60;
    public const SECTION_BLOG_COMMENTS = 61;
    public const SECTION_SELLER_PRODUCTS = 62;
    public const SECTION_PRODUCT_REVIEWS = 63;
    public const SECTION_ABUSIVE_WORDS = 64;
    public const SECTION_QUESTION_BANKS = 65;
    public const SECTION_MESSAGES = 66;
    public const SECTION_POLLING = 67;
    public const SECTION_QUESTIONS = 68;
    public const SECTION_QUESTIONNAIRES = 69;
    public const SECTION_SALES_REPORT = 70;
    public const SECTION_USERS_REPORT = 71;
    public const SECTION_PRODUCTS_REPORT = 72;
    public const SECTION_SHOPS_REPORT = 73;
    public const SECTION_TAX_REPORT = 74;
    public const SECTION_COMMISSION_REPORT = 75;
    public const SECTION_CATALOG_REPORT = 76;
    public const SECTION_PERFORMANCE_REPORT = 77;
    public const SECTION_POLICY_POINTS = 78;
    public const SECTION_SELLER_PACKAGES = 79;
    public const SECTION_SELLER_DISCOUNT_COUPONS = 80;
    public const SECTION_TOOLS = 81;
    public const SECTION_THEME_COLOR = 82;
    public const SECTION_SUBSCRIPTION_ORDERS = 83;
    public const SECTION_AFFILIATE_COMMISSION = 84;
    public const SECTION_PROMOTIONS = 85;
    public const SECTION_AFFILIATES_REPORT = 86;
    public const SECTION_ADVERTISERS_REPORT = 87;
    public const SECTION_BRAND_REQUESTS = 88;
    public const SECTION_SHIPPING_COMPANY_USERS = 89;
    public const SECTION_REWARDS_ON_PURCHASE = 90;
    public const SECTION_LANGUAGE = 91;
    public const SECTION_ORDER_STATUS = 92;
    public const SECTION_NOTIFICATION = 93;
    public const SECTION_TOOLTIP = 94;
    public const SECTION_CUSTOM_PRODUCT_REQUESTS = 95;
    public const SECTION_CUSTOM_CATALOG_PRODUCT_REQUESTS = 96;
    public const SECTION_DATABASE_BACKUP = 96;
    public const SECTION_USER_REQUESTS = 97;
    public const SECTION_PRODUCT_TEMP_IMAGES = 98;
    public const SECTION_IMPORT_INSTRUCTIONS = 99;
    public const SECTION_UPLOAD_BULK_IMAGES = 100;
    public const SECTION_SITEMAP = 101;
    public const SECTION_PLUGINS = 102;
    public const SECTION_ABANDONED_CART = 103;
    public const SECTION_PUSH_NOTIFICATION = 104;
    public const SECTION_PRODUCT_ADVERTISEMENT = 105;
    public const SECTION_IMPORT_EXPORT = 106;
    public const SECTION_APP_THEME_SETTINGS = 107;
    public const SECTION_PATCH_UPDATE = 109;
    public const SECTION_SMS_TEMPLATE = 108;
    public const SECTION_SHIPPING_PACKAGES = 109;
    public const SECTION_SHIPPING_MANAGEMENT = 110;
    public const SECTION_IMAGE_ATTRIBUTES = 111;
    public const SECTION_PICKUP_ADDRESSES = 112;

    public const PRIVILEGE_NONE = 0;
    public const PRIVILEGE_READ = 1;
    public const PRIVILEGE_WRITE = 2;

    private static $instance = null;
    private $loadedPermissions = array();

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function isAdminSuperAdmin($adminId)
    {
        return (1 == $adminId);
    }

    public static function getPermissionArr()
    {
        $arr = array(
            static::PRIVILEGE_NONE => Labels::getLabel('MSG_None', CommonHelper::getLangId()),
            static::PRIVILEGE_READ => Labels::getLabel('MSG_Read_Only', CommonHelper::getLangId()),
            static::PRIVILEGE_WRITE => Labels::getLabel('MSG_Read_and_Write', CommonHelper::getLangId())
        );
        return $arr;
    }

    public static function getPermissionModulesArr()
    {
        $arr = array(
            static::SECTION_ADMIN_DASHBOARD => Labels::getLabel('MSG_Admin_Dashboard', CommonHelper::getLangId()),
            static::SECTION_SHOPS => Labels::getLabel('MSG_Shops', CommonHelper::getLangId()),
            static::SECTION_PRODUCT_CATEGORIES => Labels::getLabel('MSG_Product_Categories', CommonHelper::getLangId()),
            static::SECTION_PRODUCTS => Labels::getLabel('MSG_Products', CommonHelper::getLangId()),
            static::SECTION_SELLER_PRODUCTS => Labels::getLabel('MSG_Seller_Products', CommonHelper::getLangId()),
            static::SECTION_PRODUCT_REVIEWS => Labels::getLabel('MSG_Product_Reviews', CommonHelper::getLangId()),
            static::SECTION_BRANDS => Labels::getLabel('MSG_Brands', CommonHelper::getLangId()),
            static::SECTION_OPTIONS => Labels::getLabel('MSG_Options', CommonHelper::getLangId()),
            static::SECTION_TAGS => Labels::getLabel('MSG_Tags', CommonHelper::getLangId()),
            static::SECTION_BRAND_REQUESTS => Labels::getLabel('MSG_Brand_Requests', CommonHelper::getLangId()),
            static::SECTION_ATTRIBUTES => Labels::getLabel('MSG_Attributes', CommonHelper::getLangId()),

            static::SECTION_USERS => Labels::getLabel('MSG_Users', CommonHelper::getLangId()),
            static::SECTION_SHIPPING_COMPANY_USERS => Labels::getLabel('MSG_Shipping_Company_Users', CommonHelper::getLangId()),
            static::SECTION_SUPPLIER_APPROVAL_FORM => Labels::getLabel('MSG_Seller_Approval_Form', CommonHelper::getLangId()),
            static::SECTION_SUPPLIER_APPROVAL_REQUESTS => Labels::getLabel('MSG_Seller_Approval_Requests', CommonHelper::getLangId()),
            static::SECTION_CATALOG_REQUESTS => Labels::getLabel('MSG_Catalog_Requests', CommonHelper::getLangId()),
            static::SECTION_CUSTOM_PRODUCT_REQUESTS => Labels::getLabel('MSG_Custom_Catalog_Requests', CommonHelper::getLangId()),
            static::SECTION_CUSTOM_CATALOG_PRODUCT_REQUESTS => Labels::getLabel('MSG_Custom_Catalog_Product_Requests', CommonHelper::getLangId()),

            static::SECTION_CONTENT_PAGES => Labels::getLabel('MSG_Content_Pages', CommonHelper::getLangId()),
            static::SECTION_CONTENT_BLOCKS => Labels::getLabel('MSG_Content_Blocks', CommonHelper::getLangId()),
            static::SECTION_NAVIGATION_MANAGEMENT => Labels::getLabel('MSG_Navigation_Management', CommonHelper::getLangId()),
            static::SECTION_COUNTRIES => Labels::getLabel('MSG_Countries', CommonHelper::getLangId()),
            /* static::SECTION_ZONES => Labels::getLabel('MSG_Zones',CommonHelper::getLangId()), */
            static::SECTION_STATES => Labels::getLabel('MSG_States', CommonHelper::getLangId()),
            static::SECTION_COLLECTIONS => Labels::getLabel('MSG_Collections', CommonHelper::getLangId()),
            static::SECTION_EMPTY_CART_ITEMS_MANAGEMENT => Labels::getLabel('MSG_Empty_Cart_Management', CommonHelper::getLangId()),
            static::SECTION_SOCIALPLATFORM => Labels::getLabel('MSG_Social_Platform', CommonHelper::getLangId()),
            static::SECTION_SHOP_REPORT_REASONS => Labels::getLabel('MSG_Shop_Report_Reasons', CommonHelper::getLangId()),
            static::SECTION_ORDER_CANCEL_REASONS => Labels::getLabel('MSG_Order_Cancel_Reasons', CommonHelper::getLangId()),
            static::SECTION_ORDER_RETURN_REASONS => Labels::getLabel('MSG_Order_Return_Reasons', CommonHelper::getLangId()),
            static::SECTION_TESTIMONIAL => Labels::getLabel('MSG_Testimonial', CommonHelper::getLangId()),
            static::SECTION_DISCOUNT_COUPONS => Labels::getLabel('MSG_Discount_Coupons', CommonHelper::getLangId()),
            static::SECTION_LANGUAGE_LABELS => Labels::getLabel('MSG_Language_Labels', CommonHelper::getLangId()),
            static::SECTION_SLIDES => Labels::getLabel('MSG_Home_Page_Slide_Management', CommonHelper::getLangId()),
            static::SECTION_BANNERS => Labels::getLabel('MSG_Banners', CommonHelper::getLangId()),

            static::SECTION_SHIPPING_APIS => Labels::getLabel('MSG_Shipping_Api_Methods', CommonHelper::getLangId()),
            static::SECTION_SHIPPING_DURATIONS => Labels::getLabel('MSG_Shipping_Durations', CommonHelper::getLangId()),
            /* static::SECTION_MANUAL_SHIPPING_API => Labels::getLabel('MSG_Manual_Shipping_Api',CommonHelper::getLangId()), */

            static::SECTION_GENERAL_SETTINGS => Labels::getLabel('MSG_General_Settings', CommonHelper::getLangId()),
            static::SECTION_PAYMENT_METHODS => Labels::getLabel('MSG_Payment_Methods', CommonHelper::getLangId()),
            static::SECTION_CURRENCY_MANAGEMENT => Labels::getLabel('MSG_Currency_Management', CommonHelper::getLangId()),
            static::SECTION_TAX => Labels::getLabel('MSG_Tax', CommonHelper::getLangId()),
            static::SECTION_COMMISSION => Labels::getLabel('MSG_Commission', CommonHelper::getLangId()),
            static::SECTION_AFFILIATE_COMMISSION => Labels::getLabel('MSG_Affiliate_Commission', CommonHelper::getLangId()),
            static::SECTION_EMAIL_TEMPLATES => Labels::getLabel('MSG_Email_Templates', CommonHelper::getLangId()),
            static::SECTION_POLICY_POINTS => Labels::getLabel('MSG_Policy_Points', CommonHelper::getLangId()),
            static::SECTION_SELLER_PACKAGES => Labels::getLabel('MSG_Seller_Packages', CommonHelper::getLangId()),
            static::SECTION_REWARDS_ON_PURCHASE => Labels::getLabel('MSG_Rewards_on_purchase', CommonHelper::getLangId()),

            static::SECTION_ORDERS => Labels::getLabel('MSG_Orders', CommonHelper::getLangId()),
            static::SECTION_VENDOR_ORDERS => Labels::getLabel('MSG_Seller_Orders', CommonHelper::getLangId()),
            static::SECTION_WITHDRAW_REQUESTS => Labels::getLabel('MSG_Withdraw_Requests', CommonHelper::getLangId()),
            static::SECTION_ORDER_CANCELLATION_REQUESTS => Labels::getLabel('MSG_Order_Cancellation_Requests', CommonHelper::getLangId()),
            static::SECTION_ORDER_RETURN_REQUESTS => Labels::getLabel('MSG_Order_Return_Requests', CommonHelper::getLangId()),

            static::SMART_RECOMENDED_WEIGHTAGES => Labels::getLabel('MSG_Recommended_Weightages', CommonHelper::getLangId()),
            static::SMART_PRODUCT_TAG_PRODUCTS => Labels::getLabel('MSG_Recommended_Tag_Products', CommonHelper::getLangId()),

            static::SECTION_PROMOTIONS => Labels::getLabel('MSG_Promotions', CommonHelper::getLangId()),

            static::SECTION_META_TAGS => Labels::getLabel('MSG_Meta_Tags', CommonHelper::getLangId()),
            static::SECTION_FAQ_CATEGORY => Labels::getLabel('MSG_Faq_Category', CommonHelper::getLangId()),
            static::SECTION_FAQ => Labels::getLabel('MSG_Faq', CommonHelper::getLangId()),
            static::SECTION_URL_REWRITE => Labels::getLabel('MSG_Url_Rewriting', CommonHelper::getLangId()),
            static::SECTION_IMAGE_ATTRIBUTES => Labels::getLabel('MSG_Image_Attributes', CommonHelper::getLangId()),

            static::SECTION_BLOG_POST_CATEGORIES => Labels::getLabel('MSG_Blog_Categories', CommonHelper::getLangId()),
            static::SECTION_BLOG_POSTS => Labels::getLabel('MSG_Blog_Posts', CommonHelper::getLangId()),
            static::SECTION_BLOG_CONTRIBUTIONS => Labels::getLabel('MSG_Blog_Contributions', CommonHelper::getLangId()),
            static::SECTION_BLOG_COMMENTS => Labels::getLabel('MSG_Blog_Comments', CommonHelper::getLangId()),

            static::SECTION_SHOP_REPORTS => Labels::getLabel('MSG_Shop_Reports', CommonHelper::getLangId()),
            static::SECTION_SHOPS_REPORT => Labels::getLabel('MSG_Shops_Report', CommonHelper::getLangId()),
            static::SECTION_SALES_REPORT => Labels::getLabel('MSG_Sales_Report', CommonHelper::getLangId()),
            static::SECTION_USERS_REPORT => Labels::getLabel('MSG_Users_Report', CommonHelper::getLangId()),
            static::SECTION_PRODUCTS_REPORT => Labels::getLabel('MSG_Products_Report', CommonHelper::getLangId()),
            static::SECTION_TAX_REPORT => Labels::getLabel('MSG_Tax_Report', CommonHelper::getLangId()),
            static::SECTION_COMMISSION_REPORT => Labels::getLabel('MSG_Commission_Report', CommonHelper::getLangId()),
            static::SECTION_CATALOG_REPORT => Labels::getLabel('MSG_Catalog_Report', CommonHelper::getLangId()),
            static::SECTION_PERFORMANCE_REPORT => Labels::getLabel('MSG_Proformance_Report', CommonHelper::getLangId()),
            static::SECTION_AFFILIATES_REPORT => Labels::getLabel('MSG_Affiliate_Report', CommonHelper::getLangId()),
            static::SECTION_ADVERTISERS_REPORT => Labels::getLabel('MSG_Advertiser_Report', CommonHelper::getLangId()),
            /* static::SECTION_SELLER_DISCOUNT_COUPONS => Labels::getLabel('MSG_Seller_Discount_Coupons',CommonHelper::getLangId()), */
            static::SECTION_THEME_COLOR => Labels::getLabel('MSG_Theme_Color', CommonHelper::getLangId()),

            static::SECTION_ADMIN_USERS => Labels::getLabel('MSG_Admin_Users', CommonHelper::getLangId()),
            static::SECTION_ADMIN_PERMISSIONS => Labels::getLabel('MSG_Admin_Roles', CommonHelper::getLangId()),

            //static::SECTION_TOOLS => Labels::getLabel('MSG_Tools', CommonHelper::getLangId()),
            static::SECTION_MESSAGES => Labels::getLabel('MSG_Messages', CommonHelper::getLangId()),
            // static::SECTION_NOTIFICATION => Labels::getLabel('MSG_Notifications',CommonHelper::getLangId()),
            static::SECTION_DATABASE_BACKUP => Labels::getLabel('MSG_Database_Backup', CommonHelper::getLangId()),
            static::SECTION_ORDER_STATUS => Labels::getLabel('MSG_Order_Status_Management', CommonHelper::getLangId()),
            static::SECTION_USER_REQUESTS => Labels::getLabel('MSG_User_Requests', CommonHelper::getLangId()),
            static::SECTION_PRODUCT_TEMP_IMAGES => Labels::getLabel('MSG_Products_Temp_Images', CommonHelper::getLangId()),
            static::SECTION_IMPORT_INSTRUCTIONS => Labels::getLabel('MSG_Import_Instructions', CommonHelper::getLangId()),
            static::SECTION_UPLOAD_BULK_IMAGES => Labels::getLabel('MSG_Bulk_Upload', CommonHelper::getLangId()),
            static::SECTION_SITEMAP => Labels::getLabel('MSG_SITEMAP', CommonHelper::getLangId()),
            static::SECTION_PUSH_NOTIFICATION => Labels::getLabel('MSG_PUSH_NOTIFICATION', CommonHelper::getLangId()),
            static::SECTION_PRODUCT_ADVERTISEMENT => Labels::getLabel('MSG_PRODUCT_ADVERTISEMENT', CommonHelper::getLangId()),
            static::SECTION_PLUGINS => Labels::getLabel('MSG_Plugins', CommonHelper::getLangId()),
            static::SECTION_APP_THEME_SETTINGS => Labels::getLabel('MSG_APP_THEME_SETTINGS', CommonHelper::getLangId()),
            static::SECTION_ABANDONED_CART => Labels::getLabel('MSG_ABANDONED_CART', CommonHelper::getLangId()),
            static::SECTION_IMPORT_EXPORT => Labels::getLabel('MSG_IMPORT_EXPORT', CommonHelper::getLangId()),
            static::SECTION_SMS_TEMPLATE => Labels::getLabel('MSG_SMS_TEMPLATE', CommonHelper::getLangId()),

            static::SECTION_ABUSIVE_WORDS => Labels::getLabel('MSG_Abusive_Words', CommonHelper::getLangId()),
            static::SECTION_SUBSCRIPTION_ORDERS => Labels::getLabel('MSG_Subscription_Orders', CommonHelper::getLangId()),

            static::SECTION_PICKUP_ADDRESSES => Labels::getLabel('MSG_Pickup_Addresses', CommonHelper::getLangId()),


            /* static::SECTION_Languages => Labels::getLabel('MSG_Languages',CommonHelper::getLangId()),
        static::SECTION_Languages => Labels::getLabel('MSG_Order_Status',CommonHelper::getLangId()), */

            /*static::SECTION_SUCCESS_STORIES => Labels::getLabel('MSG_Success_stories',CommonHelper::getLangId()),
        static::SECTION_HOME_PAGE_ELEMENTS => Labels::getLabel('MSG_Home_Page_Elements',CommonHelper::getLangId()),
        static::SECTION_QUESTION_BANKS => Labels::getLabel('MSG_Question_Banks',CommonHelper::getLangId()),

        static::SECTION_QUESTIONS => Labels::getLabel('MSG_Questions',CommonHelper::getLangId()),
        static::SECTION_QUESTIONNAIRES => Labels::getLabel('MSG_Questionnaires',CommonHelper::getLangId()), */

            /* static::SECTION_POLLING => Labels::getLabel('MSG_Polling',CommonHelper::getLangId()),
        static::SECTION_FILTER_GROUPS => 'Filter Groups',
        static::SECTION_FILTERS => 'Filters',
        static::SECTION_EXTRA_ATTRIBUTES => 'Extra Attributes',	 */
        );
        return $arr;
    }

    public static function getWriteOnlyPermissionModulesArr()
    {
        return array(
            static::SECTION_UPLOAD_BULK_IMAGES,
        );
    }

    public static function getAdminPermissionLevel($adminId, $sectionId = 0)
    {
        $db = FatApp::getDb();
        $adminId = FatUtility::int($adminId);

        /* Are you looking for permissions of administrator [ */
        if (static::isAdminSuperAdmin($adminId)) {
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

        $srch = new SearchBase('tbl_admin_permissions');
        $srch->addCondition('admperm_admin_id', '=', $adminId);
        if (0 < $sectionId) {
            $srch->addCondition('admperm_section_id', '=', $sectionId);
        }

        $srch->addMultipleFields(array('admperm_section_id', 'admperm_value'));
        $rs = $srch->getResultSet();
        $arr = $db->fetchAllAssoc($rs);

        return $arr;
    }

    private function cacheLoadedPermission($adminId, $secId, $level)
    {
        if (!isset($this->loadedPermissions[$adminId])) {
            $this->loadedPermissions[$adminId] = array();
        }
        $this->loadedPermissions[$adminId][$secId] = $level;
    }

    private function checkPermission($adminId, $secId, $level, $returnResult = false)
    {
        $db = FatApp::getDb();

        if (!in_array($level, array(1, 2))) {
            trigger_error(Labels::getLabel('MSG_Invalid_permission_level_checked', CommonHelper::getLangId()) . ' ' . $level, E_USER_ERROR);
        }

        $adminId = FatUtility::convertToType($adminId, FatUtility::VAR_INT);
        if (0 == $adminId) {
            $adminId = AdminAuthentication::getLoggedAdminId();
        }

        if (isset($this->loadedPermissions[$adminId][$secId])) {
            if ($level <= $this->loadedPermissions[$adminId][$secId]) {
                return true;
            }
            return $this->returnFalseOrDie($returnResult);
        }

        if ($this->isAdminSuperAdmin($adminId)) {
            return true;
        }

        $row_admin = Admin::getAttributesById($adminId, array('admin_active'));
        if (empty($row_admin)) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', CommonHelper::getLangId()));
        }
        if ($row_admin['admin_active'] != applicationConstants::ACTIVE) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', CommonHelper::getLangId()));
        }

        $rs = $db->query(
            "SELECT admperm_value FROM tbl_admin_permissions WHERE
				admperm_admin_id = " . $adminId . " AND admperm_section_id = " . $secId
        );
        if (!$row = $db->fetch($rs)) {
            $this->cacheLoadedPermission($adminId, $secId, static::PRIVILEGE_NONE);
            return $this->returnFalseOrDie($returnResult);
        }

        $permissionLevel = $row['admperm_value'];

        $this->cacheLoadedPermission($adminId, $secId, $permissionLevel);

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
        Message::addErrorMessage(Labels::getLabel('MSG_Unauthorized_Access!', CommonHelper::getLangId()));
        if ($msg == '') {
            $msg = Message::getHtml();
        }
        FatUtility::dieWithError($msg);
    }

    public function clearPermissionCache($adminId)
    {
        if (isset($this->loadedPermissions[$adminId])) {
            unset($this->loadedPermissions[$adminId]);
        }
    }

    public function canViewProductCategories($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PRODUCT_CATEGORIES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditProductCategories($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PRODUCT_CATEGORIES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewProducts($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PRODUCTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditProducts($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PRODUCTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewBrands($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_BRANDS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditBrands($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_BRANDS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewFilterGroups($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_FILTER_GROUPS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditFilterGroups($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_FILTER_GROUPS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewFilters($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_FILTERS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditFilters($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_FILTERS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewTags($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_TAGS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditTags($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_TAGS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewOptions($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_OPTIONS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditOptions($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_OPTIONS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewAttributes($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ATTRIBUTES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditAttributes($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ATTRIBUTES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewExtraAttributes($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_EXTRA_ATTRIBUTES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditExtraAttributes($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_EXTRA_ATTRIBUTES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewGeneralSettings($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_GENERAL_SETTINGS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditGeneralSettings($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_GENERAL_SETTINGS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewUsers($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_USERS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditUsers($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_USERS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canVerifyUsers($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_USERS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSellerApprovalForm($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SUPPLIER_APPROVAL_FORM, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSellerApprovalForm($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SUPPLIER_APPROVAL_FORM, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSellerApprovalRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SUPPLIER_APPROVAL_REQUESTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSellerApprovalRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SUPPLIER_APPROVAL_REQUESTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewShops($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHOPS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditShops($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHOPS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewPaymentMethods($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PAYMENT_METHODS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditPaymentMethods($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PAYMENT_METHODS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewContentBlocks($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_CONTENT_BLOCKS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditContentBlocks($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_CONTENT_BLOCKS, static::PRIVILEGE_WRITE, $returnResult);
    }
    public function canViewShippingDurationLabels($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHIPPING_DURATIONS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditShippingDurationLabels($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHIPPING_DURATIONS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewManualShippingApi($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_MANUAL_SHIPPING_API, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditManualShippingApi($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_MANUAL_SHIPPING_API, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewLanguageLabels($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_LANGUAGE_LABELS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditLanguageLabels($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_LANGUAGE_LABELS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewCurrencyManagement($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_CURRENCY_MANAGEMENT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditCurrencyManagement($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_CURRENCY_MANAGEMENT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewContentPages($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_CONTENT_PAGES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditContentPages($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_CONTENT_PAGES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewEmptyCartItems($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_EMPTY_CART_ITEMS_MANAGEMENT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditEmptyCartItems($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_EMPTY_CART_ITEMS_MANAGEMENT, static::PRIVILEGE_WRITE, $returnResult);
    }
    public function canViewNavigationManagement($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_NAVIGATION_MANAGEMENT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditNavigationManagement($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_NAVIGATION_MANAGEMENT, static::PRIVILEGE_WRITE, $returnResult);
    }
    public function canViewSellerCatalogRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_CATALOG_REQUESTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSellerCatalogRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_CATALOG_REQUESTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewShippingMethods($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHIPPING_APIS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditShippingMethods($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHIPPING_APIS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewShippingCompanies($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHIPPING_APIS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditShippingCompanies($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHIPPING_APIS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewCommissionSettings($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_COMMISSION, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditCommissionSettings($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_COMMISSION, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewOrders($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ORDERS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditOrders($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ORDERS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSellerOrders($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_VENDOR_ORDERS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSellerOrders($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_VENDOR_ORDERS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewWithdrawRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_WITHDRAW_REQUESTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditWithdrawRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_WITHDRAW_REQUESTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewOrderCancellationRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ORDER_CANCELLATION_REQUESTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditOrderCancellationRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ORDER_CANCELLATION_REQUESTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewOrderReturnRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ORDER_RETURN_REQUESTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditOrderReturnRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ORDER_RETURN_REQUESTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewTax($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_TAX, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditTax($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_TAX, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSlides($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SLIDES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSlides($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SLIDES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewCountries($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_COUNTRIES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditCountries($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_COUNTRIES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewZones($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ZONES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditZones($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ZONES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewStates($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_STATES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditStates($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_STATES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewEmailTemplates($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_EMAIL_TEMPLATES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditEmailTemplates($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_EMAIL_TEMPLATES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewAdminUsers($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ADMIN_USERS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditAdminUsers($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ADMIN_USERS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewBanners($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_BANNERS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditBanners($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_BANNERS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSocialPlatforms($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SOCIALPLATFORM, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSocialPlatforms($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SOCIALPLATFORM, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewCollections($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_COLLECTIONS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditCollections($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_COLLECTIONS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewHomePageElements($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_HOME_PAGE_ELEMENTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditHomePageElements($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_HOME_PAGE_ELEMENTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewShopReportReasons($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHOP_REPORT_REASONS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditShopReportReasons($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHOP_REPORT_REASONS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewShopReports($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHOP_REPORTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditShopReports($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHOP_REPORTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewOrderCancelReasons($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ORDER_CANCEL_REASONS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditOrderCancelReasons($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ORDER_CANCEL_REASONS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewOrderReturnReasons($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ORDER_RETURN_REASONS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditOrderReturnReasons($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ORDER_RETURN_REASONS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewMetaTags($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_META_TAGS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditMetaTags($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_META_TAGS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewAdminDashboard($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ADMIN_DASHBOARD, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditAdminDashboard($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ADMIN_DASHBOARD, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewFaqCategories($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_FAQ_CATEGORY, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditFaqCategories($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_FAQ_CATEGORY, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewFaq($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_FAQ, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditFaq($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_FAQ, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewUrlRewrite($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_URL_REWRITE, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditUrlRewrite($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_URL_REWRITE, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewImageAttributes($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_IMAGE_ATTRIBUTES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditImageAttributes($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_IMAGE_ATTRIBUTES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewTestimonial($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_TESTIMONIAL, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditTestimonial($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_TESTIMONIAL, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSuccessStories($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SUCCESS_STORIES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSuccessStories($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SUCCESS_STORIES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewRecomendedWeightages($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SMART_RECOMENDED_WEIGHTAGES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditRecomendedWeightages($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SMART_RECOMENDED_WEIGHTAGES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewRecomendedTagProducts($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SMART_PRODUCT_TAG_PRODUCTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditRecomendedTagProducts($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SMART_PRODUCT_TAG_PRODUCTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewAdminPermissions($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ADMIN_PERMISSIONS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditAdminPermissions($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ADMIN_PERMISSIONS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewBlogPostCategories($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_BLOG_POST_CATEGORIES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditBlogPostCategories($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_BLOG_POST_CATEGORIES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewBlogPosts($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_BLOG_POSTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditBlogPosts($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_BLOG_POSTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewDiscountCoupons($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_DISCOUNT_COUPONS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditDiscountCoupons($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_DISCOUNT_COUPONS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewBlogContributions($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_BLOG_CONTRIBUTIONS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditBlogContributions($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_BLOG_CONTRIBUTIONS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewBlogComments($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_BLOG_COMMENTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditBlogComments($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_BLOG_COMMENTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSellerProducts($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SELLER_PRODUCTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSellerProducts($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SELLER_PRODUCTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewProductReviews($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PRODUCT_REVIEWS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditProductReviews($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PRODUCT_REVIEWS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewAbusiveWords($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ABUSIVE_WORDS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditAbusiveWords($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ABUSIVE_WORDS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewQuestionBanks($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_QUESTION_BANKS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditQuestionBanks($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_QUESTION_BANKS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewMessages($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_MESSAGES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditPolling($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_POLLING, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewPolling($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_POLLING, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditQuestions($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_QUESTIONS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewQuestions($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_QUESTIONS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditQuestionnaires($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_QUESTIONNAIRES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewQuestionnaires($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_QUESTIONNAIRES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSalesReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SALES_REPORT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSalesReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SALES_REPORT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditUsersReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_USERS_REPORT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewUsersReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_USERS_REPORT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditProductsReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PRODUCTS_REPORT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewProductsReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PRODUCTS_REPORT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditCatalogReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_CATALOG_REPORT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewCatalogReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_CATALOG_REPORT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditShopsReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHOPS_REPORT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewShopsReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHOPS_REPORT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditTaxReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_TAX_REPORT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewTaxReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_TAX_REPORT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditCommissionReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_COMMISSION_REPORT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewCommissionReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_COMMISSION_REPORT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditPerformanceReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PERFORMANCE_REPORT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewPerformanceReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PERFORMANCE_REPORT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditPolicyPoints($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_POLICY_POINTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewPolicyPoints($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_POLICY_POINTS, static::PRIVILEGE_READ, $returnResult);
    }
    public function canEditSellerPackages($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SELLER_PACKAGES, static::PRIVILEGE_WRITE, $returnResult);
    }
    public function canViewSellerPackages($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SELLER_PACKAGES, static::PRIVILEGE_READ, $returnResult);
    }
    public function canEditSellerDiscountCoupons($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SELLER_DISCOUNT_COUPONS, static::PRIVILEGE_WRITE, $returnResult);
    }
    public function canViewSellerDiscountCoupons($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SELLER_DISCOUNT_COUPONS, static::PRIVILEGE_READ, $returnResult);
    }
    public function canEditThemeColor($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_THEME_COLOR, static::PRIVILEGE_WRITE, $returnResult);
    }
    public function canViewThemeColor($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_THEME_COLOR, static::PRIVILEGE_READ, $returnResult);
    }
    public function canViewProductTempImages($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PRODUCT_TEMP_IMAGES, static::PRIVILEGE_READ, $returnResult);
    }
    public function canEditProductTempImages($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PRODUCT_TEMP_IMAGES, static::PRIVILEGE_WRITE, $returnResult);
    }
    public function canUploadBulkImages($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_UPLOAD_BULK_IMAGES, static::PRIVILEGE_WRITE, $returnResult);
    }
    public function canViewImportInstructions($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_IMPORT_INSTRUCTIONS, static::PRIVILEGE_READ, $returnResult);
    }
    public function canEditImportInstructions($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_IMPORT_INSTRUCTIONS, static::PRIVILEGE_WRITE, $returnResult);
    }
    public function canEditSubscriptionOrders($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SUBSCRIPTION_ORDERS, static::PRIVILEGE_WRITE, $returnResult);
    }
    public function canViewSubscriptionOrders($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SUBSCRIPTION_ORDERS, static::PRIVILEGE_READ, $returnResult);
    }
    public function canViewTools($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_TOOLS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canViewAffiliateCommissionSettings($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_AFFILIATE_COMMISSION, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditAffiliateCommissionSettings($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_AFFILIATE_COMMISSION, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewPromotions($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PROMOTIONS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditPromotions($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PROMOTIONS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewAffiliatesReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_AFFILIATES_REPORT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditAffiliatesReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_AFFILIATES_REPORT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewAdvertisersReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ADVERTISERS_REPORT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditAdvertisersReport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ADVERTISERS_REPORT, static::PRIVILEGE_WRITE, $returnResult);
    }
    public function canViewBrandRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_BRAND_REQUESTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditBrandRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_BRAND_REQUESTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewShippingCompanyUsers($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHIPPING_COMPANY_USERS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditShippingCompanyUsers($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHIPPING_COMPANY_USERS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewRewardsOnPurchase($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_REWARDS_ON_PURCHASE, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditRewardsOnPurchase($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_REWARDS_ON_PURCHASE, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewLanguage($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_LANGUAGE, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditLanguage($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_LANGUAGE, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewOrderStatus($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ORDER_STATUS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditOrderStatus($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ORDER_STATUS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewNotifications($adminId = 0, $returnResult = false)
    {
        // return $this->checkPermission($adminId, static::SECTION_NOTIFICATION, static::PRIVILEGE_READ, $returnResult);
        return true;
    }

    public function canEditNotifications($adminId = 0, $returnResult = false)
    {
        // return $this->checkPermission($adminId, static::SECTION_NOTIFICATION, static::PRIVILEGE_WRITE, $returnResult);
        return true;
    }

    public function canViewTooltip($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_TOOLTIP, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditTooltip($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_TOOLTIP, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewCustomProductRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_CUSTOM_PRODUCT_REQUESTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditCustomProductRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_CUSTOM_PRODUCT_REQUESTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewCustomCatalogProductRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_CUSTOM_CATALOG_PRODUCT_REQUESTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditCustomCatalogProductRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_CUSTOM_CATALOG_PRODUCT_REQUESTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewDatabaseBackupView($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_DATABASE_BACKUP, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditDatabaseBackupView($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_DATABASE_BACKUP, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewUserRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_USER_REQUESTS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditUserRequests($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_USER_REQUESTS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSitemap($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SITEMAP, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditSitemap($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SITEMAP, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewPushNotification($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PUSH_NOTIFICATION, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditPushNotification($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PUSH_NOTIFICATION, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewPlugins($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PLUGINS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditPlugins($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PLUGINS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewAbandonedCart($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ABANDONED_CART, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditAbandonedCart($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_ABANDONED_CART, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewAdvertisements($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PRODUCT_ADVERTISEMENT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditAdvertisements($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PRODUCT_ADVERTISEMENT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewAppThemeSettings($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_APP_THEME_SETTINGS, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditAppThemeSettings($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_APP_THEME_SETTINGS, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewImportExport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_IMPORT_EXPORT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditImportExport($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_IMPORT_EXPORT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewPatch($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PATCH_UPDATE, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditPatch($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PATCH_UPDATE, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canEditSmsTemplate($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SMS_TEMPLATE, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewSmsTemplate($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SMS_TEMPLATE, static::PRIVILEGE_READ, $returnResult);
    }

    public function canViewShippingPackages($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHIPPING_PACKAGES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditShippingPackages($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHIPPING_PACKAGES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewShippingManagement($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHIPPING_MANAGEMENT, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditShippingManagement($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_SHIPPING_MANAGEMENT, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewPickupAddresses($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PICKUP_ADDRESSES, static::PRIVILEGE_READ, $returnResult);
    }

    public function canEditPickupAddresses($adminId = 0, $returnResult = false)
    {
        return $this->checkPermission($adminId, static::SECTION_PICKUP_ADDRESSES, static::PRIVILEGE_WRITE, $returnResult);
    }

    public function canViewTrackingRelationCode()
    {
        $plugin = new Plugin();
        $shipApiPluginKey = $plugin->getDefaultPluginKeyName(Plugin::TYPE_SHIPPING_SERVICES);
        $trackingApiPluginKey = $plugin->getDefaultPluginKeyName(Plugin::TYPE_SHIPMENT_TRACKING);
        if (Plugin::isActive($shipApiPluginKey) === true && Plugin::isActive($trackingApiPluginKey) === true) {
            return true;
        }
        return false;
    }
}
