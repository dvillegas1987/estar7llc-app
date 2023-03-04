<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
$this->includeTemplate('_partial/buyerDashboardNavigation.php'); ?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <div class="content-header row">
            <div class="col"> <?php $this->includeTemplate('_partial/dashboardTop.php'); ?> <h2 class="content-header-title"><?php echo Labels::getLabel('LBL_Dashboard', $siteLangId); ?></h2>
            </div>
            <div class="col-auto">
                <div class="btn-group">
                    <a href="<?php echo UrlHelper::generateUrl('Account', 'wishlist');?>" class="btn btn-outline-brand btn-sm"><?php echo Labels::getLabel('LBL_Favorites', $siteLangId);?> </a>
                    <a href="<?php echo UrlHelper::generateUrl('Account', 'myAddresses');?>" class="btn btn-outline-brand btn-sm"> <?php echo Labels::getLabel('LBL_Manage_Addresses', $siteLangId);?> </a>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="js-widget-scroll widget-scroll">
                <div class="widget widget-stats">
                    <a href="<?php echo UrlHelper::generateUrl('account', 'credits'); ?>">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title"><?php echo Labels::getLabel('LBL_Credits', $siteLangId); ?></h5>
                                <i class="icn">
                                    <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#credits" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#Credits"></use>
                                    </svg>
                                </i>
                            </div>
                            <div class="card-body ">
                                <div class="stats">
                                    <div class="stats-number">
                                        <ul>
                                            <li><span class="total"><?php echo Labels::getLabel('LBL_Total_Credits', $siteLangId); ?></span>
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
                <div class="widget widget-stats">
                    <a href="<?php echo UrlHelper::generateUrl('buyer', 'orders'); ?>">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title"><?php echo Labels::getLabel('LBL_Orders', $siteLangId); ?></h5>
                                <i class="icn">
                                    <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#order" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#order"></use>
                                    </svg>
                                </i>
                            </div>
                            <div class="card-body ">
                                <div class="stats">
                                    <div class="stats-number">
                                        <ul>
                                            <li><span class="total"><?php echo Labels::getLabel('LBL_Total_Orders', $siteLangId); ?></span>
                                                <span class="total-numbers"><?php echo $ordersCount; ?></span></li>
                                            <li><span class="total"><?php echo Labels::getLabel('LBL_Pending_Orders', $siteLangId); ?></span>
                                                <span class="total-numbers"><?php echo $pendingOrderCount; ?></span> </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="widget widget-stats">
                    <a href="<?php echo UrlHelper::generateUrl('buyer', 'rewardPoints'); ?>">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title"><?php echo Labels::getLabel('LBL_Reward_Points', $siteLangId); ?></h5>
                                <i class="icn">
                                    <svg class="svg">
                                        <use xlink:href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#rewards-change" href="<?php echo CONF_WEBROOT_URL; ?>images/retina/sprite.svg#rewards-change"></use>
                                    </svg>
                                </i>
                            </div>
                            <div class="card-body ">
                                <div class="stats">
                                    <div class="stats-number">
                                        <ul>
                                            <li><span class="total"><?php echo Labels::getLabel('LBL_Current_Reward_Points', $siteLangId); ?></span>
                                                <span class="total-numbers"> <?php echo $totalRewardPoints; ?></span></li>
                                            <li>
                                                <span class="total"><?php echo Labels::getLabel('LBL_Currency_Value', $siteLangId); ?></span>
                                                <span class="total-numbers"> <?php echo CommonHelper::displayMoneyFormat(CommonHelper::convertRewardPointToCurrency($totalRewardPoints)); ?></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><?php echo Labels::getLabel('LBL_Latest_Orders', $siteLangId); ?></h5>
                            <div class="action">
                                <?php if (count($orders) > 0) { ?>
                                    <a href="<?php echo UrlHelper::generateUrl('buyer', 'orders'); ?>" class="link"><?php echo Labels::getLabel('Lbl_View_All', $siteLangId); ?></a>
                                <?php } ?> </div>
                        </div>
                        <div class="card-body ">
                        <div class="js-scrollable table-wrap">
                            <table class="table table-justified">
                                <thead>
                                    <tr class="">
                                        <th width="70%"><?php echo Labels::getLabel('LBL_Order_Particulars', $siteLangId); ?></th>
                                        <th width="20%"><?php echo Labels::getLabel('LBL_Payment_Info', $siteLangId); ?></th>
                                        <th width="10%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($orders) > 0) {
                                        $canCancelOrder = true;
                                        $canReturnRefund = true;
                                        foreach ($orders as $orderId => $row) {
                                            $orderDetailUrl = UrlHelper::generateUrl('Buyer', 'viewOrder', array($row['order_id'], $row['op_id']));
                                            if ($row['op_product_type'] == Product::PRODUCT_TYPE_DIGITAL) {
                                                $canCancelOrder = (in_array($row["op_status_id"], (array)Orders::getBuyerAllowedOrderCancellationStatuses(true)));
                                                $canReturnRefund = (in_array($row["op_status_id"], (array)Orders::getBuyerAllowedOrderReturnStatuses(true)));
                                            } else {
                                                $canCancelOrder = (in_array($row["op_status_id"], (array)Orders::getBuyerAllowedOrderCancellationStatuses()));
                                                $canReturnRefund = (in_array($row["op_status_id"], (array)Orders::getBuyerAllowedOrderReturnStatuses()));
                                            }
                                            $isValidForReview = false;
                                            if (in_array($row["op_status_id"], SelProdReview::getBuyerAllowedOrderReviewStatuses())) {
                                                $isValidForReview = true;
                                            }
                                            $canSubmitFeedback = Orders::canSubmitFeedback($row['order_user_id'], $row['order_id'], $row['op_selprod_id']); ?>
                                            <tr>
                                                <td>

                                                    <div class="item">
                                                        <?php
                                                        $prodOrBatchUrl = 'javascript:void(0)';
                                                        if ($row['op_is_batch']) {
                                                            $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'batch', array($row['op_selprod_id']));
                                                            $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateUrl('image', 'BatchProduct', array($row['op_selprod_id'], $siteLangId, "SMALL"), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                                        } else {
                                                            if (Product::verifyProductIsValid($row['op_selprod_id']) == true) {
                                                                $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'view', array($row['op_selprod_id']));
                                                            }
                                                            $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateUrl('image', 'product', array($row['selprod_product_id'], "SMALL", $row['op_selprod_id'], 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                                        } ?> <figure class="item__pic"><a href="<?php echo $prodOrBatchUrl; ?>"><img src="<?php echo $prodOrBatchImgUrl; ?>" title="<?php echo $row['op_product_name']; ?>" alt="<?php echo $row['op_product_name']; ?>"></a></figure>

                                                        <div class="item__description">
                                                            <div class="item__date"><?php echo FatDate::format($row['order_date_added']); ?></div>
                                                            <div class="item__title"> <?php $prodName = '';
                                                                                        if ($row['op_selprod_title'] != '') {
                                                                                            $prodName .= $row['op_selprod_title'] . '<br/>';
                                                                                        }
                                                                                        $prodName .= $row['op_product_name']; ?> <a title="<?php echo $row['op_product_name']; ?>" href="<?php echo $prodOrBatchUrl; ?>"><?php echo $prodName; ?></a>
                                                            </div>
                                                            <!-- <div class="item__brand"><span><?php /*echo Labels::getLabel('Lbl_Brand', $siteLangId)?>:</span> <?php echo CommonHelper::displayNotApplicable($siteLangId, $row['op_brand_name']);*/ ?></div> -->
                                                            <?php if ($row['op_selprod_options'] != '') { ?> <div class="item__specification"><?php echo $row['op_selprod_options']; ?></div> <?php } ?> <?php if ($row['totOrders'] > 1) {
                                                                                                                                                                                                            echo Labels::getLabel('LBL_Part_combined_order', $siteLangId) . ' <a title="' . Labels::getLabel('LBL_View_Order_Detail', $siteLangId) . '" href="' . UrlHelper::generateUrl('Buyer', 'viewOrder', array($row['order_id'])) . '">' . $row['order_id'] . '</a>';
                                                                                                                                                                                                        } ?>
                                                        </div>
                                                    </div>


                                                </td>
                                                <td>
                                                    <div class="item__specification">
                                                        <span class="item__price"><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($row)); ?></span>
                                                        <br>
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
                                                </td>
                                                <td>
                                                    <ul class="actions">
                                                        <li><a title="<?php echo Labels::getLabel('LBL_View_Order', $siteLangId); ?>" href="<?php echo $orderDetailUrl; ?>"><i class="fa fa-eye"></i></a></li> <?php if ($canCancelOrder) { ?> <li><a href="<?php echo UrlHelper::generateUrl('buyer', 'orderCancellationRequest', array($row['op_id'])); ?>" title="<?php echo Labels::getLabel('LBL_Cancel_Order', $siteLangId); ?>"><i class="fas fa-times"></i></a></li> <?php } ?> <?php if ($canSubmitFeedback && $isValidForReview) { ?> <li><a href="<?php echo UrlHelper::generateUrl('Buyer', 'orderFeedback', array($row['op_id'])); ?>" title="<?php echo Labels::getLabel('LBL_Feedback', $siteLangId); ?>"><i class="fa fa-star"></i></a>
                                                            </li> <?php } ?> <?php if ($canReturnRefund) { ?> <li><a href="<?php echo UrlHelper::generateUrl('Buyer', 'orderReturnRequest', array($row['op_id'])); ?>" title="<?php echo Labels::getLabel('LBL_Refund', $siteLangId); ?>"><i class="fas fa-dollar-sign"></i></a></li> <?php } ?>
                                                    </ul>
                                                </td>
                                            </tr> <?php }
                                            } else { ?> <tr>
                                            <td colspan="4"> <?php $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId), false); ?> </td>
                                        </tr> <?php } ?>
                                </tbody>

                            </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title "><?php echo Labels::getLabel('LBL_Latest_Offers', $siteLangId); ?></h5>
                            <div class="action"> <?php if (count($offers) > 0) { ?> <a href="<?php echo UrlHelper::generateUrl('buyer', 'offers'); ?>" class="link"><?php echo Labels::getLabel('Lbl_View_All', $siteLangId); ?></a> <?php } ?> </div>
                        </div>
                        <div class="card-body">
                        <div class="js-scrollable table-wrap">
                         <table class="table">
                                <thead>
                                    <tr class="">
                                        <th colspan="2" width="60%"><?php echo Labels::getLabel('LBL_Offer_Particulars', $siteLangId); ?></th>
                                        <th width="20%"><?php echo Labels::getLabel('LBL_Expires_On', $siteLangId); ?></th>
                                        <th width="20%"><?php echo Labels::getLabel('LBL_Min_order', $siteLangId); ?></th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <?php if (count($offers) > 0) {
                                        foreach ($offers as $row) {
                                            $discountValue = ($row['coupon_discount_in_percent'] == ApplicationConstants::PERCENTAGE) ? $row['coupon_discount_value'] . ' %' : CommonHelper::displayMoneyFormat($row['coupon_discount_value']); ?> <tr>
                                                <td>
                                                    <figure class="item__pic"><img src="<?php echo UrlHelper::generateFullUrl('Image', 'coupon', array($row['coupon_id'], $siteLangId, 'NORMAL')) ?>" alt="<?php echo ($row['coupon_title'] == '') ? $row['coupon_identifier'] : $row['coupon_title']; ?>"></figure>
                                                </td>
                                                <td>
                                                    <div class="item__description">
                                                        <div class="item__title"><?php echo $discountValue; ?> <?php echo Labels::getLabel('LBL_OFF', $siteLangId); ?></div>
                                                        <div class="item__title"><?php echo ($row['coupon_title'] == '') ? $row['coupon_identifier'] : $row['coupon_title']; ?></div>
                                                        <span class="coupon-code"><?php echo $row['coupon_code']; ?></span>
                                                    </div>
                                                </td>
                                                <td><?php echo FatDate::format($row['coupon_end_date']); ?></td>
                                                <td> <?php echo CommonHelper::displayMoneyFormat($row['coupon_min_order_value']); ?> </td>
                                            </tr> <?php }
                                            } else { ?> <tr>
                                            <td colspan="4"> <?php $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId), false); ?> </td>
                                        </tr> <?php } ?>
                                </tbody>

                            </table>
                            </div>
                        </div>
                         <?php // $this->includeTemplate('_partial/userDashboardMessages.php');
                                ?>
                    </div>
                </div>
            </div>
            <div class="row ">
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title "><?php echo Labels::getLabel('LBL_Return_requests', $siteLangId); ?></h5> <?php if (count($returnRequests) > 0) { ?> <div class="action">
                                    <a href="<?php echo UrlHelper::generateUrl('buyer', 'orderReturnRequests'); ?>" class="link"><?php echo Labels::getLabel('Lbl_View_All', $siteLangId); ?></a>
                                </div> <?php } ?>
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
                                            $orderDetailUrl = UrlHelper::generateUrl('buyer', 'viewOrder', array($row['order_id'], $row['op_id']));
                                            $prodOrBatchUrl = 'javascript:void(0)';
                                            if ($row['op_is_batch']) {
                                                $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'batch', array($row['op_selprod_id']));
                                            } else {
                                                if (Product::verifyProductIsValid($row['op_selprod_id']) == true) {
                                                    $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'view', array($row['op_selprod_id']));
                                                }
                                            } ?> <tr>
                                                <td>
                                                    <div class="item__description">
                                                        <div class="request__date"><?php echo FatDate::format($row['orrequest_date']); ?></div>
                                                        <div class="item__title">
                                                            <a title="<?php echo Labels::getLabel('LBL_Invoice_number', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Buyer', 'viewOrder', array($row['order_id'], $row['op_id'])); ?>" href="<?php echo $orderDetailUrl; ?>"><?php echo $row['op_invoice_number']; ?></a>
                                                        </div>
                                                        <div class="item__title"> <?php if ($row['op_selprod_title'] != '') { ?> <a title="<?php echo $row['op_selprod_title']; ?>" href="<?php echo $prodOrBatchUrl; ?>"> <?php echo $row['op_selprod_title']; ?>
                                                                </a> <?php } else { ?> <a title="<?php echo $row['op_product_name']; ?>" href="<?php echo $prodOrBatchUrl; ?>"> <?php echo $row['op_product_name']; ?> </a> <?php } ?> </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="request__qty"> <?php echo $row['orrequest_qty']; ?> </div>
                                                </td>
                                                <td>
                                                    <span class="label label-inline <?php echo $OrderRetReqStatusClassArr[$row['orrequest_status']]; ?>"> <?php echo $OrderReturnRequestStatusArr[$row['orrequest_status']]; ?> </span>
                                                </td>
                                                <td>
                                                    <ul class="actions">
                                                        <li>
                                                            <a title="<?php echo Labels::getLabel('LBL_View_Request', $siteLangId); ?>" href="<?php echo UrlHelper::generateUrl('Buyer', 'ViewOrderReturnRequest', array($row['orrequest_id'])); ?>">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </td>
                                            </tr> <?php }
                                            } else { ?> <tr>
                                            <td colspan="4"> <?php $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId), false); ?> </td>
                                        </tr> <?php } ?>
                                </tbody>

                            </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 mb-4">
                    <!-- <div class="card">
                        <?php // $this->includeTemplate('_partial/userDashboardMessages.php');
                        ?>
                        </div> -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><?php echo Labels::getLabel('LBL_Cancellation_requests', $siteLangId); ?></h5> <?php if (count($cancellationRequests) > 0) { ?> <div class="action">
                                    <a href="<?php echo UrlHelper::generateUrl('buyer', 'orderCancellationRequests'); ?>" class="link"><?php echo Labels::getLabel('Lbl_View_All', $siteLangId); ?></a>
                                </div> <?php } ?>
                        </div>
                        <div class="card-body ">
                        <div class="js-scrollable table-wrap">
                            <table class="table ">
                                <thead>
                                    <tr class="">
                                        <th width="40%"><?php echo Labels::getLabel('LBL_Order_Particulars', $siteLangId); ?></th>
                                        <th width="50%"><?php echo Labels::getLabel('LBL_Details', $siteLangId); ?></th>
                                        <th width="10%"><?php echo Labels::getLabel('LBL_Status', $siteLangId); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($cancellationRequests) > 0) {
                                        foreach ($cancellationRequests as $row) {
                                            $orderDetailUrl = UrlHelper::generateUrl('buyer', 'viewOrder', array($row['order_id'], $row['op_id']));
                                            $prodOrBatchUrl = 'javascript:void(0)';
                                            if ($row['op_is_batch']) {
                                                $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'batch', array($row['op_selprod_id']));
                                            } else {
                                                if (Product::verifyProductIsValid($row['op_selprod_id']) == true) {
                                                    $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'view', array($row['op_selprod_id']));
                                                }
                                            } ?> <tr>
                                                <td>
                                                    <div class="item__description">
                                                        <div class="request__date"><?php echo FatDate::format($row['ocrequest_date']); ?></div>
                                                        <div class="item__title">
                                                            <a title="<?php echo Labels::getLabel('Lbl_Invoice_number', $siteLangId) ?>" href="<?php echo $orderDetailUrl; ?>"> <?php echo $row['op_invoice_number']; ?> </a>
                                                        </div>
                                                        <div class="item__title"> <?php if ($row['op_selprod_title'] != '') { ?> <a title="<?php echo $row['op_selprod_title']; ?>" href="<?php echo $prodOrBatchUrl; ?>"> <?php echo $row['op_selprod_title']; ?>
                                                                </a> <?php } else { ?> <a title="<?php echo $row['op_product_name']; ?>" href="<?php echo $prodOrBatchUrl; ?>"> <?php echo $row['op_product_name']; ?> </a> <?php } ?> </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="request__reason"> <?php echo Labels::getLabel('Lbl_Reason', $siteLangId) ?>: <?php echo $row['ocreason_title']; ?> </div>
                                                    <div class="request__comments"> <?php echo Labels::getLabel('Lbl_Comments', $siteLangId) ?>: <?php echo $row['ocrequest_message']; ?> </div>
                                                </td>
                                                <td>
                                                    <span class="label label-inline <?php echo $cancelReqStatusClassArr[$row['ocrequest_status']]; ?>"> <?php echo $OrderCancelRequestStatusArr[$row['ocrequest_status']]; ?> </span>
                                                </td>
                                            </tr> <?php }
                                            } else { ?> <tr>
                                            <td colspan="3"> <?php $this->includeTemplate('_partial/no-record-found.php', array('siteLangId' => $siteLangId), false); ?> </td>
                                        </tr> <?php } ?>
                                </tbody>

                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>