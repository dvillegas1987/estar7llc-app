<?php

class SellerController extends SellerBaseController
{
    // use Attributes;
    use Options;
    use CustomProducts;
    use SellerProducts;
    use SellerCollections;
    use CustomCatalogProducts;
    use SellerUsers;

    private $shippingService;
    private $trackingService;
    private $paymentPlugin;
    private $method = '';

    public function __construct($action)
    {
        $this->method = $action;
        parent::__construct($action);
    }

    public function index()
    {
        // $this->userPrivilege->canViewSellerDashboard(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;
        $user = new User($userId);
        $_SESSION[UserAuthentication::SESSION_ELEMENT_NAME]['activeTab'] = 'S';

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS", FatUtility::VAR_STRING, '')));
        $srch->joinSellerProducts();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->addCountsOfOrderedProducts();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        //$srch->addSellerOrderCounts(date('Y-m-d',strtotime("-1 days")),date('Y-m-d'),'yesterdayOrder');
        $srch->addCondition('op_selprod_user_id', '=', $userId);

        $srch->addOrder("op_id", "DESC");
        $srch->setPageNumber(1);
        $srch->setPageSize(2);

        $srch->addMultipleFields(
            array('order_id', 'order_user_id', 'op_selprod_id', 'op_is_batch', 'selprod_product_id', 'order_date_added', 'order_net_amount', 'op_invoice_number', 'totCombinedOrders as totOrders', 'op_selprod_title', 'op_product_name', 'op_id', 'op_qty', 'op_selprod_options', 'op_status_id', 'op_brand_name', 'op_shop_name', 'op_other_charges', 'op_unit_price', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name', 'op_tax_collected_by_seller', 'op_selprod_user_id', 'opshipping_by_seller_user_id', 'orderstatus_color_class', 'order_pmethod_id', 'opshipping_fulfillment_type', 'op_rounding_off')
        );

        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);

        $oObj = new Orders();
        foreach ($orders as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['op_id']);
            $order['charges'] = $charges;
        }

        /* Orders Counts [ */
        $orderSrch = new OrderProductSearch($this->siteLangId, true, true);
        $orderSrch->doNotCalculateRecords();
        $orderSrch->doNotLimitRecords();
        /* $orderSrch->addSellerOrdersCounts( date('Y-m-d',strtotime("-1 days") ), date('Y-m-d'), 'yesterdayOrder');
          $orderSrch->addSellerCompletedOrdersStats( date('Y-m-d', strtotime("-1 days")),date('Y-m-d'), 'yesterdaySold' ); */

        /* $orderSrch->addSellerOrdersCounts( date('Y-m-d',strtotime("-1 days") ), date('Y-m-d',strtotime("-1 days") ), 'todayOrder'); */
        $orderSrch->addSellerOrdersCounts(date('Y-m-d'), date('Y-m-d'), 'todayOrder');
        /* $orderSrch->addSellerCompletedOrdersStats( date('Y-m-d', strtotime("-1 days")),date('Y-m-d',strtotime("-1 days") ), 'yesterdaySold' ); */
        $orderSrch->addSellerCompletedOrdersStats(date('Y-m-d'), date('Y-m-d'), 'todaySold');

        $orderSrch->addSellerCompletedOrdersStats(false, false, 'totalSold');
        $orderSrch->addSellerInprocessOrdersStats(false, false, 'totalInprocess');
        $orderSrch->addSellerRefundedOrdersStats();
        $orderSrch->addSellerCancelledOrdersStats();
        $orderSrch->addGroupBy('order_user_id');
        $orderSrch->addCondition('op_selprod_user_id', '=', $userId);
        $orderSrch->addMultipleFields(array('todayOrderCount', 'totalInprocessSales', 'totalSoldSales', 'totalSoldCount', 'refundedOrderCount', 'refundedOrderAmount', 'cancelledOrderCount', 'cancelledOrderAmount'));
        $rs = $orderSrch->getResultSet();
        $ordersStats = FatApp::getDb()->fetch($rs);
        /* ] */

        /* $threadObj = new Thread();
          $todayUnreadMessageCount = $threadObj->getMessageCount($userId, Thread::MESSAGE_IS_UNREAD, date('Y-m-d'));
          $unreadMessageCount = $threadObj->getMessageCount($userId, Thread::MESSAGE_IS_UNREAD);
          $totalMessageCount = $threadObj->getMessageCount($userId); */
        /* ] */
        $orderObj = new Orders();
        $notAllowedStatues = $orderObj->getNotAllowedOrderCancellationStatuses();

        /* Remaining Products and Days Count [ */
        if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            $products = new Product();

            $latestOrder = OrderSubscription::getUserCurrentActivePlanDetails($this->siteLangId, $userId, array('ossubs_till_date', 'ossubs_id', 'ossubs_inventory_allowed', 'ossubs_subscription_name'));
            $pendingDaysForCurrentPlan = 0;
            $remainingAllowedProducts = 0;
            if ($latestOrder) {
                $pendingDaysForCurrentPlan = FatDate::diff(date("Y-m-d"), $latestOrder['ossubs_till_date']);
                $totalProducts = $products->getTotalProductsAddedByUser($userId);
                $remainingAllowedProducts = $latestOrder['ossubs_inventory_allowed'] - $totalProducts;
                $this->set('subscriptionTillDate', $latestOrder['ossubs_till_date']);
                $this->set('subscriptionName', $latestOrder['ossubs_subscription_name']);
            }

            $this->set('pendingDaysForCurrentPlan', $pendingDaysForCurrentPlan);
            $this->set('remainingAllowedProducts', $remainingAllowedProducts);
        }
        /* ] */

        /*
         * Return Request Listing
         */
        $srchReturnReq = $this->returnReuestsListingObj();
        $srchReturnReq->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);
        $rs = $srchReturnReq->getResultSet();
        $returnRequests = FatApp::getDb()->fetchAll($rs);

        /*
         * Transactions Listing
         */
        $transSrch = Transactions::getUserTransactionsObj($userId);
        $transSrch->doNotCalculateRecords();
        $transSrch->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);
        $rs = $transSrch->getResultSet();
        $transactions = FatApp::getDb()->fetchAll($rs, 'utxn_id');
        /*
         * Cancellation Request Listing
         */
        $canSrch = $this->cancelRequestListingObj();
        $canSrch->setPageSize(applicationConstants::DASHBOARD_PAGE_SIZE);
        $rs = $canSrch->getResultSet();
        $cancellationRequests = FatApp::getDb()->fetchAll($rs);
        $this->set('returnRequestsCount', $srchReturnReq->recordCount());

        $txnObj = new Transactions();
        $txnsSummary = $txnObj->getTransactionSummary($userId, date('Y-m-d'));

        $isShopActive = $this->isShopActive($this->userParentId);

        $this->set('transactions', $transactions);
        $this->set('returnRequests', $returnRequests);
        $this->set('OrderReturnRequestStatusArr', OrderReturnRequest::getRequestStatusArr($this->siteLangId));
        $this->set('OrderRetReqStatusClassArr', OrderReturnRequest::getRequestStatusClassArr());
        $this->set('cancellationRequests', $cancellationRequests);
        $this->set('txnStatusArr', Transactions::getStatusArr($this->siteLangId));
        $this->set('txnStatusClassArr', Transactions::getStatusClassArr());
        $this->set('OrderCancelRequestStatusArr', OrderCancelRequest::getRequestStatusArr($this->siteLangId));
        $this->set('cancelReqStatusClassArr', OrderCancelRequest::getStatusClassArr());
        $this->set('txnsSummary', $txnsSummary);
        $this->set('notAllowedStatues', $notAllowedStatues);
        $this->set('orders', $orders);
        $this->set('ordersCount', $srch->recordCount());
        $this->set('data', $user->getProfileData());
        $this->set('userBalance', User::getUserBalance($userId));
        $this->set('ordersStats', $ordersStats);
        $this->set('dashboardStats', Stats::getUserSales($userId));
        $this->set('userParentId', $this->userParentId);
        $this->set('userPrivilege', $this->userPrivilege);
        $this->set('isShopActive', $isShopActive);
        $this->set('classArr', applicationConstants::getClassArr());
        $this->_template->addJs(array('js/chartist.min.js'));
        $this->_template->addJs('js/slick.min.js');
        $this->_template->render(true, true);
    }


    /**
     * loadShippingService
     *
     * @return void
     */
    private function loadShippingService()
    {
        /* Return if already loaded. */
        if (!empty($this->shippingService)) {
            return;
        }

        $plugin = new Plugin();
        $keyName = $plugin->getDefaultPluginKeyName(Plugin::TYPE_SHIPPING_SERVICES);

        /* Carry on with default functionality if plugin not active. */
        if (false === $keyName) {
            return;
        }

        $this->shippingService = PluginHelper::callPlugin($keyName, [$this->siteLangId], $error, $this->siteLangId, false);
        if (false === $this->shippingService) {
            if ('orderproductsearchlisting' == strtolower($this->method)) {
                Message::addErrorMessage($error);
                FatUtility::dieWithError(Message::getHtml());
            } else {
                FatApp::redirectUser(UrlHelper::generateUrl("Seller", "Sales"));
            }
        };
        if (false === $this->shippingService->init()) {
            if ('orderproductsearchlisting' == strtolower($this->method)) {
                Message::addErrorMessage($this->shippingService->getError());
                FatUtility::dieWithError(Message::getHtml());
            } else {
                FatApp::redirectUser(UrlHelper::generateUrl("Seller", "Sales"));
            }
        }
    }

    /**
     * loadTrackingService
     *
     * @return void
     */
    private function loadTrackingService()
    {
        /* Return if already loaded. */
        if (!empty($this->trackingService)) {
            return;
        }

        $plugin = new Plugin();
        $keyName = $plugin->getDefaultPluginKeyName(Plugin::TYPE_SHIPMENT_TRACKING);

        /* Carry on with default functionality if plugin not active. */
        if (false === $keyName) {
            return;
        }

        $this->trackingService = PluginHelper::callPlugin($keyName, [$this->siteLangId], $error, $this->siteLangId, false);
        if (false === $this->trackingService) {
            Message::addErrorMessage($error);
            FatApp::redirectUser(UrlHelper::generateUrl("Seller", "Sales"));
        }

        if (false === $this->trackingService->init()) {
            Message::addErrorMessage($this->trackingService->getError());
            FatApp::redirectUser(UrlHelper::generateUrl("Seller", "Sales"));
        }
    }

    public function sales()
    {
        $data = FatApp::getPostedData();
        $frmOrderSrch = $this->getOrderSearchForm($this->siteLangId);
        if (!empty($data)) {
            $frmOrderSrch->fill($data);
        }
        $this->userPrivilege->canViewSales(UserAuthentication::getLoggedUserId());
        $this->set('frmOrderSrch', $frmOrderSrch);
        $this->_template->render(true, true);
    }

    public function orderProductSearchListing()
    {
        $frm = $this->getOrderSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $userId = $this->userParentId;

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinSellerProducts();
        $srch->joinPaymentMethod();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->addCountsOfOrderedProducts();
        $srch->joinOrderProductShipment();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->addCondition('op_selprod_user_id', '=', $userId);
        $srch->addOrder("op_id", "DESC");
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $srch->addMultipleFields(
            array('order_id', 'order_status', 'order_payment_status', 'order_user_id', 'op_selprod_id', 'op_is_batch', 'selprod_product_id', 'order_date_added', 'order_net_amount', 'op_invoice_number', 'totCombinedOrders as totOrders', 'op_selprod_title', 'op_product_name', 'op_id', 'op_qty', 'op_selprod_options', 'op_brand_name', 'op_shop_name', 'op_other_charges', 'op_unit_price', 'op_tax_collected_by_seller', 'op_selprod_user_id', 'opshipping_by_seller_user_id', 'orderstatus_id', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name', 'orderstatus_color_class', 'plugin_code', 'IFNULL(plugin_name, IFNULL(plugin_identifier, "Wallet")) as plugin_name', 'opship.*', 'opshipping_fulfillment_type', 'op_rounding_off', 'op_product_type', 'opshipping_carrier_code', 'opshipping_service_code')
        );

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $srch->joinOrderUser();
            $srch->addKeywordSearch($keyword);
        }

        $op_status_id = FatApp::getPostedData('status', null, '0');

        if (in_array($op_status_id, unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")))) {
            $srch->addStatusCondition($op_status_id, ($op_status_id == FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")));
        } else {
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")), ($op_status_id == FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")));
        }

        $dateFrom = FatApp::getPostedData('date_from', null, '');
        if (!empty($dateFrom)) {
            $srch->addDateFromCondition($dateFrom);
        }

        $dateTo = FatApp::getPostedData('date_to', null, '');
        if (!empty($dateTo)) {
            $srch->addDateToCondition($dateTo);
        }

        $priceFrom = FatApp::getPostedData('price_from', null, '');
        if (!empty($priceFrom)) {
            $srch->addMinPriceCondition($priceFrom);
        }

        $priceTo = FatApp::getPostedData('price_to', null, '');
        if (!empty($priceTo)) {
            $srch->addMaxPriceCondition($priceTo);
        }

        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);
        // CommonHelper::printArray($orders);
        $oObj = new Orders();
        foreach ($orders as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['op_id']);
            $order['charges'] = $charges;
        }

        /* ShipStation */
        $this->loadShippingService();
        $this->set('canShipByPlugin', (null !== $this->shippingService));
        /* ShipStation */

        $this->set('canEdit', $this->userPrivilege->canEditSales(UserAuthentication::getLoggedUserId(), true));
        $this->set('orders', $orders);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->set('classArr', applicationConstants::getClassArr());
        $this->_template->render(false, false);
    }

    private function getOrderSearchForm($langId)
    {
        $currency_id = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
        $currencyData = Currency::getAttributesById($currency_id, array('currency_code', 'currency_symbol_left', 'currency_symbol_right'));
        $currencySymbol = ($currencyData['currency_symbol_left'] != '') ? $currencyData['currency_symbol_left'] : $currencyData['currency_symbol_right'];
        $frm = new Form('frmOrderSrch');
        $frm->addTextBox('', 'keyword', '', array('placeholder' => Labels::getLabel('LBL_Keyword', $langId)));
        $frm->addSelectBox('', 'status', Orders::getOrderProductStatusArr($langId, unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS"))), '', array(), Labels::getLabel('LBL_Status', $langId));
        $frm->addTextBox('', 'price_from', '', array('placeholder' => Labels::getLabel('LBL_Price_Min', $langId) . ' [' . $currencySymbol . ']'));
        $frm->addTextBox('', 'price_to', '', array('placeholder' => Labels::getLabel('LBL_Price_Max', $langId) . ' [' . $currencySymbol . ']'));
        $frm->addDateField('', 'date_from', '', array('placeholder' => Labels::getLabel('LBL_Date_From', $langId), 'readonly' => 'readonly'));
        $frm->addDateField('', 'date_to', '', array('placeholder' => Labels::getLabel('LBL_Date_To', $langId), 'readonly' => 'readonly'));
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick' => 'clearSearch();'));
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    public function orderSearchListing()
    {
        $this->userPrivilege->canViewSubscription(UserAuthentication::getLoggedUserId());
        if (!FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }
        $frm = $this->getSubscriptionOrderSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $userId = $this->userParentId;

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addCondition('opcharge_order_type', '=', Orders::ORDER_SUBSCRIPTION);
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderSubscriptionSearch($this->siteLangId, true, true);
        $srch->joinSubscription();
        $srch->joinOrderUser();
        //$srch->addCountsOfOrderedProducts();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'oss.ossubs_id = opcc.opcharge_op_id', 'opcc');
        $srch->addCondition('order_user_id', '=', $userId);
        $srch->addCondition('order_type', '=', Orders::ORDER_SUBSCRIPTION);
        $srch->addOrder("ossubs_id", "DESC");
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $srch->addMultipleFields(
            array('order_id', 'order_user_id', 'user_autorenew_subscription', 'ossubs_id', 'ossubs_type', 'ossubs_plan_id', 'order_date_added', 'order_net_amount', 'ossubs_invoice_number', 'ossubs_subscription_name', 'ossubs_id', 'op_other_charges', 'ossubs_price', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name', 'ossubs_interval', 'ossubs_frequency', 'ossubs_till_date', 'ossubs_status_id', 'ossubs_from_date', 'order_language_id')
        );

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $srch->joinOrderUser();
            $srch->addKeywordSearch($keyword);
        }

        $op_status_id = FatApp::getPostedData('status', null, '0');

        if (in_array($op_status_id, unserialize(FatApp::getConfig("CONF_SELLER_SUBSCRIPTION_STATUS")))) {
            $srch->addStatusCondition($op_status_id);
        } else {
            $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_SELLER_SUBSCRIPTION_STATUS")));
        }

        $dateFrom = FatApp::getPostedData('date_from', null, '');
        if (!empty($dateFrom)) {
            $srch->addDateFromCondition($dateFrom);
        }

        $dateTo = FatApp::getPostedData('date_to', null, '');
        if (!empty($dateTo)) {
            $srch->addDateToCondition($dateTo);
        }

        $priceFrom = FatApp::getPostedData('price_from', null, '');
        if (!empty($priceFrom)) {
            $srch->addHaving('totOrders', '=', '1');
            $srch->addMinPriceCondition($priceFrom);
        }

        $priceTo = FatApp::getPostedData('price_to', null, '');
        if (!empty($priceTo)) {
            $srch->addHaving('totOrders', '=', '1');
            $srch->addMaxPriceCondition($priceTo);
        }
        $rs = $srch->getResultSet();
        $orders = FatApp::getDb()->fetchAll($rs);

        $oObj = new Orders();
        foreach ($orders as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['ossubs_id']);
            $order['charges'] = $charges;
        }
        $orderStatuses = Orders::getOrderSubscriptionStatusArr($this->siteLangId);
        $this->set('orders', $orders);
        $this->set('orderStatuses', $orderStatuses);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->set('canEdit', $this->userPrivilege->canEditSubscription(UserAuthentication::getLoggedUserId(), true));
        $this->_template->render(false, false);
    }

    public function viewOrder($op_id, $print = false)
    {
        $this->userPrivilege->canViewSales(UserAuthentication::getLoggedUserId());
        $op_id = FatUtility::int($op_id);
        if (1 > $op_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $orderObj = new Orders();

        $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);
        $userId = $this->userParentId;

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinOrderProductShipment();
        $srch->joinPaymentMethod();
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->joinAddress();
        $srch->addOrderProductCharges();
        $srch->addCondition('op_selprod_user_id', '=', $userId);
        $srch->addCondition('op_id', '=', $op_id);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $rs = $srch->getResultSet();
        $orderDetail = FatApp::getDb()->fetch($rs);

        if (!$orderDetail) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        /* ShipStation */
        $this->loadShippingService();
        $this->set('canShipByPlugin', (null !== $this->shippingService));

        if (!empty($orderDetail["opship_orderid"])) {
            if (null != $this->shippingService && false === $this->shippingService->loadOrder($orderDetail["opship_orderid"])) {
                Message::addErrorMessage($this->shippingService->getError());
                FatApp::redirectUser(UrlHelper::generateUrl("SellerOrders"));
            }
            $orderDetail['thirdPartyorderInfo'] = (null != $this->shippingService ? $this->shippingService->getResponse() : []);
        }
        /* ShipStation */

        /* AfterShip */
        $this->loadTrackingService();
        $this->set('canTrackByPlugin', (null !== $this->trackingService));
        /* AfterShip */

        $codOrder = false;
        if (strtolower($orderDetail['plugin_code']) == 'cashondelivery') {
            $codOrder = true;
        }

        $pickupOrder = false;
        if (strtolower($orderDetail['plugin_code']) == 'payatstore') {
            $pickupOrder = true;
        }

        if ($orderDetail['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(true, $codOrder, $pickupOrder);
        } elseif ($orderDetail['op_product_type'] == Product::PRODUCT_TYPE_PHYSICAL) {
            $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(false, $codOrder, $pickupOrder);
        } else {
            $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(false, $codOrder, $pickupOrder);
        }

        /* [ if shipping not handled by seller then seller can not update status to ship and delived */
        if (!CommonHelper::canAvailShippingChargesBySeller($orderDetail['op_selprod_user_id'], $orderDetail['opshipping_by_seller_user_id'])) {
            $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"));
            if ($pickupOrder) {
                $processingStatuses = [];
            } else {
                $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS"));
            }
        }
        /* ] */

        if ($orderDetail["opshipping_fulfillment_type"] == Shipping::FULFILMENT_PICKUP) {
            $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"));
        }

        $charges = $orderObj->getOrderProductChargesArr($op_id);
        $orderDetail['charges'] = $charges;
        $address = $orderObj->getOrderAddresses($orderDetail['op_order_id']);
        $orderDetail['billingAddress'] = (isset($address[Orders::BILLING_ADDRESS_TYPE])) ? $address[Orders::BILLING_ADDRESS_TYPE] : array();
        $orderDetail['shippingAddress'] = (isset($address[Orders::SHIPPING_ADDRESS_TYPE])) ? $address[Orders::SHIPPING_ADDRESS_TYPE] : array();

        $pickUpAddress = $orderObj->getOrderAddresses($orderDetail['op_order_id'], $orderDetail['op_id']);
        $orderDetail['pickupAddress'] = (isset($pickUpAddress[Orders::PICKUP_ADDRESS_TYPE])) ? $pickUpAddress[Orders::PICKUP_ADDRESS_TYPE] : array();

        $orderDetail['comments'] = $orderObj->getOrderComments($this->siteLangId, array("op_id" => $op_id, 'seller_id' => $userId));

        $opChargesLog = new OrderProductChargeLog($op_id);
        $taxOptions = $opChargesLog->getData($this->siteLangId);
        $orderDetail['taxOptions'] = $taxOptions;

        $data = array(
            'op_id' => $op_id,
            'op_status_id' => $orderDetail['op_status_id'],
            'tracking_number' => $orderDetail['opship_tracking_number']
        );
        $frm = $this->getOrderCommentsForm($orderDetail, $processingStatuses);
        $frm->fill($data);

        $shippedBySeller = applicationConstants::NO;
        if (CommonHelper::canAvailShippingChargesBySeller($orderDetail['op_selprod_user_id'], $orderDetail['opshipping_by_seller_user_id'])) {
            $shippedBySeller = applicationConstants::YES;
        }

        $digitalDownloads = array();
        if ($orderDetail['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $digitalDownloads = Orders::getOrderProductDigitalDownloads($op_id);
        }

        $digitalDownloadLinks = array();
        if ($orderDetail['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $digitalDownloadLinks = Orders::getOrderProductDigitalDownloadLinks($op_id);
        }

        $this->set('orderDetail', $orderDetail);
        $this->set('orderStatuses', $orderStatuses);
        $this->set('shippedBySeller', $shippedBySeller);
        $this->set('digitalDownloads', $digitalDownloads);
        $this->set('digitalDownloadLinks', $digitalDownloadLinks);
        $this->set('languages', Language::getAllNames());
        $this->set('yesNoArr', applicationConstants::getYesNoArr($this->siteLangId));
        $this->set('frm', $frm);
        $this->set('displayForm', (in_array($orderDetail['op_status_id'], $processingStatuses)));

        if ($print) {
            $print = true;
        }

        $this->set('canEdit', $this->userPrivilege->canEditSales(UserAuthentication::getLoggedUserId(), true));
        $this->set('print', $print);
        $urlParts = array_filter(FatApp::getParameters());
        $this->set('urlParts', $urlParts);

        $this->_template->render(true, true);
    }

    public function viewInvoice($op_id)
    {
        $this->userPrivilege->canViewSales(UserAuthentication::getLoggedUserId());
        $op_id = FatUtility::int($op_id);
        if (1 > $op_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $orderObj = new Orders();
        $userId = $this->userParentId;

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinPaymentMethod();
        $srch->joinSellerProducts();
        $srch->joinShop();
        $srch->joinShopSpecifics();
        $srch->joinShopCountry();
        $srch->joinShopState();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->addOrderProductCharges();
        $srch->addCondition('op_selprod_user_id', '=', $userId);
        $srch->addCondition('op_id', '=', $op_id);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $srch->addMultipleFields(array('*', 'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city'));
        $rs = $srch->getResultSet();
        $orderDetail = FatApp::getDb()->fetch($rs);

        if (!$orderDetail) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $charges = $orderObj->getOrderProductChargesArr($op_id);
        $orderDetail['charges'] = $charges;

        $shippedBySeller = applicationConstants::NO;
        if (CommonHelper::canAvailShippingChargesBySeller($orderDetail['op_selprod_user_id'], $orderDetail['opshipping_by_seller_user_id'])) {
            $shippedBySeller = applicationConstants::YES;
        }

        if (!empty($orderDetail["opship_orderid"])) {
            if (null != $this->shippingService && false === $this->shippingService->loadOrder($orderDetail["opship_orderid"])) {
                Message::addErrorMessage($this->shippingService->getError());
                FatApp::redirectUser(UrlHelper::generateUrl("SellerOrders"));
            }
            $orderDetail['thirdPartyorderInfo'] = (null != $this->shippingService ? $this->shippingService->getResponse() : []);
        }

        $address = $orderObj->getOrderAddresses($orderDetail['op_order_id']);
        $orderDetail['billingAddress'] = (isset($address[Orders::BILLING_ADDRESS_TYPE])) ? $address[Orders::BILLING_ADDRESS_TYPE] : array();
        $orderDetail['shippingAddress'] = (isset($address[Orders::SHIPPING_ADDRESS_TYPE])) ? $address[Orders::SHIPPING_ADDRESS_TYPE] : array();

        $pickUpAddress = $orderObj->getOrderAddresses($orderDetail['op_order_id'], $orderDetail['op_id']);
        $orderDetail['pickupAddress'] = (isset($pickUpAddress[Orders::PICKUP_ADDRESS_TYPE])) ? $pickUpAddress[Orders::PICKUP_ADDRESS_TYPE] : array();

        $opChargesLog = new OrderProductChargeLog($op_id);
        $taxOptions = $opChargesLog->getData($this->siteLangId);
        $orderDetail['taxOptions'] = $taxOptions;

        /* $this->set('orderDetail', $orderDetail);
        $this->set('languages', Language::getAllNames());
        $this->set('yesNoArr', applicationConstants::getYesNoArr($this->siteLangId));
        $this->set('canEdit', $this->userPrivilege->canEditSales(UserAuthentication::getLoggedUserId(), true));
        $this->_template->render(true, true); */

        $template = new FatTemplate('', '');
        $template->set('siteLangId', $this->siteLangId);
        $template->set('orderDetail', $orderDetail);
        $template->set('shippedBySeller', $shippedBySeller);

        require_once(CONF_INSTALLATION_PATH . 'library/tcpdf/tcpdf.php');
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(FatApp::getConfig("CONF_WEBSITE_NAME_" . $this->siteLangId));
        $pdf->SetKeywords(FatApp::getConfig("CONF_WEBSITE_NAME_" . $this->siteLangId));
        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->SetHeaderMargin(0);
        $pdf->SetHeaderData('', 0, '', '', array(255, 255, 255), array(255, 255, 255));
        $pdf->setFooterData(array(0, 0, 0), array(200, 200, 200));
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetMargins(10, 10, 10);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->AddPage();
        $pdf->SetTitle(Labels::getLabel('LBL_Tax_Invoice', $this->siteLangId));
        $pdf->SetSubject(Labels::getLabel('LBL_Tax_Invoice', $this->siteLangId));

        // set LTR direction for english translation
        $pdf->setRTL(('rtl' == Language::getLayoutDirection($this->siteLangId)));
        // set font
        $pdf->SetFont('dejavusans');

        $templatePath = "seller/view-invoice.php";
        $html = $template->render(false, false, $templatePath, true, true);
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->lastPage();

        ob_end_clean();
        // $saveFile = CONF_UPLOADS_PATH . 'demo-pdf.pdf';
        //$pdf->Output($saveFile, 'F');
        $pdf->Output('tax-invoice.pdf', 'I');
        return true;
    }

    public function viewSubscriptionOrder($ossubs_id)
    {
        $op_id = FatUtility::int($ossubs_id);
        if (1 > $ossubs_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $orderObj = new Orders();

        $orderStatuses = Orders::getOrderSubscriptionStatusArr($this->siteLangId);
        $userId = $this->userParentId;

        $srch = new OrderSubscriptionSearch($this->siteLangId, true, true);

        $srch->joinOrderUser();
        $srch->addOrderProductCharges();
        $srch->addCondition('order_user_id', '=', $userId);
        $srch->addCondition('ossubs_id', '=', $op_id);
        $rs = $srch->getResultSet();

        $orderDetail = FatApp::getDb()->fetch($rs);

        if (!$orderDetail) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $charges = $orderObj->getOrderProductChargesArr($op_id);
        $orderDetail['charges'] = $charges;

        $data = array('ossubs_id' => $ossubs_id, 'ossubs_status_id' => $orderDetail['ossubs_status_id']);
        //    $frm = $this->getOrderCommentsForm($orderDetail,$processingStatuses);
        //$frm->fill($data);

        $this->set('orderDetail', $orderDetail);
        $this->set('orderStatuses', $orderStatuses);
        $this->set('yesNoArr', applicationConstants::getYesNoArr($this->siteLangId));
        //$this->set('frm', $frm);
        //    $this->set('displayForm',(in_array($orderDetail['op_status_id'],$processingStatuses)));
        $this->_template->render(true, true);
    }

    public function changeOrderStatus()
    {
        $this->userPrivilege->canEditSales(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        if (!isset($post['op_id'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $status = FatApp::getPostedData('op_status_id', FatUtility::VAR_INT, 0);
        $manualShipping = FatApp::getPostedData('manual_shipping', FatUtility::VAR_INT, 0);
        $trackingNumber = FatApp::getPostedData('tracking_number', FatUtility::VAR_STRING, '');
        if ($status ==  FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS") && empty($trackingNumber) && 1 > $manualShipping) {
            Message::addErrorMessage(Labels::getLabel('MSG_PLEASE_SELECT_SELF_SHIPPING', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db = FatApp::getDb();
        $db->startTransaction();

        $op_id = FatUtility::int($post['op_id']);
        if (1 > $op_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $oCancelRequestSrch = new OrderCancelRequestSearch();
        $oCancelRequestSrch->doNotCalculateRecords();
        $oCancelRequestSrch->doNotLimitRecords();
        $oCancelRequestSrch->addCondition('ocrequest_op_id', '=', $op_id);
        $oCancelRequestSrch->addCondition('ocrequest_status', '!=', OrderCancelRequest::CANCELLATION_REQUEST_STATUS_DECLINED);
        $oCancelRequestRs = $oCancelRequestSrch->getResultSet();
        if (FatApp::getDb()->fetch($oCancelRequestRs)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Cancel_request_is_submitted_for_this_order', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $loggedUserId = $this->userParentId;

        $orderObj = new Orders();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->joinOrderProductShipment();
        $srch->joinPaymentMethod();
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->joinOrderCancellationRequest();
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $srch->addCondition('op_selprod_user_id', '=', $loggedUserId);
        $srch->addCondition('op_id', '=', $op_id);
        $rs = $srch->getResultSet();

        $orderDetail = FatApp::getDb()->fetch($rs);

        if (empty($orderDetail)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($orderDetail["op_status_id"] != $post['op_status_id'] && $orderDetail['ocrequest_status'] != '' && $orderDetail['ocrequest_status'] == OrderCancelRequest::CANCELLATION_REQUEST_STATUS_PENDING) {
            Message::addErrorMessage(Labels::getLabel('MSG_Buyer_Order_Cancellation_request_is_pending', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $codOrder = false;
        if (strtolower($orderDetail['plugin_code']) == 'cashondelivery') {
            $codOrder = true;
        }

        $pickupOrder = false;
        if (strtolower($orderDetail['plugin_code']) == 'payatstore') {
            $pickupOrder = true;
        }

        if ($orderDetail['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(true, $codOrder, $pickupOrder);
        } elseif ($orderDetail['op_product_type'] == Product::PRODUCT_TYPE_PHYSICAL) {
            $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(false, $codOrder, $pickupOrder);
        } else {
            $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses(false, $codOrder, $pickupOrder);
        }


        /* [ if shipping not handled by seller then seller can not update status to ship and delived */
        $opshipping_by_seller_user_id = isset($orderDetail['opshipping_by_seller_user_id']) ? $orderDetail['opshipping_by_seller_user_id'] : 0;
        if (!CommonHelper::canAvailShippingChargesBySeller($orderDetail['op_selprod_user_id'], $opshipping_by_seller_user_id)) {
            $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"));
            if ($pickupOrder) {
                $processingStatuses = [];
            } else {
                $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS"));
            }
        }
        /* ] */

        if ($orderDetail["opshipping_fulfillment_type"] == Shipping::FULFILMENT_PICKUP) {
            $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"));
        }

        $frm = $this->getOrderCommentsForm($orderDetail, $processingStatuses);
        $post = $frm->getFormDataFromArray($post);

        if (false == $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (in_array($orderDetail["op_status_id"], $processingStatuses) && in_array($post["op_status_id"], $processingStatuses)) {
            $trackingCourierCode = '';
            $opship_tracking_url = '';
            if ($post["op_status_id"] == OrderStatus::ORDER_SHIPPED) {
                //if (array_key_exists('manual_shipping', $post) && 0 < $post['manual_shipping'] && array_key_exists('opship_tracking_url', $post)) {
                if (array_key_exists('manual_shipping', $post) && 0 < $post['manual_shipping']) {
                    $updateData = [
                        'opship_op_id' => $post['op_id'],
                        "opship_tracking_number" => $post['tracking_number'],
                        //"opship_tracking_url" => $post['opship_tracking_url'],
                    ];
                    if (array_key_exists('opship_tracking_url', $post)) {
                        $opship_tracking_url = $post['opship_tracking_url'];
                        $updateData['opship_tracking_url'] =  $opship_tracking_url;
                    }
                    if (array_key_exists('oshistory_courier', $post)) {
                        $trackingCourierCode = $post['oshistory_courier'];
                    }

                    if (!FatApp::getDb()->insertFromArray(OrderProductShipment::DB_TBL, $updateData, false, array(), $updateData)) {
                        LibHelper::dieJsonError(FatApp::getDb()->getError());
                    }
                } else {
                    $trackingRelation = new TrackingCourierCodeRelation();
                    $trackData = $trackingRelation->getDataByShipCourierCode($orderDetail['opshipping_carrier_code']);
                    $trackingCourierCode = !empty($trackData['tccr_tracking_courier_code']) ? $trackData['tccr_tracking_courier_code'] : '';
                }
            }
            $trackingNumber = FatApp::getPostedData("tracking_number", FatUtility::VAR_STRING, '');
            if (!$orderObj->addChildProductOrderHistory($op_id, $orderDetail["order_language_id"], $post["op_status_id"], $post["comments"], $post["customer_notified"], $trackingNumber, 0, true, $trackingCourierCode, $opship_tracking_url)) {
                Message::addErrorMessage($orderObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        } else {
            Message::addErrorMessage(Labels::getLabel('M_ERROR_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }


        if (in_array(strtolower($orderDetail['plugin_code']), ['cashondelivery', 'payatshop']) && (OrderStatus::ORDER_DELIVERED == $post["op_status_id"] || OrderStatus::ORDER_COMPLETED == $post["op_status_id"]) && Orders::ORDER_PAYMENT_PAID != $orderDetail['order_payment_status']) {
            $orderProducts = new OrderProductSearch($this->siteLangId, true, true);
            $orderProducts->joinPaymentMethod();
            $orderProducts->addMultipleFields(['op_status_id']);
            $orderProducts->addCondition('op_order_id', '=', $orderDetail['order_id']);
            $orderProducts->addCondition('op_status_id', '!=', OrderStatus::ORDER_DELIVERED);
            $orderProducts->addCondition('op_status_id', '!=', OrderStatus::ORDER_COMPLETED);
            $rs = $orderProducts->getResultSet();
            if ($rs) {
                $childOrders = FatApp::getDb()->fetchAll($rs);
                if (empty($childOrders)) {
                    $updateArray = array('order_payment_status' => Orders::ORDER_PAYMENT_PAID);
                    $whr = array('smt' => 'order_id = ?', 'vals' => array($orderDetail['order_id']));
                    if (!FatApp::getDb()->updateFromArray(Orders::DB_TBL, $updateArray, $whr)) {
                        Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
                        FatUtility::dieJsonError(Message::getHtml());
                    }
                }
                if (!empty($orderDetail['order_discount_coupon_code'])) {
                    $srch = DiscountCoupons::getSearchObject();
                    $srch->addFld('coupon_id');
                    $srch->addCondition('coupon_code', '=', $orderDetail['order_discount_coupon_code']);
                    $couponData = FatApp::getDb()->fetch($srch->getResultSet());
                    if (!empty($couponData)) {
                        if (!FatApp::getDb()->insertFromArray(CouponHistory::DB_TBL, array('couponhistory_coupon_id' => $couponData['coupon_id'], 'couponhistory_order_id' => $orderDetail['order_id'], 'couponhistory_user_id' => $orderDetail['order_user_id'], 'couponhistory_amount' => $orderDetail['order_discount_total'], 'couponhistory_added_on' => $orderDetail['order_date_added'] ))) {
                            $this->error = FatApp::getDb()->getError();
                            return false;
                        }
                    }
                    FatApp::getDb()->deleteRecords(DiscountCoupons::DB_TBL_COUPON_HOLD_PENDING_ORDER, array('smt' => 'ochold_order_id = ?', 'vals' => array($orderDetail['order_id'])));
                }
            }
        }

        $db->commitTransaction();
        $this->set('op_id', $op_id);
        $this->set('msg', Labels::getLabel('MSG_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function cancelOrder($op_id)
    {
        $this->userPrivilege->canEditSales(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;

        $op_id = FatUtility::int($op_id);
        if (1 > $op_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $orderObj = new Orders();
        $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        $srch->addOrderProductCharges();
        $srch->joinShippingCharges();
        $srch->joinAddress();
        $srch->addCondition('op_selprod_user_id', '=', $userId);
        $srch->addCondition('op_id', '=', $op_id);
        $rs = $srch->getResultSet();

        $orderDetail = FatApp::getDb()->fetch($rs);

        if (empty($orderDetail)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            CommonHelper::redirectUserReferer();
        }

        $charges = $orderObj->getOrderProductChargesArr($op_id);
        $orderDetail['charges'] = $charges;

        $address = $orderObj->getOrderAddresses($orderDetail['op_order_id']);
        $orderDetail['billingAddress'] = (isset($address[Orders::BILLING_ADDRESS_TYPE])) ? $address[Orders::BILLING_ADDRESS_TYPE] : array();
        $orderDetail['shippingAddress'] = (isset($address[Orders::SHIPPING_ADDRESS_TYPE])) ? $address[Orders::SHIPPING_ADDRESS_TYPE] : array();

        $pickUpAddress = $orderObj->getOrderAddresses($orderDetail['order_id'], $op_id);
        $orderDetail['pickupAddress'] = (!empty($pickUpAddress[Orders::PICKUP_ADDRESS_TYPE])) ? $pickUpAddress[Orders::PICKUP_ADDRESS_TYPE] : array();

        $orderDetail['comments'] = $orderObj->getOrderComments($this->siteLangId, array("op_id" => $op_id, 'seller_id' => $userId));

        $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);

        $notEligible = false;
        $notAllowedStatues = $orderObj->getNotAllowedOrderCancellationStatuses();

        if (in_array($orderDetail["op_status_id"], $notAllowedStatues)) {
            $notEligible = true;
            Message::addErrorMessage(sprintf(Labels::getLabel('LBL_this_order_already', $this->siteLangId), $orderStatuses[$orderDetail["op_status_id"]]));
        }
        $opChargesLog = new OrderProductChargeLog($op_id);
        $taxOptions = $opChargesLog->getData($this->siteLangId);
        $orderDetail['taxOptions'] = $taxOptions;

        $frm = $this->getOrderCancelForm($this->siteLangId);
        $frm->fill(array('op_id' => $op_id));

        $this->set('notEligible', $notEligible);
        $this->set('frm', $frm);
        $this->set('orderDetail', $orderDetail);
        $this->set('orderStatuses', $orderStatuses);
        $this->set('yesNoArr', applicationConstants::getYesNoArr($this->siteLangId));
        $this->_template->render(true, true);
    }

    public function cancelReason()
    {
        $this->userPrivilege->canEditSales(UserAuthentication::getLoggedUserId());
        $frm = $this->getOrderCancelForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false == $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $op_id = FatUtility::int($post['op_id']);
        if (1 > $op_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userId = $this->userParentId;

        $orderObj = new Orders();
        // $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses();

        $srch = new OrderProductSearch($this->siteLangId, true, true);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $srch->joinSellerProducts();
        $srch->joinOrderUser();
        $srch->addCondition('op_selprod_user_id', '=', $userId);
        $srch->addCondition('op_id', '=', $op_id);
        $rs = $srch->getResultSet();

        $orderDetail = FatApp::getDb()->fetch($rs);
        if (empty($orderDetail)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $notAllowedStatues = $orderObj->getNotAllowedOrderCancellationStatuses();
        $orderStatuses = Orders::getOrderProductStatusArr($this->siteLangId);

        if (in_array($orderDetail["op_status_id"], $notAllowedStatues)) {
            Message::addErrorMessage(sprintf(Labels::getLabel('LBL_this_order_already', $this->siteLangId), $orderStatuses[$orderDetail["op_status_id"]]));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$orderObj->addChildProductOrderHistory($op_id, $this->siteLangId, FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS"), $post["comments"], true)) {
            Message::addErrorMessage(Labels::getLabel('MSG_ERROR_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $pluginKey = Plugin::getAttributesById($orderDetail['order_pmethod_id'], 'plugin_code');

        $paymentMethodObj = new PaymentMethods();
        if (true === $paymentMethodObj->canRefundToCard($pluginKey, $this->siteLangId)) {
            if (false == $paymentMethodObj->initiateRefund($orderDetail, PaymentMethods::REFUND_TYPE_CANCEL)) {
                FatUtility::dieJsonError($paymentMethodObj->getError());
            }

            $resp = $paymentMethodObj->getResponse();
            if (empty($resp)) {
                FatUtility::dieJsonError(Labels::getLabel('LBL_UNABLE_TO_PLACE_GATEWAY_REFUND_REQUEST', $this->siteLangId));
            }

            // Debit from wallet if plugin/payment method support's direct payment to card of customer.
            if (!empty($resp->id)) {
                $childOrderInfo = $orderObj->getOrderProductsByOpId($op_id, $this->siteLangId);
                $txnAmount = $paymentMethodObj->getTxnAmount();
                $comments = Labels::getLabel('LBL_TRANSFERED_TO_YOUR_CARD._INVOICE_#{invoice-no}', $this->siteLangId);
                $comments = CommonHelper::replaceStringData($comments, ['{invoice-no}' => $childOrderInfo['op_invoice_number']]);
                Transactions::debitWallet($childOrderInfo['order_user_id'], Transactions::TYPE_ORDER_REFUND, $txnAmount, $this->siteLangId, $comments, $op_id, $resp->id);
            }
        }

        Message::addMessage(Labels::getLabel("MSG_Updated_Successfully", $this->siteLangId));
        $this->set('msg', Labels::getLabel('MSG_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function catalog($type = 1)
    {
        $this->userPrivilege->canViewProducts(UserAuthentication::getLoggedUserId());

        if (!$this->isShopActive($this->userParentId, 0, true)) {
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'shop'));
        }
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }

        $frmSearchCatalogProduct = $this->getCatalogProductSearchForm($type);
        $this->set('canEdit', $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId(), true));
        $this->set("frmSearchCatalogProduct", $frmSearchCatalogProduct);
        $this->set('canRequestProduct', User::canRequestProduct());
        $this->set('canAddCustomProduct', User::canAddCustomProduct());
        $this->set('canAddCustomProductAvailableToAllSellers', User::canAddCustomProductAvailableToAllSellers());
        $this->set('type', $type);
        $this->_template->addJs(array('js/cropper.js', 'js/cropper-main.js', 'js/slick.min.js'));
        $this->_template->render(true, true);
    }

    public function productTags()
    {
        $this->userPrivilege->canViewProductTags(UserAuthentication::getLoggedUserId());
        if (!$this->isShopActive($this->userParentId, 0, true)) {
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'shop'));
        }
        if (!$this->isShopActive($this->userParentId, 0, true)) {
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'shop'));
        }

        if (!User::canAddCustomProduct()) {
            FatUtility::exitWithErrorCode(404);
        }

        $this->_template->addJs('js/tagify.min.js');
        $this->_template->addJs('js/tagify.polyfills.min.js');

        $frmSearchCatalogProduct = $this->getCatalogProductSearchForm();
        $this->set("frmSearch", $frmSearchCatalogProduct);
        $this->_template->render(true, true);
    }

    public function requestedCatalog()
    {
        $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId());
        if (!$this->isShopActive($this->userParentId, 0, true)) {
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'shop'));
        }
        if (!User::canRequestProduct()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'catalog'));
        }
        $this->_template->render(true, true);
    }

    public function searchRequestedCatalog()
    {
        if (!User::canRequestProduct()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $cRequestObj = new User($this->userParentId);
        $srch = $cRequestObj->getUserCatalogRequestsObj();
        $srch->addMultipleFields(
            array(
                'scatrequest_id',
                'scatrequest_user_id',
                'scatrequest_reference',
                'scatrequest_title',
                'scatrequest_comments',
                'scatrequest_status',
                'scatrequest_date'
            )
        );
        $srch->addOrder('scatrequest_date', 'DESC');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $db = FatApp::getDb();
        $rs = $srch->getResultSet();

        $arr_listing = $db->fetchAll($rs);

        $this->set("arr_listing", $arr_listing);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('catalogReqStatusArr', User::getCatalogReqStatusArr($this->siteLangId));
        $this->_template->render(false, false);
    }

    public function addCatalogRequest()
    {
        if (!User::canRequestProduct()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $frm = $this->addNewCatalogRequestForm();
        $this->set('frm', $frm);
        $this->_template->render(false, false);
    }

    public function setUpCatalogRequest()
    {
        if (!User::canRequestProduct()) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $userId = $this->userParentId;

        $frm = $this->addNewCatalogRequestForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false == $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new User($userId);
        $reference_number = $userId . '-' . time();

        $db = FatApp::getDb();
        $db->startTransaction();

        $data = array(
            'scatrequest_user_id' => $userId,
            'scatrequest_reference' => $reference_number,
            'scatrequest_title' => $post['scatrequest_title'],
            'scatrequest_content' => $post['scatrequest_content'],
            'scatrequest_date' => date('Y-m-d H:i:s'),
        );

        if (!$obj->addCatalogRequest($data)) {
            $db->rollbackTransaction();
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $scatrequest_id = FatApp::getDb()->getInsertId();
        if (!$scatrequest_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* attach file with request [ */

        if (is_uploaded_file($_FILES['file']['tmp_name'])) {
            $uploadedFile = $_FILES['file']['tmp_name'];
            $uploadedFileExt = pathinfo($uploadedFile, PATHINFO_EXTENSION);

            if (filesize($uploadedFile) > 10240000) {
                Message::addErrorMessage(Labels::getLabel('MSG_Please_upload_file_size_less_than_10MB', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }

            $fileHandlerObj = new AttachedFile();
            if (!$res = $fileHandlerObj->saveAttachment($_FILES['file']['tmp_name'], AttachedFile::FILETYPE_SELLER_CATALOG_REQUEST, $scatrequest_id, 0, $_FILES['file']['name'], -1, true)) {
                Message::addErrorMessage($fileHandlerObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        /* ] */

        if (!$obj->notifyAdminCatalogRequest($data, $this->siteLangId)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_EMAIL_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        //send notification to admin
        $notificationData = array(
            'notification_record_type' => Notification::TYPE_CATALOG,
            'notification_record_id' => $scatrequest_id,
            'notification_user_id' => $userId,
            'notification_label_key' => Notification::NEW_CATALOG_REQUEST_NOTIFICATION,
            'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            $db->rollbackTransaction();
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db->commitTransaction();
        $this->set('msg', Labels::getLabel('MSG_CATALOG_REQUESTED_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function viewRequestedCatalog($scatrequest_id)
    {
        $scatrequest_id = FatUtility::int($scatrequest_id);
        if (1 > $scatrequest_id) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        $cRequestObj = new User($this->userParentId);
        $srch = $cRequestObj->getUserCatalogRequestsObj($scatrequest_id);
        $srch->addCondition('tucr.scatrequest_user_id', '=', $this->userParentId);
        $srch->addMultipleFields(array('scatrequest_id', 'scatrequest_title', 'scatrequest_content', 'scatrequest_comments', 'scatrequest_reference'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        $rs = $srch->getResultSet();
        $row = FatApp::getDb()->fetch($rs);
        if (!$row) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        $this->set("data", $row);
        $this->_template->render(false, false);
    }

    public function catalogRequestMsgForm($requestId = 0)
    {
        $requestId = FatUtility::int($requestId);
        $frm = $this->getCatalogRequestMessageForm($requestId);

        if (0 >= $requestId) {
            FatUtility::dieWithError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }
        $userObj = new User();
        $srch = $userObj->getUserSupplierRequestsObj($requestId);
        $srch->addFld('tusr.*');

        $rs = $srch->getResultSet();

        if (!$rs || FatApp::getDb()->fetch($rs) === false) {
            FatUtility::dieWithError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }

        $this->set('requestId', $requestId);

        $this->set('frm', $frm);
        $this->set('logged_user_id', $this->userParentId);
        $this->set('logged_user_name', UserAuthentication::getLoggedUserAttribute('user_name'));

        $searchFrm = $this->getCatalogRequestMessageSearchForm();
        $searchFrm->getField('requestId')->value = $requestId;
        $this->set('searchFrm', $searchFrm);

        $this->_template->render(false, false);
    }

    public function catalogRequestMessageSearch()
    {
        $frm = $this->getCatalogRequestMessageSearchForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pageSize = 1;

        $requestId = isset($post['requestId']) ? FatUtility::int($post['requestId']) : 0;

        $srch = new CatalogRequestMessageSearch();
        $srch->joinCatalogRequests();
        $srch->joinMessageUser();
        $srch->joinMessageAdmin();
        $srch->addCondition('scatrequestmsg_scatrequest_id', '=', $requestId);
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->addOrder('scatrequestmsg_id', 'DESC');
        $srch->addMultipleFields(
            array(
                'scatrequestmsg_id', 'scatrequestmsg_from_user_id', 'scatrequestmsg_from_admin_id',
                'admin_name', 'admin_username', 'admin_email', 'scatrequestmsg_msg',
                'scatrequestmsg_date', 'msg_user.user_name as msg_user_name', 'msg_user_cred.credential_username as msg_username',
                'msg_user_cred.credential_email as msg_user_email',
                'scatrequest_status'
            )
        );

        $rs = $srch->getResultSet();
        $messagesList = FatApp::getDb()->fetchAll($rs, 'scatrequestmsg_id');
        ksort($messagesList);

        $this->set('messagesList', $messagesList);
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', $post);

        $startRecord = ($page - 1) * $pageSize + 1;
        $endRecord = $page * $pageSize;
        $totalRecords = $srch->recordCount();
        if ($totalRecords < $endRecord) {
            $endRecord = $totalRecords;
        }
        $json['totalRecords'] = $totalRecords;
        $json['startRecord'] = $startRecord;
        $json['endRecord'] = $endRecord;

        $json['html'] = $this->_template->render(false, false, 'seller/catalog-request-messages-list.php', true, false);
        $json['loadMoreBtnHtml'] = $this->_template->render(false, false, 'seller/catalog-request-messages-list-load-more-btn.php', true, false);
        FatUtility::dieJsonSuccess($json);
    }

    public function setUpCatalogRequestMessage()
    {
        $requestId = FatApp::getPostedData('requestId', null, '0');
        $frm = $this->getCatalogRequestMessageForm($requestId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $requestId = FatUtility::int($requestId);

        $srch = new CatalogRequestSearch($this->siteLangId);
        $srch->addCondition('scatrequest_id', '=', $requestId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('scatrequest_id', 'scatrequest_status'));
        $rs = $srch->getResultSet();
        $requestRow = FatApp::getDb()->fetch($rs);
        if (!$requestRow) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* save catalog request message[ */
        $dataToSave = array(
            'scatrequestmsg_scatrequest_id' => $requestRow['scatrequest_id'],
            'scatrequestmsg_from_user_id' => $this->userParentId,
            'scatrequestmsg_from_admin_id' => 0,
            'scatrequestmsg_msg' => $post['message'],
            'scatrequestmsg_date' => date('Y-m-d H:i:s'),
        );
        $catRequestMsgObj = new CatalogRequestMessage();
        $catRequestMsgObj->assignValues($dataToSave, true);
        if (!$catRequestMsgObj->save()) {
            Message::addErrorMessage($catRequestMsgObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $scatrequestmsg_id = $catRequestMsgObj->getMainTableRecordId();
        if (!$scatrequestmsg_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_contact_Technical_team', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        /* sending of email notification[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendCatalogRequestMessageNotification($scatrequestmsg_id, $this->siteLangId)) {
            Message::addErrorMessage($emailNotificationObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        /* send notification to admin [ */
        $notificationData = array(
            'notification_record_type' => Notification::TYPE_CATALOG_REQUEST,
            'notification_record_id' => $scatrequestmsg_id,
            'notification_user_id' => $this->userParentId,
            'notification_label_key' => Notification::CATALOG_REQUEST_MESSAGE_NOTIFICATION,
            'notification_added_on' => date('Y-m-d H:i:s'),
        );

        if (!Notification::saveNotifications($notificationData)) {
            Message::addErrorMessage(Labels::getLabel("MSG_NOTIFICATION_COULD_NOT_BE_SENT", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        /* ] */

        $this->set('scatrequestmsg_scatrequest_id', $requestId);
        $this->set('msg', Labels::getLabel('MSG_Message_Submitted_Successfully!', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteRequestedCatalog()
    {
        $post = FatApp::getPostedData();
        $scatrequest_id = FatUtility::int($post['scatrequest_id']);

        if (1 > $scatrequest_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $cRequestObj = new User($this->userParentId);
        $srch = $cRequestObj->getUserCatalogRequestsObj($scatrequest_id);
        $srch->addCondition('tucr.scatrequest_user_id', '=', $this->userParentId);
        $srch->addCondition('tucr.scatrequest_status', '=', 0);
        $srch->addMultipleFields(array('scatrequest_id', 'scatrequest_status'));
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();

        $rs = $srch->getResultSet();

        $row = FatApp::getDb()->fetch($rs);

        if ($row == false || ($row != false && $row['scatrequest_status'] != User::CATALOG_REQUEST_PENDING)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$cRequestObj->deleteCatalogRequest($row['scatrequest_id'])) {
            Message::addErrorMessage(Labels::getLabel($cRequestObj->getError(), $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('scatrequest_id', $row['scatrequest_id']);
        $this->set('msg', Labels::getLabel('LBL_Record_deleted_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function searchCatalogProduct()
    {
        $this->userPrivilege->canViewProducts(UserAuthentication::getLoggedUserId());
        $frmSearchCatalogProduct = $this->getCatalogProductSearchForm();
        $post = $frmSearchCatalogProduct->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        /* echo $page; die; */
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        //$srch = Product::getSearchObject($this->siteLangId);
        $srch = new ProductSearch($this->siteLangId, null, null, false, false);
        $srch->joinProductShippedBySeller($this->userParentId);
        $srch->joinTable(AttributeGroup::DB_TBL, 'LEFT OUTER JOIN', 'product_attrgrp_id = attrgrp_id', 'attrgrp');
        $srch->joinTable(UpcCode::DB_TBL, 'LEFT OUTER JOIN', 'upc_product_id = product_id', 'upc');

        /* $cnd = $srch->addCondition( 'product_seller_id', '=',0);
          $cnd->attachCondition( 'product_added_by_admin_id', '=', applicationConstants::YES,'OR');

          if( User::canAddCustomProduct() ){
          $cnd->attachCondition('product_seller_id', '=', $this->userParentId,'OR');
          } */
        $srch->addDirectCondition(
            '((CASE
                    WHEN product_seller_id = 0 THEN product_active = 1
                    WHEN product_seller_id > 0 THEN product_active IN (1, 0)
                    END ) )'
        );
        if (User::canAddCustomProduct()) {
            $srch->addDirectCondition('((product_seller_id = 0 AND product_added_by_admin_id = ' . applicationConstants::YES . ') OR product_seller_id = ' . $this->userParentId . ')');
        } else {
            $cnd = $srch->addCondition('product_seller_id', '=', 0);
            $cnd->attachCondition('product_added_by_admin_id', '=', applicationConstants::YES, 'AND');
        }

        $srch->addCondition('product_deleted', '=', applicationConstants::NO);

        $keyword = utf8_encode(FatApp::getPostedData('keyword', FatUtility::VAR_STRING, ''));
        if (!empty($keyword)) {
            $cnd = $srch->addCondition('product_name', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('product_identifier', 'like', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('attrgrp_name', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('product_model', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('upc_code', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('product_upc', 'like', '%' . $keyword . '%');
        }

        if (FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT')) {
            $is_custom_or_catalog = FatApp::getPostedData('type', FatUtility::VAR_INT, -1);
            if ($is_custom_or_catalog > -1) {
                if ($is_custom_or_catalog > 0) {
                    $srch->addCondition('product_seller_id', '>', 0);
                } else {
                    $srch->addCondition('product_seller_id', '=', 0);
                }
            }
        }

        $product_type = FatApp::getPostedData('product_type', FatUtility::VAR_INT, -1);
        if ($product_type != -1) {
            $srch->addCondition('product_type', '=', $product_type);
        }

        $srch->addMultipleFields(
            array(
                'product_id',
                'product_identifier',
                'IFNULL(product_name, product_identifier) as product_name',
                'product_added_on',
                'product_model',
                'product_attrgrp_id',
                'attrgrp_name',
                'psbs_user_id',
                'product_seller_id ',
                'product_added_by_admin_id',
                'product_type',
                'product_active',
                'product_approved',
            )
        );
        $srch->addOrder('product_active', 'DESC');
        $srch->addOrder('product_added_on', 'DESC');
        $srch->addGroupBy('product_id');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arr_listing = $db->fetchAll($rs);

        $this->set("arr_listing", $arr_listing);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('userParentId', $this->userParentId);
        $this->set('canEdit', $this->userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId(), true));
        unset($post['page']);
        $this->set('canEditShipProfile', $this->userPrivilege->canEditShippingProfiles(UserAuthentication::getLoggedUserId(), true));
        unset($post['page']);
        $frmSearchCatalogProduct->fill($post);
        $this->set("frmSearchCatalogProduct", $frmSearchCatalogProduct);
        $this->set('activeInactiveArr', applicationConstants::getActiveInactiveArr($this->siteLangId));
        $this->set('activeInactiveClassArr', applicationConstants::getActiveInactiveClassArr());
        $this->set('approveUnApproveArr', Product::getApproveUnApproveArr($this->siteLangId));
        $this->set('approveUnApproveClassArr', product::getStatusClassArr());
        $this->_template->render(false, false);
    }

    public function searchProductTags()
    {
        $frmSearchCatalogProduct = $this->getCatalogProductSearchForm();
        $post = $frmSearchCatalogProduct->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        /* echo $page; die; */
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        //$srch = Product::getSearchObject($this->siteLangId);
        $srch = new ProductSearch($this->siteLangId, null, null, true, true, true);
        $srch->joinProductShippedBySeller($this->userParentId);
        $srch->joinTable(AttributeGroup::DB_TBL, 'LEFT OUTER JOIN', 'product_attrgrp_id = attrgrp_id', 'attrgrp');
        $srch->joinTable(UpcCode::DB_TBL, 'LEFT OUTER JOIN', 'upc_product_id = product_id', 'upc');
        $srch->addCondition('product_seller_id', '=', $this->userParentId);
        $srch->addDirectCondition(
            '((CASE
                    WHEN product_seller_id = 0 THEN product_active = 1
                    WHEN product_seller_id > 0 THEN product_active IN (1, 0)
                    END ) )'
        );
        if (User::canAddCustomProduct()) {
            $srch->addDirectCondition('((product_seller_id = 0 AND product_added_by_admin_id = ' . applicationConstants::YES . ') OR product_seller_id = ' . $this->userParentId . ')');
        } else {
            $cnd = $srch->addCondition('product_seller_id', '=', 0);
            $cnd->attachCondition('product_added_by_admin_id', '=', applicationConstants::YES, 'AND');
        }

        $srch->addCondition('product_deleted', '=', applicationConstants::NO);

        $keyword = FatApp::getPostedData('keyword', null, '');
        if (!empty($keyword)) {
            $cnd = $srch->addCondition('product_name', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('product_identifier', 'like', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('attrgrp_name', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('product_model', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('upc_code', 'like', '%' . $keyword . '%');
            $cnd->attachCondition('product_upc', 'like', '%' . $keyword . '%');
        }

        $srch->addMultipleFields(
            array(
                'product_id',
                'product_identifier',
                'IFNULL(product_name, product_identifier) as product_name',
            )
        );
        $srch->addOrder('product_active', 'DESC');
        $srch->addOrder('product_added_on', 'DESC');
        $srch->addGroupBy('product_id');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arr_listing = $db->fetchAll($rs);

        $this->set("arr_listing", $arr_listing);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('siteLangId', $this->siteLangId);

        unset($post['page']);
        $frmSearchCatalogProduct->fill($post);
        $this->set("frmSearchCatalogProduct", $frmSearchCatalogProduct);
        $this->set('canEdit', $this->userPrivilege->canEditProductTags(UserAuthentication::getLoggedUserId(), true));
        $this->_template->render(false, false);
    }

    public function setUpshippedBy()
    {
        $this->userPrivilege->canEditShippingProfiles(UserAuthentication::getLoggedUserId());

        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $product_id = FatUtility::int($post['product_id']);
        $shippedBy = $post['shippedBy'];
        $userId = $this->userParentId;

        if (1 > $product_id && 1 > $userId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $db = FatApp::getDb();
        if ($shippedBy == 'admin') {
            $whr = array('smt' => 'psbs_product_id = ? and psbs_user_id = ?', 'vals' => array($product_id, $userId));
            if (!$db->deleteRecords(Product::DB_PRODUCT_SHIPPED_BY_SELLER, $whr)) {
                Message::addErrorMessage($db->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $whr = array('smt' => 'shippro_product_id = ? and shippro_user_id = ?', 'vals' => array($product_id, $userId));
            if (!$db->deleteRecords(ShippingProfileProduct::DB_TBL, $whr)) {
                Message::addErrorMessage($db->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        } elseif ($shippedBy == 'seller') {
            $data = array('psbs_product_id' => $product_id, 'psbs_user_id' => $userId);
            if (!$db->insertFromArray(Product::DB_PRODUCT_SHIPPED_BY_SELLER, $data)) {
                Message::addErrorMessage($db->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $defaultProfileId = ShippingProfile::getDefaultProfileId($userId);
            $shipProProdData = array(
                'shippro_shipprofile_id' => $defaultProfileId,
                'shippro_product_id' => $product_id,
                'shippro_user_id' => $this->userParentId
            );
            $spObj = new ShippingProfileProduct();
            if (!$spObj->addProduct($shipProProdData)) {
                Message::addErrorMessage($spObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        } else {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function taxCategories()
    {
        if (!User::canAddCustomProduct()) {
            FatUtility::exitWithErrorCode(404);
        }
        $this->userPrivilege->canViewTaxCategory(UserAuthentication::getLoggedUserId());
        $frmSearch = $this->getTaxCatSearchForm($this->siteLangId);
        $this->set("frmSearch", $frmSearch);
        $this->_template->render(true, true);
    }

    public function searchTaxCategories()
    {
        //echo $this->userParentId;
        $userId = $this->userParentId;
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);
        $frmSearch = $this->getTaxCatSearchForm($this->siteLangId);

        $data = FatApp::getPostedData();
        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : $data['page'];
        $post = $frmSearch->getFormDataFromArray($data);
        $page = (empty($page) || $page <= 0) ? 1 : $page;
        $page = FatUtility::int($page);

        $srch = Tax::getSearchObject($this->siteLangId);
        $srch->joinTable(TaxRule::DB_TBL, 'LEFT JOIN', 'taxRule.taxrule_taxcat_id = taxcat_id', 'taxRule');
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('t.taxcat_identifier', 'like', '%' . $post['keyword'] . '%');
            $cnd->attachCondition('t_l.taxcat_name', 'like', '%' . $post['keyword'] . '%');
        }

        $activatedTaxServiceId = Tax::getActivatedServiceId();
        $srch->addCondition('taxcat_plugin_id', '=', $activatedTaxServiceId);

        $srch->addMultipleFields(array('taxcat_id', 'IFNULL(taxcat_name, taxcat_identifier) as taxcat_name', 'taxcat_code', 'taxrule_rate'));
        $srch->addCondition('taxcat_deleted', '=', 0);
        $srch->addGroupBy('taxcat_id');
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);
        $srch->addOrder('taxcat_name', 'ASC');
        $rs = $srch->getResultSet();
        $taxCatData = FatApp::getDb()->fetchAll($rs, 'taxcat_id');
        $this->set('canEdit', $this->userPrivilege->canEditTaxCategory(UserAuthentication::getLoggedUserId(), true));
        $this->set("arr_listing", $taxCatData);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);
        $this->set('userId', $userId);
        $this->set('activatedTaxServiceId', $activatedTaxServiceId);
        $this->_template->render(false, false);
    }

    public function taxRules($taxCatId)
    {
        $this->userPrivilege->canViewTaxCategory(UserAuthentication::getLoggedUserId());
        $taxCatId = FatUtility::int($taxCatId);

        $srch = Tax::getSearchObject($this->siteLangId);
        $srch->addCondition('taxcat_id', '=', $taxCatId);
        $srch->setPageSize(1);
        $srch->doNotCalculateRecords();
        $srch->addMultipleFields(array('ifnull(taxcat_name, taxcat_identifier) as taxcat_name', 'taxcat_id'));
        $rs = $srch->getResultSet();
        $data = FatApp::getDb()->fetch($rs);

        if (empty($data)) {
            FatUtility::dieWithError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }
        $taxObj = new TaxRule();
        $rulesData = $taxObj->getRules($taxCatId, $this->siteLangId);
        $combinedRulesDetails = [];
        $ruleLocations = [];
        if (!empty($rulesData)) {
            $rulesIds = array_column($rulesData, 'taxrule_id');
            $combinedRulesDetails = $taxObj->getCombinedRuleDetails($rulesIds, $this->siteLangId);
            $ruleLocations = $taxObj->getLocations($taxCatId, true, $this->siteLangId);
        }
        $this->set("combinedRulesDetails", $combinedRulesDetails);
        $this->set('taxCategory', $data['taxcat_name']);
        $this->set("rulesData", $rulesData);
        $this->set('ruleLocations', $ruleLocations);
        $this->_template->render(true, true);
    }

    public function shop($tab = '', $subTab = '')
    {
        $this->userPrivilege->canViewShop(UserAuthentication::getLoggedUserId());
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }
        $this->_template->addJs('js/jscolor.js');
        $userId = $this->userParentId;
        $shopDetails = Shop::getAttributesByUserId($userId, array('shop_id'), false);

        $shop_id = 0;
        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
        }

        $this->_template->addJs('js/cropper.js');
        $this->_template->addJs('js/cropper-main.js');

        $this->set('tab', $tab);
        $this->set('subTab', $subTab);
        $this->set('shop_id', $shop_id);
        $this->set('language', Language::getAllNames());
        $this->set('siteLangId', $this->siteLangId);
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('custom/page-css/select2.min.css'));
        $this->_template->render(true, true);
    }

    public function shopForm($callbackKeyName = '')
    {
        $userId = $this->userParentId;
        $shopDetails = Shop::getAttributesByUserId($userId, null, false);
        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shop_id = 0;
        $stateId = 0;
        $countryId = (isset($shopDetails['shop_country_id'])) ? $shopDetails['shop_country_id'] : FatApp::getConfig('CONF_COUNTRY', FatUtility::VAR_INT, 223);
        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
            $stateId = isset($shopDetails['shop_state_id']) ? $shopDetails['shop_state_id'] : 0;
        }
        $shopDetails['shop_country_code'] = Countries::getCountryById($countryId, $this->siteLangId, 'country_code');
        $shopLayoutTemplateId = isset($shopDetails['shop_ltemplate_id']) ? $shopDetails['shop_ltemplate_id'] : 0;
        if ($shopLayoutTemplateId == 0) {
            $shopLayoutTemplateId = 10001;
        }
        $this->set('shopLayoutTemplateId', $shopLayoutTemplateId);
        $shopFrm = $this->getShopInfoForm($shop_id);

        $stateObj = new States();
        $statesArr = $stateObj->getStatesByCountryId($countryId, $this->siteLangId, true, 'state_code');

        $shopFrm->getField('shop_state')->options = $statesArr;
        /* url data[ */
        $urlSrch = UrlRewrite::getSearchObject();
        $urlSrch->doNotCalculateRecords();
        $urlSrch->doNotLimitRecords();
        $urlSrch->addFld('urlrewrite_custom');
        $urlSrch->addCondition('urlrewrite_original', '=', Shop::SHOP_VIEW_ORGINAL_URL . $shop_id);
        $rs = $urlSrch->getResultSet();
        $urlRow = FatApp::getDb()->fetch($rs);
        if ($urlRow) {
            $shopDetails['urlrewrite_custom'] = $urlRow['urlrewrite_custom'];
        }
        /* ] */
        if ($shopDetails) {
            $stateCode = States::getAttributesById($stateId, 'state_code');
            $shopDetails['shop_state'] = $stateCode;
        }

        $shopFrm->fill($shopDetails);
        $shopFrm->addSecurityToken();

        $plugin = new Plugin();
        $keyName = $plugin->getDefaultPluginKeyName(Plugin::TYPE_SPLIT_PAYMENT_METHOD);

        if (!empty($callbackKeyName)) {
            $this->set('action', $callbackKeyName);
        }

        $this->set('shopFrm', $shopFrm);
        $this->set('stateId', $stateId);
        $this->set('shop_id', $shop_id);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('language', Language::getAllNames());
        $this->_template->addJs('js/jscolor.js');
        $this->_template->render(false, false);
    }

    public function shopMediaForm()
    {
        $userId = $this->userParentId;
        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shopLayoutTemplateId = $shopDetails['shop_ltemplate_id'];
        if ($shopLayoutTemplateId == 0) {
            $shopLayoutTemplateId = 10001;
        }

        $this->set('shopLayoutTemplateId', $shopLayoutTemplateId);

        $shop_id = 0;
        $stateId = 0;

        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
            $stateId = $shopDetails['shop_state_id'];
        }

        $shopLogoFrm = $this->getShopLogoForm($shop_id, $this->siteLangId);
        $shopBannerFrm = $this->getShopBannerForm($shop_id, $this->siteLangId);
        $shopBackgroundImageFrm = $this->getBackgroundImageForm($shop_id, $this->siteLangId);

        $this->set('shopDetails', $shopDetails);
        $this->set('shopLogoFrm', $shopLogoFrm);
        $this->set('shopBannerFrm', $shopBannerFrm);
        $this->set('shopBackgroundImageFrm', $shopBackgroundImageFrm);
        $this->set('language', Language::getAllNames());
        $this->set('shop_id', $shop_id);
        $this->_template->render(false, false);
    }

    public function shopImages($imageType, $lang_id = 0, $slide_screen = 0)
    {
        $userId = $this->userParentId;
        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shop_id = 0;
        $stateId = 0;
        $bannerAttachments = array();
        $logoAttachments = array();
        $backgroundAttachments = array();

        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
            $stateId = $shopDetails['shop_state_id'];

            if ($imageType == 'logo') {
                $logoAttachments = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_SHOP_LOGO, $shop_id, 0, $lang_id, false);
                $this->set('images', $logoAttachments);
                $this->set('imageFunction', 'shopLogo');
            } elseif ($imageType == 'banner') {
                $bannerAttachments = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_SHOP_BANNER, $shop_id, 0, $lang_id, false, $slide_screen);
                // CommonHelper::printArray($bannerAttachments); die;
                $this->set('images', $bannerAttachments);
                $this->set('imageFunction', 'shopBanner');
            } else {
                $backgroundAttachments = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_SHOP_BACKGROUND_IMAGE, $shop_id, 0, $lang_id, false);
                $this->set('images', $backgroundAttachments);
                $this->set('imageFunction', 'shopBackgroundImage');
            }
        }
        $this->set('imageType', $imageType);
        $this->set('shopDetails', $shopDetails);
        $this->set('shop_id', $shop_id);
        $this->set('languages', applicationConstants::bannerTypeArr());
        $this->_template->render(false, false);
    }

    public function shopLangForm($shopId, $langId, $autoFillLangData = 0)
    {
        $shop_id = FatUtility::int($shopId);
        $lang_id = FatUtility::int($langId);

        if ($shop_id == 0 || $lang_id == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request_Id', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $userId = $this->userParentId;

        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $shopLayoutTemplateId = $shopDetails['shop_ltemplate_id'];
        if ($shopLayoutTemplateId == 0) {
            $shopLayoutTemplateId = 10001;
        }
        $this->set('shopLayoutTemplateId', $shopLayoutTemplateId);

        if (!$this->isShopActive($userId, $shop_id)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(Shop::DB_TBL_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($shop_id, $lang_id);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $langData = current($translatedData);
        } else {
            $langData = Shop::getAttributesByLangId($lang_id, $shop_id);
        }

        $shopLangFrm = $this->getShopLangInfoForm($shop_id, $lang_id);
        $shopLangFrm->fill($langData);

        $this->set('shopLangFrm', $shopLangFrm);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('formLangId', $lang_id);
        $this->set('shop_id', $shop_id);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function shopThemeColor()
    {
        $userId = $this->userParentId;
        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (false == $shopDetails) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shop_id = $shopDetails['shop_id'];
        $themeColorFrm = $this->getThemeColorFrm($shopDetails['shop_ltemplate_id']);
        $themeDetails = ShopTheme::getAttributesByShopId($shop_id, array('stt_bg_color', 'stt_header_color', 'stt_text_color'));
        if (!$themeDetails['stt_bg_color'] && !$themeDetails['stt_header_color'] && !$themeDetails['stt_text_color']) {
            $templateId = $shopDetails['shop_ltemplate_id'];
            $themeDetails = ShopTheme::getDefaultShopThemeColor($shopDetails['shop_ltemplate_id']);
        }
        $themeDetails['shop_custom_color_status'] = $shopDetails['shop_custom_color_status'];
        $themeColorFrm->fill($themeDetails);
        $this->set('themeColorFrm', $themeColorFrm);
        $this->set('shop_id', $shop_id);
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    private function getThemeColorFrm($shopTemplateId = 0)
    {
        $onOffArr = applicationConstants::getOnOffArr($this->siteLangId);
        $frm = new Form('shopThemeColor');

        $frm->addSelectBox(Labels::getLabel('Lbl_Use_Custom_Color', $this->siteLangId), 'shop_custom_color_status', $onOffArr, applicationConstants::OFF, array(), '');

        if ($shopTemplateId == Shop::TEMPLATE_ONE || $shopTemplateId == Shop::TEMPLATE_TWO) {
            $fld = $frm->addTextBox(Labels::getLabel('LBL_Template_Theme_Background_Color', $this->siteLangId), 'stt_bg_color');
            $fld->addFieldTagAttribute('class', 'jscolor');
        }
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Template_Header_Color', $this->siteLangId), 'stt_header_color');
        $fld->addFieldTagAttribute('class', 'jscolor');


        $fld = $frm->addTextBox(Labels::getLabel('LBL_Template_Text_Link_Color', $this->siteLangId), 'stt_text_color');
        $fld->addFieldTagAttribute('class', 'jscolor');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        $frm->addButton('', 'btn_reset', Labels::getLabel('LBL_Reset_Default_Color', $this->siteLangId));
        return $frm;
    }

    public function setupThemeColor()
    {
        $userId = $this->userParentId;

        if (!$this->isShopActive($userId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $post = FatApp::getPostedData();

        $frm = $this->getThemeColorFrm();
        /* $post = $frm->getFormDataFromArray($post); */

        if (false == $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shopDetails = Shop::getAttributesByUserId($userId, null, false);
        $data_to_save_arr = array();
        $data_to_save_arr['shop_custom_color_status'] = $post['shop_custom_color_status'];
        $shop_id = FatUtility::int($shopDetails['shop_id']);
        $shopObj = new Shop($shop_id);
        $shopObj->assignValues($data_to_save_arr);
        if (!$shopObj->save()) {
            Message::addErrorMessage($shopObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $to_save_arr = array();
        $shopTemplateId = $shopDetails['shop_ltemplate_id'];
        /* echo $shopTemplateId; die; */
        if ($shopTemplateId == Shop::TEMPLATE_ONE || $shopTemplateId == Shop::TEMPLATE_TWO) {
            $to_save_arr['stt_bg_color'] = $post['stt_bg_color'];
        }
        $to_save_arr['stt_header_color'] = $post['stt_header_color'];
        $to_save_arr['stt_text_color'] = $post['stt_text_color'];
        $to_save_arr['stt_shop_id'] = $shop_id;
        $record = new TableRecord(Shop::DB_TBL_SHOP_THEME_COLOR);
        $record->assignValues($to_save_arr);
        if (!$record->addNew(array(), $to_save_arr)) {
            Message::addErrorMessage($record->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_SET_UP_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function resetDefaultThemeColor()
    {
        $userId = $this->userParentId;

        if (!$this->isShopActive($userId)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $shopDetails = Shop::getAttributesByUserId($userId, null, false);
        $shop_id = $shopDetails['shop_id'];
        FatApp::getDb()->deleteRecords(Shop::DB_TBL_SHOP_THEME_COLOR, array('smt' => 'stt_shop_id = ?', 'vals' => array($shop_id)));


        $this->set('msg', Labels::getLabel('MSG_SET_UP_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function shopTemplate()
    {
        $userId = $this->userParentId;
        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (false == $shopDetails) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shop_id = $shopDetails['shop_id'];
        $shopLayoutTemplateId = $shopDetails['shop_ltemplate_id'];

        $shopTemplateLayouts = LayoutTemplate::getMultipleLayouts(LayoutTemplate::LAYOUTTYPE_SHOP);

        if ($shopLayoutTemplateId == 0) {
            $shopLayoutTemplateId = 10001;
        }

        $this->set('shop_id', $shop_id);
        $this->set('shopLayoutTemplateId', $shopLayoutTemplateId);
        $this->set('shopTemplateLayouts', $shopTemplateLayouts);
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setTemplate($ltemplate_id)
    {
        $userId = $this->userParentId;
        $ltemplate_id = FatUtility::int($ltemplate_id);
        if (1 > $ltemplate_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $data = LayoutTemplate::getAttributesById($ltemplate_id);
        if (false == $data) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shopDetails = Shop::getAttributesByUserId($userId, null, false);
        if (false == $shopDetails) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shop_id = FatUtility::int($shopDetails['shop_id']);

        $shopObj = new Shop($shop_id);
        $data = array('shop_ltemplate_id' => $ltemplate_id);
        $shopObj->assignValues($data);

        if (!$shopObj->save()) {
            Message::addErrorMessage($shopObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_SET_UP_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setupShop()
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;
        $post = FatApp::getPostedData();
        $shop_id = FatUtility::int($post['shop_id']);
        unset($post['shop_id']);

        if ($shop_id > 0) {
            if (!$this->isShopActive($userId, $shop_id)) {
                Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        $stateCode = $post['shop_state'];
        $frm = $this->getShopInfoForm();
        $post = $frm->getFormDataFromArray($post, [], true);
        if (false == $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $frm->expireSecurityToken(FatApp::getPostedData());
        $post['shop_country_id'] = Countries::getCountryByCode($post['shop_country_code'], 'country_id');

        $post['shop_user_id'] = $userId;
        $stateData = States::getStateByCountryAndCode($post['shop_country_id'], $stateCode);
        $post['shop_state_id'] = $stateData['state_id'];


        if ($shop_id > 0) {
            $post['shop_updated_on'] = date('Y-m-d H:i:s');
        } else {
            $post['shop_created_on'] = date('Y-m-d H:i:s');
        }

        $post['shop_phone_dcode'] = FatApp::getPostedData('shop_phone_dcode', FatUtility::VAR_STRING, '');

        $shopObj = new Shop($shop_id);
        $shopObj->assignValues($post);

        if (!$shopObj->save()) {
            Message::addErrorMessage($shopObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $shop_id = $shopObj->getMainTableRecordId();

        $post['ss_shop_id'] = $shop_id;
        $shopSpecificsObj = new ShopSpecifics($shop_id);
        $shopSpecificsObj->assignValues($post);
        $data = $shopSpecificsObj->getFlds();
        if (!$shopSpecificsObj->addNew(array(), $data)) {
            Message::addErrorMessage($shopSpecificsObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        /* $userObj = new User( $userId );
          $vendorReturnAddress = $userObj->getUserReturnAddress( $this->siteLangId );

          if( !$vendorReturnAddress ){
          $dataToSave = array(
          'ura_country_id'=>$post['shop_country_id'],
          'ura_state_id'=> $state_id,
          'ura_zip'=>$post['shop_postalcode'],
          'ura_phone_dcode'=>ValidateElement::formatDialCode($post['shop_phone_dcode']),
          'ura_phone'=>$post['shop_phone'],
          );
          if ( !$userObj->updateUserReturnAddress($dataToSave) ) {
          Message::addErrorMessage(Labels::getLabel($userObj->getError(),$this->siteLangId));
          FatUtility::dieJsonError( Message::getHtml() );
          }
          } */

        /* url data[ */
        $shopOriginalUrl = Shop::SHOP_TOP_PRODUCTS_ORGINAL_URL . $shop_id;
        if ($post['urlrewrite_custom'] == '') {
            FatApp::getDb()->deleteRecords(UrlRewrite::DB_TBL, array('smt' => 'urlrewrite_original = ?', 'vals' => array($shopOriginalUrl)));
        } else {
            $shopObj->rewriteUrlShop($post['urlrewrite_custom']);
            $shopObj->rewriteUrlReviews($post['urlrewrite_custom']);
            $shopObj->rewriteUrlTopProducts($post['urlrewrite_custom']);
            $shopObj->rewriteUrlContact($post['urlrewrite_custom']);
            $shopObj->rewriteUrlpolicy($post['urlrewrite_custom']);
        }
        /* ] */


        $newTabLangId = 0;
        if ($shop_id > 0) {
            $languages = Language::getAllNames();
            foreach ($languages as $langId => $langName) {
                if (!$row = Shop::getAttributesByLangId($langId, $shop_id)) {
                    $newTabLangId = $langId;
                    break;
                }
            }
        } else {
            $shop_id = $shopObj->getMainTableRecordId();
            $newTabLangId = $this->siteLangId;
        }

        /* if( $newTabLangId == 0 && !$this->isMediaUploaded($shop_id))
          {
          $this->set('openMediaForm', true);
          } */
        ShippingProfile::getDefaultProfileId($this->userParentId);
        $this->set('shopId', $shop_id);
        $this->set('langId', $newTabLangId);
        $this->set('msg', Labels::getLabel('MSG_SET_UP_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function setupShopLang()
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;

        $frm = $this->getShopLangInfoForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false == $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$shopDetails = $this->isShopActive($userId, 0, true)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $shop_id = FatUtility::int($shopDetails['shop_id']);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);

        if ($lang_id <= 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request_id', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }


        $shopObj = new Shop($shop_id);
        $data = array(
            'shoplang_shop_id' => $shop_id,
            'shoplang_lang_id' => $lang_id,
            'shop_name' => $post['shop_name'],
            'shop_address_line_1' => $post['shop_address_line_1'],
            'shop_address_line_2' => $post['shop_address_line_2'],
            'shop_city' => $post['shop_city'],
            'shop_contact_person' => $post['shop_contact_person'],
            'shop_description' => $post['shop_description'],
            'shop_payment_policy' => $post['shop_payment_policy'],
            'shop_delivery_policy' => $post['shop_delivery_policy'],
            'shop_refund_policy' => $post['shop_refund_policy'],
            'shop_additional_info' => $post['shop_additional_info'],
            'shop_seller_info' => $post['shop_seller_info'],
        );

        if (!$shopObj->updateLangData($lang_id, $data)) {
            Message::addErrorMessage($shopObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(Shop::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($shop_id)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        /* saving address data to user's return address, if return address is blank[ */
        /* $userObj = new User( $userId );
          $srch = new SearchBase( User::DB_TBL_USR_RETURN_ADDR_LANG );
          $srch->addCondition( 'uralang_user_id', '=', $userId );
          $srch->addCondition( 'uralang_lang_id', '=', $lang_id );
          $srch->doNotCalculateRecords();
          $srch->doNotLimitRecords();
          $rs = $srch->getResultSet();
          $vendorReturnAddress = FatApp::getDb()->fetch( $rs );
          if( !$vendorReturnAddress ){
          $dataToSave = array(
          'lang_id'    =>    $lang_id,
          'ura_name'    =>    $post['shop_name'],
          'ura_city'    =>    $post['shop_city'],
          'ura_address_line_1'    =>    '',
          'ura_address_line_2'    =>    ''
          );
          if ( !$userObj->updateUserReturnAddressLang( $dataToSave ) ) {
          Message::addErrorMessage( Labels::getLabel($userObj->getError(),$this->siteLangId) );
          FatUtility::dieJsonError( Message::getHtml() );
          }
          } */
        /* ] */


        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = Shop::getAttributesByLangId($langId, $shop_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        /* if( $newTabLangId == 0 && !$this->isMediaUploaded($shop_id))
          {
          $this->set('openMediaForm', true);
          } */

        $this->set('shopId', $shop_id);
        $this->set('langId', $newTabLangId);
        $this->set('msg', Labels::getLabel('MSG_SET_UP_SUCCESSFULLY', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function isShopRewriteUrlUnique()
    {
        $shop_id = FatApp::getPostedData('recordId', FatUtility::VAR_INT, 0);
        $urlKeyword = FatApp::getPostedData('url_keyword');
        $shopObj = new Shop($shop_id);
        $seoUrl = $shopObj->sanitizeSeoUrl($urlKeyword);
        if (1 > $shop_id) {
            $isUnique = UrlRewrite::isCustomUrlUnique($seoUrl);
            if ($isUnique) {
                FatUtility::dieJsonSuccess(UrlHelper::generateFullUrl('', '', array(), CONF_WEBROOT_FRONT_URL) . $seoUrl);
            }
            FatUtility::dieJsonError(Labels::getLabel('MSG_NOT_AVAILABLE._PLEASE_TRY_USING_ANOTHER_KEYWORD', $this->siteLangId));
        }

        $originalUrl = $shopObj->getRewriteShopOriginalUrl();
        $customUrlData = UrlRewrite::getDataByCustomUrl($seoUrl, $originalUrl);
        if (empty($customUrlData)) {
            FatUtility::dieJsonSuccess(UrlHelper::generateFullUrl('', '', array(), CONF_WEBROOT_FRONT_URL) . $seoUrl);
        }
        FatUtility::dieJsonError(Labels::getLabel('MSG_NOT_AVAILABLE._PLEASE_TRY_USING_ANOTHER_KEYWORD', $this->siteLangId));
    }

    public function uploadShopImages()
    {
        if (!$this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId(), true)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Unauthorized_Access!', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userId = $this->userParentId;

        if (!$shopDetails = $this->isShopActive($userId, 0, true)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shop_id = $shopDetails['shop_id'];
        if (1 > $shop_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post = FatApp::getPostedData();
        if (empty($post)) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request_Or_File_not_supported', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $file_type = FatApp::getPostedData('file_type', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        $slide_screen = FatApp::getPostedData('slide_screen', FatUtility::VAR_INT, 0);
        $aspectRatio = FatApp::getPostedData('ratio_type', FatUtility::VAR_INT, 0);
        if (!$file_type) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $allowedFileTypeArr = array(AttachedFile::FILETYPE_SHOP_LOGO, AttachedFile::FILETYPE_SHOP_BANNER, AttachedFile::FILETYPE_SHOP_BACKGROUND_IMAGE);

        if (!in_array($file_type, $allowedFileTypeArr)) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['cropped_image']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_select_a_file', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $unique_record = true;
        /* if ($file_type != AttachedFile::FILETYPE_SHOP_BANNER) {
          $unique_record = true;
          } */

        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveAttachment($_FILES['cropped_image']['tmp_name'], $file_type, $shop_id, 0, $_FILES['cropped_image']['name'], -1, $unique_record, $lang_id, $slide_screen, $aspectRatio)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('file', $_FILES['cropped_image']['name']);
        $this->set('shopId', $shop_id);
        /* Message::addMessage(  Labels::getLabel('MSG_File_uploaded_successfully' ,$this->siteLangId) );
          FatUtility::dieJsonSuccess(Message::getHtml()); */
        $this->set('msg', Labels::getLabel('MSG_File_uploaded_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
        /* $this->set('msg', Message::getHtml() );
          $this->_template->render(false, false, 'json-success.php'); */
    }

    public function removeShopImage($banner_id, $langId, $imageType, $slide_screen = 0)
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;
        $langId = FatUtility::int($langId);

        if (!$shopDetails = $this->isShopActive($userId, 0, true)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shop_id = $shopDetails['shop_id'];
        if (!$shop_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($imageType == 'logo') {
            $fileType = AttachedFile::FILETYPE_SHOP_LOGO;
        } elseif ($imageType == 'banner') {
            $fileType = AttachedFile::FILETYPE_SHOP_BANNER;
        } else {
            $fileType = AttachedFile::FILETYPE_SHOP_BACKGROUND_IMAGE;
        }


        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile($fileType, $shop_id, $banner_id, 0, $langId, $slide_screen)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_File_deleted_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    /* public function CategoryBanners(){
      $this->_template->render(true,false);
      } */

    public function addCategoryBanner($prodCatId)
    {
        $userId = $this->userParentId;
        $prodCatId = FatUtility::int($prodCatId);

        if (1 > $prodCatId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if (!$shopDetails = $this->isShopActive($userId, 0, true)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shop_id = $shopDetails['shop_id'];
        if (1 > $shop_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = $this->getSellerProdCategoriesObj($userId, $shop_id, $prodCatId, $this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();

        $arr_listing = $db->fetchAll($rs, 'prodcat_id');

        if (empty($arr_listing) || (!empty($arr_listing) && !array_key_exists($prodCatId, $arr_listing))) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $attachments = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_CATEGORY_BANNER_SELLER, $shop_id, $prodCatId, -1);
        $mediaFrm = $this->getCategoryMediaForm($prodCatId);

        $this->set('mediaFrm', $mediaFrm);
        /* $this->set('mode', $mode);         */
        $this->set('userId', $userId);
        $this->set('shop_id', $shop_id);
        $this->set('prodCatId', $prodCatId);
        $this->set('attachments', $attachments);
        $this->set('bannerTypeArr', applicationConstants::bannerTypeArr());
        $this->_template->render(false, false);
    }

    /* public function categoryBannerLangForm( $prodCatId, $langId ){
      $userId = $this->userParentId;
      $prodCatId = FatUtility::int($prodCatId);
      $langId = FatUtility::int($langId);

      if( !$prodCatId || !$langId ){
      Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access',$this->siteLangId));
      FatUtility::dieWithError( Message::getHtml() );
      }

      if( !$shopDetails = $this->isShopActive($userId,0,true) ){
      Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin',$this->siteLangId));
      FatUtility::dieJsonError( Message::getHtml() );
      }

      $shop_id = $shopDetails['shop_id'];
      if( !$shop_id ){
      Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID',$this->siteLangId));
      FatUtility::dieJsonError( Message::getHtml() );
      }

      $srch = $this->getSellerProdCategoriesObj( $userId, $shop_id, $prodCatId, $this->siteLangId );
      $srch->doNotCalculateRecords();
      $srch->doNotLimitRecords();
      $db = FatApp::getDb();
      $rs = $srch->getResultSet();
      $catData = $db->fetchAll( $rs, 'prodcat_id' );

      if( empty( $catData ) || ( !empty( $catData ) && !array_key_exists( $prodCatId, $catData )) ){
      Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access',$this->siteLangId));
      FatUtility::dieWithError( Message::getHtml() );
      }

      $mediaFrm =  $this->getCategoryMediaLangForm( $prodCatId, $langId );

      $this->set('mediaFrm', $mediaFrm);
      $this->set('catData', array_shift($catData) );
      $this->set( 'shop_id', $shop_id );
      $this->set( 'prodCatId', $prodCatId );
      $this->set( 'languages', Language::getAllNames() );
      $this->set( 'formLangId', $langId );
      $this->_template->render( false, false );
      } */

    public function setUpCategoryBanner()
    {
        $userId = $this->userParentId;
        $post = FatApp::getPostedData();

        $prodCatId = FatApp::getPostedData('prodcat_id', FatUtility::VAR_INT, 0);
        $lang_id = FatApp::getPostedData('lang_id', FatUtility::VAR_INT, 0);
        if (!$prodCatId) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$shopDetails = $this->isShopActive($userId, 0, true)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shop_id = $shopDetails['shop_id'];
        if (!$shop_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_select_a_file', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = $this->getSellerProdCategoriesObj($userId, $shop_id, $prodCatId, $this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arr_listing = $db->fetchAll($rs, 'prodcat_id');

        if (empty($arr_listing) || (!empty($arr_listing) && !array_key_exists($prodCatId, $arr_listing))) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$res = $fileHandlerObj->saveAttachment(
            $_FILES['file']['tmp_name'],
            AttachedFile::FILETYPE_CATEGORY_BANNER_SELLER,
            $shop_id,
            $prodCatId,
            $_FILES['file']['name'],
            -1,
            $unique_record = true,
            $lang_id
        )) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('file', $_FILES['file']['name']);
        $this->set('prodCatId', $prodCatId);
        $this->set('shop_id', $shop_id);

        Message::addMessage(Labels::getLabel('MSG_File_uploaded_successfully', $this->siteLangId));
        FatUtility::dieJsonSuccess(Message::getHtml());
        /* $this->set('msg', Labels::getLabel('MSG_File_uploaded_successfully',$this->siteLangId));
          $this->_template->render(false, false, 'json-success.php'); */
    }

    public function removeCategoryBanner($prodCatId, $langId)
    {
        $userId = $this->userParentId;
        $prodCatId = FatUtility::int($prodCatId);
        $langId = FatUtility::int($langId);

        if (!$prodCatId) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$shopDetails = $this->isShopActive($userId, 0, true)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $shop_id = $shopDetails['shop_id'];
        if (!$shop_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $fileHandlerObj = new AttachedFile();
        if (!$fileHandlerObj->deleteFile(AttachedFile::FILETYPE_CATEGORY_BANNER_SELLER, $shop_id, 0, $prodCatId, $langId)) {
            Message::addErrorMessage($fileHandlerObj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $this->set('msg', Labels::getLabel('MSG_File_deleted_successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function searchCategoryBanners()
    {
        $userId = $this->userParentId;

        $post = FatApp::getPostedData();
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : intval($post['page']);
        $pagesize = FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10);

        $srch = $this->getSellerProdCategoriesObj($userId, 0, 0, $this->siteLangId);
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arr_listing = $db->fetchAll($rs, 'prodcat_id');

        $this->set('arr_listing', $arr_listing);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pagesize);
        $this->set('postedData', $post);

        $shopDetails = Shop::getAttributesByUserId($userId, null, false);
        $shopLayoutTemplateId = $shopDetails['shop_ltemplate_id'];
        if ($shopLayoutTemplateId == 0) {
            $shopLayoutTemplateId = 10001;
        }
        $shop_id = 0;
        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
        }

        $this->set('shopLayoutTemplateId', $shopLayoutTemplateId);
        $this->set('shop_id', $shop_id);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function orderCancellationRequests()
    {
        $this->userPrivilege->canViewCancellationRequests(UserAuthentication::getLoggedUserId());
        $frm = $this->getOrderCancellationRequestsSearchForm($this->siteLangId);
        $this->set('frmOrderCancellationRequestsSrch', $frm);
        $this->_template->render(true, true);
    }

    public function orderCancellationRequestSearch()
    {
        $this->userPrivilege->canViewCancellationRequests(UserAuthentication::getLoggedUserId());
        $frm = $this->getOrderCancellationRequestsSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $srch = $this->cancelRequestListingObj();
        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        $op_invoice_number = $post['op_invoice_number'];
        if (!empty($op_invoice_number)) {
            $srch->addCondition('op_invoice_number', '=', $op_invoice_number);
        }

        $ocrequest_date_from = $post['ocrequest_date_from'];
        if (!empty($ocrequest_date_from)) {
            $srch->addCondition('ocrequest_date', '>=', $ocrequest_date_from . ' 00:00:00');
        }

        $ocrequest_date_to = $post['ocrequest_date_to'];
        if (!empty($ocrequest_date_to)) {
            $srch->addCondition('ocrequest_date', '<=', $ocrequest_date_to . ' 23:59:59');
        }

        //$ocrequest_status = $post['ocrequest_status'];
        $ocrequest_status = FatApp::getPostedData('ocrequest_status', null, -1);
        if ($ocrequest_status > -1) {
            $ocrequest_status = FatUtility::int($ocrequest_status);
            $srch->addCondition('ocrequest_status', '=', $ocrequest_status);
        }

        $rs = $srch->getResultSet();
        $requests = FatApp::getDb()->fetchAll($rs);

        $this->set('requests', $requests);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->set('OrderCancelRequestStatusArr', OrderCancelRequest::getRequestStatusArr($this->siteLangId));
        $this->set('cancelReqStatusClassArr', OrderCancelRequest::getStatusClassArr());
        $this->_template->render(false, false, 'buyer/order-cancellation-request-search.php');
    }

    private function cancelRequestListingObj()
    {
        $srch = new OrderCancelRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrderCancelReasons();
        $srch->joinOrders();
        $srch->addCondition('op_selprod_user_id', '=', $this->userParentId);
        $srch->addMultipleFields(array('ocrequest_id', 'ocrequest_date', 'ocrequest_status', 'order_id', 'op_invoice_number', 'op_id', 'IFNULL(ocreason_title, ocreason_identifier) as ocreason_title', 'ocrequest_message', 'op_selprod_title', 'op_product_name', 'op_selprod_id', 'op_is_batch'));
        $srch->addOrder('ocrequest_date', 'DESC');
        return $srch;
    }

    public function orderReturnRequests()
    {
        $this->userPrivilege->canViewReturnRequests(UserAuthentication::getLoggedUserId());
        $frm = $this->getOrderReturnRequestsSearchForm($this->siteLangId);
        $this->set('frmOrderReturnRequestsSrch', $frm);
        $this->_template->render(true, true);
    }

    public function orderReturnRequestSearch()
    {
        $this->userPrivilege->canViewReturnRequests(UserAuthentication::getLoggedUserId());
        $frm = $this->getOrderReturnRequestsSearchForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        $page = (empty($post['page']) || $post['page'] <= 0) ? 1 : FatUtility::int($post['page']);
        $user_id = $this->userParentId;
        $keyword = $post['keyword'];
        $orrequest_date_from = $post['orrequest_date_from'];
        $orrequest_date_to = $post['orrequest_date_to'];

        $page = (empty($page) || $page <= 0) ? 1 : FatUtility::int($page);
        $pagesize = FatApp::getConfig('conf_page_size', FatUtility::VAR_INT, 10);

        $srch = $this->returnReuestsListingObj();

        $orrequest_status = FatApp::getPostedData('orrequest_status', null, '-1');
        if ($orrequest_status > -1) {
            $orrequest_status = FatUtility::int($orrequest_status);
            $srch->addCondition('orrequest_status', '=', $orrequest_status);
        }

        $orrequest_type = FatApp::getPostedData('orrequest_type', null, '-1');
        if ($orrequest_type > -1) {
            $orrequest_type = FatUtility::int($orrequest_type);
            $srch->addCondition('orrequest_type', '=', $orrequest_type);
        }

        if (!empty($orrequest_date_from)) {
            $srch->addCondition('orrequest_date', '>=', $orrequest_date_from . ' 00:00:00');
        }

        if (!empty($orrequest_date_to)) {
            $srch->addCondition('orrequest_date', '<=', $orrequest_date_to . ' 23:59:59');
        }

        if (!empty($keyword)) {
            $cnd = $srch->addCondition('op_invoice_number', '=', $keyword);
            $cnd->attachCondition('op_order_id', '=', $keyword);
            $cnd->attachCondition('op_selprod_title', 'LIKE', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op_product_name', 'LIKE', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op_brand_name', 'LIKE', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op_selprod_options', 'LIKE', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op_selprod_sku', 'LIKE', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op_product_model', 'LIKE', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('orrequest_reference', 'LIKE', '%' . $keyword . '%', 'OR');
        }

        $srch->setPageNumber($page);
        $srch->setPageSize($pagesize);

        //echo $srch->getQuery(); die();
        $rs = $srch->getResultSet();
        $requests = FatApp::getDb()->fetchAll($rs);

        $this->set('sellerPage', true);
        $this->set('buyerPage', false);

        $this->set('requests', $requests);
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('recordCount', $srch->recordCount());
        $this->set('postedData', $post);
        $this->set('returnRequestTypeArr', OrderReturnRequest::getRequestTypeArr($this->siteLangId));
        $this->set('OrderReturnRequestStatusArr', OrderReturnRequest::getRequestStatusArr($this->siteLangId));
        $this->set('OrderRetReqStatusClassArr', OrderReturnRequest::getRequestStatusClassArr());
        $this->_template->render(false, false, 'buyer/order-return-request-search.php');
    }

    private function returnReuestsListingObj()
    {
        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->addCondition('op_selprod_user_id', '=', $this->userParentId);

        $srch->addMultipleFields(
            array(
                'orrequest_id', 'orrequest_user_id', 'orrequest_qty', 'orrequest_type', 'orrequest_reference', 'orrequest_date', 'orrequest_status',
                'op_invoice_number', 'op_selprod_title', 'op_product_name', 'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model', 'op_selprod_id', 'op_is_batch', 'op_id'
            )
        );
        $srch->addOrder('orrequest_date', 'DESC');

        return $srch;
    }

    public function downloadAttachedFileForReturn($recordId, $recordSubid = 0)
    {
        $recordId = FatUtility::int($recordId);

        if (1 > $recordId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'ViewOrderReturnRequest', array($recordId)));
        }

        $file_row = AttachedFile::getAttachment(AttachedFile::FILETYPE_BUYER_RETURN_PRODUCT, $recordId, $recordSubid);

        if (false == $file_row) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'ViewOrderReturnRequest', array($recordId)));
        }
        if (!file_exists(CONF_UPLOADS_PATH . $file_row['afile_physical_path'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_File_not_found', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'ViewOrderReturnRequest', array($recordId)));
        }

        $fileName = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        AttachedFile::downloadAttachment($fileName, $file_row['afile_name']);
    }

    public function viewOrderReturnRequest($orrequest_id)
    {
        $this->userPrivilege->canViewReturnRequests(UserAuthentication::getLoggedUserId());
        $orrequest_id = FatUtility::int($orrequest_id);
        $user_id = $this->userParentId;

        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrderProductSettings();
        $srch->joinOrders();
        $srch->joinShippingCharges();
        $srch->joinOrderBuyerUser();
        $srch->joinOrderReturnReasons();
        $srch->addOrderProductCharges();

        $srch->addCondition('orrequest_id', '=', $orrequest_id);
        $srch->addCondition('op_selprod_user_id', '=', $user_id);

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(
            array(
                'orrequest_id', 'orrequest_op_id', 'orrequest_user_id', 'orrequest_qty', 'orrequest_type',
                'orrequest_date', 'orrequest_status', 'orrequest_reference', 'op_invoice_number', 'op_selprod_title', 'op_product_name',
                'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model', 'op_qty',
                'op_unit_price', 'op_selprod_user_id', 'IFNULL(orreason_title, orreason_identifier) as orreason_title', 'op_shop_id', 'op_shop_name', 'op_shop_owner_name',
                'buyer.user_name as buyer_name', 'order_tax_charged', 'op_other_charges', 'op_refund_shipping', 'op_refund_amount', 'op_commission_percentage', 'op_affiliate_commission_percentage',
                'op_commission_include_tax', 'op_commission_include_shipping', 'op_free_ship_upto', 'op_actual_shipping_charges', 'op_rounding_off','opshipping_by_seller_user_id'
            )
        );

        $rs = $srch->getResultSet();
        $request = FatApp::getDb()->fetch($rs);

        if (!$request) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'orderReturnRequests'));
        }

        $oObj = new Orders();
        $charges = $oObj->getOrderProductChargesArr($request['orrequest_op_id']);
        $request['charges'] = $charges;

        $sellerUserObj = new User($request['op_selprod_user_id']);
        $vendorReturnAddress = $sellerUserObj->getUserReturnAddress($this->siteLangId);

        $returnRequestMsgsForm = $this->getOrderReturnRequestMessageSearchForm($this->siteLangId);
        $returnRequestMsgsForm->fill(array('orrequest_id' => $request['orrequest_id']));

        $frm = $this->getOrderReturnRequestMessageForm($this->siteLangId);
        $frm->fill(array('orrmsg_orrequest_id' => $request['orrequest_id']));

        $canEscalateRequest = false;
        $canApproveReturnRequest = false;
        if ($request['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING) {
            $canEscalateRequest = true;
        }

        if (($request['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING) || $request['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED) {
            $canApproveReturnRequest = true;
        }

        if ($attachedFile = AttachedFile::getAttachment(AttachedFile::FILETYPE_BUYER_RETURN_PRODUCT, $orrequest_id)) {
            if (!empty($attachedFile['afile_physical_path']) && file_exists(CONF_UPLOADS_PATH . $attachedFile['afile_physical_path'])) {
                $this->set('attachedFile', $attachedFile);
            }
        }
        $this->set('canEdit', $this->userPrivilege->canEditReturnRequests(UserAuthentication::getLoggedUserId(), true));
        $this->set('frmMsg', $frm);
        $this->set('canEscalateRequest', $canEscalateRequest);
        $this->set('canApproveReturnRequest', $canApproveReturnRequest);
        $this->set('returnRequestMsgsForm', $returnRequestMsgsForm);
        $this->set('request', $request);
        $this->set('vendorReturnAddress', $vendorReturnAddress);
        $this->set('returnRequestTypeArr', OrderReturnRequest::getRequestTypeArr($this->siteLangId));
        $this->set('requestRequestStatusArr', OrderReturnRequest::getRequestStatusArr($this->siteLangId));
        $this->set('logged_user_name', UserAuthentication::getLoggedUserAttribute('user_name'));
        $this->set('logged_user_id', $this->userParentId);
        $this->_template->render(true, true);
    }

    public function approveOrderReturnRequest($orrequest_id)
    {
        $orrequest_id = FatUtility::int($orrequest_id);
        $user_id = $this->userParentId;

        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->joinOrderProducts();
        $srch->joinOrders();
        $srch->joinOrderBuyerUser();
        $srch->joinOrderReturnReasons();

        $srch->addCondition('orrequest_id', '=', $orrequest_id);
        $srch->addCondition('op_selprod_user_id', '=', $user_id);

        $cnd = $srch->addCondition('orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_PENDING);
        $cnd->attachCondition('orrequest_status', '=', OrderReturnRequest::RETURN_REQUEST_STATUS_ESCALATED);

        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orrequest_id', 'order_pmethod_id'));

        $rs = $srch->getResultSet();
        $requestRow = FatApp::getDb()->fetch($rs);

        if (!$requestRow) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'viewOrderReturnRequest', array($requestRow['orrequest_id'])));
        }

        $transferTo = PaymentMethods::MOVE_TO_CUSTOMER_WALLET;
        $pluginKey = Plugin::getAttributesById($requestRow['order_pmethod_id'], 'plugin_code');

        $paymentMethodObj = new PaymentMethods();
        if (true === $paymentMethodObj->canRefundToCard($pluginKey, $this->siteLangId)) {
            $transferTo = PaymentMethods::MOVE_TO_CUSTOMER_CARD;
        }

        $orrObj = new OrderReturnRequest();
        if (!$orrObj->approveRequest($requestRow['orrequest_id'], $user_id, $this->siteLangId, $transferTo)) {
            Message::addErrorMessage(Labels::getLabel($orrObj->getError(), $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'viewOrderReturnRequest', array($requestRow['orrequest_id'])));
        }

        /* email notification handling[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendOrderReturnRequestStatusChangeNotification($requestRow['orrequest_id'], $this->siteLangId)) {
            Message::addErrorMessage(Labels::getLabel($emailNotificationObj->getError(), $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'viewOrderReturnRequest', array($requestRow['orrequest_id'])));
        }
        /* ] */

        Message::addMessage(Labels::getLabel('MSG_Request_Approved_Refund', $this->siteLangId));
        FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'viewOrderReturnRequest', array($requestRow['orrequest_id'])));
    }

    public function setUpReturnOrderRequestMessage()
    {
        $orrmsg_orrequest_id = FatApp::getPostedData('orrmsg_orrequest_id', null, '0');

        $frm = $this->getOrderReturnRequestMessageForm($this->siteLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieWithError(Message::getHtml());
        }

        $orrmsg_orrequest_id = FatUtility::int($orrmsg_orrequest_id);
        $parentAndTheirChildIds = User::getParentAndTheirChildIds($this->userParentId, false, true);

        $srch = new OrderReturnRequestSearch($this->siteLangId);
        $srch->addCondition('orrequest_id', '=', $orrmsg_orrequest_id);
        $srch->addCondition('op_selprod_user_id', 'in', $parentAndTheirChildIds);
        $srch->joinOrderProducts();
        $srch->joinSellerProducts();
        $srch->joinOrderReturnReasons();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('orrequest_id', 'orrequest_status'));
        $rs = $srch->getResultSet();
        $requestRow = FatApp::getDb()->fetch($rs);
        if (!$requestRow) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        if ($requestRow['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_REFUNDED || $requestRow['orrequest_status'] == OrderReturnRequest::RETURN_REQUEST_STATUS_WITHDRAWN) {
            Message::addErrorMessage(Labels::getLabel('MSG_Message_cannot_be_posted_now,_as_order_is_refunded_or_withdrawn.', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* save return request message[ */
        $returnRequestMsgDataToSave = array(
            'orrmsg_orrequest_id' => $requestRow['orrequest_id'],
            'orrmsg_from_user_id' => UserAuthentication::getLoggedUserId(),
            'orrmsg_msg' => $post['orrmsg_msg'],
            'orrmsg_date' => date('Y-m-d H:i:s'),
        );
        $oReturnRequestMsgObj = new OrderReturnRequestMessage();
        $oReturnRequestMsgObj->assignValues($returnRequestMsgDataToSave);
        if (!$oReturnRequestMsgObj->save()) {
            Message::addErrorMessage($oReturnRequestMsgObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $orrmsg_id = $oReturnRequestMsgObj->getMainTableRecordId();
        if (!$orrmsg_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Something_went_wrong,_please_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        /* sending of email notification[ */
        $emailNotificationObj = new EmailHandler();
        if (!$emailNotificationObj->sendReturnRequestMessageNotification($orrmsg_id, $this->siteLangId)) {
            Message::addErrorMessage($emailNotificationObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        /* ] */

        $this->set('orrmsg_orrequest_id', $orrmsg_orrequest_id);
        $this->set('msg', Labels::getLabel('MSG_Message_Submitted_Successfully!', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function socialPlatforms()
    {
        $this->userPrivilege->canViewShop(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;
        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
            $stateId = $shopDetails['shop_state_id'];
        }
        $this->set('shop_id', $shop_id);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function socialPlatformSearch()
    {
        $this->userPrivilege->canViewShop(UserAuthentication::getLoggedUserId());
        $srch = SocialPlatform::getSearchObject($this->siteLangId, false);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('splatform_user_id', '=', $this->userParentId);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAll($rs);
        $this->set('canEdit', $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId(), true));
        $this->set("arr_listing", $records);
        $this->_template->render(false, false, 'seller/social-platform-search.php');
    }

    public function socialPlatformForm($splatform_id = 0)
    {
        $splatform_id = FatUtility::int($splatform_id);
        $frm = $this->getSocialPlatformForm($splatform_id);

        if (0 < $splatform_id) {
            $data = SocialPlatform::getAttributesById($splatform_id);
            if ($data === false) {
                Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST_ID', $this->siteLangId));
                FatUtility::dieWithError(Message::getHtml());
            }
            $frm->fill($data);
        }

        $this->set('splatform_id', $splatform_id);
        $this->set('frm', $frm);
        $this->set('siteLangId', $this->siteLangId);
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function socialPlatformSetup()
    {
        $this->userPrivilege->canViewShop(UserAuthentication::getLoggedUserId());
        $frm = $this->getSocialPlatformForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $splatform_id = $post['splatform_id'];
        unset($post['splatform_id']);
        $data_to_be_save = $post;
        $data_to_be_save['splatform_user_id'] = $this->userParentId;

        $recordObj = new SocialPlatform($splatform_id);
        $recordObj->assignValues($data_to_be_save, true);
        if (!$recordObj->save()) {
            Message::addErrorMessage($recordObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $splatform_id = $recordObj->getMainTableRecordId();

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = SocialPlatform::getAttributesByLangId($langId, $splatform_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->siteLangId));
        $this->set('splatformId', $splatform_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function socialPlatformLangForm($splatform_id = 0, $lang_id = 0, $autoFillLangData = 0)
    {
        $splatform_id = FatUtility::int($splatform_id);
        $lang_id = FatUtility::int($lang_id);

        if ($splatform_id == 0 || $lang_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }

        $langFrm = $this->getSocialPlatformLangForm($splatform_id, $lang_id);

        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(SocialPlatform::DB_TBL_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($splatform_id, $lang_id);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $langData = current($translatedData);
        } else {
            $langData = SocialPlatform::getAttributesByLangId($lang_id, $splatform_id);
        }

        if ($langData) {
            $langFrm->fill($langData);
        }

        $this->set('languages', Language::getAllNames());
        $this->set('splatform_id', $splatform_id);
        $this->set('splatform_lang_id', $lang_id);
        $this->set('langFrm', $langFrm);
        $this->set('formLayout', Language::getLayoutDirection($lang_id));
        $this->_template->render(false, false);
    }

    public function socialPlatformLangSetup()
    {
        $this->userPrivilege->canViewShop(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        $splatform_id = FatUtility::int($post['splatform_id']);
        $lang_id = $post['lang_id'];

        if ($splatform_id == 0 || $lang_id == 0) {
            Message::addErrorMessage('Invalid Request');
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getSocialPlatformLangForm($splatform_id, $lang_id);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());
        unset($post['splatform_id']);
        unset($post['lang_id']);
        $data_to_update = array(
            'splatformlang_splatform_id' => $splatform_id,
            'splatformlang_lang_id' => $lang_id,
            'splatform_title' => $post['splatform_title'],
        );

        $socialObj = new SocialPlatform($splatform_id);
        if (!$socialObj->updateLangData($lang_id, $data_to_update)) {
            Message::addErrorMessage($socialObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(SocialPlatform::DB_TBL_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($splatform_id)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            if (!$row = SocialPlatform::getAttributesByLangId($langId, $splatform_id)) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('msg', Labels::getLabel('LBL_Setup_Successful', $this->siteLangId));
        $this->set('splatformId', $splatform_id);
        $this->set('langId', $newTabLangId);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSocialPlatform()
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;
        $splatformId = FatApp::getPostedData('splatformId', FatUtility::VAR_INT, 0);
        if ($splatformId < 1) {
            Message::addErrorMessage(Labels::getLabel("MSG_Invalid_Access", $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = SocialPlatform::getSearchObject($this->siteLangId, false);
        $srch->addCondition('splatform_user_id', '=', $userId);
        $srch->addCondition('splatform_id', '=', $splatformId);
        $rs = $srch->getResultSet();
        $orderDetail = FatApp::getDb()->fetch($rs);

        if (!$orderDetail) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $obj = new SocialPlatform($splatformId);
        if (!$obj->deleteRecord(true)) {
            Message::addErrorMessage($obj->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        FatUtility::dieJsonSuccess(Labels::getLabel("MSG_Social_Platform_deleted!", $this->siteLangId));
    }

    public function changeSocialPlatformStatus()
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $socialPlatformId = FatApp::getPostedData('socialPlatformId', FatUtility::VAR_INT, 0);

        $data = SocialPlatform::getAttributesById($socialPlatformId, array('splatform_id', 'splatform_active'));

        $status = ($data['splatform_active'] == applicationConstants::ACTIVE) ? applicationConstants::INACTIVE : applicationConstants::ACTIVE;

        $this->updateSocialPlatformStatus($socialPlatformId, $status);

        $this->set('msg', Labels::getLabel('MSG_Status_changed_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function updateSocialPlatformStatus($socialPlatformId, $status)
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $socialPlatformId = FatUtility::int($socialPlatformId);
        $status = FatUtility::int($status);
        if (1 > $socialPlatformId || -1 == $status) {
            FatUtility::dieWithError(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId)
            );
        }
        $splatform = new SocialPlatform($socialPlatformId);
        if (!$splatform->changeStatus($status)) {
            Message::addErrorMessage($splatform->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function sellerProductsAutoComplete()
    {
        $userId = $this->userParentId;
        $pageSize = FatApp::getConfig('CONF_PAGE_SIZE');
        $db = FatApp::getDb();
        $json = array();
        $post = FatApp::getPostedData();

        $srch = SellerProduct::getSearchObject($this->siteLangId);
        $srch->doNotCalculateRecords();
        $srch->joinTable(Product::DB_TBL, 'INNER JOIN', 'p.product_id = sp.selprod_product_id', 'p');
        $srch->joinTable(Product::DB_TBL_LANG, 'LEFT OUTER JOIN', 'p.product_id = p_l.productlang_product_id AND p_l.productlang_lang_id = ' . $this->siteLangId, 'p_l');
        $srch->addCondition('selprod_user_id', '=', $userId);
        $srch->addCondition('sp.selprod_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('p.product_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('p.product_approved', '=', Product::APPROVED);
        $srch->addOrder('product_name');
        $srch->addOrder('selprod_title');
        $srch->addOrder('selprod_id');
        $srch->addMultipleFields(array('selprod_id', 'IFNULL(selprod_title  ,IFNULL(product_name, product_identifier)) as selprod_title', 'IFNULL(product_name, product_identifier) as product_name', 'selprod_price'));
        //$srch->setPageSize( $pageSize );
        if (!empty($post['keyword'])) {
            $cnd = $srch->addCondition('product_name', 'LIKE', '%' . $post['keyword'] . '%');
            //$cnd->attachCondition('option_identifier', 'LIKE', '%'. $post['keyword'] . '%', 'OR');
        }

        $rs = $srch->getResultSet();
        $products = $db->fetchAll($rs, 'selprod_id');

        if ($products) {
            foreach ($products as $selprod_id => $product) {
                $options = SellerProduct::getSellerProductOptions($product['selprod_id'], true, $this->siteLangId);

                $variantStr = $product['product_name'];
                //$variantStr .= ( $product['selprod_title'] != '') ? $product['selprod_title'] : $product['product_name'];

                if (is_array($options) && count($options)) {
                    $variantStr .= ' (';
                    $counter = 1;
                    foreach ($options as $op) {
                        $variantStr .= $op['option_name'] . ': ' . $op['optionvalue_name'];
                        if ($counter != count($options)) {
                            $variantStr .= ', ';
                        }
                        $counter++;
                    }
                    $variantStr .= ' )';
                }
                $json[] = array(
                    'id' => $selprod_id,
                    'value' => strip_tags(html_entity_decode($variantStr, ENT_QUOTES, 'UTF-8')),
                );
            }
        }

        echo json_encode(array('suggestions' => $json));
        exit;
        //die(json_encode($json));
    }

    /* private function isMediaUploaded($shopId){
      if($attachment = AttachedFile::getAttachment(AttachedFile::FILETYPE_SHOP_BANNER , $shopId, 0 )){
      return true;
      }
      return false;
      } */

    private function getCatalogRequestMessageSearchForm()
    {
        $frm = new Form('frmCatalogRequestMsgsSrch');
        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'requestId');
        return $frm;
    }

    private function getCatalogRequestMessageForm($requestId)
    {
        $frm = new Form('catalogRequestMsgForm');

        $frm->addHiddenField('', 'requestId', $requestId);
        $frm->addTextArea(Labels::getLabel('LBL_Message', $this->siteLangId), 'message');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Send', $this->siteLangId));
        return $frm;
    }

    private function getTaxCatSearchForm($langId)
    {
        $frm = new Form('frmSearchTaxCat');
        $frm->addTextBox('', 'keyword');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $this->siteLangId), array('onclick' => 'clearSearch();'));
        return $frm;
    }

    private function getSocialPlatformLangForm($splatform_id = 0, $lang_id = 0)
    {
        $frm = new Form('frmSocialPlatformLang');
        $frm->addHiddenField('', 'splatform_id', $splatform_id);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->siteLangId), 'lang_id', Language::getAllNames(), $lang_id, array(), '');
        $frm->addRequiredField(Labels::getLabel('LBL_Title', $this->siteLangId), 'splatform_title');

        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $lang_id == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->siteLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Update', $this->siteLangId));
        return $frm;
    }

    private function getSocialPlatformForm($splatform_id = 0)
    {
        if ($splatform_id > 0) {
            $iconsArr = SocialPlatform::getIconArr($this->siteLangId);
        } else {
            $iconsArr = SocialPlatform::getAvailableIconsArr($this->userParentId, $this->siteLangId);
        }
        $frm = new Form('frmSocialPlatform');
        $frm->addHiddenField('', 'splatform_id', $splatform_id);
        $frm->addRequiredField(Labels::getLabel('Lbl_Identifier', $this->siteLangId), 'splatform_identifier');
        $urlFld = $frm->addTextBox(Labels::getLabel('LBL_URL', $this->siteLangId), 'splatform_url');
        $urlFld->requirements()->setRegularExpressionToValidate(ValidateElement::URL_REGEX);
        $urlFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_This_must_be_an_absolute_URL', $this->siteLangId));
        $urlFld->requirements()->setRequired();
        $fld = $frm->addSelectBox(Labels::getLabel('Lbl_Icon_Type_from_CSS', $this->siteLangId), 'splatform_icon_class', $iconsArr, '', array(), Labels::getLabel('Lbl_Select', $this->siteLangId));
        if ($splatform_id > 0) {
            $fld->setFieldTagAttribute('disabled', 'disabled');
        }
        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->siteLangId);
        $frm->addSelectBox(Labels::getLabel('Lbl_Status', $this->siteLangId), 'splatform_active', $activeInactiveArr, '', array(), '');

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    private function isShopActive($userId, $shopId = 0, $returnResult = false)
    {
        $shop = new Shop($shopId, $userId);
        if (false == $returnResult) {
            return $shop->isActive();
        }

        if ($shop->isActive()) {
            return $shop->getData();
        }

        return false;
        //return Shop::isShopActive($userId, $shopId, $returnResult);
    }

    private function getShopInfoForm($shop_id = 0)
    {
        $frm = new Form('frmShop');
        $frm->addHiddenField('', 'shop_id', $shop_id);
        $frm->addRequiredField(Labels::getLabel('Lbl_Identifier', $this->siteLangId), 'shop_identifier');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Shop_SEO_Friendly_URL', $this->siteLangId), 'urlrewrite_custom');
        $fld->requirements()->setRequired();

        $frm->addHiddenField('', 'shop_phone_dcode');
        $phnFld = $frm->addTextBox(Labels::getLabel('Lbl_phone', $this->siteLangId), 'shop_phone', '', array('class' => 'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
        $phnFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
        $phnFld->htmlAfterField = '<span class="note">' . Labels::getLabel('LBL_e.g.', $this->siteLangId) . ': ' . implode(', ', ValidateElement::PHONE_FORMATS) . '</span>';
        $phnFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Please_enter_valid_phone_number_format.', $this->siteLangId));

        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($this->siteLangId, true, 'country_code');
        $fld = $frm->addSelectBox(Labels::getLabel('Lbl_Country', $this->siteLangId), 'shop_country_code', $countriesArr, FatApp::getConfig('CONF_COUNTRY', FatUtility::VAR_INT, 223), array(), Labels::getLabel('Lbl_Select', $this->siteLangId));
        $fld->requirement->setRequired(true);

        $frm->addSelectBox(Labels::getLabel('Lbl_State', $this->siteLangId), 'shop_state', array(), '', array(), Labels::getLabel('Lbl_Select', $this->siteLangId))->requirement->setRequired(true);

        $zipFld = $frm->addTextBox(Labels::getLabel('Lbl_Postalcode', $this->siteLangId), 'shop_postalcode');
        /* $zipFld->requirements()->setRegularExpressionToValidate(ValidateElement::ZIP_REGEX);
        $zipFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Only_alphanumeric_value_is_allowed.', $this->siteLangId)); */

        $onOffArr = applicationConstants::getOnOffArr($this->siteLangId);

        $frm->addSelectBox(Labels::getLabel('Lbl_Display_Status', $this->siteLangId), 'shop_supplier_display_status', $onOffArr);

        /* $fld = $frm->addTextBox(Labels::getLabel('LBL_Free_Shipping_On', $this->siteLangId), 'shop_free_ship_upto');
        $fld->requirements()->setInt();
        $fld->requirements()->setPositive(); */

        $fld = $frm->addTextBox(Labels::getLabel('LBL_ORDER_RETURN_AGE', $this->siteLangId), 'shop_return_age');
        $fld->requirements()->setInt();
        $fld->requirements()->setPositive();

        $fld = $frm->addTextBox(Labels::getLabel('LBL_ORDER_CANCELLATION_AGE', $this->siteLangId), 'shop_cancellation_age');
        $fld->requirements()->setInt();
        $fld->requirements()->setPositive();

        $fld = $frm->addTextBox(Labels::getLabel('LBL_Display_Time_Slots_After_Order', $this->siteLangId) . ' [' . Labels::getLabel('LBL_Hours', $this->siteLangId) . ']', 'shop_pickup_interval');
        $fld->requirements()->setInt();
        $fld->requirements()->setPositive();

        $shopDetails = Shop::getAttributesByUserId(UserAuthentication::getLoggedUserId(), null, false);
        $address = new Address(0, $this->siteLangId);
        $addresses = (is_array($shopDetails) && isset($shopDetails['shop_id'])) ? $address->getData(Address::TYPE_SHOP_PICKUP, $shopDetails['shop_id']) : '';

        $fulfillmentType = empty($addresses) ? Shipping::FULFILMENT_SHIP : Shipping::FULFILMENT_ALL;

        $fulFillmentArr = Shipping::getFulFillmentArr($this->siteLangId, $fulfillmentType);
        $fld = $frm->addSelectBox(Labels::getLabel('LBL_FULFILLMENT_METHOD', $this->siteLangId), 'shop_fulfillment_type', $fulFillmentArr, applicationConstants::NO);
        $fld->requirements()->setRequired();
        /* if($shop_id > 0){
          $fld = $frm->addButton(Labels::getLabel('Lbl_Logo',$this->siteLangId),'shop_logo',Labels::getLabel('LBL_Upload_File',$this->siteLangId),
          array('class'=>'shopFile-Js','id'=>'shop_logo','data-file_type'=>AttachedFile::FILETYPE_SHOP_LOGO));
          $fld->htmlAfterField='<span id="input-field'.AttachedFile::FILETYPE_SHOP_LOGO.'"></span>
          <div><img src="'.UrlHelper::generateUrl('Image','shopLogo',array($shop_id, $this->siteLangId, 'THUMB')).'"></div>';

          $fld1 = $frm->addButton(Labels::getLabel('Lbl_Banner',$this->siteLangId),'shop_banner',Labels::getLabel('LBL_Upload_File',$this->siteLangId),
          array('class'=>'shopFile-Js','id'=>'shop_banner','data-file_type'=>AttachedFile::FILETYPE_SHOP_BANNER));
          $fld1->htmlAfterField='<span id="input-field'.AttachedFile::FILETYPE_SHOP_BANNER.'"></span>
          <div><img src="'.UrlHelper::generateUrl('Image','shopBanner',array($shop_id, $this->siteLangId, 'THUMB')).'"></div>';
          } */
        // $frm->addHtml('', '', '<div id="map" style="width:1500px; height:500px"></div>');

        /* $alphanumericFld = $frm->addRequiredField(Labels::getLabel('LBL_Invoice_number_starts_from', $this->siteLangId), 'shop_invoice_prefix', '', array('placeholder' => Labels::getLabel('LBL_Alphanumeric_value', $this->siteLangId)));
        $alphanumericFld->requirements()->setRegularExpressionToValidate(ValidateElement::ZIP_REGEX);
        $alphanumericFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Only_alphanumeric_value_is_allowed.', $this->siteLangId));
        $numericFld = $frm->addIntegerField(Labels::getLabel('LBL_Invoice_number_starts_from', $this->siteLangId), 'shop_invoice_suffix', '', array('placeholder' => Labels::getLabel('LBL_Integer_value', $this->siteLangId)));
        $numericFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Only_numeric_value_is_allowed.', $this->siteLangId));
        $alphanumericFld->attachField($numericFld); */

        $fld = $frm->addTextarea(Labels::getLabel("LBL_Government_Information_on_invoices", $this->siteLangId), 'shop_invoice_codes');
        $fld->htmlAfterField = "<small>" . Labels::getLabel("LBL_Information_mandated_by_the_Government_on_invoices.", $this->siteLangId) . "</small>";

        $frm->addHiddenField('', 'shop_lat');
        $frm->addHiddenField('', 'shop_lng');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    private function getShopLogoForm($shop_id, $langId)
    {
        $frm = new Form('frmShopLogo');
        $frm->addHiddenField('', 'shop_id', $shop_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('Lbl_Language', $langId), 'lang_id', $bannerTypeArr, '', array('class' => 'logo-language-js'), '');
        $ratioArr = AttachedFile::getRatioTypeArray($this->siteLangId);
        $frm->addRadioButtons(Labels::getLabel('LBL_Ratio', $this->siteLangId), 'ratio_type', $ratioArr, AttachedFile::RATIO_TYPE_SQUARE, array('class' => 'list-inline'));
        $frm->addHiddenField('', 'file_type', AttachedFile::FILETYPE_SHOP_LOGO);
        $frm->addHiddenField('', 'logo_min_width');
        $frm->addHiddenField('', 'logo_min_height');
        $frm->addFileUpload(Labels::getLabel('LBL_Upload', $this->siteLangId), 'shop_logo', array('accept' => 'image/*', 'data-frm' => 'frmShopLogo'));
        return $frm;
    }

    private function getBackgroundImageForm($shop_id, $langId)
    {
        $frm = new Form('frmBackgroundImage');
        $frm->addHiddenField('', 'shop_id', $shop_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('Lbl_Language', $langId), 'lang_id', $bannerTypeArr, '', array('class' => 'bg-language-js'), '');
        $fld = $frm->addButton(
            Labels::getLabel('Lbl_Background_Image', $langId),
            'shop_background_image',
            Labels::getLabel('LBL_Upload_Background_Image', $this->siteLangId),
            array('class' => 'shopFile-Js', 'id' => 'shop_background_image', 'data-file_type' => AttachedFile::FILETYPE_SHOP_BACKGROUND_IMAGE, 'data-frm' => 'frmBackgroundImage')
        );
        return $frm;
    }

    private function getShopBannerForm($shop_id, $langId)
    {
        $frm = new Form('frmShopBanner');
        $frm->addHiddenField('', 'shop_id', $shop_id);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('Lbl_Language', $langId), 'lang_id', $bannerTypeArr, '', array('class' => 'banner-language-js'), '');
        $screenArr = applicationConstants::getDisplaysArr($this->siteLangId);
        $frm->addSelectBox(Labels::getLabel("LBL_Display_For", $this->siteLangId), 'slide_screen', $screenArr, '', array(), '');
        $frm->addHiddenField('', 'file_type', AttachedFile::FILETYPE_SHOP_BANNER);
        $frm->addHiddenField('', 'banner_min_width');
        $frm->addHiddenField('', 'banner_min_height');
        $frm->addFileUpload(Labels::getLabel('LBL_Upload', $this->siteLangId), 'shop_banner', array('accept' => 'image/*', 'data-frm' => 'frmShopBanner'));
        return $frm;
    }

    private function getShopLangInfoForm($shop_id = 0, $lang_id = 0)
    {
        $frm = new Form('frmShopLang');
        $frm->addHiddenField('', 'shop_id', $shop_id);
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $lang_id), 'lang_id', Language::getAllNames(), $lang_id, array(), '');
        $frm->addRequiredField(Labels::getLabel('LbL_Shop_Name', $lang_id), 'shop_name');
        $frm->addRequiredField(Labels::getLabel('LBL_SHOP_ADDRESS_LINE_1', $lang_id), 'shop_address_line_1');
        $frm->addTextBox(Labels::getLabel('LBL_SHOP_ADDRESS_LINE_2', $lang_id), 'shop_address_line_2');
        $frm->addTextBox(Labels::getLabel('Lbl_Shop_City', $lang_id), 'shop_city');
        $frm->addTextBox(Labels::getLabel('Lbl_Contact_Person', $lang_id), 'shop_contact_person');
        $frm->addTextarea(Labels::getLabel('Lbl_Description', $lang_id), 'shop_description');
        $frm->addTextarea(Labels::getLabel('Lbl_Payment_Policy', $lang_id), 'shop_payment_policy');
        $frm->addTextarea(Labels::getLabel('Lbl_Delivery_Policy', $lang_id), 'shop_delivery_policy');
        $frm->addTextarea(Labels::getLabel('Lbl_Refund_Policy', $lang_id), 'shop_refund_policy');
        $frm->addTextarea(Labels::getLabel('Lbl_Additional_Information', $lang_id), 'shop_additional_info');
        $frm->addTextarea(Labels::getLabel('Lbl_Seller_Information', $lang_id), 'shop_seller_info');
        /* $fld = $frm->addButton(Labels::getLabel('Lbl_Logo',$this->siteLangId),'shop_logo',Labels::getLabel('LBL_Upload_Logo',$this->siteLangId),
          array('class'=>'shopFile-Js','id'=>'shop_logo','data-file_type'=>AttachedFile::FILETYPE_SHOP_LOGO));

          $fld1 =  $frm->addButton(Labels::getLabel('LBL_Banner',$this->siteLangId),'shop_banner',Labels::getLabel('LBL_Upload_Banner',$this->siteLangId),array('class'=>'shopFile-Js','id'=>'shop_banner','data-file_type'=>AttachedFile::FILETYPE_SHOP_BANNER)); */

        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $lang_id == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $lang_id), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $lang_id));
        return $frm;
    }

    private function getCatalogProductSearchForm($type = '')
    {
        $frm = new Form('frmSearchCatalogProduct');
        $frm->addTextBox(Labels::getLabel('LBL_Search_By', $this->siteLangId), 'keyword');

        /* if( !User::canAddCustomProductAvailableToAllSellers() ){ */
        if (FatApp::getConfig('CONF_ENABLED_SELLER_CUSTOM_PRODUCT')) {
            //$frm->addSelectBox(Labels::getLabel('LBL_Product', $this->siteLangId), 'type', array(-1 => Labels::getLabel('LBL_All', $this->siteLangId)) + applicationConstants::getCatalogTypeArrForFrontEnd($this->siteLangId), '-1', array('id' => 'type'), '');
            $frm->addHiddenField('', 'type', $type);
        }

        $frm->addSelectBox(Labels::getLabel('LBL_Product_Type', $this->siteLangId), 'product_type', array(-1 => Labels::getLabel('LBL_All', $this->siteLangId)) + Product::getProductTypes($this->siteLangId), '-1', array(), '');
        /* }  */

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Submit', $this->siteLangId));

        /* if( !User::canAddCustomProductAvailableToAllSellers() ){ */
        $frm->addButton('', 'btn_clear', Labels::getLabel('LBL_Clear', $this->siteLangId));
        /* } */
        //$fldSubmit->attachField($fldCancel);
        $frm->addHiddenField('', 'page');
        return $frm;
    }

    private function addNewCatalogRequestForm()
    {
        $frm = new Form('frmAddCatalogRequest', array('enctype' => "multipart/form-data"));
        $frm->addRequiredField(Labels::getLabel('LBL_Title', $this->siteLangId), 'scatrequest_title');
        /* $fld = $frm->addHtmlEditor(Labels::getLabel('LBL_Content',$this->siteLangId),'scatrequest_content');
          $fld->htmlBeforeField = '<div class="editor-bar">';
          $fld->htmlAfterField = '</div>'; */
        $frm->addTextArea(Labels::getLabel('LBL_Content', $this->siteLangId), 'scatrequest_content');
        $fileFld = $frm->addFileUpload(Labels::getLabel('LBL_Upload_File', $this->siteLangId), 'file', array('accept' => 'image/*,.zip', 'enctype' => "multipart/form-data"));
        $fileFld->htmlBeforeField = '<div class="filefield"><span class="filename"></span>';
        $fileFld->htmlAfterField = '</div><span class="form-text text-muted">' . Labels::getLabel('MSG_Only_Image_extensions_and_zip_is_allowed', $this->siteLangId) . '</span>';
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    private function getSellerProdCategoriesObj($userId, $shopId = 0, $prodcat_id = 0, $lang_id = 0)
    {
        return Shop::getProdCategoriesObj($userId, $lang_id, $shopId, $prodcat_id);
    }

    private function getCategoryMediaForm($prodCatId)
    {
        $frm = new Form('frmCategoryMedia');
        $frm->addHiddenField('', 'prodcat_id', $prodCatId);
        $bannerTypeArr = applicationConstants::bannerTypeArr();
        $frm->addSelectBox(Labels::getLabel('Lbl_Language', $this->siteLangId), 'lang_id', $bannerTypeArr, '', array(), '');
        $fld1 = $frm->addButton('', 'category_banner', Labels::getLabel('LBL_Upload_File', $this->siteLangId), array('class' => 'catFile-Js', 'id' => 'category_banner'));
        return $frm;
    }

    private function getOrderCommentsForm($orderData = array(), $processingOrderStatus = [])
    {
        $frm = new Form('frmOrderComments');
        $frm->addTextArea(Labels::getLabel('LBL_Your_Comments', $this->siteLangId), 'comments');
        $orderStatusArr = Orders::getOrderProductStatusArr($this->siteLangId, $processingOrderStatus, $orderData['op_status_id']);

        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->siteLangId), 'op_status_id', $orderStatusArr, '', [], Labels::getLabel('Lbl_Select', $this->siteLangId));
        $fld->requirements()->setRequired();

        $frm->addSelectBox(Labels::getLabel('LBL_Notify_Customer', $this->siteLangId), 'customer_notified', applicationConstants::getYesNoArr($this->siteLangId), applicationConstants::YES, array(), Labels::getLabel('Lbl_Select', $this->siteLangId))->requirements()->setRequired();

        if (array_key_exists('opship_tracking_number', $orderData) && empty($orderData['opship_tracking_number'])) {
            $manualFld = $frm->addCheckBox(Labels::getLabel('LBL_SELF_SHIPPING', $this->siteLangId), 'manual_shipping', 1, array(), false, 0);

            $manualShipUnReqObj = new FormFieldRequirement('manual_shipping', Labels::getLabel('LBL_SELF_SHIPPING', $this->siteLangId));
            $manualShipUnReqObj->setRequired(false);
            $manualShipReqObj = new FormFieldRequirement('manual_shipping', Labels::getLabel('LBL_SELF_SHIPPING', $this->siteLangId));
            $manualShipReqObj->setRequired(true);

            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS", FatUtility::VAR_INT, OrderStatus::ORDER_SHIPPED), 'eq', 'manual_shipping', $manualShipReqObj);
            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS", FatUtility::VAR_INT, OrderStatus::ORDER_SHIPPED), 'ne', 'manual_shipping', $manualShipUnReqObj);


            $frm->addTextBox(Labels::getLabel('LBL_Tracking_Number', $this->siteLangId), 'tracking_number');

            $trackingUnReqObj = new FormFieldRequirement('tracking_number', Labels::getLabel('LBL_Tracking_Number', $this->siteLangId));
            $trackingUnReqObj->setRequired(false);
            $trackingReqObj = new FormFieldRequirement('tracking_number', Labels::getLabel('LBL_Tracking_Number', $this->siteLangId));
            $trackingReqObj->setRequired(true);

            $manualFld->requirements()->addOnChangerequirementUpdate(applicationConstants::YES, 'eq', 'tracking_number', $trackingReqObj);
            $manualFld->requirements()->addOnChangerequirementUpdate(applicationConstants::NO, 'eq', 'tracking_number', $trackingUnReqObj);

            $trackUrlFld = $frm->addTextBox(Labels::getLabel('LBL_TRACKING_URL', $this->siteLangId), 'opship_tracking_url');
            $trackUrlFld->htmlAfterField = '<span class="note">' . Labels::getLabel('LBL_ENTER_THE_URL_TO_TRACK_THE_SHIPMENT.', $this->siteLangId) . '</span>';

            $trackingUrlUnReqObj = new FormFieldRequirement('opship_tracking_url', Labels::getLabel('LBL_TRACKING_URL', $this->siteLangId));
            $trackingUrlUnReqObj->setRequired(false);
            $trackingurlReqObj = new FormFieldRequirement('opship_tracking_url', Labels::getLabel('LBL_TRACKING_URL', $this->siteLangId));
            $trackingurlReqObj->setRequired(true);

            $manualFld->requirements()->addOnChangerequirementUpdate(applicationConstants::YES, 'eq', 'opship_tracking_url', $trackingurlReqObj);
            $manualFld->requirements()->addOnChangerequirementUpdate(applicationConstants::NO, 'eq', 'opship_tracking_url', $trackingUrlUnReqObj);

            $shipmentTracking = new ShipmentTracking();
            if (false !== $shipmentTracking->init($this->siteLangId) && false !== $shipmentTracking->getTrackingCouriers()) {
                $trackCarriers = $shipmentTracking->getResponse();

                $frm->addSelectBox(Labels::getLabel('LBL_TRACKING_COURIER', $this->siteLangId), 'oshistory_courier', $trackCarriers, '', array(), Labels::getLabel('LBL_Select', $this->siteLangId));

                $trackCarrierFldUnReqObj = new FormFieldRequirement('oshistory_courier', Labels::getLabel('LBL_TRACKING_COURIER', $this->siteLangId));
                $trackCarrierFldUnReqObj->setRequired(false);

                $trackCarrierFldReqObj = new FormFieldRequirement('oshistory_courier', Labels::getLabel('LBL_TRACKING_COURIER', $this->siteLangId));
                $trackCarrierFldReqObj->setRequired(true);

                $manualFld->requirements()->addOnChangerequirementUpdate(applicationConstants::YES, 'eq', 'oshistory_courier', $trackCarrierFldReqObj);
                $manualFld->requirements()->addOnChangerequirementUpdate(applicationConstants::NO, 'eq', 'oshistory_courier', $trackCarrierFldUnReqObj);
            }
        }

        $frm->addHiddenField('', 'op_id', 0);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        return $frm;
    }

    private function getSubscriptionOrderSearchForm($langId)
    {
        $currency_id = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
        $currencyData = Currency::getAttributesById($currency_id, array('currency_code', 'currency_symbol_left', 'currency_symbol_right'));
        $currencySymbol = ($currencyData['currency_symbol_left'] != '') ? $currencyData['currency_symbol_left'] : $currencyData['currency_symbol_right'];

        $frm = new Form('frmOrderSrch');
        $frm->addTextBox('', 'keyword', '', array('placeholder' => Labels::getLabel('LBL_Keyword', $langId)));
        /* $frm->addSelectBox('','status', Orders::getOrderSubscriptionStatusArr( $langId, unserialize(FatApp::getConfig("CONF_SUBSCRIPTION_ORDER_STATUS")) ), '', array(), Labels::getLabel('LBL_Status', $langId) ); */
        $frm->addDateField('', 'date_from', '', array('placeholder' => Labels::getLabel('LBL_Date_From', $langId), 'readonly' => 'readonly'));
        $frm->addDateField('', 'date_to', '', array('placeholder' => Labels::getLabel('LBL_Date_To', $langId), 'readonly' => 'readonly'));
        /* $frm->addTextBox( '', 'price_from', '', array('placeholder' => Labels::getLabel('LBL_Order_From', $langId).' ['.$currencySymbol.']' ) );
          $frm->addTextBox( '', 'price_to', '', array('placeholder' => Labels::getLabel('LBL_Order_to', $langId).' ['.$currencySymbol.']' ) ); */
        $fldSubmit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $langId));
        $fldCancel = $frm->addButton("", "btn_clear", Labels::getLabel("LBL_Clear", $langId), array('onclick' => 'clearSearch();'));
        $frm->addHiddenField('', 'page');
        //$fldSubmit->attachField($fldCancel);
        return $frm;
    }

    private function getOrderCancelForm($langId)
    {
        $frm = new Form('frmOrderCancel');
        $frm->addHiddenField('', 'op_id');
        $fld = $frm->addTextArea(Labels::getLabel('LBL_Comments', $langId), 'comments');
        $fld->requirements()->setRequired(true);
        $fld->requirements()->setCustomErrorMessage(Labels::getLabel('ERR_Reason_cancellation', $langId));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));
        return $frm;
    }

    /* -- - --   Packges  ----- */

    public function packages()
    {
        $this->userPrivilege->canViewSubscription(UserAuthentication::getLoggedUserId());
        if (!FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl());
        }
        $includeFreeSubscription = OrderSubscription::canUserBuyFreeSubscription($this->siteLangId, $this->userParentId);
        $packagesArr = SellerPackages::getSellerVisiblePackages($this->siteLangId, $includeFreeSubscription);

        $currentPlanData = OrderSubscription::getUserCurrentActivePlanDetails($this->siteLangId, $this->userParentId, array(OrderSubscription::DB_TBL_PREFIX . 'plan_id'));
        $currentActivePlanId = is_array($currentPlanData) && isset($currentPlanData[OrderSubscription::DB_TBL_PREFIX . 'plan_id']) ? $currentPlanData[OrderSubscription::DB_TBL_PREFIX . 'plan_id'] : 0;

        foreach ($packagesArr as $key => $package) {
            $packagesArr[$key]['plans'] = SellerPackagePlans::getSellerVisiblePackagePlans($package[SellerPackages::DB_TBL_PREFIX . 'id']);
            $packagesArr[$key]['cheapPlan'] = SellerPackagePlans::getCheapestPlanByPackageId($package[SellerPackages::DB_TBL_PREFIX . 'id']);
        }
        $obj = new Extrapage();
        $pageData = $obj->getContentByPageType(Extrapage::SUBSCRIPTION_PAGE_BLOCK, $this->siteLangId);
        $this->set('pageData', $pageData);

        $this->set('includeFreeSubscription', $includeFreeSubscription);
        $this->set('currentActivePlanId', $currentActivePlanId);
        $this->set('packagesArr', $packagesArr);
        $this->_template->render(true, true);
    }

    /*  Subscription Orders */

    public function subscriptions()
    {
        $this->userPrivilege->canViewSubscription(UserAuthentication::getLoggedUserId());
        if (!FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId)
            );
            FatApp::redirectUser(UrlHelper::generateUrl('account'));
        }
        $currentActivePlan = OrderSubscription::getUserCurrentActivePlanDetails($this->siteLangId, $this->userParentId, array(OrderSubscription::DB_TBL_PREFIX . 'till_date', OrderSubscription::DB_TBL_PREFIX . 'price', OrderSubscription::DB_TBL_PREFIX . 'type'));

        $frmOrderSrch = $this->getSubscriptionOrderSearchForm($this->siteLangId);
        $userId = $this->userParentId;
        $autoRenew = User::getAttributesById($userId, 'user_autorenew_subscription');
        $this->set('canEdit', $this->userPrivilege->canEditSubscription(UserAuthentication::getLoggedUserId(), true));
        $this->set('currentActivePlan', $currentActivePlan);
        $this->set('frmOrderSrch', $frmOrderSrch);
        $this->set('autoRenew', $autoRenew);
        $this->_template->render(true, true);
    }

    public function addCatalogPopup()
    {
        $this->_template->render(false, false);
    }

    public function sellerShippingForm($productId)
    {
        $this->userPrivilege->canEditShippingProfiles(UserAuthentication::getLoggedUserId());
        $productId = FatUtility::int($productId);
        $srch = Product::getSearchObject($this->siteLangId);
        $srch->addMultipleFields(
            array(
                'product_id', 'product_ship_package', 'product_seller_id', 'product_added_by_admin_id',
                'IFNULL(product_name,product_identifier)as product_name'
            )
        );
        $srch->addCondition('product_id', '=', $productId);
        $rs = $srch->getResultSet();
        $productDetails = FatApp::getDb()->fetch($rs);

        if ($productDetails['product_seller_id'] > 0) {
            Message::addErrorMessage(
                Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId)
            );
            FatUtility::dieJsonError(Message::getHtml());
        }
        $shipping_rates = array();
        $post = FatApp::getPostedData();
        $userId = $this->userParentId;

        //$shipping_rates = Products::getProductShippingRates();
        $this->set('siteLangId', $this->siteLangId);
        $shipping_rates = array();

        $shipping_rates = Product::getProductShippingRates($productId, $this->siteLangId, 0, $userId);
        $shippingDetails = Product::getProductShippingDetails($productId, $this->siteLangId, $userId);
        if (isset($shippingDetails['ps_from_country_id']) && $shippingDetails['ps_from_country_id']) {
            $shippingDetails['shipping_country'] = Countries::getCountryById($shippingDetails['ps_from_country_id'], $this->siteLangId, 'country_name');
        }
        $shippingDetails['ps_product_id'] = $productId;
        $shippingDetails['product_ship_package'] = $productDetails['product_ship_package'];
        $shippingFrm = $this->getShippingForm();

        /* [ GET ATTACHED PROFILE ID */
        $profSrch = ShippingProfileProduct::getSearchObject();
        $profSrch->addCondition('shippro_product_id', '=', $productId);
        $profSrch->addCondition('shippro_user_id', '=', $userId);
        $proRs = $profSrch->getResultSet();
        $profileData = FatApp::getDb()->fetch($proRs);
        if (!empty($profileData)) {
            $shippingDetails['shipping_profile'] = $profileData['profile_id'];
        }
        /* ]*/

        $shippingFrm->fill($shippingDetails);

        $this->set('shippingFrm', $shippingFrm);

        $this->set('productDetails', $productDetails);
        $this->set('product_id', $productId);
        $this->set('shipping_rates', $shipping_rates);
        $this->_template->render(false, false);
    }

    public function getShippingForm()
    {
        $frm = new Form('frmCustomProduct');
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Shipping_country', $this->siteLangId), 'shipping_country');

        $shipProfileArr = ShippingProfile::getProfileArr($this->siteLangId, $this->userParentId, true, true);
        $frm->addSelectBox(Labels::getLabel('LBL_Shipping_Profile', $this->siteLangId), 'shipping_profile', $shipProfileArr, '', [])->requirements()->setRequired();

        /* if (FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 1)) {
            $shipPackArr = ShippingPackage::getAllNames();
            $frm->addSelectBox(Labels::getLabel('LBL_Shipping_Package', $this->siteLangId), 'product_ship_package', $shipPackArr)->requirements()->setRequired();
        } */
        //$fld = $frm->addCheckBox(Labels::getLabel('LBL_Free_Shipping', $this->siteLangId), 'ps_free', 1);
        //$frm->addHtml('', '', '<div id="tab_shipping"></div>');

        $frm->addHiddenField('', 'ps_from_country_id');
        $frm->addHiddenField('', 'ps_product_id');
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Cancel', $this->siteLangId));
        return $frm;
    }

    public function setupSellerShipping()
    {
        $frm = $this->getShippingForm();
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        $productShiping = FatApp::getPostedData('product_shipping');



        if (false === $post) {
            FatUtility::dieWithError(current($frm->getValidationErrors()));
        }
        $product_id = FatUtility::int($post['ps_product_id']);

        /* Validate product belongs to current logged seller[ */
        if ($product_id) {
            $productRow = Product::getAttributesById($product_id, array('product_seller_id'));
            if ($productRow['product_seller_id'] != 0) {
                FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            }
        }
        /* ] */

        unset($post['product_id']);
        unset($post['product_shipping']);
        //$prodObj = new Product($product_id);

        /* $prod = new Product($product_id);
        if (!$prod->saveProductData($post)) {
            Message::addErrorMessage($prod->getError());
            FatUtility::dieWithError(Message::getHtml());
        } */

        /* Save Product Shipping  [ */
        $data_to_be_save = $post;
        $data_to_be_save['ps_product_id'] = $product_id;
        if (!$this->addUpdateProductSellerShipping($product_id, $data_to_be_save)) {
            Message::addErrorMessage(FatApp::getDb()->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        /* ] */

        /* Save Prodcut Shipping Details [ */
        /* if (!$this->addUpdateProductShippingRates($product_id, $productShiping)) {
            Message::addErrorMessage($taxObj->getError());
            FatUtility::dieWithError(Message::getHtml());
        } */
        /* ] */

        if (isset($post['shipping_profile']) && $post['shipping_profile'] > 0) {
            $shipProProdData = array(
                'shippro_shipprofile_id' => $post['shipping_profile'],
                'shippro_product_id' => $product_id,
                'shippro_user_id' => $this->userParentId
            );
            $spObj = new ShippingProfileProduct();
            if (!$spObj->addProduct($shipProProdData)) {
                Message::addErrorMessage($spObj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $this->set('msg', Labels::getLabel('LBL_Shipping_Setup_Successful', $this->siteLangId));
        $this->set('product_id', $product_id);

        $this->_template->render(false, false, 'json-success.php');
    }

    public function toggleAutoRenewalSubscription()
    {
        $userId = $this->userParentId;
        $status = User::getAttributesById($userId, 'user_autorenew_subscription');
        if ($status) {
            $status = applicationConstants::OFF;
        } else {
            $status = applicationConstants::ON;
        }
        $dataToUpdate = array('user_autorenew_subscription' => $status);
        $record = new User($userId);
        $record->assignValues($dataToUpdate);

        if (!$record->save()) {
            Message::addErrorMessage(Labels::getLabel('M_Unable_to_Process_the_request,Please_try_later', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('M_Settings_updated_successfully', $this->siteLangId));
        $this->set('autoRenew', $status);
        $this->_template->render(false, false, 'json-success.php');
    }

    public function productLinks($product_id)
    {
        //$this->objPrivilege->canViewProducts();
        $product_id = FatUtility::int($product_id);
        if ($product_id == 0) {
            FatUtility::dieWithError($this->str_invalid_request);
        }
        $prodCatObj = new ProductCategory();
        $arr_options = $prodCatObj->getProdCatTreeStructure(0, $this->siteLangId);
        $prodObj = new Product();
        $product_categories = $prodObj->getProductCategories($product_id);

        $this->set('selectedCats', $product_categories);
        $this->set('arr_options', $arr_options);
        $this->set('product_id', $product_id);
        $this->_template->render(false, false);
    }

    public function updateProductLink()
    {
        //$this->objPrivilege->canEditProducts();
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $product_id = FatUtility::int($post['product_id']);
        $option_id = FatUtility::int($post['option_id']);
        if (!$product_id || !$option_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $prodObj = new Product($product_id);
        if (!$prodObj->addUpdateProductCategory($option_id)) {
            Message::addErrorMessage(Labels::getLabel($prodObj->getError(), FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1)));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('LBL_Record_Updated_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function removeProductCategory()
    {
        $post = FatApp::getPostedData();
        if (false === $post) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $product_id = FatUtility::int($post['product_id']);
        $option_id = FatUtility::int($post['option_id']);
        if (!$product_id || !$option_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieWithError(Message::getHtml());
        }
        $prodObj = new Product($product_id);
        if (!$prodObj->removeProductCategory($option_id)) {
            Message::addErrorMessage(Labels::getLabel($prodObj->getError(), FatApp::getConfig('CONF_ADMIN_DEFAULT_LANG', FatUtility::VAR_INT, 1)));
            FatUtility::dieWithError(Message::getHtml());
        }
        $this->set('msg', Labels::getLabel('MSG_Category_Removed_Successfully', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getCustomProductForm($type = 'CUSTOM_PRODUCT', $prodcat_id = 0)
    {
        $langId = $this->siteLangId;
        $frm = new Form('frmCustomProduct');
        $fld = $frm->addRequiredField(Labels::getLabel('LBL_Product_Identifier', $langId), 'product_identifier');
        $fld->htmlAfterField = '<br/><span class="note">' . Labels::getLabel('LBL_Product_Identifier_can_be_same_as_of_Product_Name', $langId) . '</span>';
        $pTypeFld = $frm->addSelectBox(Labels::getLabel('LBL_Product_Type', $langId), 'product_type', Product::getProductTypes($langId), '', array('id' => 'product_type'), '');

        $fld_model = $frm->addTextBox(Labels::getLabel('LBL_Model', $langId), 'product_model');
        if (FatApp::getConfig("CONF_PRODUCT_MODEL_MANDATORY", FatUtility::VAR_INT, 1)) {
            $fld_model->requirements()->setRequired();
        }

        /* if($type == 'CATALOG_PRODUCT'){ */
        $frm->addRequiredField(Labels::getLabel('LBL_Brand/Manfacturer', $this->siteLangId), 'brand_name');
        $frm->addHiddenField('', 'product_brand_id');
        /* } */

        $fld = $frm->addFloatField(Labels::getLabel('LBL_Minimum_Selling_Price', $this->siteLangId) . ' [' . CommonHelper::getCurrencySymbol(true) . ']', 'product_min_selling_price', '');
        $fld->requirements()->setPositive();

        $taxCategories = Tax::getSaleTaxCatArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Tax_Category', $this->siteLangId), 'ptt_taxcat_id', $taxCategories, '', array(), Labels::getLabel('Lbl_Select', $this->siteLangId))->requirements()->setRequired(true);

        if (FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 1)) {
            /* dimension unit[ */
            $lengthUnitsArr = applicationConstants::getLengthUnitsArr($langId);
            $frm->addSelectBox(Labels::getLabel('LBL_Dimensions_Unit', $langId), 'product_dimension_unit', $lengthUnitsArr, '', array(), Labels::getLabel('LBL_Select', $langId))->requirements()->setRequired();
            $pDimensionUnitUnReqObj = new FormFieldRequirement('product_dimension_unit', Labels::getLabel('LBL_Dimensions_Unit', $langId));
            $pDimensionUnitUnReqObj->setRequired(false);

            $pDimensionUnitReqObj = new FormFieldRequirement('product_dimension_unit', Labels::getLabel('LBL_Dimensions_Unit', $langId));
            $pDimensionUnitReqObj->setRequired(true);
            /* ] */

            /* length [ */
            $pLengthFld = $frm->addFloatField(Labels::getLabel('LBL_Length', $langId), 'product_length', '0.00');
            $pLengthUnReqObj = new FormFieldRequirement('product_length', Labels::getLabel('LBL_Length', $langId));
            $pLengthUnReqObj->setRequired(false);

            $pLengthReqObj = new FormFieldRequirement('product_length', Labels::getLabel('LBL_Length', $langId));
            $pLengthReqObj->setRequired(true);
            $pLengthReqObj->setFloatPositive();
            $pLengthReqObj->setRange('0.00001', '9999999999');
            /* ] */

            /* width[ */
            $pWidthFld = $frm->addFloatField(Labels::getLabel('LBL_Width', $langId), 'product_width', '0.00');
            $pWidthUnReqObj = new FormFieldRequirement('product_width', Labels::getLabel('LBL_Width', $langId));
            $pWidthUnReqObj->setRequired(false);

            $pWidthReqObj = new FormFieldRequirement('product_width', Labels::getLabel('LBL_Width', $langId));
            $pWidthReqObj->setRequired(true);
            $pWidthReqObj->setFloatPositive();
            $pWidthReqObj->setRange('0.00001', '9999999999');
            /* ] */

            /* height[ */
            $pHeightFld = $frm->addFloatField(Labels::getLabel('LBL_Height', $langId), 'product_height', '0.00');
            $pHeightUnReqObj = new FormFieldRequirement('product_height', Labels::getLabel('LBL_Height', $langId));
            $pHeightUnReqObj->setRequired(false);

            $pHeightReqObj = new FormFieldRequirement('product_height', Labels::getLabel('LBL_Height', $langId));
            $pHeightReqObj->setRequired(true);
            $pHeightReqObj->setFloatPositive();
            $pHeightReqObj->setRange('0.00001', '9999999999');
            /* ] */

            /* weight unit[ */
            $weightUnitsArr = applicationConstants::getWeightUnitsArr($langId);
            $pWeightUnitsFld = $frm->addSelectBox(Labels::getLabel('LBL_Weight_Unit', $langId), 'product_weight_unit', $weightUnitsArr, '', array(), Labels::getLabel('LBL_Select', $langId))->requirements()->setRequired();;

            $pWeightUnitUnReqObj = new FormFieldRequirement('product_weight_unit', Labels::getLabel('LBL_Weight_Unit', $langId));
            $pWeightUnitUnReqObj->setRequired(false);

            $pWeightUnitReqObj = new FormFieldRequirement('product_weight_unit', Labels::getLabel('LBL_Weight_Unit', $langId));
            $pWeightUnitReqObj->setRequired(true);
            /* ] */

            /* weight[ */
            $pWeightFld = $frm->addFloatField(Labels::getLabel('LBL_Weight', $langId), 'product_weight', '0.00');
            $pWeightUnReqObj = new FormFieldRequirement('product_weight', Labels::getLabel('LBL_Weight', $langId));
            $pWeightUnReqObj->setRequired(false);

            $pWeightReqObj = new FormFieldRequirement('product_weight', Labels::getLabel('LBL_Weight', $langId));
            $pWeightReqObj->setRequired(true);
            /* ] */

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_DIGITAL, 'eq', 'product_length', $pLengthUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_PHYSICAL, 'eq', 'product_length', $pLengthReqObj);

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_DIGITAL, 'eq', 'product_width', $pWidthUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_PHYSICAL, 'eq', 'product_width', $pWidthReqObj);

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_DIGITAL, 'eq', 'product_height', $pHeightUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_PHYSICAL, 'eq', 'product_height', $pHeightReqObj);


            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_DIGITAL, 'eq', 'product_dimension_unit', $pDimensionUnitUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_PHYSICAL, 'eq', 'product_dimension_unit', $pDimensionUnitReqObj);

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_DIGITAL, 'eq', 'product_weight', $pWeightUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_PHYSICAL, 'eq', 'product_weight', $pWeightReqObj);

            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_DIGITAL, 'eq', 'product_weight_unit', $pWeightUnitUnReqObj);
            $pTypeFld->requirements()->addOnChangerequirementUpdate(Product::PRODUCT_TYPE_PHYSICAL, 'eq', 'product_weight_unit', $pWeightUnitReqObj);
        }

        /* $frm->addFloatField( Labels::getLabel('LBL_Minimum_Selling_Price', $langId).' ['.CommonHelper::getCurrencySymbol(true).']', 'product_min_selling_price', ''); */

        $frm->addTextBox(Labels::getLabel('LBL_EAN/UPC/GTIN_code', $this->siteLangId), 'product_upc');

        $frm->addCheckBox(Labels::getLabel('LBL_Product_Featured', $this->siteLangId), 'product_featured', 1, array(), false, 0);

        /* $frm->addSelectBox(Labels::getLabel('LBL_Shipped_by_me',$langId), 'product_shipped_by_me', $yesNoArr, applicationConstants::YES, array(), ''); */



        $activeInactiveArr = applicationConstants::getActiveInactiveArr($langId);
        $frm->addSelectBox(Labels::getLabel('LBL_Product_Status', $langId), 'product_active', $activeInactiveArr, applicationConstants::ACTIVE, array(), '');

        $yesNoArr = applicationConstants::getYesNoArr($langId);
        $codFld = $frm->addSelectBox(Labels::getLabel('LBL_Available_for_COD', $langId), 'product_cod_enabled', $yesNoArr, applicationConstants::NO, array(), '');
        $paymentMethod = new PaymentMethods();
        if (!$paymentMethod->cashOnDeliveryIsActive()) {
            $codFld->addFieldTagAttribute('disabled', 'disabled');
            $codFld->htmlAfterField = '<span class="note">' . Labels::getLabel('LBL_COD_option_is_disabled_in_payment_gateway_settings', $langId) . '</span>';
        }

        $fld = $frm->addCheckBox(Labels::getLabel('LBL_Free_Shipping', $langId), 'ps_free', 1);

        $fld = $frm->addTextBox(Labels::getLabel('LBL_Shipping_country', $langId), 'shipping_country');

        if ($type == 'CATALOG_PRODUCT') {
            $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Add_Option_Groups', $this->siteLangId), 'option_name');
            $fld1->htmlAfterField = '<div class=""><small> <a class="" href="javascript:void(0);" onClick="optionForm(0);">' . Labels::getLabel('LBL_Add_New_Option', $this->siteLangId) . '</a></small></div><div class="col-md-12"><ul class="list--vertical" id="product_options_list"></ul></div>';

            $fld1 = $frm->addTextBox(Labels::getLabel('LBL_Add_Tag', $this->siteLangId), 'tag_name');
            $fld1->htmlAfterField = '<div class=""><small><a href="javascript:void(0);" onClick="addTagForm(0);">' . Labels::getLabel('LBL_Tag_Not_Found?_Click_here_to_', $this->siteLangId) . ' ' . Labels::getLabel('LBL_Add_New_Tag', $this->siteLangId) . '</a></small></div><div class="col-md-12"><ul class="list--vertical" id="product-tag-js"></ul></div>';
        }

        $fld = $frm->addTextBox(Labels::getLabel('LBL_PRODUCT_WARRANTY', $this->siteLangId), 'product_warranty');
        $fld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $this->siteLangId) . ' </small>';

        $frm->addHiddenField('', 'ps_from_country_id');
        $frm->addHiddenField('', 'product_id');
        $frm->addHiddenField('', 'preq_id');
        $frm->addHiddenField('', 'product_options');
        $frm->addHiddenField('', 'preq_prodcat_id', $prodcat_id);

        $fld1 = $frm->addHtml('', 'shipping_info_html', '<div class="heading4 not-digital-js">' . Labels::getLabel('LBL_Shipping_Info/Charges', $langId) . '</div><div class="divider not-digital-js"></div>');
        $fld2 = $frm->addHtml('', '', '<div id="tab_shipping"></div>');
        $fld1->attachField($fld2);

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $langId));

        return $frm;
    }

    private function getSellerProductForm($product_id, $selprod_id = 0, $type = 'SELLER_PRODUCT')
    {
        /* Type is used when we called this form for custom catalog request with selprod data */

        $defaultProductCond = '';
        $frm = new Form('frmSellerProduct');

        if ($type == 'CUSTOM_CATALOG') {
            $reqData = ProductRequest::getAttributesById($product_id, array('preq_content'));
            $productData = array_merge($reqData, json_decode($reqData['preq_content'], true));
            $productData['sellerProduct'] = 0;
            $optionArr = isset($productData['product_option']) ? $productData['product_option'] : array();
            foreach ($optionArr as $val) {
                $optionSrch = Option::getSearchObject($this->siteLangId);
                $optionSrch->addMultipleFields(array('IFNULL(option_name,option_identifier) as option_name', 'option_id'));
                $optionSrch->doNotCalculateRecords();
                $optionSrch->setPageSize(1);
                $optionSrch->addCondition('option_id', '=', $val);
                $rs = $optionSrch->getResultSet();
                $option = FatApp::getDb()->fetch($rs);
                if ($option == false) {
                    continue;
                }
                $optionValues = Product::getOptionValues($option['option_id'], $this->siteLangId);
                $option_name = ($option['option_name'] != '') ? $option['option_name'] : $option['option_identifier'];
                $fld = $frm->addSelectBox($option_name, 'selprodoption_optionvalue_id[' . $option['option_id'] . ']', $optionValues, '', array('class' => 'selprodoption_optionvalue_id'), Labels::getLabel('LBL_Select', $this->siteLangId));
                $fld->requirements()->setRequired();
            }
        } else {
            $productData = Product::getAttributesById($product_id, array('product_type', 'product_min_selling_price', 'product_cod_enabled', 'if(product_seller_id > 0, 1, 0) as sellerProduct', 'product_seller_id'));
            if ($productData['product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
                $defaultProductCond = Product::CONDITION_NEW;
            }
        }

        $frm->addRequiredField(Labels::getLabel('LBL_Title', $this->siteLangId), 'selprod_title' . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1));
        $frm->addCheckBox(Labels::getLabel('LBL_System_Should_Maintain_Stock_Levels', $this->siteLangId), 'selprod_subtract_stock', applicationConstants::YES, array(), false, 0);
        $frm->addCheckBox(Labels::getLabel('LBL_System_Should_Track_Product_Inventory', $this->siteLangId), 'selprod_track_inventory', Product::INVENTORY_TRACK, array(), false, 0);
        $fld = $frm->addTextBox(Labels::getLabel('LBL_Alert_Stock_Level', $this->siteLangId), 'selprod_threshold_stock_level');
        $fld->requirements()->setInt();
        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Minimum_Purchase_Quantity', $this->siteLangId), 'selprod_min_order_qty');
        $fld->requirements()->setRange(1, SellerProduct::MAX_RANGE_OF_MINIMUM_PURCHANGE_QTY);

        if ($productData['product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $fld = $frm->addIntegerField(Labels::getLabel('LBL_Max_Download_Times', $this->siteLangId), 'selprod_max_download_times');
            $fld->htmlAfterField = '<span class="note">' . Labels::getLabel('LBL_-1_for_unlimited', $this->siteLangId) . '</span>';

            $fld1 = $frm->addIntegerField(Labels::getLabel('LBL_Validity_(days)', $this->siteLangId), 'selprod_download_validity_in_days');
            $fld1->htmlAfterField = '<span class="note">' . Labels::getLabel('LBL_-1_for_unlimited', $this->siteLangId) . '</span>';
            $frm->addHiddenField('', 'selprod_condition', $defaultProductCond);
        } else {
            $fld = $frm->addSelectBox(Labels::getLabel('LBL_Product_Condition', $this->siteLangId), 'selprod_condition', Product::getConditionArr($this->siteLangId), $defaultProductCond, array(), Labels::getLabel('LBL_Select_Condition', $this->siteLangId));
            $fld->requirements()->setRequired();
        }
        $frm->addDateField(Labels::getLabel('LBL_Date_Available', $this->siteLangId), 'selprod_available_from', '', array('readonly' => 'readonly'))->requirements()->setRequired();
        $frm->addSelectBox(Labels::getLabel('LBL_Publish_Inventory', $this->siteLangId), 'selprod_active', applicationConstants::getYesNoArr($this->siteLangId), applicationConstants::YES, array(), '');

        $useShopPolicy = $frm->addCheckBox(Labels::getLabel('LBL_USE_SHOP_RETURN_AND_CANCELLATION_POLICY', $this->siteLangId), 'use_shop_policy', 1, ['id' => 'use_shop_policy'], false, 0);

        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Product_Order_Return_Period_(Days)', $this->siteLangId), 'selprod_return_age');

        $orderReturnAgeReqFld = new FormFieldRequirement('selprod_return_age', Labels::getLabel('LBL_Product_Order_Return_Period_(Days)', $this->siteLangId));
        $orderReturnAgeReqFld->setRequired(true);
        $orderReturnAgeReqFld->setPositive();
        $orderReturnAgeReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_IN_DAYS', $this->siteLangId) . ' </small>';

        $orderReturnAgeUnReqFld = new FormFieldRequirement('selprod_return_age', Labels::getLabel('LBL_Product_Order_Return_Period_(Days)', $this->siteLangId));
        $orderReturnAgeUnReqFld->setRequired(false);
        $orderReturnAgeUnReqFld->setPositive();
        $orderReturnAgeUnReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_IN_DAYS', $this->siteLangId) . ' </small>';

        $fld = $frm->addIntegerField(Labels::getLabel('LBL_Product_Order_Cancellation_Period_(Days)', $this->siteLangId), 'selprod_cancellation_age');

        $orderCancellationAgeReqFld = new FormFieldRequirement('selprod_cancellation_age', Labels::getLabel('LBL_Product_Order_Cancellation_Period_(Days)', $this->siteLangId));
        $orderCancellationAgeReqFld->setRequired(true);
        $orderCancellationAgeReqFld->setPositive();
        $orderCancellationAgeReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $this->siteLangId) . ' </small>';

        $orderCancellationAgeUnReqFld = new FormFieldRequirement('selprod_cancellation_age', Labels::getLabel('LBL_Product_Order_Cancellation_Period_(Days)', $this->siteLangId));
        $orderCancellationAgeUnReqFld->setRequired(false);
        $orderCancellationAgeUnReqFld->setPositive();
        $orderCancellationAgeUnReqFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_WARRANTY_IN_DAYS', $this->siteLangId) . ' </small>';

        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'eq', 'selprod_return_age', $orderReturnAgeUnReqFld);
        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'ne', 'selprod_return_age', $orderReturnAgeReqFld);

        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'eq', 'selprod_cancellation_age', $orderCancellationAgeUnReqFld);
        $useShopPolicy->requirements()->addOnChangerequirementUpdate(Shop::USE_SHOP_POLICY, 'ne', 'selprod_cancellation_age', $orderCancellationAgeReqFld);

        if ($type != 'CUSTOM_CATALOG') {
            $yesNoArr = applicationConstants::getYesNoArr($this->siteLangId);
            $codFld = $frm->addSelectBox(Labels::getLabel('LBL_Available_for_COD', $this->siteLangId), 'selprod_cod_enabled', $yesNoArr, '0', array(), '');

            $paymentMethod = new PaymentMethods();
            if (!$paymentMethod->cashOnDeliveryIsActive() || $productData['product_cod_enabled'] != applicationConstants::YES) {
                $codFld->addFieldTagAttribute('disabled', 'disabled');
                if ($productData['product_cod_enabled'] != applicationConstants::YES) {
                    $codFld->htmlAfterField = '<span class="note">' . Labels::getLabel('LBL_COD_option_is_disabled_in_Product', $this->siteLangId) . '</span>';
                } else {
                    $codFld->htmlAfterField = '<span class="note">' . Labels::getLabel('LBL_COD_option_is_disabled_in_payment_gateway_settings', $this->siteLangId) . '</span>';
                }
            }


            $shipBySeller = Product::isProductShippedBySeller($product_id, $productData['product_seller_id'], UserAuthentication::getLoggedUserId());
            // $shipBySeller = SellerProduct::prodShipByseller($product_id);

            $fulfillmentType = -1;
            if ($productData['sellerProduct'] > 0 || $shipBySeller) {
                $sellerId = ($shipBySeller > 0) ? UserAuthentication::getLoggedUserId() : $productData['product_seller_id'];
                $fulfillmentType = Shop::getAttributesByUserId($sellerId, 'shop_fulfillment_type');
            } else {
                $fulfillmentType = FatApp::getConfig('CONF_FULFILLMENT_TYPE', FatUtility::VAR_INT, -1);
            }

            $shopDetails = Shop::getAttributesByUserId(UserAuthentication::getLoggedUserId(), null, false);
            $address = new Address(0, $this->siteLangId);
            $addresses = $address->getData(Address::TYPE_SHOP_PICKUP, $shopDetails['shop_id']);

            $fulfillmentType = empty($addresses) ? Shipping::FULFILMENT_SHIP : $fulfillmentType;

            $fulFillmentArr = Shipping::getFulFillmentArr($this->siteLangId, $fulfillmentType);
            if ($productData['product_type'] == Product::PRODUCT_TYPE_PHYSICAL && true == $shipBySeller) {
                $frm->addSelectBox(Labels::getLabel('LBL_FULFILLMENT_METHOD', $this->siteLangId), 'selprod_fulfillment_type', $fulFillmentArr, applicationConstants::NO, []);
            }
            $frm->addRequiredField(Labels::getLabel('LBL_Url_Keyword', $this->siteLangId), 'selprod_url_keyword');
            $productOptions = Product::getProductOptions($product_id, $this->siteLangId, true);
            if (!empty($productOptions) && $selprod_id == 0) {
                $optionCombinations = CommonHelper::combinationOfElementsOfArr($productOptions, 'optionValues', '_');
                if ($optionCombinations) {
                    $i = $j = 0;
                    foreach ($optionCombinations as $optionKey => $optionValue) {
                        if (SellerProduct::UPDATE_OPTIONS_COUNT < $i) {
                            $j++;
                            $i = 0;
                        }
                        /* Check if product already added for this option [ */
                        $selProdCode = $product_id . '_' . $optionKey;
                        $selProdAvailable = Product::isSellProdAvailableForUser($selProdCode, $this->siteLangId, $this->userParentId);
                        if (!empty($selProdAvailable) && !$selProdAvailable['selprod_deleted']) {
                            continue;
                        }
                        /* ] */
                        // $frm->addRequiredField(Labels::getLabel('LBL_Url_Keyword', $this->siteLangId), 'selprod_url_keyword'.$optionKey);
                        $frm->addTextBox(Labels::getLabel('LBL_Cost_Price', $this->siteLangId) . ' [' . CommonHelper::getCurrencySymbol(true) . ']', 'varients[' . $j . '][selprod_cost' . $optionKey . ']');
                        $frm->addTextBox(Labels::getLabel('LBL_Price', $this->siteLangId) . ' [' . CommonHelper::getCurrencySymbol(true) . ']', 'varients[' . $j . '][selprod_price' . $optionKey . ']');
                        $frm->addTextBox(Labels::getLabel('LBL_Quantity', $this->siteLangId), 'varients[' . $j . '][selprod_stock' . $optionKey . ']');
                        $frm->addTextBox(Labels::getLabel('LBL_Product_SKU', $this->siteLangId), 'varients[' . $j . '][selprod_sku' . $optionKey . ']');
                        $i++;
                    }
                }
            } else {
                $costPrice = $frm->addFloatField(Labels::getLabel('LBL_Cost_Price', $this->siteLangId) . ' [' . CommonHelper::getCurrencySymbol(true) . ']', 'selprod_cost');
                $costPrice->requirements()->setPositive();

                $fld = $frm->addFloatField(Labels::getLabel('LBL_Price', $this->siteLangId) . ' [' . CommonHelper::getCurrencySymbol(true) . ']', 'selprod_price');
                $fld->requirements()->setPositive();
                if (isset($productData['product_min_selling_price'])) {
                    $fld->requirements()->setRange($productData['product_min_selling_price'], 9999999999);
                    // $fld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Minimum_selling_price_for_this_product_is', $this->siteLangId).' '.CommonHelper::displayMoneyFormat($productData['product_min_selling_price'], true, true));

                    /* $fld->htmlAfterField = '<span class="note">' . Labels::getLabel('LBL_This_price_is_excluding_the_tax_rates.', $this->siteLangId) . '</span> <span class="note">' . Labels::getLabel('LBL_Min_Selling_price', $this->siteLangId) . ' ' . CommonHelper::displayMoneyFormat($productData['product_min_selling_price'], true, true) . '</small>'; */
                }

                $fld = $frm->addIntegerField(Labels::getLabel('LBL_Quantity', $this->siteLangId), 'selprod_stock');
                $fld->requirements()->setPositive();
                $fld_sku = $frm->addTextBox(Labels::getLabel('LBL_Product_SKU', $this->siteLangId), 'selprod_sku');
                if (FatApp::getConfig("CONF_PRODUCT_SKU_MANDATORY", FatUtility::VAR_INT, 1)) {
                    $fld_sku->requirements()->setRequired();
                }
                /* $fld_sku->htmlAfterField = '<br/><span class="note">' . Labels::getLabel('LBL_Stock_Keeping_Unit', $this->siteLangId) . '</span>'; */
            }
        }
        $frm->addTextArea(Labels::getLabel('LBL_Any_Extra_Comment_for_buyer', $this->siteLangId), 'selprod_comments' . FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1));

        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
        $languages = Language::getAllNames();
        unset($languages[FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1)]);
        if (!empty($translatorSubscriptionKey) && count($languages) > 0) {
            $frm->addCheckBox(Labels::getLabel('LBL_Translate_To_Other_Languages', $this->siteLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        foreach ($languages as $langId => $langName) {
            $frm->addTextBox(Labels::getLabel('LBL_Title', $this->siteLangId), 'selprod_title' . $langId);
            $frm->addTextArea(Labels::getLabel('LBL_Any_Extra_Comment_for_buyer', $this->siteLangId), 'selprod_comments' . $langId);
        }
        $frm->addHiddenField('', 'selprod_product_id', $product_id);
        $frm->addHiddenField('', 'selprod_urlrewrite_id');
        $frm->addHiddenField('', 'selprod_id', $selprod_id);
        $fld1 = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        if ($type != 'CUSTOM_CATALOG') {
            $fld2 = $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Discard', $this->siteLangId), array('onClick' => 'gotToProucts()'));
            //$fld1->attachField($fld2);
        }
        return $frm;
    }

    public function catalogInfo(int $product_id)
    {
        $prodSrchObj = new ProductSearch($this->siteLangId, null, null, false, false);
        /* fetch requested product[ */
        $prodSrch = clone $prodSrchObj;
        $prodSrch->joinProductToCategory(0, false, false, false);
        //$prodSrch->joinProductToTax();
        $prodSrch->joinBrands(0, false, false, false);
        $prodSrch->addCondition('product_id', '=', $product_id);
        $prodSrch->doNotLimitRecords();


        $prodSrch->addMultipleFields(
            array(
                'product_id', 'product_identifier', 'IFNULL(product_name,product_identifier) as product_name', 'product_seller_id', 'product_model', 'product_type', 'product_short_description', 'prodcat_id', 'IFNULL(prodcat_name,prodcat_identifier) as prodcat_name', 'brand_id', 'IFNULL(brand_name, brand_identifier) as brand_name', 'product_min_selling_price'
            )
        );
        $productRs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($productRs);
        /* ] */

        $taxData = Tax::getTaxCatByProductId($product_id, $this->userParentId, $this->siteLangId, array('ptt_taxcat_id'));
        if (!empty($taxData)) {
            $product = array_merge($product, $taxData);
        }

        if (!$product) {
            Message::addErrorMessage(Labels::getLabel('VLBL_INVALID_PRODUCT', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        /* Get Product Specifications [ */
        $specSrchObj = clone $prodSrchObj;
        $specSrchObj->doNotCalculateRecords();
        $specSrchObj->doNotLimitRecords();
        $specSrchObj->joinTable(Product::DB_PRODUCT_SPECIFICATION, 'LEFT OUTER JOIN', 'product_id = tcps.prodspec_product_id', 'tcps');
        $specSrchObj->joinTable(Product::DB_PRODUCT_LANG_SPECIFICATION, 'INNER JOIN', 'tcps.prodspec_id = tcpsl.prodspeclang_prodspec_id and   prodspeclang_lang_id  = ' . $this->siteLangId, 'tcpsl');
        $specSrchObj->addMultipleFields(array('prodspec_id', 'prodspec_name', 'prodspec_value'));
        $specSrchObj->addGroupBy('prodspec_id');
        $specSrchObj->addCondition('prodspec_product_id', '=', $product['product_id']);
        $specSrchObjRs = $specSrchObj->getResultSet();
        $productSpecifications = FatApp::getDb()->fetchAll($specSrchObjRs);

        $productImagesArr = AttachedFile::getMultipleAttachments(AttachedFile::FILETYPE_PRODUCT_IMAGE, $product_id, 0, $this->siteLangId);
        /* ] */

        $this->set('productImagesArr', $productImagesArr);
        $this->set('product', $product);
        $this->set('productSpecifications', $productSpecifications);
        $this->_template->render(false, false);
    }

    public function returnAddress()
    {
        $userId = $this->userParentId;
        $userObj = new User($userId);
        $data = $userObj->getUserReturnAddress($this->siteLangId);
        $this->set('info', $data);
        $this->_template->render(false, false);
    }

    public function returnAddressForm()
    {
        $userId = $this->userParentId;

        $frm = $this->getReturnAddressForm();
        $stateId = 0;

        $userObj = new User($userId);
        $data = $userObj->getUserReturnAddress();

        if ($data != false) {
            $frm->fill($data);
            $stateId = $data['ura_state_id'];
        }


        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shop_id = 0;

        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
        }

        $this->set('shop_id', $shop_id);
        $this->set('language', Language::getAllNames());
        $this->set('siteLangId', $this->siteLangId);
        $this->set('frm', $frm);
        $this->set('stateId', $stateId);
        $this->set('languages', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function setReturnAddress()
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;

        $post = FatApp::getPostedData();
        $ura_state_id = FatUtility::int($post['ura_state_id']);
        $frm = $this->getReturnAddressForm();
        $post = $frm->getFormDataFromArray($post);
        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $post['ura_state_id'] = $ura_state_id;
        $post['ura_phone_dcode'] = FatApp::getPostedData('ura_phone_dcode', FatUtility::VAR_STRING, '');

        $userObj = new User($userId);
        if (!$userObj->updateUserReturnAddress($post)) {
            Message::addErrorMessage(Labels::getLabel($userObj->getError(), $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }
        $newTabLangId = $this->siteLangId;
        $this->set('langId', $newTabLangId);
        $this->set('msg', Labels::getLabel('MSG_Setup_successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function returnAddressLangForm($langId, $autoFillLangData = 0)
    {
        $langId = FatUtility::int($langId);
        $userId = $this->userParentId;
        $userId = FatUtility::int($userId);

        if (1 > $langId || 1 > $userId) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $frm = $this->getReturnAddressLangForm($langId);
        $stateId = 0;

        if (0 < $autoFillLangData) {
            $updateLangDataobj = new TranslateLangData(User::DB_TBL_USR_RETURN_ADDR_LANG);
            $translatedData = $updateLangDataobj->getTranslatedData($userId, $langId);
            if (false === $translatedData) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
            $data = current($translatedData);
        } else {
            $userObj = new User($userId);
            $data = $userObj->getUserReturnAddress($langId);
        }

        if ($data != false) {
            $frm->fill($data);
        }


        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shop_id = 0;

        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
        }

        $this->set('shop_id', $shop_id);
        $this->set('language', Language::getAllNames());
        $this->set('siteLangId', $this->siteLangId);
        $this->set('frm', $frm);
        $this->set('stateId', $stateId);
        $this->set('formLangId', $langId);
        $this->set('formLayout', Language::getLayoutDirection($langId));
        $this->_template->render(false, false);
    }

    public function setReturnAddressLang()
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $post = FatApp::getPostedData();
        $lang_id = $post['lang_id'];
        $userId = $this->userParentId;

        if ($userId == 0 || $lang_id == 0) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $frm = $this->getReturnAddressLangForm($lang_id);
        $post = $frm->getFormDataFromArray($post);

        if (false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $userObj = new User($userId);
        if (!$userObj->updateUserReturnAddressLang($post)) {
            Message::addErrorMessage(Labels::getLabel($userObj->getError(), $this->siteLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $autoUpdateOtherLangsData = FatApp::getPostedData('auto_update_other_langs_data', FatUtility::VAR_INT, 0);
        if (0 < $autoUpdateOtherLangsData) {
            $updateLangDataobj = new TranslateLangData(User::DB_TBL_USR_RETURN_ADDR_LANG);
            if (false === $updateLangDataobj->updateTranslatedData($userId)) {
                Message::addErrorMessage($updateLangDataobj->getError());
                FatUtility::dieWithError(Message::getHtml());
            }
        }

        $newTabLangId = 0;
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $langName) {
            $userObj = new User($userId);
            $srch = new SearchBase(User::DB_TBL_USR_RETURN_ADDR_LANG);
            $srch->addCondition('uralang_user_id', '=', $userId);
            $srch->addCondition('uralang_lang_id', '=', $langId);
            $srch->doNotCalculateRecords();
            $srch->doNotLimitRecords();
            $rs = $srch->getResultSet();
            $vendorReturnAddress = FatApp::getDb()->fetch($rs);


            if (!$vendorReturnAddress) {
                $newTabLangId = $langId;
                break;
            }
        }

        $this->set('langId', $newTabLangId);
        $this->set('msg', Labels::getLabel('MSG_Setup_successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getReturnAddressForm()
    {
        $frm = new Form('frmReturnAddress');

        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($this->siteLangId);

        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Country', $this->siteLangId), 'ura_country_id', $countriesArr, FatApp::getConfig('CONF_COUNTRY'), array(), Labels::getLabel('LBL_Select', $this->siteLangId));
        $fld->requirement->setRequired(true);

        $frm->addSelectBox(Labels::getLabel('LBL_State', $this->siteLangId), 'ura_state_id', array(), '', array(), Labels::getLabel('LBL_Select', $this->siteLangId))->requirement->setRequired(true);
        /* $frm->addTextBox(Labels::getLabel('LBL_City',$this->siteLangId), 'ura_city');     */
        $zipFld = $frm->addTextBox(Labels::getLabel('LBL_Postalcode', $this->siteLangId), 'ura_zip');
        /* $zipFld->requirements()->setRegularExpressionToValidate(ValidateElement::ZIP_REGEX);
        $zipFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Only_alphanumeric_value_is_allowed.', $this->siteLangId)); */
        $frm->addHiddenField('', 'ura_phone_dcode');
        $phnFld = $frm->addTextBox(Labels::getLabel('LBL_Phone', $this->siteLangId), 'ura_phone', '', array('class' => 'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
        $phnFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
        $phnFld->htmlAfterField = '<span class="note">' . Labels::getLabel('LBL_e.g.', $this->siteLangId) . ': ' . implode(', ', ValidateElement::PHONE_FORMATS) . '</span>';

        $phnFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Please_enter_valid_phone_number_format.', $this->siteLangId));

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE_CHANGES', $this->siteLangId));
        return $frm;
    }

    private function getReturnAddressLangForm($formLangId)
    {
        $formLangId = FatUtility::int($formLangId);

        $frm = new Form('frmReturnAddressLang');
        $frm->addSelectBox(Labels::getLabel('LBL_LANGUAGE', $this->siteLangId), 'lang_id', Language::getAllNames(), $formLangId, array(), '');
        $frm->addTextBox(Labels::getLabel('LBL_Name', $formLangId), 'ura_name')->requirement->setRequired(true);;
        $frm->addTextBox(Labels::getLabel('LBL_City', $formLangId), 'ura_city')->requirement->setRequired(true);;
        $frm->addTextarea(Labels::getLabel('LBL_Address1', $formLangId), 'ura_address_line_1')->requirement->setRequired(true);;
        $frm->addTextarea(Labels::getLabel('LBL_Address2', $formLangId), 'ura_address_line_2');

        $siteLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');

        if (!empty($translatorSubscriptionKey) && $formLangId == $siteLangId) {
            $frm->addCheckBox(Labels::getLabel('LBL_UPDATE_OTHER_LANGUAGES_DATA', $this->siteLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_SAVE_CHANGES', $this->siteLangId));
        return $frm;
    }

    public function sellerOffers()
    {
        $this->userPrivilege->canViewSubscription(UserAuthentication::getLoggedUserId());
        $this->_template->render(true, true);
    }

    public function searchSellerOffers()
    {
        $offers = DiscountCoupons::getUserCoupons($this->userParentId, $this->siteLangId, DiscountCoupons::TYPE_SELLER_PACKAGE);

        if ($offers) {
            $this->set('offers', $offers);
        } else {
            $this->set('noRecordsHtml', $this->_template->render(false, false, '_partial/no-record-found.php', true));
        }
        $this->_template->render(false, false);
    }

    public function productTooltipInstruction($type)
    {
        $this->set('type', $type);
        $this->_template->render(false, false);
    }

    public function specialPrice($selProd_id = 0)
    {
        $this->userPrivilege->canViewSpecialPrice(UserAuthentication::getLoggedUserId());
        if (!UserPrivilege::isUserHasValidSubsription($this->userParentId)) {
            Message::addInfo(Labels::getLabel("MSG_Please_buy_subscription", $this->siteLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('Seller', 'Packages'));
        }

        $selProd_id = FatUtility::int($selProd_id);

        if (0 < $selProd_id || 0 > $selProd_id) {
            $selProd_id = SellerProduct::getAttributesByID($selProd_id, 'selprod_id', false);
            if (empty($selProd_id)) {
                Message::addErrorMessage(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
                FatApp::redirectUser(UrlHelper::generateUrl('SellerProducts', 'specialPrice'));
            }
        }

        $srchFrm = $this->getSpecialPriceSearchForm();
        $selProdIdsArr = FatApp::getPostedData('selprod_ids', FatUtility::VAR_INT, 0);

        $dataToEdit = array();
        if (!empty($selProdIdsArr) || 0 < $selProd_id) {
            $selProdIdsArr = (0 < $selProd_id) ? array($selProd_id) : $selProdIdsArr;
            $productsTitle = SellerProduct::getProductDisplayTitle($selProdIdsArr, $this->siteLangId);
            foreach ($selProdIdsArr as $selProdId) {
                $dataToEdit[] = array(
                    'product_name' => html_entity_decode($productsTitle[$selProdId], ENT_QUOTES, 'UTF-8'),
                    'splprice_selprod_id' => $selProdId
                );
            }
        } else {
            $post = $srchFrm->getFormDataFromArray(FatApp::getPostedData());

            if (false === $post) {
                FatUtility::dieJsonError(current($srchFrm->getValidationErrors()));
            } else {
                unset($post['btn_submit'], $post['btn_clear']);
                $srchFrm->fill($post);
            }
        }
        if (0 < $selProd_id) {
            $srchFrm->addHiddenField('', 'selprod_id', $selProd_id);
            $srchFrm->fill(array('keyword' => $productsTitle[$selProd_id]));
        }
        $this->set("canEdit", $this->userPrivilege->canEditSpecialPrice(UserAuthentication::getLoggedUserId(), true));
        $this->set("dataToEdit", $dataToEdit);
        $this->set("frmSearch", $srchFrm);
        $this->set("selProd_id", $selProd_id);
        $this->_template->addJs(array('js/select2.js'));
        $this->_template->addCss(array('custom/page-css/select2.min.css'));
        $this->_template->render();
    }

    public function searchSpecialPriceProducts()
    {
        $this->userPrivilege->canViewSpecialPrice(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;
        $post = FatApp::getPostedData();
        $page = FatApp::getPostedData('page', FatUtility::VAR_INT, 1);
        $selProdId = FatApp::getPostedData('selprod_id', FatUtility::VAR_INT, 0);
        $keyword = FatApp::getPostedData('keyword', FatUtility::VAR_STRING, '');

        $srch = SellerProduct::searchSpecialPriceProductsObj($this->siteLangId, $selProdId, $keyword, $userId);
        $srch->setPageNumber($page);

        $db = FatApp::getDb();
        $rs = $srch->getResultSet();
        $arrListing = $db->fetchAll($rs);

        $this->set("arrListing", $arrListing);
        $this->set('canEdit', $this->userPrivilege->canEditSpecialPrice(UserAuthentication::getLoggedUserId(), true));
        $this->set('page', $page);
        $this->set('pageCount', $srch->pages());
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());
        $this->set('pageSize', FatApp::getConfig('CONF_PAGE_SIZE', FatUtility::VAR_INT, 10));
        $this->_template->render(false, false);
    }

    private function getSpecialPriceSearchForm()
    {
        $frm = new Form('frmSearch', array('id' => 'frmSearch'));
        $frm->addTextBox('', 'keyword', '', array('placeholder' => Labels::getLabel('LBL_Keyword', $this->siteLangId)));

        $fld_submit = $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Search', $this->siteLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear', $this->siteLangId), array('onclick' => 'clearSearch();'));
        return $frm;
    }

    public function updateSpecialPriceRow()
    {
        $this->userPrivilege->canEditSpecialPrice(UserAuthentication::getLoggedUserId());
        $data = FatApp::getPostedData();
        if (empty($data)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        $splPriceId = $this->updateSelProdSplPrice($data, true);
        if (!$splPriceId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        // last Param of getProductDisplayTitle function used to get title in html form.
        $productName = SellerProduct::getProductDisplayTitle($data['splprice_selprod_id'], $this->siteLangId, true);
        $data['product_name'] = $productName;
        $this->set('data', $data);
        $this->set('splPriceId', $splPriceId);
        $json = array(
            'status' => true,
            'msg' => Labels::getLabel('LBL_Special_Price_Setup_Successful', $this->siteLangId),
            'data' => $this->_template->render(false, false, 'seller/update-special-price-row.php', true)
        );

        $productId = SellerProduct::getAttributesById($data['splprice_selprod_id'], 'selprod_product_id');
        Product::updateMinPrices($productId);
        FatUtility::dieJsonSuccess($json);
    }

    private function updateSelProdSplPrice($post, $return = false)
    {
        $this->userPrivilege->canEditSpecialPrice(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;
        $selprod_id = !empty($post['splprice_selprod_id']) ? FatUtility::int($post['splprice_selprod_id']) : 0;
        $splprice_id = !empty($post['splprice_id']) ? FatUtility::int($post['splprice_id']) : 0;

        if (1 > $selprod_id) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        if (strtotime($post['splprice_start_date']) > strtotime($post['splprice_end_date'])) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Dates', $this->siteLangId));
        }

        $prodSrch = new ProductSearch($this->siteLangId);
        $prodSrch->joinSellerProducts($userId, '', array(), false);
        $prodSrch->addCondition('selprod_id', '=', $selprod_id);
        $prodSrch->addMultipleFields(array('product_min_selling_price', 'selprod_price', 'selprod_available_from'));
        $prodSrch->setPageSize(1);
        $rs = $prodSrch->getResultSet();
        $product = FatApp::getDb()->fetch($rs);

        if (strtotime($post['splprice_start_date']) < strtotime($product['selprod_available_from'])) {
            $str = Labels::getLabel('MSG_Special_Price_Date_Must_Be_Greater_Or_Than_Equal_To_{availablefrom}', $this->siteLangId);
            $message = CommonHelper::replaceStringData($str, array('{availablefrom}' => date('Y-m-d', strtotime($product['selprod_available_from']))));
            FatUtility::dieJsonError($message);
        }

        if (!isset($post['splprice_price']) || $post['splprice_price'] < $product['product_min_selling_price'] || $post['splprice_price'] >= $product['selprod_price']) {
            $str = Labels::getLabel('MSG_Price_must_between_min_selling_price_{minsellingprice}_and_selling_price_{sellingprice}', $this->siteLangId);
            $minSellingPrice = CommonHelper::displayMoneyFormat($product['product_min_selling_price'], false, true, true);
            $sellingPrice = CommonHelper::displayMoneyFormat($product['selprod_price'], false, true, true);

            $message = CommonHelper::replaceStringData($str, array('{minsellingprice}' => $minSellingPrice, '{sellingprice}' => $sellingPrice));
            FatUtility::dieJsonError($message);
        }

        /* Check if same date already exists [ */
        $tblRecord = new TableRecord(SellerProduct::DB_TBL_SELLER_PROD_SPCL_PRICE);

        $smt = 'splprice_selprod_id = ? AND ';
        $smt .= '(
            ((splprice_start_date between ? AND ?) OR (splprice_end_date between ? AND ?))
            OR
            ((? BETWEEN splprice_start_date AND splprice_end_date) OR (? BETWEEN  splprice_start_date AND splprice_end_date))
        )';
        $smtValues = array(
            $selprod_id,
            $post['splprice_start_date'],
            $post['splprice_end_date'],
            $post['splprice_start_date'],
            $post['splprice_end_date'],
            $post['splprice_start_date'],
            $post['splprice_end_date'],
        );

        if (0 < $splprice_id) {
            $smt .= ' AND splprice_id != ?';
            $smtValues[] = $splprice_id;
        }
        $condition = array(
            'smt' => $smt,
            'vals' => $smtValues
        );
        // CommonHelper::printArray($condition, true);
        if ($tblRecord->loadFromDb($condition)) {
            $specialPriceRow = $tblRecord->getFlds();
            if ($specialPriceRow['splprice_id'] != $splprice_id) {
                FatUtility::dieJsonError(Labels::getLabel('MSG_Special_price_for_this_date_already_added', $this->siteLangId));
            }
        }
        /* ] */

        $data_to_save = array(
            'splprice_selprod_id' => $selprod_id,
            'splprice_start_date' => $post['splprice_start_date'],
            'splprice_end_date' => $post['splprice_end_date'],
            'splprice_price' => $post['splprice_price'],
        );

        if (0 < $splprice_id) {
            $data_to_save['splprice_id'] = $splprice_id;
        }

        $sellerProdObj = new SellerProduct();

        // Return Special Price ID if $return is true else it will return bool value.
        $splPriceId = $sellerProdObj->addUpdateSellerProductSpecialPrice($data_to_save, $return);
        if (false === $splPriceId) {
            FatUtility::dieJsonError(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
        }

        return $splPriceId;
    }

    public function updateSpecialPriceColValue()
    {
        $this->userPrivilege->canEditSpecialPrice(UserAuthentication::getLoggedUserId());
        $splPriceId = FatApp::getPostedData('splprice_id', FatUtility::VAR_INT, 0);
        if (1 > $splPriceId) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        $attribute = FatApp::getPostedData('attribute', FatUtility::VAR_STRING, '');

        $columns = array('splprice_start_date', 'splprice_end_date', 'splprice_price');
        if (!in_array($attribute, $columns)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }

        $otherColumns = array_values(array_diff($columns, [$attribute]));

        $otherColumnsValue = SellerProductSpecialPrice::getAttributesById($splPriceId, $otherColumns);
        if (empty($otherColumnsValue)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        $value = FatApp::getPostedData('value');
        $selProdId = FatApp::getPostedData('selProdId', FatUtility::VAR_INT, 0);

        $dataToUpdate = array(
            'splprice_selprod_id' => $selProdId,
            'splprice_id' => $splPriceId,
            $attribute => $value,
        );

        $dataToUpdate += $otherColumnsValue;

        if (!$this->updateSelProdSplPrice($dataToUpdate)) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Something_went_wrong._Please_Try_Again.', $this->siteLangId));
        }

        if ('splprice_price' == $attribute) {
            $value = CommonHelper::displayMoneyFormat($value, true, true);
        }
        $json = array(
            'status' => true,
            'msg' => Labels::getLabel('MSG_Success', $this->siteLangId),
            'data' => array('value' => $value)
        );
        FatUtility::dieJsonSuccess($json);
    }

    public function deleteSellerProductSpecialPrice()
    {
        $this->userPrivilege->canEditSpecialPrice(UserAuthentication::getLoggedUserId());
        $splPriceId = FatApp::getPostedData('splprice_id', FatUtility::VAR_INT, 0);
        if (1 > $splPriceId) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Invalid_Request', $this->siteLangId));
        }
        $specialPriceRow = SellerProduct::getSellerProductSpecialPriceById($splPriceId);
        if (empty($specialPriceRow) || 1 > count($specialPriceRow)) {
            FatUtility::dieWithError(Labels::getLabel('MSG_Already_Deleted', $this->siteLangId));
        }
        $this->deleteSpecialPrice($splPriceId, $specialPriceRow['selprod_id']);
        $this->set('selprod_id', $specialPriceRow['selprod_id']);
        $this->set('msg', Labels::getLabel('LBL_Special_Price_Record_Deleted', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function deleteSpecialPriceRows()
    {
        $this->userPrivilege->canEditSpecialPrice(UserAuthentication::getLoggedUserId());
        $splpriceIdArr = FatApp::getPostedData('selprod_ids');
        $splpriceIds = FatUtility::int($splpriceIdArr);
        foreach ($splpriceIds as $splPriceId => $selProdId) {
            $specialPriceRow = SellerProduct::getSellerProductSpecialPriceById($splPriceId);
            $this->deleteSpecialPrice($splPriceId, $specialPriceRow['selprod_id']);
        }
        $this->set('selprod_id', $specialPriceRow['selprod_id']);
        $this->set('msg', Labels::getLabel('LBL_Special_Price_Record_Deleted', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function deleteSpecialPrice($splPriceId, $selProdId)
    {
        $this->userPrivilege->canEditSpecialPrice(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;
        $sellerProdObj = new SellerProduct($selProdId);
        if (!$sellerProdObj->deleteSellerProductSpecialPrice($splPriceId, $selProdId, $userId)) {
            FatUtility::dieWithError(Labels::getLabel($sellerProdObj->getError(), $this->siteLangId));
        }
        return true;
    }

    public function checkIfAvailableForInventory($productId)
    {
        $productId = FatUtility::int($productId);
        $userId = $this->userParentId;
        if (0 == $productId) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }
        $available = Product::availableForAddToStore($productId, $userId);
        if (!$available) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Inventory_for_all_possible_product_options_have_been_added._Please_access_the_shop_inventory_section_to_update', $this->siteLangId));
        }
        FatUtility::dieJsonSuccess(array());
    }

    public function getTranslatedOptionData()
    {
        $dataToTranslate = FatApp::getPostedData('option_name1', FatUtility::VAR_STRING, '');
        if (!empty($dataToTranslate)) {
            $translatedText = $this->translateLangFields(Option::DB_TBL_LANG, ['option_name' => $dataToTranslate]);
            $data = [];
            foreach ($translatedText as $langId => $value) {
                $data[$langId]['option_name' . $langId] = $value['option_name'];
            }
            CommonHelper::jsonEncodeUnicode($data, true);
        }
        FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
    }

    public function getTranslatedData()
    {
        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $prodSpecName = FatApp::getPostedData('prod_spec_name', FatUtility::VAR_STRING, '');
        $prodSpecValue = FatApp::getPostedData('prod_spec_value', FatUtility::VAR_STRING, '');

        if (!empty($prodSpecName) && !empty($prodSpecValue)) {
            $data = [];

            $translatedText = $this->translateLangFields(ProductRequest::DB_TBL_LANG, $prodSpecName[$siteDefaultLangId]);
            foreach ($translatedText as $langId => $textArr) {
                foreach ($textArr as $index => $value) {
                    if ('preqlang_lang_id' === $index) {
                        continue;
                    }
                    $data[$langId]['prod_spec_name[' . $langId . '][' . $index . ']'] = $value;
                }
            }

            $translatedText = $this->translateLangFields(ProductRequest::DB_TBL_LANG, $prodSpecValue[$siteDefaultLangId]);
            foreach ($translatedText as $langId => $textArr) {
                foreach ($textArr as $index => $value) {
                    if ('preqlang_lang_id' === $index) {
                        continue;
                    }
                    $data[$langId]['prod_spec_value[' . $langId . '][' . $index . ']'] = $value;
                }
            }

            CommonHelper::jsonEncodeUnicode($data, true);
        }
        FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
    }

    private function getCustomProductIntialSetUpFrm($productId = 0, $preqId = 0)
    {
        $frm = new Form('frmProductIntialSetUp');
        $frm->addRequiredField(Labels::getLabel('LBL_Product_Identifier', $this->siteLangId), 'product_identifier');
        $frm->addSelectBox(Labels::getLabel('LBL_Product_Type', $this->siteLangId), 'product_type', Product::getProductTypes($this->siteLangId), Product::PRODUCT_TYPE_PHYSICAL, array(), '');
        $brandFld = $frm->addTextBox(Labels::getLabel('LBL_Brand', $this->siteLangId), 'brand_name');
        if (FatApp::getConfig("CONF_PRODUCT_BRAND_MANDATORY", FatUtility::VAR_INT, 1)) {
            $brandFld->requirements()->setRequired();
        }
        $frm->addRequiredField(Labels::getLabel('LBL_Category', $this->siteLangId), 'category_name');

        $siteDefaultLangId = FatApp::getConfig('conf_default_site_lang', FatUtility::VAR_INT, 1);
        $languages = Language::getAllNames();
        foreach ($languages as $langId => $lang) {
            if ($langId == $siteDefaultLangId) {
                $frm->addRequiredField(Labels::getLabel('LBL_Product_Name', $this->siteLangId), 'product_name[' . $langId . ']');
            } else {
                $frm->addTextBox(Labels::getLabel('LBL_Product_Name', $this->siteLangId), 'product_name[' . $langId . ']');
            }
            //$frm->addTextArea(Labels::getLabel('LBL_Description', $this->siteLangId), 'product_description[' . $langId . ']');
            $frm->addHtmlEditor(Labels::getLabel('LBL_Description', $this->siteLangId), 'product_description_' . $langId);
            $frm->addTextBox(Labels::getLabel('LBL_Youtube_Video_Url', $this->siteLangId), 'product_youtube_video[' . $langId . ']');
        }

        $translatorSubscriptionKey = FatApp::getConfig('CONF_TRANSLATOR_SUBSCRIPTION_KEY', FatUtility::VAR_STRING, '');
        unset($languages[$siteDefaultLangId]);
        if (!empty($translatorSubscriptionKey) && count($languages) > 0) {
            $frm->addCheckBox(Labels::getLabel('LBL_Translate_To_Other_Languages', $this->siteLangId), 'auto_update_other_langs_data', 1, array(), false, 0);
        }

        $frm->addRequiredField(Labels::getLabel('LBL_Tax_Category', $this->siteLangId), 'taxcat_name');
        $fldMinSelPrice = $frm->addFloatField(Labels::getLabel('LBL_Minimum_Selling_Price', $this->siteLangId) . ' [' . CommonHelper::getCurrencySymbol(true) . ']', 'product_min_selling_price', '');
        $fldMinSelPrice->requirements()->setPositive();

        $activeInactiveArr = applicationConstants::getActiveInactiveArr($this->siteLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->siteLangId), 'product_active', $activeInactiveArr, applicationConstants::YES, array(), '');
        $frm->addHiddenField('', 'product_id', $productId);
        $frm->addHiddenField('', 'preq_id', $preqId);
        $frm->addHiddenField('', 'product_brand_id');
        $frm->addHiddenField('', 'ptc_prodcat_id');
        $frm->addHiddenField('', 'ptt_taxcat_id');
        $frm->addButton('', 'btn_discard', Labels::getLabel('LBL_Discard', $this->siteLangId));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_And_Next', $this->siteLangId));
        return $frm;
    }

    private function getProductAttributeAndSpecificationsFrm($productId, $preqId = 0)
    {
        $frm = new Form('frmProductAttributeAndSpecifications');
        $fldModel = $frm->addTextBox(Labels::getLabel('LBL_Model', $this->siteLangId), 'product_model');
        if (FatApp::getConfig("CONF_PRODUCT_MODEL_MANDATORY", FatUtility::VAR_INT, 1)) {
            $fldModel->requirements()->setRequired();
        }
        $warrantyFld = $frm->addRequiredField(Labels::getLabel('LBL_PRODUCT_WARRANTY', $this->siteLangId), 'product_warranty');
        $warrantyFld->requirements()->setInt();
        $warrantyFld->requirements()->setPositive();
        $frm->addCheckBox(Labels::getLabel('LBL_Mark_This_Product_As_Featured?', $this->siteLangId), 'product_featured', 1, array(), false, 0);

        if ($preqId > 0) {
            $preqContent = ProductRequest::getAttributesById($preqId, 'preq_content');
            $preqContentData = json_decode($preqContent, true);
            $productType = $preqContentData['product_type'];
        } else {
            $productType = Product::getAttributesById($productId, 'product_type');
        }

        if($productType == Product::PRODUCT_TYPE_DIGITAL){
            $warrantyFld->requirements()->setRequired(false);
        }

        $frm->addHiddenField('', 'product_id', $productId);
        $frm->addHiddenField('', 'preq_id', $preqId);
        $frm->addButton('', 'btn_back', Labels::getLabel('LBL_Back', $this->siteLangId));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_And_Next', $this->siteLangId));
        return $frm;
    }

    private function getProductShippingFrm($productId, $preqId = 0, $forCatalogReq = false)
    {
        $frm = new Form('frmProductShipping');
        if ($preqId > 0) {
            $preqContent = ProductRequest::getAttributesById($preqId, 'preq_content');
            $preqContentData = json_decode($preqContent, true);
            $productType = $preqContentData['product_type'];
        } else {
            $productType = Product::getAttributesById($productId, 'product_type');
        }

        $userId = $this->userParentId;
        if (true == $forCatalogReq) {
            $userId = 0;
        }
        $pluginObj = new Plugin();
        if (!FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0) && !$pluginObj->getDefaultPluginKeyName(Plugin::TYPE_SHIPPING_SERVICES)) {
            $shipProfileArr = ShippingProfile::getProfileArr($this->siteLangId, $userId, true, true);
            $frm->addSelectBox(Labels::getLabel('LBL_Shipping_Profile', $this->siteLangId), 'shipping_profile', $shipProfileArr)->requirements()->setRequired();
        }
        if ($productType == Product::PRODUCT_TYPE_PHYSICAL) {
            if (FatApp::getConfig("CONF_PRODUCT_DIMENSIONS_ENABLE", FatUtility::VAR_INT, 1)) {
                $shipPackArr = ShippingPackage::getAllNames();
                $frm->addSelectBox(Labels::getLabel('LBL_Shipping_Package', $this->siteLangId), 'product_ship_package', $shipPackArr)->requirements()->setRequired();
            }

            $weightUnitsArr = applicationConstants::getWeightUnitsArr($this->siteLangId);
            $frm->addSelectBox(Labels::getLabel('LBL_Weight_Unit', $this->siteLangId), 'product_weight_unit', $weightUnitsArr)->requirements()->setRequired();

            $weightFld = $frm->addFloatField(Labels::getLabel('LBL_Weight', $this->siteLangId), 'product_weight', '0.00');
            $weightFld->requirements()->setRequired(true);
            $weightFld->requirements()->setFloatPositive();
            $weightFld->requirements()->setRange('0.01', '9999999999');

            if (!FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
                /*  $frm->addCheckBox(Labels::getLabel('LBL_Product_Is_Eligible_For_Free_Shipping?', $this->siteLangId), 'ps_free', 1, array(), false, 0); */

                $codFld = $frm->addCheckBox(Labels::getLabel('LBL_Product_Is_Available_for_Cash_on_Delivery_(COD)?', $this->siteLangId), 'product_cod_enabled', 1, array(), false, 0);
                $paymentMethod = new PaymentMethods();
                if (!$paymentMethod->cashOnDeliveryIsActive()) {
                    $codFld->addFieldTagAttribute('disabled', 'disabled');
                    $codFld->htmlAfterField = '<br/><small>' . Labels::getLabel('LBL_COD_option_is_disabled_in_payment_gateway_settings', $this->siteLangId) . '</small>';
                }
            }

            /* ] */
        }
        if ($preqId == 0 && !FatApp::getConfig('CONF_SHIPPED_BY_ADMIN_ONLY', FatUtility::VAR_INT, 0)) {
            $frm->addTextBox(Labels::getLabel('LBL_Country_the_Product_is_being_shipped_from', $this->siteLangId), 'shipping_country');
            //$frm->addHtml('', '', '<div id="tab_shipping"></div>');
        }

        $frm->addHiddenField('', 'ps_from_country_id');
        $frm->addHiddenField('', 'product_id', $productId);
        $frm->addHiddenField('', 'preq_id', $preqId);
        $frm->addButton('', 'btn_back', Labels::getLabel('LBL_Back', $this->siteLangId));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_And_Next', $this->siteLangId));
        return $frm;
    }

    public function translatedProductData()
    {
        $prodName = FatApp::getPostedData('product_name', FatUtility::VAR_STRING, '');
        $prodDesc = FatApp::getPostedData('product_description', FatUtility::VAR_STRING, '');
        $toLangId = FatApp::getPostedData('toLangId', FatUtility::VAR_INT, 0);
        $data = array(
            'product_name' => $prodName,
            'product_description' => $prodDesc,
        );
        $product = new Product();
        $translatedData = $product->getTranslatedProductData($data, $toLangId);
        if (!$translatedData) {
            Message::addErrorMessage($product->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }
        $this->set('productName', $translatedData[$toLangId]['product_name']);
        $this->set('productDesc', $translatedData[$toLangId]['product_description']);
        $this->set('msg', Labels::getLabel('LBL_Product_Data_Translated_Successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
    public function compareWithInventoryMinPurchase()
    {
        $selProdId = FatApp::getPostedData('selProdId', FatUtility::VAR_INT, 0);
        $qty = FatApp::getPostedData('qty', FatUtility::VAR_INT, 0);
        if ($selProdId < 1) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Please_choose_product', $this->siteLangId));
        }
        $minPurchaseQty = SellerProduct::getAttributesById($selProdId, 'selprod_min_order_qty');
        if ($qty < $minPurchaseQty) {
            FatUtility::dieJsonError(Labels::getLabel('MSG_Quantity_cannot_be_less_than_the_Minimum_Order_Quantity', $this->siteLangId) . ': ' . $minPurchaseQty);
        }
        $this->_template->render(false, false, 'json-success.php');
    }

    public function pickupAddress()
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;
        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        if (!false == $shopDetails) {
            $shop_id = $shopDetails['shop_id'];
        }
        $address = new Address(0, $this->siteLangId);
        $addresses = $address->getData(Address::TYPE_SHOP_PICKUP, $shopDetails['shop_id']);

        if ($addresses) {
            $this->set('addresses', $addresses);
        } else {
            if (true === MOBILE_APP_API_CALL) {
                $this->set('addresses', array());
            }
            $this->set('noRecordsHtml', $this->_template->render(false, false, '_partial/no-record-found.php', true));
        }
        if (true === MOBILE_APP_API_CALL) {
            $cartObj = new Cart($userId);
            $shipping_address_id = $cartObj->getCartShippingAddress();
            $this->set('shippingAddressId', $shipping_address_id);
            $this->_template->render();
        }
        $this->set('canEdit', $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId(), true));
        $this->set('shop_id', $shop_id);
        $this->set('language', Language::getAllNames());
        $this->_template->render(false, false);
    }

    public function pickupAddressForm(int $addrId = 0)
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;
        $shopDetails = Shop::getAttributesByUserId($userId, null, false);

        if (!false == $shopDetails && $shopDetails['shop_active'] != applicationConstants::ACTIVE) {
            Message::addErrorMessage(Labels::getLabel('MSG_Your_shop_deactivated_contact_admin', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }
        $shopId = 0;
        $stateId = 0;
        $slotData = [];
        if (!false == $shopDetails) {
            $shopId = $shopDetails['shop_id'];
        }
        $frm = $this->getPickUpAddressForm($addrId);
        $availability = TimeSlot::DAY_INDIVIDUAL_DAYS;
        if ($addrId > 0) {
            $address = new Address($addrId, $this->siteLangId);
            $data = $address->getData(Address::TYPE_SHOP_PICKUP, $shopId);
            if (!empty($data)) {
                $stateId = $data['addr_state_id'];

                $timeSlot = new TimeSlot();
                $timeSlots = $timeSlot->timeSlotsByAddrId($addrId);

                $timeSlotsRow = current($timeSlots);
                $availability = isset($timeSlotsRow['tslot_availability']) ? $timeSlotsRow['tslot_availability'] : $availability;
                if ($availability == TimeSlot::DAY_ALL_DAYS) {
                    $data['tslot_from_all'] = date('H:i', strtotime($timeSlotsRow['tslot_from_time']));
                    $data['tslot_to_all'] = date('H:i', strtotime($timeSlotsRow['tslot_to_time']));
                }
                $data['tslot_availability'] = $availability;
                $frm->fill($data);
                if (!empty($timeSlots)) {
                    foreach ($timeSlots as $key => $slot) {
                        $slotData['tslot_day'][$slot['tslot_day']] = $slot['tslot_day'];
                        $slotData['tslot_from_time'][$slot['tslot_day']][] = $slot['tslot_from_time'];
                        $slotData['tslot_to_time'][$slot['tslot_day']][] = $slot['tslot_to_time'];
                    }
                }
            }
        }

        $this->set('availability', $availability);
        $this->set('shop_id', $shopId);
        $this->set('language', Language::getAllNames());
        $this->set('siteLangId', $this->siteLangId);
        $this->set('frm', $frm);
        $this->set('stateId', $stateId);
        $this->set('languages', Language::getAllNames());
        $this->set('slotData', $slotData);
        $this->_template->render(false, false);
    }

    public function checkIfNotAnyInventory($productId)
    {
        $productId = FatUtility::int($productId);
        if (0 == $productId) {
            FatUtility::dieJsonError(Labels::getLabel('LBL_Invalid_Request', $this->siteLangId));
        }
        $available = SellerProduct::getCatelogFromProductId($productId);
        if (count($available) > 0) {
            FatUtility::dieJsonSuccess(array());
        }
        FatUtility::dieJsonError(Labels::getLabel('LBL_Not_any_Inventory_yet', $this->siteLangId));
    }

    public function orderTrackingInfo($trackingNumber, $courier, $orderNumber)
    {
        if (empty($trackingNumber) || empty($courier)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request', $this->siteLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shipmentTracking = new ShipmentTracking();
        if (false === $shipmentTracking->init($this->siteLangId)) {
            Message::addErrorMessage($shipmentTracking->getError());
            FatUtility::dieWithError(Message::getHtml());
        }

        $shipmentTracking->createTracking($trackingNumber, $courier, $orderNumber);

        if (false === $shipmentTracking->getTrackingInfo($trackingNumber, $courier)) {
            Message::addErrorMessage($shipmentTracking->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
        $trackingInfo = $shipmentTracking->getResponse();

        $this->set('trackingInfo', $trackingInfo);
        $this->_template->render(false, false);
    }

    private function getPickUpAddressForm($addressId = 0)
    {
        $addressId = FatUtility::int($addressId);
        $frm = new Form('frmPickUpAddress');
        $frm->addHiddenField('', 'addr_id', $addressId);
        $frm->addTextBox(Labels::getLabel('LBL_Address_Label', $this->siteLangId), 'addr_title');
        $frm->addRequiredField(Labels::getLabel('LBL_Name', $this->siteLangId), 'addr_name');
        $frm->addRequiredField(Labels::getLabel('LBL_Address_Line1', $this->siteLangId), 'addr_address1');
        $frm->addTextBox(Labels::getLabel('LBL_Address_Line2', $this->siteLangId), 'addr_address2');

        $countryObj = new Countries();
        $countriesArr = $countryObj->getCountriesArr($this->siteLangId);
        $frm->addSelectBox(Labels::getLabel('LBL_Country', $this->siteLangId), 'addr_country_id', $countriesArr, '', array(), Labels::getLabel('LBL_Select', $this->siteLangId))->requirement->setRequired(true);

        $frm->addSelectBox(Labels::getLabel('LBL_State', $this->siteLangId), 'addr_state_id', array(), '', array(), Labels::getLabel('LBL_Select', $this->siteLangId))->requirement->setRequired(true);
        $frm->addRequiredField(Labels::getLabel('LBL_City', $this->siteLangId), 'addr_city');

        $zipFld = $frm->addRequiredField(Labels::getLabel('LBL_Postalcode', $this->siteLangId), 'addr_zip');
        $frm->addHiddenField('', 'addr_phone_dcode');
        $phnFld = $frm->addRequiredField(Labels::getLabel('LBL_Phone', $this->siteLangId), 'addr_phone', '', array('class' => 'phone-js ltr-right', 'placeholder' => ValidateElement::PHONE_NO_FORMAT, 'maxlength' => ValidateElement::PHONE_NO_LENGTH));
        $phnFld->requirements()->setRegularExpressionToValidate(ValidateElement::PHONE_REGEX);
        $phnFld->requirements()->setCustomErrorMessage(Labels::getLabel('LBL_Please_enter_valid_phone_number_format.', $this->siteLangId));

        $slotTimingsTypeArr = TimeSlot::getSlotTypeArr($this->siteLangId);
        $frm->addRadioButtons(Labels::getLabel('LBL_Slot_Timings', $this->siteLangId), 'tslot_availability', $slotTimingsTypeArr, TimeSlot::DAY_INDIVIDUAL_DAYS);

        $daysArr = TimeSlot::getDaysArr($this->siteLangId);
        for ($i = 0; $i < count($daysArr); $i++) {
            $frm->addCheckBox($daysArr[$i], 'tslot_day[' . $i . ']', $i, array(), false);
            $frm->addSelectBox(Labels::getLabel('LBL_From', $this->siteLangId), 'tslot_from_time[' . $i . '][]', TimeSlot::getTimeSlotsArr(), '', array(), Labels::getLabel('LBL_Select', $this->siteLangId));
            $frm->addSelectBox(Labels::getLabel('LBL_To', $this->siteLangId), 'tslot_to_time[' . $i . '][]', TimeSlot::getTimeSlotsArr(), '', array(), Labels::getLabel('LBL_Select', $this->siteLangId));
            //$frm->addButton('', 'btn_add_row['.$i.']', '+');                      
        }

        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->siteLangId));
        $fldCancel = $frm->addButton('', 'btn_cancel', Labels::getLabel('LBL_Cancel', $this->siteLangId));
        return $frm;
    }

    public function setPickupAddress()
    {
        $this->userPrivilege->canEditShop(UserAuthentication::getLoggedUserId());
        $userId = $this->userParentId;
        $shopId = Shop::getAttributesByUserId($userId, 'shop_id');

        $post = FatApp::getPostedData();
        $availability = FatApp::getPostedData('tslot_availability', FatUtility::VAR_INT, 1);

        $addrStateId = FatUtility::int($post['addr_state_id']);
        $slotDays = isset($post['tslot_day']) ? $post['tslot_day'] : array();
        $slotFromTime = $post['tslot_from_time'];
        $slotToTime = $post['tslot_to_time'];

        $frm = $this->getPickUpAddressForm($post['addr_id']);
        $postedData = $frm->getFormDataFromArray($post);
        if (false === $postedData) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if($availability == TimeSlot::DAY_ALL_DAYS  && !isset($slotFromTime[TimeSlot::DAY_SUNDAY])){
            FatUtility::dieJsonError(Labels::getLabel('MSG_INVALID_REQUEST', $this->siteLangId));
        }

        $addressId = $post['addr_id'];
        unset($post['addr_id']);

        $address = new Address($addressId);
        $data = $post;
        $data['addr_state_id'] = $addrStateId;
        $data['addr_record_id'] = $shopId;
        $data['addr_lang_id'] = $this->siteLangId;
        $data['addr_type'] =  Address::TYPE_SHOP_PICKUP;;
        $address->assignValues($data);
        if (!$address->save()) {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError($address->getError());
            }
            Message::addErrorMessage($address->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $addrId = $address->getMainTableRecordId();
        if (!FatApp::getDb()->deleteRecords(TimeSlot::DB_TBL, array('smt' => 'tslot_type = ? and tslot_record_id = ?', 'vals' => array(Address::TYPE_SHOP_PICKUP, $addrId)))) {
            if (true === MOBILE_APP_API_CALL) {
                LibHelper::dieJsonError(FatApp::getDb()->getError());
            }
            Message::addErrorMessage(FatApp::getDb()->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!empty($slotDays) && $availability == TimeSlot::DAY_INDIVIDUAL_DAYS) {
            foreach ($slotDays as $day) {
                foreach ($slotFromTime[$day] as $key => $fromTime) {
                    if (!empty($fromTime) && !empty($slotToTime[$day][$key])) {
                        $slotData['tslot_type'] = Address::TYPE_SHOP_PICKUP;
                        $slotData['tslot_availability'] = $availability;
                        $slotData['tslot_record_id'] = $addrId;
                        $slotData['tslot_day'] = $day;
                        $slotData['tslot_from_time'] = $fromTime;
                        $slotData['tslot_to_time'] = $post['tslot_to_time'][$day][$key];
                        $timeSlot = new TimeSlot();
                        $timeSlot->assignValues($slotData);
                        if (!$timeSlot->save()) {
                            if (true === MOBILE_APP_API_CALL) {
                                LibHelper::dieJsonError($timeSlot->getError());
                            }
                            Message::addErrorMessage($timeSlot->getError());
                            FatUtility::dieJsonError(Message::getHtml());
                        }
                    }
                }
            }
        }

        if (!empty($slotDays) && $availability == TimeSlot::DAY_ALL_DAYS) {
            $daysArr = TimeSlot::getDaysArr($this->siteLangId);
            foreach ($daysArr as $day => $label) {
                foreach ($slotFromTime[TimeSlot::DAY_SUNDAY] as $key => $fromTime) {
                    if (!empty($fromTime) && !empty($slotToTime[TimeSlot::DAY_SUNDAY][$key])) {
                        $slotData['tslot_type'] = Address::TYPE_SHOP_PICKUP;
                        $slotData['tslot_availability'] = $availability;
                        $slotData['tslot_record_id'] = $addrId;
                        $slotData['tslot_day'] = $day;
                        $slotData['tslot_from_time'] = $fromTime;
                        $slotData['tslot_to_time'] = $post['tslot_to_time'][TimeSlot::DAY_SUNDAY][$key];
                        $timeSlot = new TimeSlot();
                        $timeSlot->assignValues($slotData);
                        if (!$timeSlot->save()) {
                            Message::addErrorMessage($timeSlot->getError());
                            FatUtility::dieJsonError(Message::getHtml());
                        }
                    }
                }
            }
        }

        $this->set('msg', Labels::getLabel('MSG_Setup_successful', $this->siteLangId));
        $this->_template->render(false, false, 'json-success.php');
    }
}
