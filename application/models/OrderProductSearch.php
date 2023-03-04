<?php

class OrderProductSearch extends SearchBase
{
    private $langId;
    private $isOrdersTableJoined;
    private $isOrderUserTableJoined;
    private $isShopJoined;
    private $commonLangId;

    public function __construct($langId = 0, $joinOrders = false, $joinOrderProductStatus = false, $deletedOrders = false)
    {
        parent::__construct(Orders::DB_TBL_ORDER_PRODUCTS, 'op');
        $this->langId = FatUtility::int($langId);
        $this->isOrdersTableJoined = false;
        $this->isOrderUserTableJoined = false;
        $this->isOrderProductStatusJoined = false;
        $this->isShippingChargesTblJoined = false;
        $this->isShopJoined = false;
        $this->commonLangId = CommonHelper::getLangId();
        if ($this->langId > 0) {
            $this->joinTable(
                Orders::DB_TBL_ORDER_PRODUCTS_LANG,
                'LEFT OUTER JOIN',
                'oplang_op_id = op.op_id AND oplang_lang_id = ' . $this->langId,
                'op_l'
            );
        }

        if ($joinOrders) {
            $this->joinOrders();
        }

        if ($joinOrderProductStatus) {
            $this->joinOrderProductStatus($this->langId);
        }

        if ($joinOrders && false == $deletedOrders) {
            $this->addCondition('order_deleted', '=', applicationConstants::NO);
        }

        $this->joinSettings();
    }

    public function joinSellerUser()
    {
        $this->joinTable(User::DB_TBL, 'LEFT OUTER JOIN', 'seller.user_id = op.op_selprod_user_id', 'seller');
        $this->joinTable(User::DB_TBL_CRED, 'LEFT OUTER JOIN', 'seller.user_id = credential_user_id', 'seller_cred');
    }

    public function joinSettings()
    {
        $this->joinTable(OrderProduct::DB_TBL_SETTINGS, 'LEFT OUTER JOIN', 'op.op_id = opst.opsetting_op_id', 'opst');
    }

    public function joinOrders()
    {
        if ($this->isOrdersTableJoined) {
            trigger_error(Labels::getLabel('MSG_Orders_Table_is_already_joined', $this->commonLangId), E_USER_ERROR);
        }
        $this->isOrdersTableJoined = true;
        $this->joinTable(Orders::DB_TBL, 'INNER JOIN', 'o.order_id = op.op_order_id', 'o');
    }

    public function joinOrderProductStatus($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        if ($this->isOrderProductStatusJoined) {
            trigger_error(Labels::getLabel('MSG_OrderProduct_Status_is_already_joined', $this->commonLangId), E_USER_ERROR);
        }
        $this->isOrderProductStatusJoined = true;
        $this->joinTable(Orders::DB_TBL_ORDERS_STATUS, 'LEFT OUTER JOIN', 'os.orderstatus_id = op.op_status_id', 'os');
        if ($langId) {
            $this->joinTable(Orders::DB_TBL_ORDERS_STATUS_LANG, 'LEFT OUTER JOIN', 'os_l.orderstatuslang_orderstatus_id = os.orderstatus_id AND os_l.orderstatuslang_lang_id = ' . $langId, 'os_l');
        }
    }

    public function joinOrderUser()
    {
        if (!$this->isOrdersTableJoined) {
            trigger_error(Labels::getLabel('MSG_joinOrderUser_can_be_joined_only,_if_joinOrders_is_Joined,_So,_Please_Use_joinOrders()_first,_then_try_to_join_joinOrderUser', $this->commonLangId), E_USER_ERROR);
        }
        $this->joinTable(User::DB_TBL, 'INNER JOIN', 'ou.user_id = o.order_user_id', 'ou');
        $this->joinTable(User::DB_TBL_CRED, 'INNER JOIN', 'ou.user_id = ouc.credential_user_id', 'ouc');
        $this->isOrderUserTableJoined = true;
    }

    public function joinSellerProducts($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(SellerProduct::DB_TBL, 'LEFT OUTER JOIN', 'sp.selprod_id = op.op_selprod_id and op.op_is_batch = 0', 'sp');
        if ($langId) {
            $this->joinTable(SellerProduct::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sp_l.selprodlang_selprod_id = sp.selprod_id AND sp_l.selprodlang_lang_id = ' . $langId, 'sp_l');
        }
    }

    public function joinSellerProductGroup($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(ProductGroup::DB_TBL, 'LEFT OUTER JOIN', 'pg.prodgroup_id = op.op_selprod_id and op.op_is_batch = 1', 'pg');
        if ($langId) {
            $this->joinTable(ProductGroup::DB_TBL_LANG, 'LEFT OUTER JOIN', 'pg_l.prodgrouplang_prodgroup_id = pg.prodgroup_id AND pg_l.prodgrouplang_lang_id = ' . $langId, 'pg_l');
        }
    }

    public function joinShop($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        $this->joinTable(Shop::DB_TBL, 'LEFT OUTER JOIN', 'sh.shop_user_id = op.op_selprod_user_id', 'sh');
        if ($langId) {
            $this->joinTable(Shop::DB_TBL_LANG, 'LEFT OUTER JOIN', 'sh_l.shoplang_shop_id = sh.shop_id AND sh_l.shoplang_lang_id = ' . $langId, 'sh_l');
        }
        $this->isShopJoined = true;
    }

    public function joinShopCountry($langId = 0, $isActive = true)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        if (!$this->isShopJoined) {
            trigger_error("Please use joinShop first, then try to join joinShopCountry");
        }
        $this->joinTable(Countries::DB_TBL, 'LEFT OUTER JOIN', 'sh.shop_country_id = shop_country.country_id', 'shop_country');

        if ($langId) {
            $this->joinTable(Countries::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop_country.country_id = shop_country_l.countrylang_country_id AND shop_country_l.countrylang_lang_id = ' . $langId, 'shop_country_l');
        }
        if ($isActive) {
            $this->addCondition('shop_country.country_active', '=', applicationConstants::ACTIVE);
        }
    }

    public function joinShopState($langId = 0, $isActive = true)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }
        if (!$this->isShopJoined) {
            trigger_error("Please use joinShop first, then try to join joinShopState");
        }
        $this->joinTable(States::DB_TBL, 'LEFT OUTER JOIN', 'sh.shop_state_id = shop_state.state_id', 'shop_state');

        if ($langId) {
            $this->joinTable(States::DB_TBL_LANG, 'LEFT OUTER JOIN', 'shop_state.state_id = shop_state_l.statelang_state_id AND shop_state_l.statelang_lang_id = ' . $langId, 'shop_state_l');
        }
        if ($isActive) {
            $this->addCondition('shop_state.state_active', '=', applicationConstants::ACTIVE);
        }
    }

    public function joinPaymentMethod($langId = 0)
    {
        $langId = FatUtility::int($langId);
        if ($this->langId) {
            $langId = $this->langId;
        }

        if (!$this->isOrdersTableJoined) {
            trigger_error(Labels::getLabel('MSG_Please_use_joinOrders()_first,_then_try_to_join_joinPaymentMethod()', $this->commonLangId), E_USER_ERROR);
        }
        $this->joinTable(Plugin::DB_TBL, 'LEFT OUTER JOIN', 'o.order_pmethod_id = pm.plugin_id', 'pm');
        if ($langId) {
            $this->joinTable(Plugin::DB_TBL_LANG, 'LEFT OUTER JOIN', 'pm.plugin_id = pm_l.pluginlang_plugin_id AND pm_l.pluginlang_lang_id = ' . $langId, 'pm_l');
        }
    }

    public function joinOrderProductCharges($type, $alias = 'opc_temp')
    {
        $this->joinTable(OrderProduct::DB_TBL_CHARGES, 'LEFT OUTER JOIN', $alias . '.opcharge_op_id = op.op_id and ' . $alias . '.opcharge_type = ' . $type, $alias);
    }

    public function joinShippingUsers()
    {
        $this->joinTable(OrderProduct::DB_TBL_OP_TO_SHIPPING_USERS, 'LEFT OUTER JOIN', 'optosu.optsu_op_id = op.op_id', 'optosu');
    }

    public function joinShippingCharges()
    {
        $this->isShippingChargesTblJoined = true;
        $this->joinTable(Orders::DB_TBL_ORDER_PRODUCTS_SHIPPING, 'LEFT OUTER JOIN', 'ops.opshipping_op_id = op.op_id', 'ops');
    }

    public function joinAddress(int $langId = 0)
    {
        if (1 > $langId) {
            $langId = $this->commonLangId;
        }

        if (false === $this->isShippingChargesTblJoined) {
            trigger_error(Labels::getLabel('MSG_PLEASE_USE_JOINSHIPPINGCHARGES()_FIRST,_THEN_TRY_TO_JOIN_JOINADDRESS()', $langId), E_USER_ERROR);
        }

        $this->joinTable(Address::DB_TBL, 'LEFT OUTER JOIN', 'addr.addr_id = ops.opshipping_pickup_addr_id', 'addr');
        $this->joinTable(States::DB_TBL, 'LEFT JOIN', 'addr.addr_state_id=ts.state_id', 'ts');
        $this->joinTable(States::DB_TBL_LANG, 'LEFT OUTER JOIN', 'st_l.statelang_state_id = ts.state_id and st_l.statelang_lang_id = ' . $langId, 'st_l');
        $this->joinTable(Countries::DB_TBL, 'LEFT JOIN', 'addr.addr_country_id=tc.country_id', 'tc');
        $this->joinTable(Countries::DB_TBL_LANG, 'LEFT OUTER JOIN', 'c_l.countrylang_country_id = tc.country_id and c_l.countrylang_lang_id = ' . $langId, 'c_l');
    }

    public function joinOrderCancellationRequest()
    {
        $this->joinTable(OrderCancelRequest::DB_TBL, 'LEFT OUTER JOIN', 'ocr.ocrequest_op_id = op.op_id', 'ocr');
    }

    public function joinDigitalDownloads($type = AttachedFile::FILETYPE_ORDER_PRODUCT_DIGITAL_DOWNLOAD, $join = 'INNER JOIN')
    {
        $this->joinTable(AttachedFile::DB_TBL, $join, 'opa.afile_record_id = op.op_id and afile_type = ' . $type, 'opa');
    }

    public function joinDigitalDownloadLinks($join = 'INNER JOIN')
    {
        $this->joinTable(OrderProductDigitalLinks::DB_TBL, $join, 'opd.opddl_op_id = op.op_id', 'opd');
    }

    public function addDigitalDownloadCondition()
    {
        $this->addCondition('op_product_type', '=', Product::PRODUCT_TYPE_DIGITAL);
        $arr = Orders::getBuyerAllowedDigitalDownloadStatues();
        if (!empty($arr)) {
            $this->addCondition('op_status_id', 'in', $arr);
        } else {
            $this->addCondition('op.op_status_id', '=', 0);
        }
    }

    public function addOrderProductCharges()
    {
        $srch = new SearchBase(OrderProduct::DB_TBL_CHARGES, 'opc');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addMultipleFields(array('opcharge_op_id', 'sum(opcharge_amount) as op_other_charges'));
        $srch->addGroupBy('opc.opcharge_op_id');
        $srch->addCondition('opc.opcharge_order_type', '=', ORDERS::ORDER_PRODUCT);
        $qryOtherCharges = $srch->getQuery();
        $this->joinTable('(' . $qryOtherCharges . ')', 'LEFT OUTER JOIN', 'op.op_id = opcc.opcharge_op_id', 'opcc');
    }

    public function addCountsOfOrderedProducts()
    {
        $srch = new SearchBase(Orders::DB_TBL_ORDER_PRODUCTS, 'temp_op');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy('temp_op.op_order_id');
        $srch->addMultipleFields(array('temp_op.op_order_id', "count(temp_op.op_order_id) as totCombinedOrders"));
        $qryCombinedOrders = $srch->getQuery();
        $this->joinTable('(' . $qryCombinedOrders . ')', 'LEFT OUTER JOIN', 'op.op_order_id = co.op_order_id', 'co');
    }

    public function addBuyerOrdersCounts($startDate = false, $endDate = false, $alias = 'buyerOrder')
    {
        if (!$this->isOrdersTableJoined) {
            trigger_error(Labels::getLabel('MSG_addBuyerOrdersCounts_can_be_joined_only,_if_joinOrders_is_Joined,_So,_Please_Use_joinOrders()_first,_then_try_to_join_joinOrderUser', $this->commonLangId), E_USER_ERROR);
        }
        $srch = new SearchBase(Orders::DB_TBL, $alias);
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy($alias . '.order_user_id');
        if ($startDate) {
            $srch->addCondition($alias . '.order_date_added', '>=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $srch->addCondition($alias . '.order_date_added', '<=', $endDate . ' 23:59:59');
        }
        $srch->addMultipleFields(array($alias . '.order_user_id as ' . $alias . '_order_user_id', "count(" . $alias . ".order_id) as " . $alias . 'Count'));
        $qrytotalOrders = $srch->getQuery();
        $this->joinTable('(' . $qrytotalOrders . ')', 'LEFT OUTER JOIN', 'o.order_user_id = ' . $alias . '.' . $alias . '_order_user_id', $alias);
    }

    public function addSellerOrdersCounts($startDate = false, $endDate = false, $alias = 'sellerOrder')
    {
        $srch = new SearchBase(Orders::DB_TBL_ORDER_PRODUCTS, $alias);
        $srch->joinTable(Orders::DB_TBL, 'LEFT OUTER JOIN', $alias . '.op_order_id = ' . $alias . 'temp.order_id', $alias . 'temp');
        $srch->doNotCalculateRecords();
        $srch->doNotLimitRecords();
        $srch->addGroupBy($alias . '.op_selprod_user_id');
        if ($startDate) {
            $srch->addCondition($alias . 'temp.order_date_added', '>=', $startDate . ' 00:00:00');
        }
        if ($endDate) {
            $srch->addCondition($alias . 'temp.order_date_added', '<=', $endDate . ' 23:59:59');
        }
        $srch->addMultipleFields(array($alias . '.op_selprod_user_id as ' . $alias . '_op_selprod_user_id', "count(" . $alias . ".op_id) as " . $alias . 'Count'));
        $qrytotalOrders = $srch->getQuery();
        $this->joinTable('(' . $qrytotalOrders . ')', 'LEFT OUTER JOIN', 'op.op_selprod_user_id = ' . $alias . '.' . $alias . '_op_selprod_user_id', $alias);
    }

    public function addSellerCompletedOrdersStats($startDate = false, $endDate = false, $alias = 'CompleteOrder')
    {
        $this->sellerOrdersStats($startDate, $endDate, $alias, Stats::COMPLETED_SALES);
    }

    public function addSellerInprocessOrdersStats($startDate = false, $endDate = false, $alias = 'inprocessOrder')
    {
        $this->sellerOrdersStats($startDate, $endDate, $alias, Stats::INPROCESS_SALES);
    }

    public function addSellerRefundedOrdersStats($startDate = false, $endDate = false, $alias = 'refundedOrder')
    {
        $this->sellerOrdersStats($startDate, $endDate, $alias, Stats::REFUNDED_SALES);
    }

    public function addSellerCancelledOrdersStats($startDate = false, $endDate = false, $alias = 'cancelledOrder')
    {
        $this->sellerOrdersStats($startDate, $endDate, $alias, Stats::CANCELLED_SALES);
    }

    public function sellerOrdersStats($startDate, $endDate, $alias, $type)
    {
        $srch = Stats::getSalesStatsObj($startDate, $endDate, $alias, $type);

        $subSrch = Stats::getSalesStatsObj($startDate, $endDate, $alias . '_t', $type);

        $subSrch->joinTable(OrderProduct::DB_TBL_CHARGES, 'LEFT OUTER JOIN', $alias . '_tc.opcharge_op_id = ' . $alias . '_t.op_id', $alias . '_tc');
        $subSrch->joinTable(OrderProduct::DB_TBL_SETTINGS, 'LEFT OUTER JOIN', $alias . '_ts.opsetting_op_id = ' . $alias . '_t.op_id', $alias . '_ts');
        $subSrch->joinTable(Orders::DB_TBL_ORDER_PRODUCTS_SHIPPING, 'LEFT OUTER JOIN', $alias . '_tops.opshipping_op_id = ' . $alias . '_t.op_id', $alias . '_tops');
        /* $cnd = $subSrch->addCondition($alias.'_tc.opcharge_type','=',OrderProduct::CHARGE_TYPE_SHIPPING);
        $cnd->attachCondition($alias.'_tc.opcharge_type','=',OrderProduct::CHARGE_TYPE_TAX,'OR'); */
        $subSrch->addFld($alias . '_tc.opcharge_op_id,SUM(' . $alias . '_tc.opcharge_amount) as opcharge_amount');
        $subSrch->addGroupBy($alias . '_tc.opcharge_op_id');

        $cnd = $subSrch->addCondition($alias . '_tc.opcharge_type', '=', OrderProduct::CHARGE_TYPE_SHIPPING);
        $cnd->attachCondition('opshipping_by_seller_user_id', '>', 0, 'AND');
        $cnd = $subSrch->addCondition($alias . '_tc.opcharge_type', '=', OrderProduct::CHARGE_TYPE_TAX, 'OR');
        $cnd->attachCondition('op_tax_collected_by_seller', '>', 0, 'AND');
        $subSrch->addCondition($alias . '_tc.opcharge_type', 'IN', array(OrderProduct::CHARGE_TYPE_VOLUME_DISCOUNT, /*OrderProduct::CHARGE_TYPE_DISCOUNT, OrderProduct::CHARGE_TYPE_REWARD_POINT_DISCOUNT*/), 'OR');
        
        $srch->joinTable('(' . $subSrch->getQuery() . ')', 'LEFT OUTER JOIN', $alias . 'c.opcharge_op_id = ' . $alias . '.op_id', $alias . 'c');

        switch ($type) {
            case Stats::REFUNDED_SALES:
                $srch->addMultipleFields(array($alias . '.op_selprod_user_id as ' . $alias . '_op_selprod_user_id', "count(" . $alias . ".op_id) as " . $alias . 'Count', 'SUM(' . $alias . '.op_refund_amount) AS ' . $alias . 'Amount'));
                break;
            case Stats::CANCELLED_SALES:
                $srch->addMultipleFields(array($alias . '.op_selprod_user_id as ' . $alias . '_op_selprod_user_id', "count(" . $alias . ".op_id) as " . $alias . 'Count', 'SUM((' . $alias . '.op_unit_price * ' . $alias . '.op_qty) + IFNULL(' . $alias . 'c.opcharge_amount,0)) AS ' . $alias . 'Amount'));
                break;
            default:
                $srch->addMultipleFields(array($alias . '.op_selprod_user_id as ' . $alias . '_op_selprod_user_id', "count(" . $alias . ".op_id) as " . $alias . 'Count', 'SUM(((' . $alias . '.op_unit_price * ' . $alias . '.op_qty) + IFNULL(' . $alias . 'c.opcharge_amount,0)) - ' . $alias . '.op_refund_amount) AS ' . $alias . 'Sales'));
                break;
        }

        $srch->addGroupBy($alias . '.op_selprod_user_id');
       
        $qrytotalOrders = $srch->getQuery();
        $this->joinTable('(' . $qrytotalOrders . ')', 'LEFT OUTER JOIN', 'op.op_selprod_user_id = ' . $alias . '.' . $alias . '_op_selprod_user_id', $alias);
    }

    public function addKeywordSearch($keyword, $cnd = false)
    {
        if (!$cnd) {
            $cnd = $this->addCondition('op.op_order_id', 'like', '%' . $keyword . '%');
        }
        $cnd->attachCondition('op.op_invoice_number', 'like', '%' . $keyword . '%', 'OR');
        if ($this->isOrderUserTableJoined) {
            $cnd->attachCondition('ou.user_name', 'like', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('ouc.credential_email', 'like', '%' . $keyword . '%', 'OR');
        }
        if ($this->langId) {
            $cnd->attachCondition('op_selprod_title', 'like', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op_product_name', 'like', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op_brand_name', 'like', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op_shop_name', 'like', '%' . $keyword . '%', 'OR');
            $cnd->attachCondition('op_selprod_options', 'like', '%' . $keyword . '%', 'OR');
        }
    }

    public function addDateFromCondition($dateFrom)
    {
        $dateFrom = FatDate::convertDatetimeToTimestamp($dateFrom);
        $dateFrom = date('Y-m-d', strtotime($dateFrom));

        if (!$this->isOrdersTableJoined) {
            trigger_error(Labels::getLabel('MSG_Order_Date_Condition_cannot_be_applied,_as_Orders_Table_is_not_Joined,_So,_Please_Use_joinOrders()_first,_then_try_to_add_Order_date_from_condition', $this->commonLangId), E_USER_ERROR);
        }
        if ($dateFrom != '') {
            $this->addCondition('o.order_date_added', '>=', $dateFrom . ' 00:00:00');
        }
    }

    public function addDateToCondition($dateTo)
    {
        $dateTo = FatDate::convertDatetimeToTimestamp($dateTo);
        $dateTo = date('Y-m-d', strtotime($dateTo));

        if (!$this->isOrdersTableJoined) {
            trigger_error(Labels::getLabel('MSG_Order_Date_Condition_cannot_be_applied,_as_Orders_Table_is_not_Joined,_So,_Please_Use_joinOrders()_first,_then_try_to_add_Order_date_to_condition', $this->commonLangId), E_USER_ERROR);
        }
        if ($dateTo != '') {
            $this->addCondition('o.order_date_added', '<=', $dateTo . ' 23:59:59');
        }
    }

    public function addMinPriceCondition($priceFrom)
    {
        if (!$this->isOrdersTableJoined) {
            trigger_error(Labels::getLabel('MSG_Order_Price_Condition_cannot_be_applied,_as_Orders_Table_is_not_Joined,_So,_Please_Use_joinOrders()_first,_then_try_to_add_Order_Price_condition', $this->commonLangId), E_USER_ERROR);
        }
        $this->addCondition('o.order_net_amount', '>=', $priceFrom);
    }

    public function addMaxPriceCondition($priceTo)
    {
        if (!$this->isOrdersTableJoined) {
            trigger_error(Labels::getLabel('MSG_Order_Price_Condition_cannot_be_applied,_as_Orders_Table_is_not_Joined,_So,_Please_Use_joinOrders()_first,_then_try_to_add_Order_Price_condition', $this->commonLangId), E_USER_ERROR);
        }
        $this->addCondition('o.order_net_amount', '<=', $priceTo);
    }

    public function addStatusCondition($op_status, $orderPaymentCancel = false)
    {
        if (is_array($op_status)) {
            if (!empty($op_status)) {
                $cnd = $this->addCondition('op.op_status_id', 'IN', $op_status);
            } else {
                $cnd = $this->addCondition('op.op_status_id', '=', 0);
            }
        } else {
            $op_status_id = FatUtility::int($op_status);
            $cnd = $this->addCondition('op.op_status_id', '=', $op_status_id);
        }

        if (true === $orderPaymentCancel) {
            $cnd->attachCondition('order_payment_status', '=', Orders::ORDER_PAYMENT_CANCELLED, 'OR');
        }
    }

    public function joinShopSpecifics()
    {
        $this->joinTable(ShopSpecifics::DB_TBL, 'LEFT OUTER JOIN', 'ss.ss_shop_id = op.op_shop_id', 'ss');
    }

    public function joinSellerProductSpecifics()
    {
        $this->joinTable(SellerProductSpecifics::DB_TBL, 'LEFT OUTER JOIN', 'sps.sps_selprod_id = op.op_selprod_id', 'sps');
    }

    public function joinOrderProductSpecifics()
    {
        $this->joinTable(OrderProductSpecifics::DB_TBL, 'LEFT JOIN', 'opspec.ops_op_id = op.op_id', 'opspec');
    }

    public function joinOrderProductShipment()
    {
        $this->joinTable(OrderProductShipment::DB_TBL, 'LEFT JOIN', OrderProductShipment::DB_TBL_PREFIX . 'op_id = op.op_id', 'opship');
    }
}
