<?php

class AbandonedCart extends MyAppModel
{
    public const DB_TBL = 'tbl_abandoned_cart';
    public const DB_TBL_PREFIX = 'abandonedcart_';
    
    public const TYPE_PRODUCT = 1;
     
    public const ACTION_ADDED = 1;
    public const ACTION_DELETED = 2;
    public const ACTION_PURCHASED = 3;
    
    public const MAX_EMAIL_COUNT = 2;
    public const MAX_DISCOUNT_NOTIFICATION = 1;
    public const DELETE_OLD_RECORD_MONTH = 3;
    
    private $totalRecords;
    private $totalPages;
    private $pageSize;
    
    public function __construct($id = 0)
    {
        parent::__construct(static::DB_TBL, static::DB_TBL_PREFIX . 'id', $id);
    }
    
    public static function saveAbandonedCart($userId, $selProdId, $qty, $action, $amount = 0)
    {
        $userId = FatUtility::int($userId);
        $selProdId = FatUtility::int($selProdId);
        $qty = FatUtility::int($qty);
        $action = FatUtility::int($action);
        $amount = FatUtility::int($amount);
        if ($userId < 1 || $selProdId < 1 || $qty < 1 || !in_array($action, array_keys(static::getActionArr()))) {
            return false;
        }
        
        self::deleteOldRecords();
        $data = array(
            static::DB_TBL_PREFIX . 'user_id' => $userId,
            static::DB_TBL_PREFIX . 'selprod_id' => $selProdId,
            static::DB_TBL_PREFIX . 'type' => static::TYPE_PRODUCT,
            static::DB_TBL_PREFIX . 'qty' => $qty,
            static::DB_TBL_PREFIX . 'amount' => $amount,
            static::DB_TBL_PREFIX . 'action' => $action,
            static::DB_TBL_PREFIX . 'added_on' => date('Y-m-d H:i:s'),
        );
        
        if ($action == static::ACTION_PURCHASED) {
            $srch = new AbandonedCartSearch();
            $srch->addActionCondition();
            $srch->addCondition(static::DB_TBL_PREFIX . 'user_id', '=', $userId);
            $srch->addCondition(static::DB_TBL_PREFIX . 'selprod_id', '=', $selProdId);
            $srch->addOrder(static::DB_TBL_PREFIX . 'added_on', 'DESC');
            $srch->addMultipleFields(array(static::DB_TBL_PREFIX . 'email_count', static::DB_TBL_PREFIX . 'discount_notification'));
            $srch->setPageSize(1);
            $rs = $srch->getResultSet();
            $record = FatApp::getDb()->fetch($rs);
            
            $data[static::DB_TBL_PREFIX . 'email_count'] = isset($record[static::DB_TBL_PREFIX . 'email_count']) ? $record[static::DB_TBL_PREFIX . 'email_count'] : '';
            $data[static::DB_TBL_PREFIX . 'discount_notification'] = isset($record[static::DB_TBL_PREFIX . 'discount_notification']) ? $record[static::DB_TBL_PREFIX . 'discount_notification'] : '';
        }
        
        $record = new TableRecord(static::DB_TBL);
        $record->assignValues($data);
        if (!$record->addNew(array(), $data)) {
            return false;
        }
        return true;
    }
    
    public static function deleteOldRecords()
    {
        FatApp::getDb()->deleteRecords(static::DB_TBL, array('smt' => static::DB_TBL_PREFIX . 'added_on < ?', 'vals' => array(date('Y-m-d H:i:s', strtotime('-' . static::DELETE_OLD_RECORD_MONTH . ' months')))));
    }
    
    public static function getActionArr($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($langId < 1) {
            $langId = FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG');
        }
        return array(
            static::ACTION_ADDED => Labels::getLabel('LBL_In_Cart', $langId),
            static::ACTION_DELETED => Labels::getLabel('LBL_Removed_From_Cart', $langId),
            static::ACTION_PURCHASED => Labels::getLabel('LBL_Cart_Recoverd', $langId)
        );
    }

    public function getAbandonedCartList($userId = 0, $selProdId = 0, $action = 0, $dateFrom = '', $dateTo = '', $page = 1)
    {
        $page = FatUtility::int($page);
        $page = ($page > 0) ? $page : 1;
        $srch = new AbandonedCartSearch();
        $srch->joinUsers();
        $srch->joinSellerProducts($this->commonLangId);
        $srch->addActionCondition($action);
        if ($userId > 0) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'user_id', '=', $userId);
        }
        if ($selProdId > 0) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'selprod_id', '=', $selProdId);
        }
        if (!empty($dateFrom)) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'added_on', '>=', $dateFrom . ' 00:00:00');
        }
        if (!empty($dateTo)) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'added_on', '<=', $dateTo . ' 23:59:59');
        }
        if ($action != static::ACTION_PURCHASED) {
            $srch->addSubQueryCondition();
            $srch->addCondition(static::DB_TBL_PREFIX . 'email_count', '<', static::MAX_EMAIL_COUNT);
            $srch->addCondition(static::DB_TBL_PREFIX . 'discount_notification', '<=', static::MAX_DISCOUNT_NOTIFICATION);
        }
        if ($action == static::ACTION_PURCHASED) {
            $cnd = $srch->addCondition(static::DB_TBL_PREFIX . 'email_count', '>', 0);
            $cnd->attachCondition(static::DB_TBL_PREFIX . 'discount_notification', '>', 0);
        }
        $srch->addMultipleFields(array('ch.*', 'user_name', 'selprod_product_id', 'selprod_title'));
        $srch->addOrder(static::DB_TBL_PREFIX . 'added_on', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($this->setPageSize()); 
        $rs = $srch->getResultSet();
        $this->totalRecords = $srch->recordCount();
        $this->totalPages = $srch->pages();
        $this->pageSize = $this->setPageSize();
        return FatApp::getDb()->fetchAll($rs);
    }
    
    public function getCartRecoveredTotal($userId = 0, $selProdId = 0, $dateFrom = '', $dateTo = '')
    {
        $srch = new AbandonedCartSearch();
        $srch->joinUsers();
        $srch->joinSellerProducts($this->commonLangId);
        if ($userId > 0) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'user_id', '=', $userId);
        }
        if ($selProdId > 0) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'selprod_id', '=', $selProdId);
        }
        if (!empty($dateFrom)) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'added_on', '>=', $dateFrom . ' 00:00:00');
        }
        if (!empty($dateTo)) {
            $srch->addCondition(static::DB_TBL_PREFIX . 'added_on', '<=', $dateTo . ' 23:59:59');
        }
        $srch->addActionCondition(static::ACTION_PURCHASED);
        $cnd = $srch->addCondition(static::DB_TBL_PREFIX . 'email_count', '>', 0);
        $cnd->attachCondition(static::DB_TBL_PREFIX . 'discount_notification', '>', 0);
        $srch->addMultipleFields(array('sum(abandonedcart_amount) as amount'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();        
        return FatApp::getDb()->fetch($rs);
    }
    
    public function getAbandonedCartProducts($page = 1)
    {
        $page = FatUtility::int($page);
        $page = ($page > 0) ? $page : 1;
        $srch = new AbandonedCartSearch();
        $srch->joinSellerProducts($this->commonLangId);
        $srch->addSubQueryCondition();
        $srch->addActionCondition();
        $srch->addMultipleFields(array(static::DB_TBL_PREFIX.'selprod_id', 'selprod_title', 'count('.static::DB_TBL_PREFIX.'selprod_id'.') as product_count')); 
        $srch->addGroupBy(static::DB_TBL_PREFIX.'selprod_id');
        $srch->addOrder('product_count', 'DESC');
        $srch->setPageNumber($page);        
        $srch->setPageSize($this->setPageSize());        
        $rs = $srch->getResultSet();                  
        $this->totalRecords = $srch->recordCount();
        $this->totalPages = $srch->pages();
        $this->pageSize = $this->setPageSize();
        return FatApp::getDb()->fetchAll($rs);
    }
    
    public function sendDiscountEmail($couponId)
    {
        $couponId = FatUtility::int($couponId);
        if ($couponId < 1) {
            return false;
        }
        
        $couponData = DiscountCoupons::getAttributesById($couponId);
        $srch = new AbandonedCartSearch();
        $srch->joinUsers(true);
        $srch->joinSellerProducts($this->commonLangId);           
        $srch->addCondition(static::DB_TBL_PREFIX . 'id', '=', $this->mainTableRecordId);
        $srch->addMultipleFields(array('abandonedcart_action', 'user.user_id', 'user.user_name', 'user_cred.credential_email', 'selprod_id', 'selprod_product_id', 'selprod_title', 'selprod_price'));
        $rs = $srch->getResultSet(); 
        $abandonedData = FatApp::getDb()->fetch($rs);

        $discount = ($couponData['coupon_discount_in_percent'] == applicationConstants::PERCENTAGE) ? $couponData['coupon_discount_value'] . '%' : CommonHelper::displayMoneyFormat($couponData['coupon_discount_value']);
        $arrReplacements = array(
            '{user_full_name}' => trim($abandonedData['user_name']),
            '{checkout_now}' => UrlHelper::generateFullUrl('GuestUser', 'redirectAbandonedCartUser', array($abandonedData['user_id'], $abandonedData['selprod_id']), CONF_WEBROOT_FRONTEND),
            '{coupon_code}' => $couponData['coupon_code'],
            '{discount}' => $discount,
            '{product_name}' => trim($abandonedData['selprod_title'])
        );
        
        $tpl = "";
        if ($abandonedData['abandonedcart_action'] == static::ACTION_ADDED) {
            $prodImage = UrlHelper::generateFullUrl('image', 'product', array($abandonedData['selprod_product_id'], "THUMB", $abandonedData['selprod_id'], 0, $this->commonLangId), CONF_WEBROOT_FRONTEND);
            $arrReplacements['{product_image}'] = $prodImage;
            $arrReplacements['{product_price}'] = CommonHelper::displayMoneyFormat($abandonedData['selprod_price']);
            $tpl = "abandoned_cart_discount_notification";
        }
        if ($abandonedData['abandonedcart_action'] == static::ACTION_DELETED) {
            $tpl = "abandoned_cart_deleted_discount_notification";
        }
        if (!EmailHandler::sendMailTpl($abandonedData['credential_email'], $tpl, $this->commonLangId, $arrReplacements)) {
            $this->error = Labels::getLabel('MSG_Email_Not_Sent', $this->commonLangId);
            return false;
        }
        return true;
    }
    
    public function updateDiscountNotification()
    {
        if (!FatApp::getDb()->updateFromArray(static::DB_TBL, array(static::DB_TBL_PREFIX . 'discount_notification' => 1), array('smt' => static::DB_TBL_PREFIX . 'id = ?', 'vals' => array($this->mainTableRecordId)))) {
            return false;
        }
        return true;
    }
    
    public static function sendReminderAbandonedCart()
    { 
        $langId = FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1);
        $srch = new AbandonedCartSearch();
        $srch->joinUsers(true);
        $srch->joinSellerProducts($langId);
        $srch->addSubQueryCondition();
        $srch->addActionCondition(static::ACTION_ADDED);
        $srch->addCondition(static::DB_TBL_PREFIX . 'email_count', '<', static::MAX_EMAIL_COUNT);
        $srch->addCondition(static::DB_TBL_PREFIX . 'discount_notification', '=', 0);
        $srch->addMultipleFields(array(static::DB_TBL_PREFIX . 'id', 'user_id', 'user_name', 'credential_email', 'selprod_id', 'selprod_product_id', 'selprod_title', 'selprod_price'));
        $srch->addOrder(static::DB_TBL_PREFIX . 'user_id');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
     
        $prevUserId = 0;
        $productHtml = "";
        $abandonedCartIds = array();
        foreach ($records as $key => $data) {
            $product = static::validateProductForNotification($data['selprod_product_id']);
            if(empty($product)){
                continue;
            }
            
            if ($prevUserId == 0 || $prevUserId == $data['user_id']) {
                $prevUserId = $data['user_id'];
            } else {
                if (self::sendReminderEmail($records[$key - 1]['user_id'], $records[$key - 1]['user_name'], $records[$key - 1]['credential_email'], $productHtml)) {
                    self::updateReminderCount($abandonedCartIds);
                }
                $prevUserId = $data['user_id'];
                $productHtml = "";
                $abandonedCartIds = array();
            }

            $abandonedCartIds[] = $data[static::DB_TBL_PREFIX . 'id'];
            $tpl = new FatTemplate('', '');
            $tpl->set('data', $data);
            $tpl->set('langId', $langId);
            $productHtml .= $tpl->render(false, false, '_partial/abandoned-cart-product-html.php', true);
            
            if (($key + 1) == count($records)) {
                if (self::sendReminderEmail($data['user_id'], $data['user_name'], $data['credential_email'], $productHtml)) {
                    self::updateReminderCount($abandonedCartIds);
                }
            }
        }
        return true;
    }
    
    public static function sendReminderEmail($userId, $userName, $userEmail, $productHtml)
    {
        $tpl = new FatTemplate('', '');
        $tpl->set('userId', $userId);
        $checkOutButtonHtml = $tpl->render(false, false, '_partial/abandoned-cart-checkout-button.php', true);
        $arrReplacements = array(
            '{user_full_name}' => $userName,
            '{product_detail_table}' => $productHtml . $checkOutButtonHtml
        );
        $tpl = "abandoned_cart_email";
        $langId = FatApp::getConfig('CONF_DEFAULT_SITE_LANG', FatUtility::VAR_INT, 1);
        if (!EmailHandler::sendMailTpl($userEmail, $tpl, $langId, $arrReplacements)) {
            return false;
        }
        return true;
    }

    public static function updateReminderCount($abandonedCartIds)
    {
        if (!is_array($abandonedCartIds)) {
            return false;
        }
        foreach ($abandonedCartIds as $id) {
            $where = array('smt' => static::DB_TBL_PREFIX . 'id = ?', 'vals' => array($id));
            $data = array(static::DB_TBL_PREFIX . 'email_count' => 'mysql_func_' . static::DB_TBL_PREFIX . 'email_count + 1');
            if (!FatApp::getDb()->updateFromArray(static::DB_TBL, $data, $where, true)) {
                return false;
            }
        }
        return true;
    }
    
    public static function validateProductForNotification($productId)
    {
        $productId = FatUtility::int($productId);
        $prodSrch = new ProductSearch(CommonHelper::getLangId());
        $prodSrch->setDefinedCriteria(0, 0, array(), false);
        $prodSrch->joinProductToCategory();
        $prodSrch->joinSellerSubscription();
        $prodSrch->addSubscriptionValidCondition();
        $prodSrch->addCondition('product_id', '=', $productId);
        $prodSrch->addFld('product_id');
        $productRs = $prodSrch->getResultSet();
        return FatApp::getDb()->fetch($productRs);
    }

    public function recordCount()
    {
        return $this->totalRecords;
    }
    
    public function pages()
    {
        return $this->totalPages;
    }
    
    public function getPageSize()
    {
        return $this->pageSize;
    }
    
    public function setPageSize()
    {
        return FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);
    }
}
