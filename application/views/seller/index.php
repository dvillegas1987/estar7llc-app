<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Dashboard', $siteLangId); ?></h2>
            </div>
            <div class="col-auto">
                <div class="btn-group">
                    <?php if (!$isShopActive) { ?>
                        <a href="<?php echo  UrlHelper::generateUrl('Seller', 'shop'); ?>" class="btn btn-outline-brand btn-sm">
                            <?php echo Labels::getLabel('LBL_Create_Shop', $siteLangId); ?>
                        </a>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewProducts(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <?php if (User::canAddCustomProduct() && $userPrivilege->canEditProducts(UserAuthentication::getLoggedUserId(), true)) { ?>
                            <a href="<?php echo UrlHelper::generateUrl('seller', 'customProductForm'); ?>" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Add_new_catalog', $siteLangId); ?></a>
                            <a href="<?php echo UrlHelper::generateUrl('seller', 'catalog'); ?>" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_My_products', $siteLangId); ?></a>
                            <?php } ?>                       
                        <a href="<?php echo UrlHelper::generateUrl('seller', 'products'); ?>" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Shop_Inventory', $siteLangId); ?></a>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="content-body">
            <?php if (
                $userPrivilege->canViewSales(UserAuthentication::getLoggedUserId(), true) ||
                $userPrivilege->canViewCancellationRequests(UserAuthentication::getLoggedUserId(), true) ||
                $userPrivilege->canViewReturnRequests(UserAuthentication::getLoggedUserId(), true)
            ) { ?>
                <div class="js-widget-scroll widget-scroll">
                    <?php if ($userPrivilege->canViewSales(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <div class="widget widget-stats">
                            <a href="<?php echo UrlHelper::generateUrl('Seller', 'sales'); ?>">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title"><?php echo Labels::getLabel('LBL_My_Sales', $siteLangId); ?></h5>
                                        <i class="icn"><svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#my-sales" href="
                                    <?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#my-sales"></use>
                                            </svg>
                                        </i>
                                    </div>
                                    <div class="card-body ">
                                        <div class="stats">
                                            <div class="stats-number">
                                                <ul>
                                                    <li>
                                                        <span class="total"><?php echo Labels::getLabel('LBL_Completed_Sales', $siteLangId); ?></span>
                                                        <span class="total-numbers">
                                                            <?php
                                                            $totalSoldSales = isset($ordersStats['totalSoldSales']) ? $ordersStats['totalSoldSales'] : 0;
                                                            echo CommonHelper::displayMoneyFormat($totalSoldSales);
                                                            ?>
                                                        </span>
                                                    </li>
                                                    <li>
                                                        <span class="total"><?php echo Labels::getLabel('LBL_Inprocess_Sales', $siteLangId); ?></span>
                                                        <span class="total-numbers">
                                                            <?php
                                                            $totalInprocessSales = isset($ordersStats['totalInprocessSales']) ? $ordersStats['totalInprocessSales'] : 0;
                                                            echo CommonHelper::displayMoneyFormat($totalInprocessSales);
                                                            ?>
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php } ?>
                    <?php if ($userParentId == UserAuthentication::getLoggedUserId()) { ?>
                        <div class="widget widget-stats">
                            <a href="<?php echo UrlHelper::generateUrl('Account', 'credits'); ?>">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title"><?php echo Labels::getLabel('LBL_Credits', $siteLangId); ?></h5>
                                        <i class="icn"><svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#credits" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#Credits"></use>
                                            </svg>
                                        </i>
                                    </div>
                                    <div class="card-body ">
                                        <div class="stats">
                                            <div class="stats-number">
                                                <ul>
                                                    <li>
                                                        <span class="total"><?php echo Labels::getLabel('LBL_Total', $siteLangId); ?></span>
                                                        <span class="total-numbers"><?php echo CommonHelper::displayMoneyFormat($userBalance); ?></span>
                                                    </li>
                                                    <li>
                                                        <span class="total"><?php echo Labels::getLabel('LBL_Credits_earned_today', $siteLangId); ?></span>
                                                        <span class="total-numbers"><?php echo CommonHelper::displayMoneyFormat($txnsSummary['total_earned']); ?></span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewSales(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <div class="widget widget-stats">
                            <a onClick="redirectfunc('<?php echo UrlHelper::generateUrl('Seller', 'Sales'); ?>', <?php echo FatApp::getConfig("CONF_DEFAULT_COMPLETED_ORDER_STATUS", null, ''); ?>)" href="javaScript:void(0)">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title"><?php echo Labels::getLabel('LBL_Order', $siteLangId); ?></h5>
                                        <i class="icn"><svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#order" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#order"></use>
                                            </svg>
                                        </i>
                                    </div>
                                    <div class="card-body ">
                                        <div class="stats">
                                            <div class="stats-number">
                                                <ul>
                                                    <li>
                                                        <span class="total"><?php echo Labels::getLabel('LBL_Completed_Orders', $siteLangId); ?></span>
                                                        <span class="total-numbers">
                                                            <?php
                                                            $totalSoldCount = isset($ordersStats['totalSoldCount']) ? $ordersStats['totalSoldCount'] : 0;
                                                            echo FatUtility::int($totalSoldCount);
                                                            ?>
                                                        </span>
                                                    </li>
                                                    <li>
                                                        <span class="total"><?php echo Labels::getLabel('LBL_Pending_Orders', $siteLangId); ?></span>
                                                        <span class="total-numbers">
                                                            <?php $pendingOrders = $ordersCount - $totalSoldCount;
                                                            echo $pendingOrders; ?>
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewSubscription(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <?php if (FatApp::getConfig('CONF_ENABLE_SELLER_SUBSCRIPTION_MODULE')) { ?>
                            <div class="widget widget-stats">
                                <a href="<?php echo UrlHelper::generateUrl('Seller', 'subscriptions'); ?>">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title"><?php echo Labels::getLabel('LBL_Active_Subscription', $siteLangId); ?></h5>
                                            <i class="icn"><svg class="svg">
                                                    <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#messages" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#messages"></use>
                                                </svg>
                                            </i>
                                        </div>
                                        <div class="card-body ">
                                            <div class="stats">
                                                <div class="stats-number">
                                                    <ul>
                                                        <?php if ($pendingDaysForCurrentPlan >= 0) { ?>
                                                            <li>
                                                                <span class="total"><?php echo Labels::getLabel('LBL_Remaining', $siteLangId); ?></span>
                                                                <span class="total-numbers"><?php echo $pendingDaysForCurrentPlan; ?> <?php echo Labels::getLabel('LBL_Days', $siteLangId); ?></span>
                                                            </li>
                                                            <li>
                                                                <span class="total"><?php echo Labels::getLabel('LBL_Allowed_Products', $siteLangId); ?></span>
                                                                <span class="total-numbers"><?php echo ($remainingAllowedProducts > 0) ? $remainingAllowedProducts : 0; ?></span>
                                                            </li>
                                                        <?php } else { ?>
                                                            <li>
                                                                <span class="total"><?php echo Labels::getLabel('LBL_Subscription_Name', $siteLangId); ?></span>
                                                                <span class="total-numbers"><?php echo $subscriptionName; ?></span>
                                                            </li>
                                                            <li>
                                                                <span class="total"><?php echo Labels::getLabel('LBL_Expires_On', $siteLangId); ?></span>
                                                                <span class="total-numbers"><?php echo (isset($subscriptionTillDate)) ? $subscriptionTillDate : ''; ?></span>
                                                            </li>
                                                        <?php } ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        <?php } ?>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewReturnRequests(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <div class="widget widget-stats">
                            <a href="<?php echo UrlHelper::generateUrl('Seller', 'orderReturnRequests'); ?>">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title"><?php echo Labels::getLabel('LBL_Refund', $siteLangId); ?></h5>
                                        <i class="icn"><svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#refund" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#refund"></use>
                                            </svg>
                                        </i>
                                    </div>
                                    <div class="card-body ">
                                        <div class="stats">
                                            <div class="stats-number">
                                                <ul>
                                                    <li>
                                                        <span class="total"><?php echo Labels::getLabel('LBL_Refunded_Orders', $siteLangId); ?></span>
                                                        <span class="total-numbers"><?php echo isset($ordersStats['refundedOrderCount']) ? FatUtility::int($ordersStats['refundedOrderCount']) : 0; ?></span>
                                                    </li>
                                                    <li>
                                                        <span class="total"><?php echo Labels::getLabel('LBL_Refunded_Amount', $siteLangId); ?></span>
                                                        <span class="total-numbers">
                                                            <?php
                                                            $refundedOrderAmount = isset($ordersStats['refundedOrderAmount']) ? $ordersStats['refundedOrderAmount'] : 0;
                                                            echo CommonHelper::displayMoneyFormat($refundedOrderAmount);
                                                            ?>
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php } ?>
                    <?php if ($userPrivilege->canViewCancellationRequests(UserAuthentication::getLoggedUserId(), true)) { ?>
                        <div class="widget widget-stats">
                            <a onClick="redirectfunc('<?php echo UrlHelper::generateUrl('Seller', 'Sales'); ?>', <?php echo FatApp::getConfig("CONF_DEFAULT_CANCEL_ORDER_STATUS", null, ''); ?>)" href="javaScript:void(0)">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title"><?php echo Labels::getLabel('LBL_Cancellation', $siteLangId); ?></h5>
                                        <i class="icn"><svg class="svg">
                                                <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#cancel" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#cancel"></use>
                                            </svg>
                                        </i>
                                    </div>
                                    <div class="card-body ">
                                        <div class="stats">
                                            <div class="stats-number">
                                                <ul>
                                                    <li>
                                                        <span class="total"><?php echo Labels::getLabel('LBL_Cancelled_Orders', $siteLangId); ?></span>
                                                        <span class="total-numbers"><?php echo isset($ordersStats['cancelledOrderCount']) ? FatUtility::int($ordersStats['cancelledOrderCount']) : 0; ?></span>
                                                    </li>
                                                    <li>
                                                        <span class="total"><?php echo Labels::getLabel('LBL_Cancelled_Orders_Amount', $siteLangId); ?></span>
                                                        <span class="total-numbers">
                                                            <?php
                                                            $cancelledOrderAmount = isset($ordersStats['cancelledOrderAmount']) ? $ordersStats['cancelledOrderAmount'] : 0;
                                                            echo CommonHelper::displayMoneyFormat($cancelledOrderAmount);
                                                            ?>
                                                        </span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
            <?php if ($userPrivilege->canViewSales(UserAuthentication::getLoggedUserId(), true)) { ?>
                <div class="row">
                    <div class="col-xl-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title "><?php echo Labels::getLabel('LBL_Sales_Graph', $siteLangId); ?></h5>
                            </div>
                            <div class="card-body  graph"> <?php $this->includeTemplate('_partial/seller/sellerSalesGraph.php'); ?> </div>
                        </div>
                    </div>
                    <div class="col-xl-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title "><?php echo Labels::getLabel('LBL_Latest_Orders', $siteLangId); ?></h5>
                                <?php if (count($orders) > 0) { ?>
                                    <div class="action">
                                        <a href="<?php echo UrlHelper::generateUrl('seller', 'sales'); ?>" class="link"><?php echo Labels::getLabel('Lbl_View_All', $siteLangId); ?></a>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="card-body ">
                                <div class="js-scrollable table-wrap">
                                    <table class="table table-justified">
                                        <thead>
                                            <tr class="">
                                                <th width="60%"><?php echo Labels::getLabel('LBL_Order_Particulars', $siteLangId); ?></th>
                                                <th width="20%"><?php echo Labels::getLabel('LBL_Amount', $siteLangId); ?></th>
                                                <th width="20%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($orders) > 0) {
                                                foreach ($orders as $orderId => $row) {
                                                    $orderDetailUrl = UrlHelper::generateUrl('seller', 'viewOrder', array($row['op_id']));
                                                    $prodOrBatchUrl = 'javascript:void(0)';
                                                    if ($row['op_is_batch']) {
                                                        $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'batch', array($row['op_selprod_id']));
                                                        $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'BatchProduct', array($row['op_selprod_id'], $siteLangId, "SMALL"), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                                    } else {
                                                        if (Product::verifyProductIsValid($row['op_selprod_id']) == true) {
                                                            $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'view', array($row['op_selprod_id']));
                                                        }
                                                        $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($row['selprod_product_id'], "SMALL", $row['op_selprod_id'], 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                                    }
                                                    /* $prodName = '';
                                                if($row['op_selprod_title']!=''){
                $prodName.= $row['op_selprod_title'].'<br/>';
                                                }
                                                $prodName.= $row['op_product_name']; */ ?> <tr>

                                                        <td>
                                                            <div class="item">
                                                                <figure class="item__pic"><a href="<?php echo $prodOrBatchUrl; ?>"><img src="<?php echo $prodOrBatchImgUrl; ?>" title="<?php echo $row['op_product_name']; ?>" alt="<?php echo $row['op_product_name']; ?>"></a>
                                                                </figure>
                                                                <div class="item__description">
                                                                    <div class="item__date"><?php echo FatDate::format($row['order_date_added']); ?></div>
                                                                    <?php if ($row['op_selprod_title'] != '') { ?>
                                                                        <div class="item__title">
                                                                            <a title="<?php echo $row['op_selprod_title']; ?>" href="<?php echo $prodOrBatchUrl; ?>"><?php echo $row['op_selprod_title']; ?></a>
                                                                        </div>
                                                                    <?php } else { ?>
                                                                        <div class="item__sub_title">
                                                                            <?php echo $row['op_product_name']; ?>
                                                                            <a title="<?php echo $row['op_product_name']; ?>" href="<?php echo $prodOrBatchUrl; ?>"><?php echo $row['op_product_name']; ?> </a>
                                                                        </div>
                                                                    <?php } ?>
                                                                    <div class="item__brand">
                                                                        <?php echo Labels::getLabel('Lbl_Brand', $siteLangId) ?>: <?php echo CommonHelper::displayNotApplicable($siteLangId, $row['op_brand_name']); ?>
                                                                    </div>
                                                                    <?php if ($row['op_selprod_options'] != '') { ?>
                                                                        <div class="item__specification"><?php echo $row['op_selprod_options']; ?></div>
                                                                    <?php } ?>
                                                                    <div class="item__specification">
                                                                        <span class="label label-inline <?php echo $classArr[$row['orderstatus_color_class']]; ?>">
                                                                            <?php
                                                                            echo $row['orderstatus_name'];
                                                                            /* $paymentMethodCode = Plugin::getAttributesById($row['order_pmethod_id'], 'plugin_code');
                                                                            if (strtolower($paymentMethodCode) == 'cashondelivery' && $row['opshipping_fulfillment_type'] == Shipping::FULFILMENT_PICKUP) {
                                                                                echo Labels::getLabel('LBL_PAY_ON_PICKUP', $siteLangId);
                                                                            } else {
                                                                                echo $row['orderstatus_name']; 
                                                                            } */
                                                                            ?>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="item__price"><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($row, 'netamount', false, User::USER_TYPE_SELLER)); ?></div>
                                                        </td>
                                                        <td>
                                                            <ul class="actions">
                                                                <li><a title="<?php echo Labels::getLabel('LBL_View_Order', $siteLangId); ?>" href="<?php echo $orderDetailUrl; ?>"><i class="fa fa-eye"></i></a></li>
                                                                <?php if (!in_array($row["op_status_id"], $notAllowedStatues)) { ?>
                                                                    <li><a href="<?php echo UrlHelper::generateUrl('seller', 'cancelOrder', array($row['op_id'])); ?>" title="<?php echo Labels::getLabel('LBL_Cancel_Order', $siteLangId); ?>"><i class="fas fa-times"></i></a></li>
                                                                <?php } ?>
                                                            </ul>
                                                        </td>
                                                    </tr>
                                                <?php }
                                            } else { ?>
                                                <tr>
                                                    <td colspan="3"> <?php $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId), false); ?> </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <?php if ($userParentId == UserAuthentication::getLoggedUserId()) { ?>
                <div class="row">
                    <div class="col-lg-12 col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title "><?php echo Labels::getLabel('LBL_Transaction_History', $siteLangId); ?></h5>
                                <?php if (count($transactions) > 0) { ?>
                                    <div class="action">
                                        <a href="<?php echo UrlHelper::generateUrl('Account', 'credits'); ?>" class="link"><?php echo Labels::getLabel('Lbl_View_All', $siteLangId); ?></a>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="card-body ">
                                <div class="js-scrollable table-wrap">
                                    <table class="table table-justified">
                                        <thead>
                                            <tr class="">
                                                <th><?php echo Labels::getLabel('LBL_Txn._Id', $siteLangId); ?></th>
                                                <th><?php echo Labels::getLabel('LBL_Date', $siteLangId); ?></th>
                                                <th><?php echo Labels::getLabel('LBL_Credit', $siteLangId); ?></th>
                                                <th><?php echo Labels::getLabel('LBL_Debit', $siteLangId); ?></th>
                                                <th><?php echo Labels::getLabel('LBL_Balance', $siteLangId); ?></th>
                                                <th><?php echo Labels::getLabel('LBL_Comments', $siteLangId); ?></th>
                                                <th><?php echo Labels::getLabel('LBL_Status', $siteLangId); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($transactions) > 0) {
                                                foreach ($transactions as $row) { ?>
                                                    <tr>
                                                        <td>
                                                            <div class="txn__id"><?php echo Transactions::formatTransactionNumber($row['utxn_id']); ?> </div>
                                                        </td>
                                                        <td>
                                                            <div class="txn__date"> <?php echo FatDate::format($row['utxn_date']); ?> </div>
                                                        </td>
                                                        <td>
                                                            <div class="txn__credit"> <?php echo CommonHelper::displayMoneyFormat($row['utxn_credit']); ?> </div>
                                                        </td>
                                                        <td>
                                                            <div class="txn__debit"> <?php echo CommonHelper::displayMoneyFormat($row['utxn_debit']); ?> </div>
                                                        </td>
                                                        <td>
                                                            <div class="txn__balance"> <?php echo CommonHelper::displayMoneyFormat($row['balance']); ?> </div>
                                                        </td>
                                                        <td>
                                                            <div class="txn__comments"> <?php echo $row['utxn_comments']; ?> </div>
                                                        </td>
                                                        <td>
                                                            <div class="txn__status"><span class="label label-inline <?php echo $txnStatusClassArr[$row['utxn_status']] ?>"><?php echo $txnStatusArr[$row['utxn_status']]; ?></span> </div>
                                                        </td>
                                                    </tr>
                                                <?php }
                                            } else { ?>
                                                <tr>
                                                    <td colspan="7"> <?php $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId), false); ?> </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="row">
                <?php if ($userPrivilege->canViewReturnRequests(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <div class="col-xl-6 col-md-12 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title "><?php echo Labels::getLabel('LBL_Return_requests', $siteLangId); ?></h5>
                                <?php if (count($returnRequests) > 0) { ?>
                                    <div class="action">
                                        <a href="<?php echo UrlHelper::generateUrl('seller', 'orderReturnRequests'); ?>" class="link"><?php echo Labels::getLabel('Lbl_View_All', $siteLangId); ?></a>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="card-body ">
                                <div class="js-scrollable table-wrap">
                                    <table class="table table-justified">
                                        <thead>
                                            <tr class="">
                                                <th width="60%"><?php echo Labels::getLabel('LBL_Order_Particulars', $siteLangId); ?></th>
                                                <th width="10%"><?php echo Labels::getLabel('LBL_Qty', $siteLangId); ?></th>
                                                <th width="20%"><?php echo Labels::getLabel('LBL_Status', $siteLangId); ?></th>
                                                <th width="10%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($returnRequests) > 0) {
                                                foreach ($returnRequests as $row) {
                                                    $orderDetailUrl = UrlHelper::generateUrl('seller', 'viewOrder', array($row['op_id']));
                                                    $prodOrBatchUrl = 'javascript:void(0)';
                                                    if ($row['op_is_batch']) {
                                                        $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'batch', array($row['op_selprod_id']));
                                                    } else {
                                                        if (Product::verifyProductIsValid($row['op_selprod_id']) == true) {
                                                            $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'view', array($row['op_selprod_id']));
                                                        }
                                                    } ?>
                                                    <tr>
                                                        <td>
                                                            <div class="item__description">
                                                                <div class="request__date"><?php echo FatDate::format($row['orrequest_date']); ?></div>
                                                                <div class="item__title">
                                                                    <a title="<?php echo Labels::getLabel('LBL_Invoice_number', $siteLangId); ?>" href="<?php echo $orderDetailUrl; ?>"><?php echo $row['op_invoice_number']; ?></a>
                                                                </div>
                                                                <div class="item__title">
                                                                    <?php if ($row['op_selprod_title'] != '') { ?>
                                                                        <a title="<?php echo $row['op_selprod_title']; ?>" href="<?php echo $prodOrBatchUrl; ?>"> <?php echo $row['op_selprod_title']; ?> </a>
                                                                    <?php } else { ?>
                                                                        <a title="<?php echo $row['op_product_name']; ?>" href="<?php echo $prodOrBatchUrl; ?>"> <?php echo $row['op_product_name']; ?> </a>
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="request__qty"> <?php echo $row['orrequest_qty']; ?> </div>
                                                        </td>
                                                        <td>
                                                            <div class="request__status"> <span class="label label-inline <?php echo $OrderRetReqStatusClassArr[$row['orrequest_status']]; ?>"><?php echo $OrderReturnRequestStatusArr[$row['orrequest_status']]; ?> </span></div>
                                                        </td>
                                                        <td> <?php
                                                                $url = UrlHelper::generateUrl('Seller', 'ViewOrderReturnRequest', array($row['orrequest_id'])); ?> <ul class="actions">
                                                                <li>
                                                                    <a title="<?php echo Labels::getLabel('LBL_View_Request', $siteLangId); ?>" href="<?php echo $url; ?>">
                                                                        <i class="fa fa-eye"></i>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </td>
                                                    </tr>
                                                <?php }
                                            } else { ?>
                                                <tr>
                                                    <td colspan="4"> <?php $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId), false); ?> </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <?php if ($userPrivilege->canViewCancellationRequests(UserAuthentication::getLoggedUserId(), true)) { ?>
                    <div class="col-xl-6 col-md-12 mb-4">
                        <!-- <div class="card">
                    <?php // $this->includeTemplate('_partial/userDashboardMessages.php');
                    ?>
                </div> -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title "><?php echo Labels::getLabel('LBL_Cancellation_requests', $siteLangId); ?></h5>
                                <?php if (count($cancellationRequests) > 0) { ?>
                                    <div class="action">
                                        <a href="<?php echo UrlHelper::generateUrl('seller', 'orderCancellationRequests'); ?>" class="link"><?php echo Labels::getLabel('Lbl_View_All', $siteLangId); ?></a>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="card-body ">
                                <div class="js-scrollable table-wrap">
                                    <table class="table table-justified">
                                        <thead>
                                            <tr class="">
                                                <th width="40%"><?php echo Labels::getLabel('LBL_Order_Particulars', $siteLangId); ?></th>
                                                <th width="50%"><?php echo Labels::getLabel('LBL_Request_detail', $siteLangId); ?></th>
                                                <th width="10%"><?php echo Labels::getLabel('LBL_Status', $siteLangId); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($cancellationRequests) > 0) {
                                                foreach ($cancellationRequests as $row) {
                                                    $orderDetailUrl = UrlHelper::generateUrl('seller', 'viewOrder', array($row['op_id']));
                                                    $prodOrBatchUrl = 'javascript:void(0)';
                                                    if ($row['op_is_batch']) {
                                                        $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'batch', array($row['op_selprod_id']));
                                                    } else {
                                                        if (Product::verifyProductIsValid($row['op_selprod_id']) == true) {
                                                            $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'view', array($row['op_selprod_id']));
                                                        }
                                                    } ?>
                                                    <tr>
                                                        <td>
                                                            <div class="item__description">
                                                                <div class="request__date"><?php echo FatDate::format($row['ocrequest_date']); ?></div>
                                                                <div class="item__title">
                                                                    <a title="<?php echo Labels::getLabel('Lbl_Invoice_number', $siteLangId) ?>" href="<?php echo $orderDetailUrl; ?>"> <?php echo $row['op_invoice_number']; ?> </a>
                                                                </div>
                                                                <div class="item__title">
                                                                    <?php if ($row['op_selprod_title'] != '') { ?>
                                                                        <a title="<?php echo $row['op_selprod_title']; ?>" href="<?php echo $prodOrBatchUrl; ?>"> <?php echo $row['op_selprod_title']; ?> </a>
                                                                    <?php } else { ?>
                                                                        <a title="<?php echo $row['op_product_name']; ?>" href="<?php echo $prodOrBatchUrl; ?>"> <?php echo $row['op_product_name']; ?> </a>
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="request__reason"> <?php echo Labels::getLabel('Lbl_Reason', $siteLangId) ?>: <?php echo $row['ocreason_title']; ?> </div>
                                                            <div class="request__comments"> <?php echo Labels::getLabel('Lbl_Comments', $siteLangId) ?>: <?php echo $row['ocrequest_message']; ?> </div>
                                                        </td>
                                                        <td>
                                                            <span class="label label-inline <?php echo $cancelReqStatusClassArr[$row['ocrequest_status']]; ?>"> <?php echo $OrderCancelRequestStatusArr[$row['ocrequest_status']]; ?> </span>
                                                        </td>
                                                    </tr>
                                                <?php }
                                            } else { ?>
                                                <tr>
                                                    <td colspan="3"> <?php $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId), false); ?> </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</main>

<script>
    /******** for tooltip ****************/
    $('.info--tooltip-js').hover(function() {
        $(this).toggleClass("is-active");
        return false;
    }, function() {
        $(this).toggleClass("is-active");
        return false;
    });
</script>