<?php defined('SYSTEM_INIT') or die('Invalid Usage.');
if (!$print) {
    $this->includeTemplate('_partial/seller/sellerDashboardNavigation.php'); ?>
<?php
}
$shippingCharges = CommonHelper::orderProductAmount($orderDetail, 'shipping');

$orderStatusLbl = Labels::getLabel('LBL_AWAITING_SHIPMENT', $siteLangId);
$orderStatus = '';
if (!empty($orderDetail["thirdPartyorderInfo"]) && isset($orderDetail["thirdPartyorderInfo"]['orderStatus'])) {
    $orderStatus = $orderDetail["thirdPartyorderInfo"]['orderStatus'];
    $orderStatusLbl = strpos($orderStatus, "_") ? str_replace('_', ' ', $orderStatus) : $orderStatus;
}

?>
<main id="main-area" class="main"   >
    <div class="content-wrapper content-space">
        <?php if (!$print) { ?>
            <div class="content-header row">
                <div class="col">
                    <?php $this->includeTemplate('_partial/dashboardTop.php'); ?>
                    <h2 class="content-header-title">
                        <?php echo Labels::getLabel('LBL_View_Sale_Order', $siteLangId); ?>
                    </h2>
                </div>
                <?php
                $orderObj = new Orders();
                $processingStatuses = $orderObj->getVendorAllowedUpdateOrderStatuses();
                $processingStatuses = array_diff($processingStatuses, [OrderStatus::ORDER_DELIVERED]);
                if (in_array($orderDetail['orderstatus_id'], $processingStatuses) && $canEdit) { ?>
                    <div class="col-auto">
                        <div class="btn-group">
                            <ul class="actions">
                                <li>
                                    <a href="<?php echo UrlHelper::generateUrl('seller', 'cancelOrder', array($orderDetail['op_id'])); ?>" class="icn-highlighted" title="<?php echo Labels::getLabel('LBL_Cancel_Order', $siteLangId); ?>"><i class="fas fa-times"></i></a>
                                </li>
                            </ul>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
        <div class="content-body">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><?php echo Labels::getLabel('LBL_Order_Details', $siteLangId); ?></h5>
                    <?php if (!$print) { ?>
                        <div class="">
                            <iframe src="<?php echo Fatutility::generateUrl('seller', 'viewOrder', $urlParts) . '/print'; ?>" name="frame" class="printFrame-js" style="display:none" width="1" height="1"></iframe>
                            <a href="<?php echo UrlHelper::generateUrl('Seller', 'sales'); ?>" class="btn btn-outline-brand  btn-sm no-print" title="<?php echo Labels::getLabel('LBL_Back_to_order', $siteLangId); ?>">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                            <a target="_blank" href="<?php echo UrlHelper::generateUrl('Seller', 'viewInvoice', [$orderDetail['op_id']]); ?>" class="btn btn-outline-brand btn-sm no-print" title="
								<?php echo Labels::getLabel('LBL_Print', $siteLangId); ?>">
                                <i class="fas fa-print"></i>
                            </a>
                            <a target="_blank" href="<?php echo UrlHelper::generateUrl('Account', 'viewBuyerOrderInvoice', [$orderDetail['order_id'],$orderDetail['op_id']]); ?>" class="btn btn-outline-brand btn-sm no-print" title="
				<?php echo Labels::getLabel('LBL_PRINT_BUYER_INVOICE', $siteLangId); ?>">
                                <i class="fas fa-print"></i>
                            </a>
                            <?php if ($shippedBySeller && true === $canShipByPlugin && ('CashOnDelivery' == $orderDetail['plugin_code'] || Orders::ORDER_PAYMENT_PAID == $orderDetail['order_payment_status'])) {
                                $opId = $orderDetail['op_id'];
                                if (empty($orderDetail['opship_response']) && empty($orderDetail['opship_tracking_number'])) {
                                    $orderId = $orderDetail['order_id']; ?>
                                    <a href="javascript:void(0)" onclick='generateLabel(<?php echo $opId; ?>)' class="btn btn-outline-brand  btn-sm no-print" title="<?php echo Labels::getLabel('LBL_GENERATE_LABEL', $siteLangId); ?>"><i class="fas fa-file-download"></i></a>
                                <?php } elseif (!empty($orderDetail['opship_response'])) { ?>
                                    <a target="_blank" href="<?php echo UrlHelper::generateUrl("ShippingServices", 'previewLabel', [$orderDetail['op_id']]); ?>" class="btn btn-outline-brand  btn-sm no-print" title="<?php echo Labels::getLabel('LBL_PREVIEW_LABEL', $siteLangId); ?>"><i class="fas fa-file-export"></i></a>
                                <?php }

                                if (!empty($orderStatus) && 'awaiting_shipment' == $orderStatus && !empty($orderDetail['opship_response'])) { ?>
                                    <a href="javascript:void(0)" onclick="proceedToShipment(<?php echo $opId; ?>)" class="btn btn-outline-brand  btn-sm no-print" title="<?php echo Labels::getLabel('LBL_PROCEED_TO_SHIPMENT', $siteLangId); ?>"><i class="fas fa-shipping-fast"></i></a>
                            <?php }
                            } ?>
                        </div>
                    <?php } ?>
                </div>
                <div class="card-body ">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 mb-4">
                            <div class="info--order">
                                <p><strong><?php echo Labels::getLabel('LBL_Customer_Name', $siteLangId); ?>: </strong><?php echo $orderDetail['user_name']; ?></p>
                                <?php
                                $selected_method = '';
                                if ($orderDetail['order_pmethod_id'] > 0) {
                                    $selected_method .= CommonHelper::displayNotApplicable($siteLangId, $orderDetail["plugin_name"]);
                                }
                                if ($orderDetail['order_is_wallet_selected'] > 0) {
                                    $selected_method .= ($selected_method != '') ? ' + ' . Labels::getLabel("LBL_Wallet", $siteLangId) : Labels::getLabel("LBL_Wallet", $siteLangId);
                                }
                                if ($orderDetail['order_reward_point_used'] > 0) {
                                    $selected_method .= ($selected_method != '') ? ' + ' . Labels::getLabel("LBL_Rewards", $siteLangId) : Labels::getLabel("LBL_Rewards", $siteLangId);
                                }

                                if (in_array(strtolower($orderDetail['plugin_code']), ['cashondelivery', 'payatstore'])) {
                                    $selected_method = (empty($orderDetail['plugin_name'])) ? $orderDetail['plugin_identifier'] : $orderDetail['plugin_name'];
                                }
                                ?>
                                <p><strong><?php echo Labels::getLabel('LBL_Payment_Method', $siteLangId); ?>: </strong><?php echo $selected_method; ?></p>
                                <p><strong><?php echo Labels::getLabel('LBL_Status', $siteLangId); ?>: </strong>
                                    <?php echo Orders::getOrderPaymentStatusArr($siteLangId)[$orderDetail['order_payment_status']];
                                    if ('' != $orderDetail['plugin_name'] && 'CashOnDelivery' == $orderDetail['plugin_code']) {
                                        echo ' (' . $orderDetail['plugin_name'] . ' )';
                                    } ?>
                                </p>
                                <p><strong><?php echo Labels::getLabel('LBL_Cart_Total', $siteLangId); ?>: </strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'CART_TOTAL'), true, false, true, false, true); ?></p>

                                <?php if ($shippedBySeller && 0 < $shippingCharges) { ?>
                                    <p><strong><?php echo Labels::getLabel('LBL_Delivery', $siteLangId); ?>: </strong><?php echo CommonHelper::displayMoneyFormat($shippingCharges, true, false, true, false, true); ?></p>
                                <?php } ?>

                                <?php if ($orderDetail['op_tax_collected_by_seller']) { ?>
                                    <?php if (empty($orderDetail['taxOptions'])) { ?>
                                        <p>
                                            <strong><?php echo Labels::getLabel('LBL_Tax', $siteLangId); ?>:</strong>
                                            <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'TAX'), true, false, true, false, true); ?>
                                        </p>
                                        <?php } else {
                                        foreach ($orderDetail['taxOptions'] as $key => $val) { ?>
                                            <p><strong><?php echo CommonHelper::displayTaxPercantage($val, true) ?>:</strong> <?php echo CommonHelper::displayMoneyFormat($val['value'], true, false, true, false, true); ?></p>
                                    <?php }
                                    } ?>
                                <?php } ?>
                                <?php /*
                        <p><strong><?php echo Labels::getLabel('LBL_Discount',$siteLangId);?>:</strong> <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail,'DISCOUNT'));?></p> */ ?>
                                <?php $volumeDiscount = CommonHelper::orderProductAmount($orderDetail, 'VOLUME_DISCOUNT');
                                if (0 < $volumeDiscount) { ?>
                                    <p><strong><?php echo Labels::getLabel('LBL_Volume/Loyalty_Discount', $siteLangId); ?>:</strong> <?php echo CommonHelper::displayMoneyFormat($volumeDiscount, true, false, true, false, true); ?></p>
                                <?php } ?>
                                <?php
                                /* $rewardPointDiscount = CommonHelper::orderProductAmount($orderDetail,'REWARDPOINT');
                        if($rewardPointDiscount != 0){?>
                                <p><strong><?php echo Labels::getLabel('LBL_Reward_Point_Discount',$siteLangId);?>:</strong> <?php echo CommonHelper::displayMoneyFormat($rewardPointDiscount);?></p>
                                <?php }  */ ?>
                                <?php if (array_key_exists('order_rounding_off', $orderDetail) && 0 != $orderDetail['order_rounding_off'] ) { ?>
                                    <p>
                                        <strong>
                                        <?php echo (0 < $orderDetail['order_rounding_off']) ? Labels::getLabel('LBL_Rounding_Up', $siteLangId) : Labels::getLabel('LBL_Rounding_Down', $siteLangId); ?>:
                                        </strong>
                                        <?php echo CommonHelper::displayMoneyFormat($orderDetail['order_rounding_off'], true, false, true, false, true); ?>
                                    </p>
                                <?php } ?>
                                <p><strong><?php echo Labels::getLabel('LBL_Order_Total', $siteLangId); ?>: </strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'netamount', false, User::USER_TYPE_SELLER), true, false, true, false, true); ?>

                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 mb-4">
                            <div class="info--order">
                                <p><strong><?php echo Labels::getLabel('LBL_Invoice', $siteLangId); ?> #: </strong><?php echo $orderDetail['op_invoice_number']; ?></p>
                                <p><strong><?php echo Labels::getLabel('LBL_Date', $siteLangId); ?>: </strong><?php echo FatDate::format($orderDetail['order_date_added']); ?></p>
                                <?php if ($orderDetail["opshipping_fulfillment_type"] == Shipping::FULFILMENT_PICKUP) { ?>
                                    <p><strong><?php echo Labels::getLabel('LBL_Pickup_Date', $siteLangId); ?>: </strong>
                                        <?php
                                        $fromTime = isset($orderDetail["opshipping_time_slot_from"]) ? date('H:i', strtotime($orderDetail["opshipping_time_slot_from"])) : '';
                                        $toTime = isset($orderDetail["opshipping_time_slot_to"]) ? date('H:i', strtotime($orderDetail["opshipping_time_slot_to"])) : '';
                                        $shippingDate = isset($orderDetail["opshipping_date"]) ? FatDate::format($orderDetail["opshipping_date"]) : '';
                                        echo  $shippingDate . ' ' . $fromTime . ' - ' . $toTime;
                                        ?>
                                    </p>
                                <?php } ?>
                                <span class="gap"></span>
                            </div>
                        </div>
                    </div>
                    <div class="js-scrollable table-wrap">
                        <table class="table">
                            <thead>
                                <tr class="">
                                    <th><?php echo Labels::getLabel('LBL_Order_Particulars', $siteLangId); ?></th>
                                    <?php if (!$print) { ?>
                                        <th class="no-print"></th>
                                    <?php } ?>
                                    <th>
                                        <?php
                                        if (!empty($orderDetail['pickupAddress'])) {
                                            echo Labels::getLabel('LBL_PICKUP_DETAIL', $siteLangId);
                                        } ?>
                                    </th>
                                    <th><?php echo Labels::getLabel('LBL_Qty', $siteLangId); ?></th>
                                    <th><?php echo Labels::getLabel('LBL_Price', $siteLangId); ?></th>
                                    <?php if ($shippedBySeller && 0 < $shippingCharges) { ?>
                                        <th><?php echo Labels::getLabel('LBL_Shipping_Charges', $siteLangId); ?></th>
                                    <?php } ?>
                                    <?php if ($volumeDiscount) { ?>
                                        <th><?php echo Labels::getLabel('LBL_Volume/Loyalty_Discount', $siteLangId); ?></th>
                                    <?php } ?>
                                    <?php if ($orderDetail['op_tax_collected_by_seller']) { ?>
                                        <th><?php echo Labels::getLabel('LBL_Tax_Charges', $siteLangId); ?></th>
                                    <?php } ?>
                                    <th><?php echo Labels::getLabel('LBL_Total', $siteLangId); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <?php if (!$print) { ?>
                                        <td>
                                            <div class="pic--cell-left">
                                                <?php
                                                $prodOrBatchUrl = 'javascript:void(0)';
                                                if ($orderDetail['op_is_batch']) {
                                                    $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'batch', array($orderDetail['op_selprod_id']));
                                                    $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'BatchProduct', array($orderDetail['op_selprod_id'], $siteLangId, "SMALL"), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                                } else {
                                                    if (Product::verifyProductIsValid($orderDetail['op_selprod_id']) == true) {
                                                        $prodOrBatchUrl = UrlHelper::generateUrl('Products', 'view', array($orderDetail['op_selprod_id']));
                                                    }
                                                    $prodOrBatchImgUrl = UrlHelper::getCachedUrl(UrlHelper::generateFileUrl('image', 'product', array($orderDetail['selprod_product_id'], "SMALL", $orderDetail['op_selprod_id'], 0, $siteLangId), CONF_WEBROOT_URL), CONF_IMG_CACHE_TIME, '.jpg');
                                                }  ?>
                                                <figure class="item__pic"><a href="<?php echo $prodOrBatchUrl; ?>"><img src="<?php echo $prodOrBatchImgUrl; ?>" title="<?php echo $orderDetail['op_product_name']; ?>" alt="<?php echo $orderDetail['op_product_name']; ?>"></a></figure>
                                                <!--</td>
                                                <td>-->
                                            </div>
                                        </td>
                                    <?php } ?>
                                    <td>
                                        <div class="item__description">
                                            <?php if ($orderDetail['op_selprod_title'] != '') { ?>
                                                <div class="item__title"><a title="<?php echo $orderDetail['op_selprod_title']; ?>" href="<?php echo $prodOrBatchUrl; ?>"><?php echo $orderDetail['op_selprod_title']; ?></a></div>
                                                <div class="item__category"><?php echo $orderDetail['op_product_name']; ?></div>
                                            <?php } else { ?>
                                                <div class="item__brand"><a title="<?php echo $orderDetail['op_product_name']; ?>" href="<?php echo $prodOrBatchUrl; ?>"><?php echo $orderDetail['op_product_name']; ?>
                                                    </a></div>
                                            <?php } ?>
                                            <div class="item__brand"><?php echo Labels::getLabel('Lbl_Brand', $siteLangId) ?>: <?php echo CommonHelper::displayNotApplicable($siteLangId, $orderDetail['op_brand_name']); ?></div>
                                            <?php if ($orderDetail['op_selprod_options'] != '') { ?>
                                                <div class="item__specification"><?php echo $orderDetail['op_selprod_options']; ?></div>
                                            <?php } ?>
                                            <?php if ($orderDetail['op_shipping_duration_name'] != '') { ?>
                                                <div class="item__shipping"><?php echo Labels::getLabel('LBL_Shipping_Method', $siteLangId); ?>: <?php echo $orderDetail['op_shipping_durations'] . '-' . $orderDetail['op_shipping_duration_name']; ?></div>
                                        </div>
                                    <?php } ?>
                                    </td>
                                    <td>
                                        <?php
                                        if (Shipping::FULFILMENT_PICKUP == $orderDetail['opshipping_fulfillment_type']) { ?>
                                            <p>
                                                <strong>
                                                    <?php
                                                    $opshippingDate = isset($orderDetail['opshipping_date']) ? $orderDetail['opshipping_date'] . ' ' : '';
                                                    $timeSlotFrom = isset($orderDetail['opshipping_time_slot_from']) ? ' (' . date('H:i', strtotime($orderDetail['opshipping_time_slot_from'])) . ' - ' : '';
                                                    $timeSlotTo = isset($orderDetail['opshipping_time_slot_to']) ? date('H:i', strtotime($orderDetail['opshipping_time_slot_to'])) . ')' : '';
                                                    echo $opshippingDate . $timeSlotFrom . $timeSlotTo;
                                                    ?>
                                                </strong><br>
                                                <?php echo $orderDetail['addr_name']; ?>,
                                                <?php
                                                $address1 = !empty($orderDetail['addr_address1']) ? $orderDetail['addr_address1'] : '';
                                                $address2 = !empty($orderDetail['addr_address2']) ? ', ' . $orderDetail['addr_address2'] : '';
                                                $city = !empty($orderDetail['addr_city']) ? '<br>' . $orderDetail['addr_city'] : '';
                                                $state = !empty($orderDetail['state_name']) ? ', ' . $orderDetail['state_name'] : '';
                                                $country = !empty($orderDetail['country_name']) ? ' ' . $orderDetail['country_name'] : '';
                                                $zip = !empty($orderDetail['addr_zip']) ? '(' . $orderDetail['addr_zip'] . ')' : '';

                                                echo $address1 . $address2 . $city . $state . $country . $zip;
                                                ?>
                                            </p>
                                        <?php } ?>
                                    </td>
                                    <td><?php echo $orderDetail['op_qty']; ?></td>
                                    <td><?php echo CommonHelper::displayMoneyFormat($orderDetail['op_unit_price'], true, false, true, false, true); ?></td>

                                    <?php if ($shippedBySeller && 0 < $shippingCharges) { ?>
                                        <td><?php echo CommonHelper::displayMoneyFormat($shippingCharges, true, false, true, false, true); ?></td>
                                    <?php } ?>

                                    <?php if ($volumeDiscount) { ?>
                                        <td><?php echo CommonHelper::displayMoneyFormat($volumeDiscount, true, false, true, false, true); ?></td>
                                    <?php } ?>

                                    <?php if ($orderDetail['op_tax_collected_by_seller']) { ?>
                                        <td>
                                            <?php
                                            if (empty($orderDetail['taxOptions'])) {
                                                echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'TAX'), true, false, true, false, true);
                                            } else {
                                                foreach ($orderDetail['taxOptions'] as $key => $val) { ?>
                                                    <p><strong><?php echo CommonHelper::displayTaxPercantage($val, true) ?>:</strong> <?php echo CommonHelper::displayMoneyFormat($val['value'], true, false, true, false, true); ?></p>
                                            <?php }
                                            } ?>
                                        </td>
                                    <?php } ?>
                                    <td>
                                        <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($orderDetail, 'netamount', false, User::USER_TYPE_SELLER), true, false, true, false, true); ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="divider"></div>
                    <div class="gap"></div>
                    <div class="gap"></div>
                    <div class="row">
                        <div class="col-lg-6 col-md-6 mb-4">
                            <h5><?php echo Labels::getLabel('LBL_Billing_Details', $siteLangId); ?></h5>
                            <?php $billingAddress = $orderDetail['billingAddress']['oua_name'] . '<br>';
                            if ($orderDetail['billingAddress']['oua_address1'] != '') {
                                $billingAddress .= $orderDetail['billingAddress']['oua_address1'] . '<br>';
                            }

                            if ($orderDetail['billingAddress']['oua_address2'] != '') {
                                $billingAddress .= $orderDetail['billingAddress']['oua_address2'] . '<br>';
                            }

                            if ($orderDetail['billingAddress']['oua_city'] != '') {
                                $billingAddress .= $orderDetail['billingAddress']['oua_city'] . ', ';
                            }

                            if ($orderDetail['billingAddress']['oua_state'] != '') {
                                $billingAddress .= $orderDetail['billingAddress']['oua_state'] . ', ';
                            }

                            if ($orderDetail['billingAddress']['oua_country'] != '') {
                                $billingAddress .= $orderDetail['billingAddress']['oua_country'];
                            }

                            if ($orderDetail['billingAddress']['oua_zip'] != '') {
                                $billingAddress .= '-' . $orderDetail['billingAddress']['oua_zip'];
                            }

                            if ($orderDetail['billingAddress']['oua_phone'] != '') {
                                $billingAddress .= '<br>' . ValidateElement::formatDialCode($orderDetail['billingAddress']['oua_phone_dcode']) . $orderDetail['billingAddress']['oua_phone'];
                            }
                            ?>
                            <div class="info--order">
                                <p><?php echo $billingAddress; ?></p>
                            </div>
                        </div>
                        <?php if (!empty($orderDetail['shippingAddress'])) { ?>
                            <div class="col-lg-6 col-md-6 mb-4">
                                <h5><?php echo Labels::getLabel('LBL_Shipping_Details', $siteLangId); ?></h5>
                                <?php $shippingAddress = $orderDetail['shippingAddress']['oua_name'] . '<br>';
                                if ($orderDetail['shippingAddress']['oua_address1'] != '') {
                                    $shippingAddress .= $orderDetail['shippingAddress']['oua_address1'] . '<br>';
                                }

                                if ($orderDetail['shippingAddress']['oua_address2'] != '') {
                                    $shippingAddress .= $orderDetail['shippingAddress']['oua_address2'] . '<br>';
                                }

                                if ($orderDetail['shippingAddress']['oua_city'] != '') {
                                    $shippingAddress .= $orderDetail['shippingAddress']['oua_city'] . ', ';
                                }

                                if ($orderDetail['shippingAddress']['oua_state'] != '') {
                                    $shippingAddress .= $orderDetail['shippingAddress']['oua_state'] . ', ';
                                }

                                if ($orderDetail['shippingAddress']['oua_country'] != '') {
                                    $shippingAddress .= $orderDetail['shippingAddress']['oua_country'];
                                }

                                if ($orderDetail['shippingAddress']['oua_zip'] != '') {
                                    $shippingAddress .= '-' . $orderDetail['shippingAddress']['oua_zip'];
                                }

                                if ($orderDetail['shippingAddress']['oua_phone'] != '') {
                                    $shippingAddress .= '<br>' . ValidateElement::formatDialCode($orderDetail['shippingAddress']['oua_phone_dcode']) . $orderDetail['shippingAddress']['oua_phone'];
                                } ?>
                                <div class="info--order">
                                    <p><?php echo $shippingAddress; ?></p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <?php if ($canEdit && $displayForm && !$print) { ?>
                        <div class="section--repeated no-print">
                            <h5><?php echo Labels::getLabel('LBL_Comments_on_order', $siteLangId); ?></h5>
                            <?php
                            $frm->setFormTagAttribute('onsubmit', 'updateStatus(this); return(false);');
                            $frm->setFormTagAttribute('class', 'form markAsShipped-js');
                            $frm->developerTags['colClassPrefix'] = 'col-md-';
                            $frm->developerTags['fld_default_col'] = 12;

                            $manualFld = $frm->getField('manual_shipping');

                            $fld = $frm->getField('op_status_id');
                            if (null != $fld) {
                                $fld->developerTags['col'] = (null != $manualFld) ? 4 : 6;
                            }

                            $statusFld = $frm->getField('op_status_id');
                            $statusFld->setFieldTagAttribute('class', 'status-js fieldsVisibility-js');

                            $fld1 = $frm->getField('customer_notified');
                            $fld1->setFieldTagAttribute('class', 'notifyCustomer-js');
                            $fld1->developerTags['col'] = (null != $manualFld) ? 4 : 6;

                            
                            if (null != $manualFld) {
                                $manualFld->setFieldTagAttribute('class', 'manualShipping-js fieldsVisibility-js');
                                $manualFld->developerTags['col'] = 4;

                                $fld = $frm->getField('tracking_number');
                                $fld->developerTags['col'] = 4;

                                $fld = $frm->getField('opship_tracking_url');
                                $courierFld = $frm->getField('oshistory_courier');
                                if (null != $fld) {
                                    $fld->developerTags['col'] = 4;
                                    $fld->setWrapperAttribute('class', 'trackingUrlBlk--js');
                                    $fld->setFieldTagAttribute('class', 'trackingUrlFld--js');
                                    if (null != $courierFld) {
                                        $fld->htmlAfterField = '<a href="javascript:void(0)" onclick="courierFld()" class="link"><small>' . Labels::getLabel(
                                            'LBL_OR_SELECT_COURIER_?',
                                            $siteLangId
                                        ) . '</small></a>';
                                    }
                                }

                                if (null != $courierFld) {
                                    $courierFld->developerTags['col'] = 4;
                                    $courierFld->setWrapperAttribute('class', 'courierBlk--js d-none');
                                    $courierFld->setFieldTagAttribute('class', 'courierFld--js');
                                    $courierFld->htmlAfterField = '<a href="javascript:void(0)" onclick="trackingUrlFld()" class="link"><small>' . Labels::getLabel(
                                        'LBL_OR_TRACK_THROUGH_URL_?',
                                        $siteLangId
                                    ) . '</small></a>';
                                }
                            }

                            $fldBtn = $frm->getField('btn_submit');
                            $fldBtn->setFieldTagAttribute('class', 'btn btn-brand');
                            $fldBtn->developerTags['col'] = 6;
                            echo $frm->getFormHtml(); ?>
                        </div>
                    <?php } ?>
                    <span class="gap"></span>
                    <?php if (!empty($orderDetail['comments']) && !$print) { ?>
                        <div class="section--repeated no-print js-scrollable table-wrap">
                            <h5><?php echo Labels::getLabel('LBL_Posted_Comments', $siteLangId); ?></h5>
                            <table class="table  table--orders">
                                <thead>

                                    <tr class="">
                                        <th><?php echo Labels::getLabel('LBL_Date_Added', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_Customer_Notified', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_Status', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_Comments', $siteLangId); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($orderDetail['comments'] as $row) { ?>
                                        <tr>
                                            <td><?php echo FatDate::format($row['oshistory_date_added'], true); ?></td>
                                            <td><?php echo $yesNoArr[$row['oshistory_customer_notified']]; ?></td>
                                            <td>
                                                <?php
                                                echo ($row['oshistory_orderstatus_id'] > 0) ? $orderStatuses[$row['oshistory_orderstatus_id']] : CommonHelper::displayNotApplicable($siteLangId, '');
                                                if ($row['oshistory_orderstatus_id'] ==  OrderStatus::ORDER_SHIPPED) {
                                                    if (empty($row['oshistory_courier'])) {
                                                        $str = !empty($row['oshistory_tracking_number']) ? ': ' . Labels::getLabel('LBL_Tracking_Number', $siteLangId) . ' ' . $row['oshistory_tracking_number'] : '';
                                                        if (empty($orderDetail['opship_tracking_url']) && !empty($row['oshistory_tracking_number'])) {
                                                            $str .=  " VIA <em>" . CommonHelper::displayNotApplicable($siteLangId, $orderDetail["opshipping_label"]) . "</em>";
                                                        } elseif (!empty($orderDetail['opship_tracking_url']) && !empty($row['oshistory_tracking_number'])) {
                                                            $str .=  " <a class='btn btn-outline-secondary btn-sm' href='" . $orderDetail['opship_tracking_url'] . "' target='_blank'>" . Labels::getLabel("MSG_TRACK", $siteLangId) . "</a>";
                                                        }
                                                        echo $str;
                                                    } else {
                                                        echo ($row['oshistory_tracking_number']) ? ': ' . Labels::getLabel('LBL_Tracking_Number', $siteLangId) : '';
                                                        $trackingNumber = $row['oshistory_tracking_number'];
                                                        $carrier = $row['oshistory_courier']; ?>
                                                        <a href="javascript:void(0)" title="<?php echo Labels::getLabel('LBL_TRACK', $siteLangId); ?>" onClick="trackOrder('<?php echo trim($trackingNumber); ?>', '<?php echo trim($carrier); ?>', '<?php echo $orderDetail['op_invoice_number']; ?>')">
                                                            <?php echo $trackingNumber; ?>
                                                        </a>
                                                        <?php echo Labels::getLabel('LBL_VIA', $siteLangId); ?> <em><?php echo CommonHelper::displayNotApplicable($siteLangId, $orderDetail["opshipping_label"]); ?></em>
                                                <?php }
                                                } ?>
                                            </td>
                                            <td><?php echo !empty($row['oshistory_comments']) ? nl2br($row['oshistory_comments']) : Labels::getLabel('LBL_N/A', $siteLangId); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                    <span class="gap"></span>
                    <?php if (!empty($digitalDownloads)) { ?>
                        <div class="section--repeated js-scrollable table-wrap">
                            <h5><?php echo Labels::getLabel('LBL_Downloads', $siteLangId); ?></h5>
                            <table class="table table-justified table--orders">
                                <tbody>
                                    <tr class="">
                                        <th><?php echo Labels::getLabel('LBL_#', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_File', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_Language', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_Download_times', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_Downloaded_count', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_Expired_on', $siteLangId); ?></th>
                                        <?php if ($canEdit) { ?>
                                            <th></th>
                                        <?php } ?>
                                    </tr>
                                    <?php $sr_no = 1;
                                    foreach ($digitalDownloads as $key => $row) {
                                        $lang_name = Labels::getLabel('LBL_All', $siteLangId);
                                        if ($row['afile_lang_id'] > 0) {
                                            $lang_name = $languages[$row['afile_lang_id']];
                                        }

                                        $fileName = '<a href="' . UrlHelper::generateUrl('Seller', 'downloadDigitalFile', array($row['afile_id'], $row['afile_record_id'], AttachedFile::FILETYPE_ORDER_PRODUCT_DIGITAL_DOWNLOAD)) . '">' . $row['afile_name'] . '</a>';
                                        $downloads = '<li><a href="' . UrlHelper::generateUrl('Seller', 'downloadDigitalFile', array($row['afile_id'], $row['afile_record_id'], AttachedFile::FILETYPE_ORDER_PRODUCT_DIGITAL_DOWNLOAD)) . '"><i class="fa fa-download"></i></a></li>';

                                        $expiry = Labels::getLabel('LBL_N/A', $siteLangId);
                                        if ($row['expiry_date'] != '') {
                                            $expiry = FatDate::Format($row['expiry_date']);
                                        }

                                        $downloadableCount = Labels::getLabel('LBL_N/A', $siteLangId);
                                        if ($row['downloadable_count'] != -1) {
                                            $downloadableCount = $row['downloadable_count'];
                                        } ?>
                                        <tr>
                                            <td><?php echo $sr_no; ?></td>
                                            <td><?php echo $fileName; ?></td>
                                            <td><?php echo $lang_name; ?></td>
                                            <td><?php echo $downloadableCount; ?></td>
                                            <td><?php echo $row['afile_downloaded_times']; ?></td>
                                            <td><?php echo $expiry; ?></td>
                                            <td>
                                                <ul class="actions"><?php echo ($canEdit) ? $downloads : ''; ?></ul>
                                            </td>
                                        </tr>
                                    <?php $sr_no++;
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>

                    <span class="gap"></span>
                    <?php if (!empty($digitalDownloadLinks)) { ?>
                        <div class="section--repeated js-scrollable table-wrap">
                            <h5><?php echo Labels::getLabel('LBL_Downloads', $siteLangId); ?></h5>
                            <table class="table  table--orders">
                                <tbody>
                                    <tr class="">
                                        <th><?php echo Labels::getLabel('LBL_#', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_Link', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_Download_times', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_Downloaded_count', $siteLangId); ?></th>
                                        <th><?php echo Labels::getLabel('LBL_Expired_on', $siteLangId); ?></th>
                                    </tr>
                                    <?php $sr_no = 1;
                                    foreach ($digitalDownloadLinks as $key => $row) {
                                        /* $fileName = '<a href="'.UrlHelper::generateUrl('Seller','downloadDigitalFile',array($row['afile_id'],$row['afile_record_id'],AttachedFile::FILETYPE_ORDER_PRODUCT_DIGITAL_DOWNLOAD)).'">'.$row['afile_name'].'</a>'; */
                                        /* $downloads = '<li><a href="'.UrlHelper::generateUrl('Seller','downloadDigitalFile',array($row['afile_id'],$row['afile_record_id'],AttachedFile::FILETYPE_ORDER_PRODUCT_DIGITAL_DOWNLOAD)).'"><i class="fa fa-download"></i></a></li>'; */

                                        $expiry = Labels::getLabel('LBL_N/A', $siteLangId);
                                        if ($row['expiry_date'] != '') {
                                            $expiry = FatDate::Format($row['expiry_date']);
                                        }

                                        $downloadableCount = Labels::getLabel('LBL_N/A', $siteLangId);
                                        if ($row['downloadable_count'] != -1) {
                                            $downloadableCount = $row['downloadable_count'];
                                        } ?>
                                        <tr>
                                            <td><?php echo $sr_no; ?></td>
                                            <td><a target="_blank" href="<?php echo $row['opddl_downloadable_link']; ?>" title="<?php echo Labels::getLabel('LBL_Click_to_download', $siteLangId); ?>"><?php echo $row['opddl_downloadable_link']; ?></a></td>
                                            <td><?php echo $downloadableCount; ?></td>
                                            <td><?php echo $row['opddl_downloaded_times']; ?></td>
                                            <td><?php echo $expiry; ?></td>
                                        </tr>
                                    <?php $sr_no++;
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</main>
<?php if ($print) { ?>
    <script>
        $(".sidebar-is-expanded").addClass('sidebar-is-reduced').removeClass('sidebar-is-expanded');
        /*window.print();
        window.onafterprint = function() {
            location.href = history.back();
        }*/
    </script>
<?php } ?>

<script>
    $(document).ready(function() {
        setTimeout(function() {
            $('.printBtn-js').fadeIn();
        }, 500);
        $(document).on('click', '.printBtn-js', function() {
            $('.printFrame-js').show();
            setTimeout(function() {
                frames['frame'].print();
                $('.printFrame-js').hide();
            }, 500);
        });
    });
    var canShipByPlugin = <?php echo (true === $canShipByPlugin ? 1 : 0); ?>;
    var orderShippedStatus = <?php echo OrderStatus::ORDER_SHIPPED; ?>;
</script>