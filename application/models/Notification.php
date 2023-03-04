<?php

class Notification extends MyAppModel
{
    public const DB_TBL = 'tbl_notifications';
    public const DB_TBL_PREFIX = 'notification_';

    public const TYPE_USER = 1;
    public const TYPE_CATALOG = 3;
    public const TYPE_BRAND = 4;
    public const TYPE_ORDER = 5;
    public const TYPE_ORDER_CANCELATION = 6;
    public const TYPE_ORDER_PRODUCT = 7;
    public const TYPE_ORDER_RETURN_REQUEST = 8;
    public const TYPE_CATALOG_REQUEST = 9;
    public const TYPE_PRODUCT_REVIEW = 10;
    public const TYPE_WITHDRAWAL_REQUEST = 11;
    public const TYPE_WITHDRAW_RETURN_REQUEST = 12;
    public const TYPE_SHOP = 13;
    public const TYPE_PROMOTION = 14;
    public const TYPE_ADMIN = 15;
    public const TYPE_ORDER_RETURN_REQUEST_MESSAGE = 16;
    public const TYPE_BLOG = 17;
    public const TYPE_PRODUCT_CATEGORY = 18;

    public const NEW_USER_REGISTERATION_NOTIFICATION = 1;
    public const NEW_SUPPLIER_REGISTERATION_NOTIFICATION = 2;
    public const NEW_CATALOG_REQUEST_NOTIFICATION = 5;
    public const BRAND_REQUEST_NOTIFICATION = 7;
    public const NEW_ORDER_STATUS_NOTIFICATION = 8;
    public const ORDER_CANCELLATION_NOTIFICATION = 9;
    public const ORDER_RETURNED_NOTIFICATION = 10;
    public const ORDER_RETURNED_REQUEST_NOTIFICATION = 11;
    public const CATALOG_REQUEST_MESSAGE_NOTIFICATION = 12;
    public const NEW_SUBSCRIPTION_PURCHASE_NOTIFICATION = 16;
    public const ABUSIVE_REVIEW_POSTED_NOTIFICATION = 17;
    public const ORDER_RETURNED_REQUEST_MESSAGE_NOTIFICATION = 18;
    public const NEW_SUPPLIER_APPROVAL_NOTIFICATION = 19;
    public const NEW_SELLER_APPROVED_NOTIFICATION = 20;
    public const PROMOTION_APPROVAL_NOTIFICATION = 21;
    public const WITHDRAWL_REQUEST_NOTIFICATION = 22;
    public const REPORT_SHOP_NOTIFICATION = 23;
    public const ORDER_PAYMENT_STATUS_CHANGE_NOTIFICATION = 24;
    public const RETURN_REQUEST_MESSAGE_TO_USER_NOTIFICATION = 25;
    public const ORDER_EMAIL_NOTIFICATION = 26;
    public const NEW_CUSTOM_CATALOG_REQUEST_NOTIFICATION = 27;
    public const PRODUCT_REVIEW_NOTIFICATION = 28;
    public const BLOG_COMMENT_NOTIFICATION = 29;
    public const BLOG_CONTRIBUTION_NOTIFICATION = 30;
    public const PRODUCT_CATEGORY_REQUEST_NOTIFICATION = 31;
    public const ORDER_PAYMENT_TRANSFERRED_TO_BANK = 32;

    public const GUEST_AFFILIATE_REGISTERATION = 3;
    public const GUEST_ADVISER_REGISTERATION = 4;
    public const SUPPLIER_APPROVAL = 6;
    public const RETURN_REQUEST_STATUS_CHANGE_NOTIFICATION = 13;
    public const NOTIFICATION_ABUSIVE_WORD = 15;



    public function __construct($notificationId = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $notificationId);
    }

    public static function saveNotifications($notificationData)
    {
        $notificationObj = new Notification();
        $notificationObj->assignValues($notificationData);
        if (!$notificationObj->save()) {
            return false;
        }
        return true;
    }


    public static function getLabelKeyString($langId)
    {
        $brandRequestApproval = FatApp::getConfig('CONF_BRAND_REQUEST_APPROVAL');
        $brandRequestUrl = ($brandRequestApproval) ? 'brands/brand-requests' : 'brands';

        $labelArr = array(
        Notification::NEW_USER_REGISTERATION_NOTIFICATION => array(Labels::getLabel('LBL_user_registration_notification', $langId), 'users'),
        Notification::NEW_SUPPLIER_REGISTERATION_NOTIFICATION => array(Labels::getLabel('LBL_supplier_registration_notification', $langId), 'users'),
        Notification::GUEST_AFFILIATE_REGISTERATION => array(Labels::getLabel('LBL_guest_adviser_registration_notification', $langId), 'users'),
        Notification::GUEST_ADVISER_REGISTERATION => array(Labels::getLabel('LBL_user_order_placed_notification', $langId), 'orders'),
        Notification::NEW_CATALOG_REQUEST_NOTIFICATION => array(Labels::getLabel('LBL_Catalog_request_notification', $langId), 'products'),
        Notification::SUPPLIER_APPROVAL => array(Labels::getLabel('LBL_user_supplier_approval_notification', $langId), 'users/seller-approval-requests'),
        Notification::BRAND_REQUEST_NOTIFICATION => array(Labels::getLabel('LBL_seller_brand_request_notification', $langId), $brandRequestUrl),
        Notification::NEW_ORDER_STATUS_NOTIFICATION => array(Labels::getLabel('LBL_user_order_status_notification', $langId), 'orders'),
        Notification::ORDER_CANCELLATION_NOTIFICATION => array(Labels::getLabel('LBL_user_order_cancellation_notification', $langId), 'order-cancellation-requests'),
        Notification::ORDER_RETURNED_NOTIFICATION => array(Labels::getLabel('LBL_user_order_return_notification', $langId), 'order-return-requests'),
        Notification::ORDER_RETURNED_REQUEST_NOTIFICATION => array(Labels::getLabel('LBL_user_order_return_request_notification', $langId), 'order-return-requests'),
        Notification::CATALOG_REQUEST_MESSAGE_NOTIFICATION => array(Labels::getLabel('LBL_user_catalog_request_message_notification', $langId), 'custom-products'),
        Notification::RETURN_REQUEST_STATUS_CHANGE_NOTIFICATION => array(Labels::getLabel('LBL_buyer_return_request_status_change_notification', $langId), 'order-return-requests'),
        Notification::REPORT_SHOP_NOTIFICATION => array(Labels::getLabel('LBL_user_report_shop_notification', $langId), 'shop-report-reasons'),
        Notification::NOTIFICATION_ABUSIVE_WORD => array(Labels::getLabel('LBL_user_abusive_word_notification', $langId), ''),
        Notification::NEW_SUBSCRIPTION_PURCHASE_NOTIFICATION => array(Labels::getLabel('LBL_user_new_subscription_purchase_notification', $langId), ''),
        Notification::PROMOTION_APPROVAL_NOTIFICATION => array(Labels::getLabel('LBL_user_promotion_approval_notification', $langId), 'promotions'),
        Notification::WITHDRAWL_REQUEST_NOTIFICATION => array(Labels::getLabel('LBL_user_withdrawl_request_notification', $langId), 'withdrawal-requests'),
        Notification::NEW_SUPPLIER_APPROVAL_NOTIFICATION => array(Labels::getLabel('LBL_user_supplier_approval_notification', $langId), 'users/seller-approval-requests'),
        Notification::NEW_SELLER_APPROVED_NOTIFICATION => array(Labels::getLabel('LBL_user_seller_approved_notification', $langId), 'users/seller-approval-requests'),
        Notification::ABUSIVE_REVIEW_POSTED_NOTIFICATION => array(Labels::getLabel('LBL_admin_abusive_review_posted_notification', $langId), ''),
        Notification::PRODUCT_REVIEW_NOTIFICATION => array(Labels::getLabel('LBL_admin_Product_review_notification', $langId), 'product-reviews'),
        Notification::ORDER_PAYMENT_STATUS_CHANGE_NOTIFICATION => array(Labels::getLabel('LBL_admin_order_payment_status_change_notification', $langId), 'orders'),
        Notification::ORDER_RETURNED_REQUEST_MESSAGE_NOTIFICATION => array(Labels::getLabel('LBL_admin_order_return_request_message_notification', $langId), 'order-return-requests'),
        Notification::RETURN_REQUEST_MESSAGE_TO_USER_NOTIFICATION => array(Labels::getLabel('LBL_admin_order_return_request_message_to_user_notification', $langId), 'order-return-requests'),
        Notification::ORDER_EMAIL_NOTIFICATION => array(Labels::getLabel('LBL_admin_order_email_notification', $langId), ''),
        Notification::NEW_CUSTOM_CATALOG_REQUEST_NOTIFICATION => array(Labels::getLabel('LBL_admin_custom_catalog_request_notification', $langId), 'custom-products'),
        Notification::BLOG_COMMENT_NOTIFICATION => array(Labels::getLabel('LBL_user_blog_comment_notification', $langId), 'blog-comments'),
        Notification::BLOG_CONTRIBUTION_NOTIFICATION => array(Labels::getLabel('LBL_user_blog_contibution_notification', $langId), 'blog-contributions'),
        Notification::PRODUCT_CATEGORY_REQUEST_NOTIFICATION => array(Labels::getLabel('LBL_Product_category_request_notification', $langId), 'product-categories/requests'),
        Notification::ORDER_PAYMENT_TRANSFERRED_TO_BANK => array(Labels::getLabel('LBL_ORDER_PAYMENT_TRANSFERRED_TO_BANK', $langId), 'orders'),
        );

        return $labelArr;
    }

    public static function getSearchObject()
    {
        $srch = new SearchBase(static::DB_TBL, 'n');
        $srch->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'u.' . User::DB_TBL_PREFIX . 'id = n.notification_user_id', 'u');
        //$srch->joinTable( User::DB_TBL_CRED,'LEFT OUTER JOIN','uc.'.User::DB_TBL_CRED_PREFIX.'user_id = u.user_id', 'uc' );

        $srch->addMultipleFields(
            array(
            'n.*',
            'u.' . User::DB_TBL_PREFIX . 'name',
            )
        );

        return $srch;
    }

    public function deleteNotifications($recordId)
    {
        $db = FatApp::getDb();
        if (!$db->query("UPDATE tbl_notifications SET notification_deleted = 1 WHERE notification_id in (" . $recordId . ")")) {
            return false;
        }
        return true;
    }

    public function changeNotifyStatus($status, $recordId)
    {
        $db = FatApp::getDb();
        if (!$db->query("UPDATE tbl_notifications SET notification_marked_read = " . $status . " WHERE notification_id in (" . $recordId . ")")) {
            return false;
        }
        return true;
    }

    public static function getPermissionsArr()
    {
        return array(
        static::TYPE_USER => AdminPrivilege::SECTION_USERS,
        static::TYPE_BRAND => AdminPrivilege::SECTION_BRANDS,
        static::TYPE_ORDER => AdminPrivilege::SECTION_ORDERS,
        static::TYPE_ORDER_PRODUCT => AdminPrivilege::SECTION_ORDERS,
        static::TYPE_ORDER_CANCELATION => AdminPrivilege::SECTION_ORDER_CANCELLATION_REQUESTS,
        static::TYPE_ORDER_RETURN_REQUEST => AdminPrivilege::SECTION_ORDER_RETURN_REQUESTS,
        static::TYPE_CATALOG => AdminPrivilege::SECTION_CATALOG_REQUESTS,
        static::TYPE_CATALOG_REQUEST => AdminPrivilege::SECTION_CATALOG_REQUESTS,
        static::TYPE_PRODUCT_REVIEW => AdminPrivilege::SECTION_PRODUCT_REVIEWS,
        static::TYPE_WITHDRAWAL_REQUEST => AdminPrivilege::SECTION_WITHDRAW_REQUESTS,
        static::TYPE_WITHDRAW_RETURN_REQUEST => AdminPrivilege::SECTION_WITHDRAW_REQUESTS,
        static::TYPE_SHOP => AdminPrivilege::SECTION_SHOPS,
        static::TYPE_PROMOTION => AdminPrivilege::SECTION_PROMOTIONS,
        static::TYPE_ORDER_RETURN_REQUEST_MESSAGE => AdminPrivilege::SECTION_ORDER_RETURN_REQUESTS,
        static::TYPE_BLOG => AdminPrivilege::SECTION_BLOG_POSTS,
        static::TYPE_PRODUCT_CATEGORY => AdminPrivilege::SECTION_PRODUCT_CATEGORIES,
        static::TYPE_ADMIN => AdminPrivilege::SECTION_ADMIN_USERS,
        );
    }

    public static function getAllowedRecordTypeArr($adminId)
    {
        if (AdminPrivilege::isAdminSuperAdmin($adminId)) {
            return array_flip(self::getPermissionsArr());
        }

        $privilege = new AdminPrivilege();
        $userPermissions = array_filter($privilege->getAdminPermissionLevel($adminId));

        $permissionsArr = self::getPermissionsArr();
        $validType = array(-1 => -1);
        foreach ($permissionsArr as $notificationType => $permissionType) {
            if (array_key_exists($permissionType, $userPermissions)) {
                $validType[] = $notificationType;
            }
        }
        return $validType;
    }
}
