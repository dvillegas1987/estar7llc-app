<?php

class SellerOrdersController extends AdminBaseController
{
    private $shippingService;
    private $trackingService;
    private $paymentPlugin;
    private $method = '';

    public function __construct($action)
    {
        $ajaxCallArray = array();
        if (!FatUtility::isAjaxCall() && in_array($action, $ajaxCallArray)) {
            die($this->str_invalid_Action);
        }
        $this->method = $action;
        parent::__construct($action);
        $this->admin_id = AdminAuthentication::getLoggedAdminId();
        $this->canView = $this->objPrivilege->canViewSellerOrders($this->admin_id, true);
        $this->canEdit = $this->objPrivilege->canEditSellerOrders($this->admin_id, true);
        $this->set("canView", $this->canView);
        $this->set("canEdit", $this->canEdit);
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

        $this->shippingService = PluginHelper::callPlugin($keyName, [$this->adminLangId], $error, $this->adminLangId, false);

        if (false === $this->shippingService) {
            if ('search' == strtolower($this->method)) {
                Message::addErrorMessage($error);
                FatUtility::dieWithError(Message::getHtml());
            } else {
                FatApp::redirectUser(UrlHelper::generateUrl("SellerOrders"));
            }
        }

        if (false === $this->shippingService->init()) {
            if ('search' == strtolower($this->method)) {
                Message::addErrorMessage($this->shippingService->getError());
                FatUtility::dieWithError(Message::getHtml());
            } else {
                FatApp::redirectUser(UrlHelper::generateUrl("SellerOrders"));
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

        $this->trackingService = PluginHelper::callPlugin($keyName, [$this->adminLangId], $error, $this->adminLangId, false);
        if (false === $this->trackingService) {
            Message::addErrorMessage($error);
            FatUtility::dieWithError(Message::getHtml());
        }

        if (false === $this->trackingService->init()) {
            Message::addErrorMessage($this->shippingService->getError());
            FatUtility::dieWithError(Message::getHtml());
        }
    }

    public function index($order_id = '')
    {
        $this->objPrivilege->canViewSellerOrders();
        $frm = $this->getOrderSearchForm($this->adminLangId);
        $frm->fill(array('order_id' => $order_id));
        $this->set('frmSearch', $frm);
        $this->_template->render();
    }

    public function search()
    {
        $this->objPrivilege->canViewSellerOrders();
        $frmSearch = $this->getOrderSearchForm($this->adminLangId);

        $data = FatApp::getPostedData();
        $post = $frmSearch->getFormDataFromArray($data);


        $page = (empty($data['page']) || $data['page'] <= 0) ? 1 : FatUtility::int($data['page']);
        $pageSize = FatApp::getConfig('CONF_ADMIN_PAGESIZE', FatUtility::VAR_INT, 10);

        $ocSrch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $ocSrch->doNotCalculateRecords();
        $ocSrch->doNotLimitRecords();
        $ocSrch->addMultipleFields(array('opcharge_op_id', 'sum(opcharge_amount) as op_other_charges'));
        $ocSrch->addGroupBy('opc.opcharge_op_id');
        $qryOtherCharges = $ocSrch->getQuery();

        $srch = new OrderProductSearch($this->adminLangId, true, true);
        $srch->joinOrderUser();
        $srch->joinPaymentMethod();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->joinOrderProductShipment();
        $srch->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
        $srch->setPageNumber($page);
        $srch->setPageSize($pageSize);
        $srch->addOrder('op_id', 'DESC');

        $srch->addMultipleFields(array('op_id', 'order_id', 'order_payment_status', 'op_order_id', 'op_invoice_number', 'order_net_amount', 'order_date_added', 'ou.user_id', 'ou.user_name as buyer_name', 'ouc.credential_username as buyer_username', 'ouc.credential_email as buyer_email', 'ou.user_phone_dcode as buyer_phone_dcode', 'ou.user_phone as buyer_phone', 'op.op_shop_owner_name', 'op.op_shop_owner_username', 'op.op_shop_owner_email', 'op.op_shop_owner_phone_dcode', 'op.op_shop_owner_phone', 'op_shop_name', 'op_other_charges', 'op.op_qty', 'op.op_unit_price', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name', 'op_status_id', 'op_tax_collected_by_seller', 'op_selprod_user_id', 'opshipping_by_seller_user_id', 'plugin_code', 'IFNULL(plugin_name, IFNULL(plugin_identifier, "Wallet")) as plugin_name', 'opship.*', 'opshipping_fulfillment_type', 'orderstatus_color_class', 'op_rounding_off', 'op_product_type', 'opshipping_carrier_code', 'opshipping_service_code'));
        if (isset($post['order_id']) && $post['order_id'] != '') {
            $srch->addCondition('op_order_id', '=', $post['order_id']);
        }

        $keyword = FatApp::getPostedData('keyword', null, '');

        if (!empty($keyword)) {
            $cnd = $srch->addCondition('op.op_order_id', 'like', '%' . $keyword . '%');
            $srch->addKeywordSearch($keyword, $cnd);
            $cnd->attachCondition('op.op_shop_owner_name', 'like', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op.op_shop_owner_username', 'like', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op.op_shop_owner_email', 'like', '%' . $keyword . '%', 'OR');
        }

        $user_id = FatApp::getPostedData('user_id', '', -1);
        if ($user_id > 0) {
            $srch->addCondition('user_id', '=', $user_id);
        } else {
            $customer_name = FatApp::getPostedData('buyer', null, '');
            if (!empty($customer_name)) {
                $cnd = $srch->addCondition('ou.user_name', 'like', '%' . $customer_name . '%');
                $cnd->attachCondition('ou.user_phone', 'like', '%' . $customer_name . '%', 'OR');
                $cnd->attachCondition('ouc.credential_email', 'like', '%' . $customer_name . '%', 'OR');
            }
        }

        $shipping_company_user_id = FatApp::getPostedData('shipping_company_user_id', FatUtility::VAR_INT, 0);
        if ($shipping_company_user_id > 0) {
            $srch->joinShippingUsers();
            $srch->addCondition('optsu_user_id', '=', $shipping_company_user_id);
        }

        if (isset($post['op_status_id']) && $post['op_status_id'] != '') {
            $op_status_id = FatUtility::int($post['op_status_id']);
            $srch->addStatusCondition($op_status_id, ($op_status_id == FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS")));
        }

        $shop_name = FatApp::getPostedData('shop_name', null, '');
        if (!empty($shop_name)) {
            $cnd = $srch->addCondition('op_l.op_shop_name', 'like', '%' . $shop_name . '%');
            $cnd->attachCondition('op.op_shop_owner_name', 'like', '%' . $shop_name . '%', 'OR');
            $cnd->attachCondition('op.op_shop_owner_username', 'like', '%' . $shop_name . '%', 'OR');
            $cnd->attachCondition('op.op_shop_owner_email', 'like', '%' . $shop_name . '%', 'OR');
            $cnd->attachCondition('op.op_shop_owner_phone', 'like', '%' . $shop_name . '%', 'OR');
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
        $vendorOrdersList = FatApp::getDb()->fetchAll($rs);

        $oObj = new Orders();
        foreach ($vendorOrdersList as &$order) {
            $charges = $oObj->getOrderProductChargesArr($order['op_id']);
            $order['charges'] = $charges;
        }

        /* ShipStation */
        $this->loadShippingService();
        $this->set('canShipByPlugin', (NULL !== $this->shippingService));
        /* ShipStation */

        $this->set("vendorOrdersList", $vendorOrdersList);
        $this->set('pageCount', $srch->pages());
        $this->set('page', $page);
        $this->set('pageSize', $pageSize);
        $this->set('postedData', $post);
        $this->set('recordCount', $srch->recordCount());
        $this->set('classArr', applicationConstants::getClassArr());
        $this->set('canViewUsers', $this->objPrivilege->canViewUsers($this->admin_id, true));
        $this->_template->render(false, false);
    }

    public function view($op_id, $print = false)
    {
        $this->objPrivilege->canViewSellerOrders();
        $op_id = FatUtility::int($op_id);

        $srch = new OrderProductSearch($this->adminLangId, true, true);
        $srch->joinOrderProductShipment();
        $srch->joinOrderUser();
        $srch->joinPaymentMethod();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->joinAddress();
        $srch->addOrderProductCharges();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(
            array(
                'ops.*', 'order_id', 'order_payment_status', 'order_pmethod_id', 'order_tax_charged', 'order_date_added', 'op_id', 'op_qty', 'op_unit_price', 'op_selprod_user_id', 'op_invoice_number', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name', 'ou.user_name as buyer_user_name', 'ouc.credential_username as buyer_username', 'plugin_code', 'IFNULL(plugin_name, IFNULL(plugin_identifier, "Wallet")) as plugin_name', 'op_commission_charged', 'op_qty', 'op_commission_percentage', 'ou.user_name as buyer_name', 'ouc.credential_username as buyer_username', 'ouc.credential_email as buyer_email', 'ou.user_phone_dcode as buyer_phone_dcode', 'ou.user_phone as buyer_phone', 'op.op_shop_owner_name', 'op.op_shop_owner_username', 'op_l.op_shop_name', 'op.op_shop_owner_email', 'op.op_shop_owner_phone_dcode', 'op.op_shop_owner_phone',
                'op_selprod_title', 'op_product_name', 'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model', 'op_product_type',
                'op_shipping_duration_name', 'op_shipping_durations', 'op_status_id', 'op_refund_qty', 'op_refund_amount', 'op_refund_commission', 'op_other_charges', 'optosu.optsu_user_id', 'op_tax_collected_by_seller', 'order_is_wallet_selected', 'order_reward_point_used', 'op_product_tax_options', 'ops.*', 'opship.*', 'addr.*', 'op_rounding_off'
            )
        );
        $srch->addCondition('op_id', '=', $op_id);

        $opRs = $srch->getResultSet();
        $opRow = FatApp::getDb()->fetch($opRs);
        if ($opRow == false) {
            Message::addErrorMessage($this->str_invalid_request);
            CommonHelper::redirectUserReferer();
        }

        if ($opRow['opshipping_fulfillment_type'] == Shipping::FULFILMENT_SHIP) {
            /* ShipStation */
            $this->loadShippingService();
            $this->set('canShipByPlugin', (null !== $this->shippingService));

            if (!empty($opRow["opship_orderid"])) {
                if (null != $this->shippingService && false === $this->shippingService->loadOrder($opRow["opship_orderid"])) {
                    Message::addErrorMessage($this->shippingService->getError());
                    FatApp::redirectUser(UrlHelper::generateUrl("SellerOrders"));
                }
                $opRow['thirdPartyorderInfo'] = (null != $this->shippingService ? $this->shippingService->getResponse() : []);
            }
            /* ShipStation */

            /* AfterShip */
            $this->loadTrackingService();
            $this->set('canTrackByPlugin', (null !== $this->trackingService));
            /* AfterShip */

            if (null !== $this->shippingService && null !== $this->trackingService) {
                $srch = TrackingCourierCodeRelation::getSearchObject();
                $srch->addCondition("tccr_shipapi_courier_code", "=", $opRow['opshipping_carrier_code']);
                $srch->doNotCalculateRecords();
                $srch->setPageSize(1);
                $rs = $srch->getResultSet();
                $data = FatApp::getDb()->fetch($rs);
                if (null === $data) {
                    Message::addErrorMessage(Labels::getLabel("MSG_PLEASE_MAP_YOUR_SHIPPING_CARRIER_CODE_WITH_TRACKING_CARRIER_CODE", $this->adminLangId));
                    FatApp::redirectUser(UrlHelper::generateUrl("TrackingCodeRelation"));
                }
            }
        } else {
            $this->set('canShipByPlugin', '');
        }

        $orderObj = new Orders($opRow['order_id']);

        $charges = $orderObj->getOrderProductChargesArr($op_id);
        $opRow['charges'] = $charges;

        $addresses = $orderObj->getOrderAddresses($opRow['order_id']);
        $opRow['billingAddress'] = $addresses[Orders::BILLING_ADDRESS_TYPE];
        $opRow['shippingAddress'] = (!empty($addresses[Orders::SHIPPING_ADDRESS_TYPE])) ? $addresses[Orders::SHIPPING_ADDRESS_TYPE] : array();

        $pickUpAddress = $orderObj->getOrderAddresses($opRow['order_id'], $op_id);
        $opRow['pickupAddress'] = (!empty($pickUpAddress[Orders::PICKUP_ADDRESS_TYPE])) ? $pickUpAddress[Orders::PICKUP_ADDRESS_TYPE] : array();

        $opRow['comments'] = $orderObj->getOrderComments($this->adminLangId, array("op_id" => $op_id));

        if ($opRow['plugin_code'] == 'CashOnDelivery') {
            $processingStatuses = $orderObj->getAdminAllowedUpdateOrderStatuses(true);
        } else if ($opRow['plugin_code'] == 'PayAtStore') {
            $processingStatuses = $orderObj->getAdminAllowedUpdateOrderStatuses(false, false, true);
        } else {
            $processingStatuses = $orderObj->getAdminAllowedUpdateOrderStatuses(false, $opRow['op_product_type']);
        }

        $data = [
            'op_id' => $op_id,
            'op_status_id' => $opRow['op_status_id'],
            'tracking_number' => $opRow['opship_tracking_number']
        ];

        if ($opRow["opshipping_fulfillment_type"] == Shipping::FULFILMENT_PICKUP) {
            $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS", FatUtility::VAR_INT, 0));
            // $processingStatuses = array_diff($processingStatuses, (array) FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS"));
        }
        $frm = $this->getOrderCommentsForm($opRow, $processingStatuses);
        $frm->fill($data);

        $orderStatuses = Orders::getOrderProductStatusArr($this->adminLangId);

        $shippingHanldedBySeller = CommonHelper::canAvailShippingChargesBySeller($opRow['op_selprod_user_id'], $opRow['opshipping_by_seller_user_id']);

        $allowedShippingUserStatuses = $orderObj->getAdminAllowedUpdateShippingUser();
        $displayShippingUserForm = false;

        if (((in_array(strtolower($opRow['plugin_code']), ['cashondelivery', 'payatstore'])) || (in_array($opRow['op_status_id'], $allowedShippingUserStatuses))) && $this->canEdit && !$shippingHanldedBySeller && ($opRow['op_product_type'] == Product::PRODUCT_TYPE_PHYSICAL && $opRow['order_payment_status'] != Orders::ORDER_PAYMENT_CANCELLED)) {
            $displayShippingUserForm = true;
            /*
            if ($opRow["opshipping_fulfillment_type"] == Shipping::FULFILMENT_PICKUP) {
                $displayShippingUserForm = false;
            }
             * 
             */
            $shippingUserFrm = $this->getShippingCompanyUserForm($displayShippingUserForm);
            $shippingUserdata = array('op_id' => $op_id, 'optsu_user_id' => $opRow['optsu_user_id']);
            $shippingUserFrm->fill($shippingUserdata);
            $this->set('shippingUserFrm', $shippingUserFrm);
        }

        $digitalDownloads = array();
        if ($opRow['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $digitalDownloads = Orders::getOrderProductDigitalDownloads($op_id);
        }

        $digitalDownloadLinks = array();
        if ($opRow['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
            $digitalDownloadLinks = Orders::getOrderProductDigitalDownloadLinks($op_id);
        }

        $opChargesLog = new OrderProductChargeLog($op_id);
        $taxOptions = $opChargesLog->getData($this->adminLangId);
        $opRow['taxOptions'] = $taxOptions;

        $this->set('allLanguages', Language::getAllNames(false, 0, false, false));
        $this->set('frm', $frm);
        $this->set('shippingHanldedBySeller', $shippingHanldedBySeller);
        $this->set('order', $opRow);
        $this->set('orderStatuses', $orderStatuses);
        $this->set('digitalDownloads', $digitalDownloads);
        $this->set('digitalDownloadLinks', $digitalDownloadLinks);
        $this->set('yesNoArr', applicationConstants::getYesNoArr($this->adminLangId));
        $this->set('displayForm', (in_array($opRow['op_status_id'], $processingStatuses) && $this->canEdit && $opRow['order_payment_status'] != Orders::ORDER_PAYMENT_CANCELLED));
        $this->set('displayShippingUserForm', $displayShippingUserForm);

        if ($print) {
            $print = true;
        }
        $this->set('print', $print);
        $urlParts = array_filter(FatApp::getParameters());
        $this->set('urlParts', $urlParts);

        $this->_template->render(true, !$print);
    }

    public function viewInvoice($op_id)
    {
        $this->objPrivilege->canViewSellerOrders();
        $op_id = FatUtility::int($op_id);
        if (1 > $op_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
            CommonHelper::redirectUserReferer();
        }

        $orderObj = new Orders();

        $srch = new OrderProductSearch($this->adminLangId, true, true);
        $srch->joinPaymentMethod();
        $srch->joinSellerProducts();
        $srch->joinShop();
        $srch->joinShopSpecifics();
        $srch->joinShopCountry();
        $srch->joinShopState();
        $srch->joinShippingUsers();
        $srch->joinShippingCharges();
        $srch->addOrderProductCharges();
        $srch->addCondition('op_id', '=', $op_id);
        $srch->addStatusCondition(unserialize(FatApp::getConfig("CONF_VENDOR_ORDER_STATUS")));
        $srch->addMultipleFields(array('*', 'shop_country_l.country_name as shop_country_name', 'shop_state_l.state_name as shop_state_name', 'shop_city'));
        $rs = $srch->getResultSet();
        $orderDetail = FatApp::getDb()->fetch($rs);

        if (!$orderDetail) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
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
        $taxOptions = $opChargesLog->getData($this->adminLangId);
        $orderDetail['taxOptions'] = $taxOptions;

        /* $this->set('orderDetail', $orderDetail);
        $this->set('languages', Language::getAllNames());
        $this->set('yesNoArr', applicationConstants::getYesNoArr($this->adminLangId));
        $this->set('canEdit', $this->objPrivilege->canEditSales(UserAuthentication::getLoggedUserId(), true));
        $this->_template->render(true, true); */

        $template = new FatTemplate('', '');
        $template->set('adminLangId', $this->adminLangId);
        $template->set('orderDetail', $orderDetail);
        $template->set('shippedBySeller', $shippedBySeller);

        require_once(CONF_INSTALLATION_PATH . 'library/tcpdf/tcpdf.php');
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(FatApp::getConfig("CONF_WEBSITE_NAME_" . $this->adminLangId));
        $pdf->SetKeywords(FatApp::getConfig("CONF_WEBSITE_NAME_" . $this->adminLangId));
        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->SetHeaderMargin(0);
        $pdf->SetHeaderData('', 0, '', '', array(255, 255, 255), array(255, 255, 255));
        $pdf->setFooterData(array(0, 0, 0), array(200, 200, 200));
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetMargins(10, 10, 10);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->AddPage();
        $pdf->SetTitle(Labels::getLabel('LBL_Tax_Invoice', $this->adminLangId));
        $pdf->SetSubject(Labels::getLabel('LBL_Tax_Invoice', $this->adminLangId));

        // set LTR direction for english translation
        $pdf->setRTL(('rtl' == Language::getLayoutDirection($this->adminLangId)));
        // set font
        $pdf->SetFont('dejavusans');

        $templatePath = "seller-orders/view-invoice.php";
        $html = $template->render(false, false, $templatePath, true, true);
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->lastPage();

        ob_end_clean();
        // $saveFile = CONF_UPLOADS_PATH . 'demo-pdf.pdf';
        //$pdf->Output($saveFile, 'F');
        $pdf->Output('tax-invoice.pdf', 'I');
        return true;
    }

    public function updateShippingCompany()
    {
        $this->objPrivilege->canEditSellerOrders();
        $post = FatApp::getPostedData();
        $op_id = FatApp::getPostedData('op_id', FatUtility::VAR_INT, 0);
        if (1 > $op_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = new OrderProductSearch($this->adminLangId, true, true);
        $srch->joinPaymentMethod();
        $srch->joinShippingUsers();
        $srch->joinOrderUser();
        $srch->addOrderProductCharges();
        $srch->addCondition('op_id', '=', $op_id);
        //$srch->addMultipleFields(array('op_id','op_order_id','optsu_user_id'));
        $srch->addMultipleFields(
            array(
                'order_id', 'order_pmethod_id', 'order_date_added', 'op_id', 'op_qty', 'op_unit_price',
                'op_invoice_number', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name', 'ou.user_name as buyer_user_name', 'ouc.credential_username as buyer_username', 'IFNULL(plugin_name, IFNULL(plugin_identifier, "Wallet")) as plugin_name', 'op_commission_charged', 'op_commission_percentage',   'ou.user_name as buyer_name', 'ouc.credential_username as buyer_username', 'ouc.credential_email as buyer_email', 'ou.user_phone_dcode as buyer_phone_dcode', 'ou.user_phone as buyer_phone', 'op.op_shop_owner_name', 'op.op_shop_owner_username', 'op_l.op_shop_name', 'op.op_shop_owner_email', 'op.op_shop_owner_phone_dcode', 'op.op_shop_owner_phone', 'op_selprod_title', 'op_product_name', 'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model', 'op_shipping_duration_name', 'op_shipping_durations', 'op_status_id', 'op_other_charges', 'op_rounding_off', 'optsu_user_id', 'op_product_weight', 'credential_email', 'plugin_code'
            )
        );
        $rs = $srch->getResultSet();
        $orderDetail = FatApp::getDb()->fetch($rs);

        if (!$orderDetail) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $srch = new SearchBase(OrderProduct::DB_TBL_OP_TO_SHIPPING_USERS, 'optosu');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('optosu.optsu_op_id', '=', $orderDetail['op_id']);
        $rs = $srch->getResultSet();
        $shippingUserRow = FatApp::getDb()->fetch($rs);
        if ($shippingUserRow) {
            Message::addErrorMessage('Already Assigned to shipping company user');
            FatUtility::dieJsonError(Message::getHtml());
        }

        $frm = $this->getShippingCompanyUserForm();
        $post = $frm->getFormDataFromArray($post);

        if (!false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $db = FatApp::getDb();
        $db->startTransaction();

        $data = array('optsu_op_id' => $op_id, 'optsu_user_id' => $post['optsu_user_id']);
        if ($orderDetail['optsu_user_id'] == null) {
            $row = $db->insertFromArray(OrderProduct::DB_TBL_OP_TO_SHIPPING_USERS, $data);
        } else {
            $row = $db->updateFromArray(OrderProduct::DB_TBL_OP_TO_SHIPPING_USERS, $data, array('smt' => 'optsu_op_id = ?', 'vals' => array($op_id)));
        }

        if (!$row) {
            Message::addErrorMessage($db->getError());
            FatUtility::dieJsonError(Message::getHtml());
        }

        $orderObj = new Orders($orderDetail['order_id']);
        $addresses = $orderObj->getOrderAddresses($orderDetail['order_id']);
        $orderDetail['billingAddress'] = $addresses[Orders::BILLING_ADDRESS_TYPE];
        $orderDetail['shippingAddress'] = (!empty($addresses[Orders::SHIPPING_ADDRESS_TYPE])) ? $addresses[Orders::SHIPPING_ADDRESS_TYPE] : $addresses[Orders::BILLING_ADDRESS_TYPE];

        $shopSrch = new ShopSearch(1);
        $shopSrch->joinShopCountry();
        $shopSrch->joinShopState();
        $shopSrch->addCondition('shop_id', '=', 1);
        $shopSrch->addMultipleFields(array('ifnull(country_name,country_code) as country_name', 'ifnull(state_name,state_identifier) as state_name', 'shop_city', 'shop_address_line_1', 'shop_address_line_2'));
        $rs = $shopSrch->getResultSet();
        $orderDetail['shopDetail'] = FatApp::getDb()->fetch($rs);


        $srch = new SearchBase(OrderProduct::DB_TBL_OP_TO_SHIPPING_USERS, 'optosu');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addCondition('optosu.optsu_op_id', '=', $orderDetail['op_id']);
        $rs = $srch->getResultSet();
        $shippingUserRow = FatApp::getDb()->fetch($rs);
        if ($shippingUserRow && $orderDetail['plugin_code'] == "CashOnDelivery") {
            $comments = Labels::getLabel('Msg_Cash_will_collect_against_COD_order', $this->adminLangId) . ' ' . $orderDetail['op_invoice_number'];
            $amt = CommonHelper::orderProductAmount($orderDetail);
            $txnObj = new Transactions();
            $txnDataArr = array(
                'utxn_user_id' => $shippingUserRow['optsu_user_id'],
                'utxn_comments' => $comments,
                'utxn_status' => Transactions::STATUS_COMPLETED,
                'utxn_debit' => $amt,
                'utxn_op_id' => $orderDetail['op_id'],
            );
            if (!$txnObj->addTransaction($txnDataArr)) {
                $db->rollbackTransaction();
                Message::addErrorMessage($txnObj->getError());
                FatUtility::dieJsonError(Message::getHtml());
            }
        }

        $db->commitTransaction();

        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    public function changeOrderStatus()
    {
        $this->objPrivilege->canEditSellerOrders();
        $db = FatApp::getDb();
        $db->startTransaction();

        $post = FatApp::getPostedData();
        if (!isset($post['op_id'])) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $op_id = FatUtility::int($post['op_id']);
        if (1 > $op_id) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        $status = FatApp::getPostedData('op_status_id', FatUtility::VAR_INT, 0);
        $manualShipping = FatApp::getPostedData('manual_shipping', FatUtility::VAR_INT, 0);
        $trackingNumber = FatApp::getPostedData('tracking_number', FatUtility::VAR_STRING, '');
        if ($status ==  FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS") && empty($trackingNumber) && 1 > $manualShipping) {
            Message::addErrorMessage(Labels::getLabel('MSG_PLEASE_SELECT_SELF_SHIPPING', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $oCancelRequestSrch = new OrderCancelRequestSearch();
        $oCancelRequestSrch->doNotCalculateRecords();
        $oCancelRequestSrch->doNotLimitRecords();
        $oCancelRequestSrch->addCondition('ocrequest_op_id', '=', $op_id);
        $oCancelRequestSrch->addCondition('ocrequest_status', '!=', OrderCancelRequest::CANCELLATION_REQUEST_STATUS_DECLINED);
        $oCancelRequestRs = $oCancelRequestSrch->getResultSet();
        if (FatApp::getDb()->fetch($oCancelRequestRs)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Cancel_request_is_submitted_for_this_order', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $orderObj = new Orders();

        $srch = new OrderProductSearch($this->adminLangId, true, true);
        $srch->joinPaymentMethod();
        $srch->joinShippingUsers();
        //$srch->joinSellerProducts();
        $srch->joinShippingCharges();
        $srch->joinOrderUser();
        $srch->addCondition('op_id', '=', $op_id);
        $rs = $srch->getResultSet();
        $orderDetail = array();
        if ($rs) {
            $orderDetail = FatApp::getDb()->fetch($rs);
        }

        if (empty($orderDetail)) {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if ($orderDetail['plugin_code'] == 'CashOnDelivery') {
            $processingStatuses = $orderObj->getAdminAllowedUpdateOrderStatuses(true);
        } else if ($orderDetail['plugin_code'] == 'PayAtStore') {
            $processingStatuses = $orderObj->getAdminAllowedUpdateOrderStatuses(false, false, true);
        } else {
            $processingStatuses = $orderObj->getAdminAllowedUpdateOrderStatuses(false, $orderDetail['op_product_type']);
        }
        $frm = $this->getOrderCommentsForm($orderDetail, $processingStatuses);
        $post = $frm->getFormDataFromArray($post);

        if (!false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $restrictOrderStatusChange = array_merge(
            (array) FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"),
            (array) FatApp::getConfig("CONF_DEFAULT_DEIVERED_ORDER_STATUS"),
            (array) FatApp::getConfig("CONF_COMPLETED_ORDER_STATUS")
        );

        if (in_array(strtolower($orderDetail['plugin_code']), ['cashondelivery', 'payatstore']) && !CommonHelper::canAvailShippingChargesBySeller($orderDetail['op_selprod_user_id'], $orderDetail['opshipping_by_seller_user_id']) && !$orderDetail['optsu_user_id'] && in_array($post["op_status_id"], $restrictOrderStatusChange) && $orderDetail['op_product_type'] == Product::PRODUCT_TYPE_PHYSICAL) {
            Message::addErrorMessage(Labels::getLabel('MSG_Please_assign_shipping_user', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (in_array($orderDetail["op_status_id"], $processingStatuses) && in_array($post["op_status_id"], $processingStatuses)) {
            $trackingCourierCode = '';
            $opship_tracking_url = FatApp::getPostedData('opship_tracking_url', FatUtility::VAR_STRING, '');
            if ($post["op_status_id"] == OrderStatus::ORDER_SHIPPED) {
                if (0 < $manualShipping) {
                    $updateData = [
                        'opship_op_id' => $post['op_id'],
                        "opship_tracking_number" => $trackingNumber,
                        //    "opship_tracking_url" => $post['opship_tracking_url'],
                    ];
                    
                    if (!empty($opship_tracking_url)) {
                        $updateData['opship_tracking_url'] =  $opship_tracking_url;
                    }

                    $oshistory_courier = FatApp::getPostedData('oshistory_courier', FatUtility::VAR_STRING, '');
                    if (!empty($oshistory_courier)) {
                        $trackingCourierCode = $oshistory_courier;
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
                Message::addErrorMessage($this->str_invalid_request);
                FatUtility::dieJsonError(Message::getHtml());
            }
        } else {
            Message::addErrorMessage($this->str_invalid_request);
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (in_array(strtolower($orderDetail['plugin_code']), ['cashondelivery', 'payatstore']) && (OrderStatus::ORDER_DELIVERED == $post["op_status_id"] || OrderStatus::ORDER_COMPLETED == $post["op_status_id"]) && Orders::ORDER_PAYMENT_PAID != $orderDetail['order_payment_status']) {
            $orderProducts = new OrderProductSearch($this->adminLangId, true, true);
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
                        Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
                        FatUtility::dieJsonError(Message::getHtml());
                    } 
                }
                if (!empty($orderDetail['order_discount_coupon_code'])) {
                    $srch = DiscountCoupons::getSearchObject();
                    $srch->addFld('coupon_id');
                    $srch->doNotCalculateRecords();
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
        $this->set('msg', Labels::getLabel('LBL_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    // exists in MyAppController
    public function digitalDownloads($aFileId, $recordId = 0)
    {
        $aFileId = FatUtility::int($aFileId);
        $recordId = FatUtility::int($recordId);

        if (1 > $aFileId || 1 > $recordId) {
            Message::addErrorMessage(Labels::getLabel('LBL_Invalid_Request', $this->adminLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('SellerOrders'));
        }

        $file_row = AttachedFile::getAttributesById($aFileId);

        if ($file_row == false || ($file_row['afile_record_id'] != $recordId)) {
            Message::addErrorMessage(Labels::getLabel("MSG_INVALID_ACCESS", $this->adminLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('SellerOrders'));
        }

        if (!file_exists(CONF_UPLOADS_PATH . $file_row['afile_physical_path'])) {
            Message::addErrorMessage(Labels::getLabel('LBL_File_not_found', $this->adminLangId));
            FatApp::redirectUser(UrlHelper::generateUrl('SellerOrders'));
        }

        $fileName = isset($file_row['afile_physical_path']) ? $file_row['afile_physical_path'] : '';
        AttachedFile::downloadAttachment($fileName, $file_row['afile_name']);
    }

    public function checkIsShippingMode()
    {
        $json = array();
        $post = FatApp::getPostedData();
        if (isset($post["val"])) {
            if ($post["val"] == FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS")) {
                $json["shipping"] = 1;
            }
        }
        echo json_encode($json);
    }

    public function CancelOrder($op_id)
    {
        $this->objPrivilege->canEditSellerOrders();
        $op_id = FatUtility::int($op_id);

        if (false !== OrderCancelRequest::getCancelRequestById($op_id)) {
            Message::addErrorMessage(Labels::getLabel('MSG_User_have_already_sent_the_cancellation_request_for_this_order', $this->adminLangId));
            CommonHelper::redirectUserReferer();
        }

        $srch = new OrderProductSearch($this->adminLangId, true, true);
        $srch->joinOrderUser();
        $srch->joinPaymentMethod();
        $srch->addOrderProductCharges();
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(
            array(
                'order_id', 'order_pmethod_id', 'order_date_added', 'op_id', 'op_qty', 'op_unit_price',
                'op_invoice_number', 'IFNULL(orderstatus_name, orderstatus_identifier) as orderstatus_name', 'ou.user_name as buyer_user_name', 'ouc.credential_username as buyer_username', 'IFNULL(plugin_name, IFNULL(plugin_identifier, "Wallet")) as plugin_name', 'op_commission_charged', 'op_commission_percentage',   'ou.user_name as buyer_name', 'ouc.credential_username as buyer_username', 'ouc.credential_email as buyer_email', 'ou.user_phone_dcode as buyer_phone_dcode', 'ou.user_phone as buyer_phone', 'op.op_shop_owner_name', 'op.op_shop_owner_username', 'op_l.op_shop_name', 'op.op_shop_owner_email', 'op.op_shop_owner_phone_dcode', 'op.op_shop_owner_phone', 'op_selprod_title', 'op_product_name', 'op_brand_name', 'op_selprod_options', 'op_selprod_sku', 'op_product_model', 'op_shipping_duration_name', 'op_shipping_durations', 'op_status_id', 'op_other_charges', 'op_rounding_off'
            )
        );
        $srch->addCondition('op_id', '=', $op_id);
        $opRs = $srch->getResultSet();
        $opRow = FatApp::getDb()->fetch($opRs);
        if (!$opRow) {
            Message::addErrorMessage($this->str_invalid_request);
            CommonHelper::redirectUserReferer();
        }
        $orderObj = new Orders($opRow['order_id']);

        $charges = $orderObj->getOrderProductChargesArr($op_id);
        $opRow['charges'] = $charges;

        $addresses = $orderObj->getOrderAddresses($opRow['order_id']);
        $opRow['billingAddress'] = $addresses[Orders::BILLING_ADDRESS_TYPE];
        $opRow['shippingAddress'] = (!empty($addresses[Orders::SHIPPING_ADDRESS_TYPE])) ? $addresses[Orders::SHIPPING_ADDRESS_TYPE] : array();
        $opRow['comments'] = $orderObj->getOrderComments($this->adminLangId, array("op_id" => $op_id));

        $orderStatuses = Orders::getOrderProductStatusArr($this->adminLangId);

        $notEligible = false;
        $notAllowedStatues = $orderObj->getNotAllowedOrderCancellationStatuses();

        if (in_array($opRow["op_status_id"], $notAllowedStatues)) {
            $notEligible = true;
            Message::addErrorMessage(sprintf(Labels::getLabel('LBL_this_order_already', $this->adminLangId), $orderStatuses[$opRow["op_status_id"]]));
            //FatUtility::dieWithError( Message::getHtml() );
            CommonHelper::redirectUserReferer();
        }

        $frm = $this->getOrderCancelForm($this->adminLangId);
        $frm->fill(array('op_id' => $op_id));

        $this->set('notEligible', $notEligible);
        $this->set('frm', $frm);
        $this->set('order', $opRow);
        $this->_template->render();
    }

    public function cancelReason()
    {
        $frm = $this->getOrderCancelForm($this->adminLangId);
        $post = $frm->getFormDataFromArray(FatApp::getPostedData());

        if (!false === $post) {
            Message::addErrorMessage(current($frm->getValidationErrors()));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $op_id = FatUtility::int($post['op_id']);
        if (1 > $op_id) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_access', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (false !== OrderCancelRequest::getCancelRequestById($op_id)) {
            Message::addErrorMessage(Labels::getLabel('MSG_User_have_already_sent_the_cancellation_request_for_this_order', $this->adminLangId));
            CommonHelper::redirectUserReferer();
        }

        $orderObj = new Orders();
        $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses();

        $srch = new OrderProductSearch($this->adminLangId, true, true);
        $srch->joinOrderUser();
        $srch->addCondition('op_id', '=', $op_id);
        $rs = $srch->getResultSet();
        $orderDetail = array();
        if ($rs) {
            $orderDetail = FatApp::getDb()->fetch($rs);
        }

        if (empty($orderDetail)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_Access', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $notAllowedStatues = $orderObj->getNotAllowedOrderCancellationStatuses();
        $orderStatuses = Orders::getOrderProductStatusArr($this->adminLangId);

        if (in_array($orderDetail["op_status_id"], $notAllowedStatues)) {
            Message::addErrorMessage(sprintf(Labels::getLabel('LBL_this_order_already', $this->adminLangId), $orderStatuses[$orderDetail["op_status_id"]]));
            FatUtility::dieJsonError(Message::getHtml());
        }

        if (!$orderObj->addChildProductOrderHistory($op_id, $this->adminLangId, FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS"), $post["comments"], true)) {
            Message::addErrorMessage(Labels::getLabel('MSG_ERROR_INVALID_REQUEST', $this->adminLangId));
            FatUtility::dieJsonError(Message::getHtml());
        }

        $pluginKey = Plugin::getAttributesById($orderDetail['order_pmethod_id'], 'plugin_code');

        $paymentMethodObj = new PaymentMethods();
        if (true === $paymentMethodObj->canRefundToCard($pluginKey, $this->adminLangId)) {
            if (false == $paymentMethodObj->initiateRefund($orderDetail, PaymentMethods::REFUND_TYPE_CANCEL)) {
                FatUtility::dieJsonError($paymentMethodObj->getError());
            }

            $resp = $paymentMethodObj->getResponse();
            if (empty($resp)) {
                FatUtility::dieJsonError(Labels::getLabel('LBL_UNABLE_TO_PLACE_GATEWAY_REFUND_REQUEST', $this->adminLangId));
            }

            // Debit from wallet if plugin/payment method support's direct payment to card of customer.
            if (!empty($resp->id)) {
                $childOrderInfo = $orderObj->getOrderProductsByOpId($op_id, $this->adminLangId);
                $txnAmount = $paymentMethodObj->getTxnAmount();
                $comments = Labels::getLabel('LBL_TRANSFERED_TO_YOUR_CARD._INVOICE_#{invoice-no}', $this->adminLangId);
                $comments = CommonHelper::replaceStringData($comments, ['{invoice-no}' => $childOrderInfo['op_invoice_number']]);
                Transactions::debitWallet($childOrderInfo['order_user_id'], Transactions::TYPE_ORDER_REFUND, $txnAmount, $this->adminLangId, $comments, $op_id, $resp->id);
            }
        }

        $this->set('msg', Labels::getLabel('MSG_Updated_Successfully', $this->adminLangId));
        $this->_template->render(false, false, 'json-success.php');
    }

    private function getShippingCompanyUserForm($displayShippingUserForm = false)
    {
        $frm = new Form('frmShippingUser');
        $srch = User::getSearchObject(true);
        $srch->addOrder('u.user_id', 'DESC');
        $srch->addCondition('u.user_is_shipping_company', '=', applicationConstants::YES);
        $srch->addMultipleFields(array('user_id', 'credential_username'));
        $srch->addCondition('uc.credential_active', '=', applicationConstants::ACTIVE);
        $srch->addCondition('uc.credential_verified', '=', applicationConstants::YES);
        $rs = $srch->getResultSet();
        $records = FatApp::getDb()->fetchAllAssoc($rs);

        $frm->addSelectBox(Labels::getLabel('LBL_Shipping_User', $this->adminLangId), 'optsu_user_id', $records, '', [], Labels::getLabel('LBL_Select', $this->adminLangId))->requirements()->setRequired();
        $frm->addHiddenField('', 'op_id', 0);
        if ($displayShippingUserForm) {
            $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        }
        return $frm;
    }

    private function getOrderCommentsForm($orderData = array(), $processingOrderStatus = [])
    {
        $frm = new Form('frmOrderComments');
        $frm->addTextArea(Labels::getLabel('LBL_Your_Comments', $this->adminLangId), 'comments');

        $orderStatusArr = Orders::getOrderProductStatusArr($this->adminLangId, $processingOrderStatus, $orderData['op_status_id']);

        $fld = $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'op_status_id', $orderStatusArr, '', [], Labels::getLabel('LBL_Select', $this->adminLangId));
        $fld->requirements()->setRequired();

        $frm->addSelectBox(Labels::getLabel('LBL_Notify_Customer', $this->adminLangId), 'customer_notified', applicationConstants::getYesNoArr($this->adminLangId), '', [], Labels::getLabel('LBL_Select', $this->adminLangId))->requirements()->setRequired();

        if (array_key_exists('opship_tracking_number', $orderData) && empty($orderData['opship_tracking_number'])) {
            $manualFld = $frm->addCheckBox(Labels::getLabel('LBL_SELF_SHIPPING', $this->adminLangId), 'manual_shipping', 1, array(), false, 0);
            $manualShipUnReqObj = new FormFieldRequirement('manual_shipping', Labels::getLabel('LBL_SELF_SHIPPING', $this->adminLangId));
            $manualShipUnReqObj->setRequired(false);
            $manualShipReqObj = new FormFieldRequirement('manual_shipping', Labels::getLabel('LBL_SELF_SHIPPING', $this->adminLangId));
            $manualShipReqObj->setRequired(true);

            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"), 'eq', 'manual_shipping', $manualShipReqObj);
            $fld->requirements()->addOnChangerequirementUpdate(FatApp::getConfig("CONF_DEFAULT_SHIPPING_ORDER_STATUS"), 'ne', 'manual_shipping', $manualShipUnReqObj);

            $frm->addTextBox(Labels::getLabel('LBL_Tracking_Number', $this->adminLangId), 'tracking_number');

            $trackingUnReqObj = new FormFieldRequirement('tracking_number', Labels::getLabel('LBL_Tracking_Number', $this->adminLangId));
            $trackingUnReqObj->setRequired(false);

            $trackingReqObj = new FormFieldRequirement('tracking_number', Labels::getLabel('LBL_Tracking_Number', $this->adminLangId));
            $trackingReqObj->setRequired(true);

            $manualFld->requirements()->addOnChangerequirementUpdate(applicationConstants::YES, 'eq', 'tracking_number', $trackingReqObj);
            $manualFld->requirements()->addOnChangerequirementUpdate(applicationConstants::NO, 'eq', 'tracking_number', $trackingUnReqObj);

            $frm->addTextBox(Labels::getLabel('LBL_TRACKING_URL', $this->adminLangId), 'opship_tracking_url');

            $trackingUrlUnReqObj = new FormFieldRequirement('opship_tracking_url', Labels::getLabel('LBL_TRACKING_URL', $this->adminLangId));
            $trackingUrlUnReqObj->setRequired(false);

            $trackingurlReqObj = new FormFieldRequirement('opship_tracking_url', Labels::getLabel('LBL_TRACKING_URL', $this->adminLangId));
            $trackingurlReqObj->setRequired(true);

            $manualFld->requirements()->addOnChangerequirementUpdate(applicationConstants::YES, 'eq', 'opship_tracking_url', $trackingurlReqObj);
            $manualFld->requirements()->addOnChangerequirementUpdate(applicationConstants::NO, 'eq', 'opship_tracking_url', $trackingUrlUnReqObj);

            $shipmentTracking = new ShipmentTracking();
            if (false !== $shipmentTracking->init($this->adminLangId) && false !== $shipmentTracking->getTrackingCouriers()) {
                $trackCarriers = $shipmentTracking->getResponse();
                $frm->addSelectBox(Labels::getLabel('LBL_TRACKING_COURIER', $this->adminLangId), 'oshistory_courier', $trackCarriers, '', array(), Labels::getLabel('LBL_Select', $this->adminLangId));

                $trackCarrierFldUnReqObj = new FormFieldRequirement('oshistory_courier', Labels::getLabel('LBL_TRACKING_COURIER', $this->adminLangId));
                $trackCarrierFldUnReqObj->setRequired(false);

                $trackCarrierFldReqObj = new FormFieldRequirement('oshistory_courier', Labels::getLabel('LBL_TRACKING_COURIER', $this->adminLangId));
                $trackCarrierFldReqObj->setRequired(true);

                $manualFld->requirements()->addOnChangerequirementUpdate(applicationConstants::YES, 'eq', 'oshistory_courier', $trackCarrierFldReqObj);
                $manualFld->requirements()->addOnChangerequirementUpdate(applicationConstants::NO, 'eq', 'oshistory_courier', $trackCarrierFldUnReqObj);
            }
        }

        $frm->addHiddenField('', 'op_id', 0);
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getOrderCancelForm($langId)
    {
        $frm = new Form('frmOrderCancel');
        $frm->addHiddenField('', 'op_id');
        $fld = $frm->addTextArea(Labels::getLabel('LBL_Comments', $this->adminLangId), 'comments');
        $fld->requirements()->setRequired(true);
        $fld->requirements()->setCustomErrorMessage(Labels::getLabel('ERR_Reason_cancellation', $langId));
        $frm->addSubmitButton('', 'btn_submit', Labels::getLabel('LBL_Save_Changes', $this->adminLangId));
        return $frm;
    }

    private function getOrderSearchForm($langId)
    {
        $currency_id = FatApp::getConfig('CONF_CURRENCY', FatUtility::VAR_INT, 1);
        $currencyData = Currency::getAttributesById($currency_id, array('currency_code', 'currency_symbol_left', 'currency_symbol_right'));
        $currencySymbol = ($currencyData['currency_symbol_left'] != '') ? $currencyData['currency_symbol_left'] : $currencyData['currency_symbol_right'];

        $frm = new Form('frmVendorOrderSearch');
        $keyword = $frm->addTextBox(Labels::getLabel('LBL_Keyword', $this->adminLangId), 'keyword', '', array('id' => 'keyword', 'autocomplete' => 'off'));
        $frm->addTextBox(Labels::getLabel('LBL_Buyer', $this->adminLangId), 'buyer', '');
        $frm->addSelectBox(Labels::getLabel('LBL_Status', $this->adminLangId), 'op_status_id', Orders::getOrderProductStatusArr($langId), '', array(), Labels::getLabel('LBL_All', $this->adminLangId));
        $frm->addTextBox(Labels::getLabel('LBL_Seller_Shop', $this->adminLangId), 'shop_name');
        /* $frm->addTextBox(Labels::getLabel('LBL_Customer',$this->adminLangId),'customer_name'); */

        $frm->addDateField('', 'date_from', '', array('placeholder' => Labels::getLabel('LBL_Date_From', $this->adminLangId), 'readonly' => 'readonly'));
        $frm->addDateField('', 'date_to', '', array('placeholder' => Labels::getLabel('LBL_Date_To', $this->adminLangId), 'readonly' => 'readonly'));
        // $frm->addTextBox('', 'price_from', '', array('placeholder' => Labels::getLabel('LBL_Order_From', $this->adminLangId) . ' [' . $currencySymbol . ']'));
        // $frm->addTextBox('', 'price_to', '', array('placeholder' => Labels::getLabel('LBL_Order_to', $this->adminLangId) . ' [' . $currencySymbol . ']'));

        $frm->addHiddenField('', 'page');
        $frm->addHiddenField('', 'user_id');
        $frm->addHiddenField('', 'order_id');
        $fld_submit = $frm->addSubmitButton('&nbsp;', 'btn_submit', Labels::getLabel('LBL_Search', $this->adminLangId));
        $fld_cancel = $frm->addButton("", "btn_clear", Labels::getLabel('LBL_Clear_Search', $this->adminLangId));
        $fld_submit->attachField($fld_cancel);
        return $frm;
    }

    public function orderTrackingInfo($trackingNumber, $courier, $orderNumber)
    {
        if (empty($trackingNumber) || empty($courier)) {
            Message::addErrorMessage(Labels::getLabel('MSG_Invalid_request', $this->adminLangId));
            FatUtility::dieWithError(Message::getHtml());
        }

        $shipmentTracking = new ShipmentTracking();
        if (false === $shipmentTracking->init($this->adminLangId)) {
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
}
