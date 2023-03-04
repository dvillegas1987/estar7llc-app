<?php defined('SYSTEM_INIT') or die('Invalid Usage . '); ?>
<table width="100%" cellspacing="0" cellpadding="10" border="0" style="font-size: 14px;background: #f2f2f2;">
    <tbody>
        <tr>
            <td>
                <table cellspacing="0" cellpadding="0" border="0" style="margin: 0 auto;border:2px solid #ddd;background-color: #fff;">
                    <tbody>
                        <tr>
                            <td>
                                <!--main Start-->
                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td style="font-size: 32px;font-weight: 700;color: #000;padding:25px 10px;text-align:center;"><?php echo Labels::getlabel('LBL_Tax_Invoice', $siteLangId); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <?php $count = 0;
                                foreach ($childOrderDetail as $childOrder) { ?>
                                    <?php if ($count != 0) { ?>
                                        <br pagebreak="true" />
                                    <?php } ?>
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-top: 1px solid #ddd;">
                                        <tbody>
                                            <tr>
                                                <td style="padding:15px;border-bottom: 1px solid #ddd;">
                                                    <h4 style="margin:0;font-size:18px;font-weight:bold;padding-bottom: 5px;"><?php echo Labels::getLabel('LBL_Sold_By', $siteLangId); ?>: <?php echo $childOrder['op_shop_name']; ?></h4>
                                                    <p style="margin:0;padding-bottom: 15px;"><?php echo Labels::getLabel('LBL_Shop_Address', $siteLangId); ?>: <?php echo $childOrder['shop_city'] . ', ' . $childOrder['shop_state_name'] . ', ' . $childOrder['shop_country_name'] . ' - ' . $childOrder['shop_postalcode']; ?></p>
                                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                                        <?php $shopCodes = $childOrder['shop_invoice_codes'];
                                                        $codesArr = explode("\n", $shopCodes); ?>
                                                        <tbody>
                                                            <?php $count = 1; ?>
                                                            <tr>
                                                                <?php foreach ($codesArr as $code) { ?>
                                                                    <td style="<?php echo ($count % 2 == 0) ? 'text-align: right;' : ''; ?> font-weight: 700;"><?php echo $code; ?></td>
                                                                <?php
                                                                    if ($count % 2 == 0) {
                                                                        echo '</tr><tr>';
                                                                    }
                                                                    $count++;
                                                                } ?>

                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <td style="border-bottom: 1px solid #ddd;">
                                                    <table width="100%" border="0" cellpadding="10" cellspacing="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="padding:15px;border-right: 1px solid #ddd;">
                                                                    <h4 style="margin:0;padding:0;font-size:18px;font-weight:bold;padding-bottom: 5px;"><?php echo Labels::getLabel('LBL_Bill_to', $siteLangId); ?></h4>
                                                                    <p style="margin:0;padding-bottom: 15px; display:block;">
                                                                        <?php $billingAddress = $orderDetail['billingAddress']['oua_name'] . '<br/>';
                                                                        if ($orderDetail['billingAddress']['oua_address1'] != '') {
                                                                            $billingAddress .= $orderDetail['billingAddress']['oua_address1'] . '<br/>';
                                                                        }

                                                                        if ($orderDetail['billingAddress']['oua_address2'] != '') {
                                                                            $billingAddress .= $orderDetail['billingAddress']['oua_address2'] . '<br/>';
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
                                                                            $billingAddress  .= '-' . $orderDetail['billingAddress']['oua_zip'];
                                                                        }

                                                                        if ($orderDetail['billingAddress']['oua_phone'] != '') {
                                                                            $billingAddress  .= '<br/>' . ValidateElement::formatDialCode($orderDetail['billingAddress']['oua_phone_dcode']) . $orderDetail['billingAddress']['oua_phone'];
                                                                        }
                                                                        ?>
                                                                        <?php echo $billingAddress; ?>
                                                                    </p>
                                                                </td>
                                                                <?php if (($childOrder['op_product_type'] != Product::PRODUCT_TYPE_DIGITAL) && !empty($orderDetail['shippingAddress'])) {  ?>
                                                                    <td style="padding:15px;">
                                                                        <h4 style="margin:0;font-size:18px;font-weight:bold;padding-bottom: 5px;"><?php echo Labels::getLabel('LBL_Ship_to', $siteLangId); ?></h4>
                                                                        <p style="margin:0;padding-bottom: 15px;">
                                                                            <?php $shippingAddress = $orderDetail['shippingAddress']['oua_name'] . '<br/>';
                                                                            if ($orderDetail['shippingAddress']['oua_address1'] != '') {
                                                                                $shippingAddress .= $orderDetail['shippingAddress']['oua_address1'] . '<br/>';
                                                                            }

                                                                            if ($orderDetail['shippingAddress']['oua_address2'] != '') {
                                                                                $shippingAddress .= $orderDetail['shippingAddress']['oua_address2'] . '<br/>';
                                                                            }

                                                                            if ($orderDetail['shippingAddress']['oua_city'] != '') {
                                                                                $shippingAddress .= $orderDetail['shippingAddress']['oua_city'] . ',';
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
                                                                                $shippingAddress .= '<br/>' . ValidateElement::formatDialCode($orderDetail['shippingAddress']['oua_phone_dcode']) . $orderDetail['shippingAddress']['oua_phone'];
                                                                            } ?>
                                                                            <?php echo $shippingAddress; ?>
                                                                        </p>
                                                                    </td>
                                                                <?php } ?>
                                                                <?php if (!empty($orderDetail['pickupAddress'])) { ?>
                                                                    <td style="padding:15px;">
                                                                        <h4 style="margin:0;font-size:18px;font-weight:bold;padding-bottom: 5px;"><?php echo Labels::getLabel('LBL_Pickup_Details', $siteLangId); ?></h4>
                                                                        <p style="margin:0;padding-bottom: 15px;">
                                                                            <?php $pickUpAddress = $orderDetail['pickupAddress']['oua_name'] . '<br/>';
                                                                            if ($orderDetail['pickupAddress']['oua_address1'] != '') {
                                                                                $pickUpAddress .= $orderDetail['pickupAddress']['oua_address1'] . '<br/>';
                                                                            }

                                                                            if ($orderDetail['pickupAddress']['oua_address2'] != '') {
                                                                                $pickUpAddress .= $orderDetail['pickupAddress']['oua_address2'] . '<br/>';
                                                                            }

                                                                            if ($orderDetail['pickupAddress']['oua_city'] != '') {
                                                                                $pickUpAddress .= $orderDetail['pickupAddress']['oua_city'] . ',';
                                                                            }

                                                                            if ($orderDetail['pickupAddress']['oua_zip'] != '') {
                                                                                $pickUpAddress .= $orderDetail['pickupAddress']['oua_state'];
                                                                            }

                                                                            if ($orderDetail['pickupAddress']['oua_zip'] != '') {
                                                                                $pickUpAddress .= '-' . $orderDetail['pickupAddress']['oua_zip'];
                                                                            }

                                                                            if ($orderDetail['pickupAddress']['oua_phone'] != '') {
                                                                                $pickUpAddress .= '<br/>' . ValidateElement::formatDialCode($orderDetail['pickupAddress']['oua_phone_dcode']) . $orderDetail['pickupAddress']['oua_phone'];
                                                                            } ?>
                                                                            <?php echo $pickUpAddress; ?>
                                                                        </p>
                                                                    </td>
                                                                <?php } ?>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <td style="border-bottom: 1px solid #ddd;">
                                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                                        <tbody>
                                                            <tr>
                                                                <td style="padding:15px;">
                                                                    <p><strong><?php echo Labels::getLabel('LBL_Order', $siteLangId); ?>:</strong> <?php echo $childOrder['op_order_id']; ?> </p>
                                                                    <p><strong><?php echo Labels::getLabel('LBL_Invoice_Number', $siteLangId); ?>:</strong> <?php echo $childOrder['op_invoice_number']; ?></p>
                                                                    <p><strong><?php echo Labels::getLabel('LBL_Payment_Method', $siteLangId); ?>:</strong>
                                                                        <?php
                                                                        $paymentMethodName = empty($childOrder['plugin_name']) ? $childOrder['plugin_identifier'] : $childOrder['plugin_name'];
                                                                        if (!empty($paymentMethodName) && $childOrder['order_pmethod_id'] > 0 && $childOrder['order_is_wallet_selected'] > 0) {
                                                                            $paymentMethodName  .= ' + ';
                                                                        }
                                                                        if ($childOrder['order_is_wallet_selected'] > 0) {
                                                                            $paymentMethodName .= Labels::getLabel("LBL_Wallet", $siteLangId);
                                                                        }
                                                                        echo $paymentMethodName;
                                                                        ?>
                                                                    </p>
                                                                </td>
                                                                <td style="padding:15px;">
                                                                    <p><strong><?php echo Labels::getLabel('LBL_Order_Date', $siteLangId); ?>:</strong> <?php echo FatDate::format($childOrder['order_date_added']); ?> </p>
                                                                    <?php /* if (!empty($childOrder['opshipping_date'])) { ?>
                                                                        <p><strong><?php echo Labels::getLabel('LBL_Invoice_Date', $siteLangId); ?>:</strong> <?php echo FatDate::format($childOrder['opshipping_date']); ?></p>
                                                                    <?php } */ ?>
                                                                    <?php if (!empty($childOrder['opship_tracking_number'])) { ?>
                                                                        <p><strong><?php echo Labels::getLabel('LBL_Tracking_ID', $siteLangId); ?>:</strong> <?php echo $childOrder['opship_tracking_number']; ?> </p>
                                                                    <?php } ?>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <td style="border-bottom: 1px solid #ddd;">
                                                    <table width="100%" border="0" cellpadding="10px" cellspacing="0">
                                                        <tbody>
                                                            <tr>
                                                                <th width="30%" style="padding:10px;text-align: left; border-bottom:1px solid #ddd;font-size:12px;">
                                                                    <?php echo Labels::getLabel('LBL_Item', $siteLangId); ?>
                                                                </th>
                                                                <th width="15%" style="padding:10px;text-align: center; border-bottom:1px solid #ddd;font-size:12px;">
                                                                    <?php if (FatApp::getConfig('CONF_TAX_CATEGORIES_CODE', FatUtility::VAR_INT, 1)) {
                                                                        echo $childOrder['op_tax_code'] . ' (' . Labels::getLabel('LBL_Tax', $siteLangId) . ')'; ?>
                                                                    <?php } else {
                                                                        echo Labels::getLabel('LBL_Tax', $siteLangId);
                                                                    } ?>
                                                                </th>
                                                                <th width="10%" style="padding:10px;text-align: center; border-bottom:1px solid #ddd;font-size:12px;"><?php echo Labels::getLabel('LBL_Qty', $siteLangId); ?></th>
                                                                <th width="15%" style="padding:10px;text-align: center; border-bottom:1px solid #ddd;font-size:12px;"><?php echo Labels::getLabel('LBL_Price', $siteLangId); ?></th>
                                                                <th width="15%" style="padding:10px;text-align: center; border-bottom:1px solid #ddd;font-size:12px;"><?php echo Labels::getLabel('LBL_Savings', $siteLangId); ?></th>
                                                                <th width="15%" style="padding:10px;text-align: center; border-bottom:1px solid #ddd;font-size:12px;"><?php echo Labels::getLabel('LBL_Total_Amount', $siteLangId); ?></th>
                                                            </tr>
                                                            <tr>
                                                                <td style="padding:10px;text-align: left;font-size:12px;">
                                                                    <?php
                                                                    echo ($childOrder['op_selprod_title'] != '') ? $childOrder['op_selprod_title'] : $childOrder['op_product_name'];
                                                                    echo '<br>';
                                                                    echo Labels::getLabel('Lbl_Brand', $siteLangId) . ' : ';
                                                                    echo CommonHelper::displayNotApplicable($siteLangId, $childOrder['op_brand_name']);
                                                                    echo '<br>';
                                                                    if ($childOrder['op_selprod_options'] != '') {
                                                                        echo $childOrder['op_selprod_options'] . '<br>';
                                                                    }
                                                                    echo Labels::getLabel('LBL_Sold_By', $siteLangId) . ': ' . $childOrder['op_shop_name'];
                                                                    if ($childOrder['op_shipping_duration_name'] != '') {
                                                                        echo '<br>';
                                                                        echo Labels::getLabel('LBL_Shipping_Method', $siteLangId) . ' : ';
                                                                        echo $childOrder['op_shipping_durations'] . '-' . $childOrder['op_shipping_duration_name'];
                                                                    }
                                                                    ?>
                                                                </td>
                                                                <td style="padding:10px;text-align: center;font-size:12px;">
                                                                    <?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'TAX'), true, false, true, false, true); ?>
                                                                </td>
                                                                <td style="padding:10px;text-align: center;font-size:12px;"><?php echo $childOrder['op_qty']; ?></td>
                                                                <td style="padding:10px;text-align: center;font-size:12px;"><?php echo CommonHelper::displayMoneyFormat($childOrder['op_unit_price'], true, false, true, false, true); ?></td>
                                                                <?php $couponDiscount = CommonHelper::orderProductAmount($childOrder, 'DISCOUNT');
                                                                $volumeDiscount = CommonHelper::orderProductAmount($childOrder, 'VOLUME_DISCOUNT');
                                                                $totalSavings = $couponDiscount + $volumeDiscount; ?>
                                                                <td style="padding:10px;text-align: center;font-size:12px;">
                                                                    <?php echo CommonHelper::displayMoneyFormat($totalSavings); ?>
                                                                </td>
                                                                <td style="padding:10px;text-align: center;font-size:12px;"><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'CART_TOTAL'), true, false, true, false, true); ?></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="padding:10px;font-size:18px;text-align: left;font-weight:700;background-color: #f0f0f0;" colspan="2"><?php echo Labels::getLabel('Lbl_Summary', $siteLangId) ?> </td>
                                                                <td style="padding:10px;text-align: center;background-color: #f0f0f0;font-size: 16px;"><strong><?php echo $childOrder['op_qty']; ?></strong></td>
                                                                <td style="padding:10px;text-align: center;background-color: #f0f0f0;font-size: 16px;"><strong><?php echo CommonHelper::displayMoneyFormat($childOrder['op_unit_price'], true, false, true, false, true); ?></strong></td>
                                                                <td style="padding:10px;text-align: center;background-color: #f0f0f0;font-size: 16px;"><strong><?php echo CommonHelper::displayMoneyFormat($totalSavings); ?></strong></td>
                                                                <td style="padding:10px;text-align: center;background-color: #f0f0f0;font-size: 16px;"><strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'CART_TOTAL'), true, false, true, false, true); ?></strong></td>
                                                            </tr>
                                                            <tr>
                                                                <td style="padding:10px;font-size:20px;text-align: left;font-weight:700; vertical-align: top;" colspan="3" rowspan="6">
                                                                    <?php
                                                                    if ($totalSavings != 0) {
                                                                        $str = Labels::getLabel("LBL_You_have_saved_{totalsaving}_on_this_order", $siteLangId);
                                                                        $str = str_replace("{totalsaving}", -$totalSavings, $str);
                                                                        echo $str;
                                                                    } ?>
                                                                </td>
                                                                <td style="padding:10px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2"><?php echo Labels::getLabel('Lbl_Cart_Total', $siteLangId) ?></td>
                                                                <td style="padding:10px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1"><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'cart_total'), true, false, true, false, true); ?></td>
                                                            </tr>
                                                            <?php if ($childOrder['op_product_type'] != Product::PRODUCT_TYPE_DIGITAL) {  ?>
                                                                <tr>
                                                                    <td style="padding:10px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2"><?php echo Labels::getLabel('LBL_Delivery_Charges', $siteLangId) ?></td>
                                                                    <td style="padding:10px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1"><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'shipping'), true, false, true, false, true); ?></td>
                                                                </tr>
                                                            <?php } ?>
                                                            <?php /* $rewardPointDiscount = CommonHelper::orderProductAmount($childOrder, 'REWARDPOINT');
                                                                if ($rewardPointDiscount != 0) { ?>
                                                                <tr>                                                                              
                                                                    <td style="padding:10px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2"><?php echo Labels::getLabel('LBL_Reward_Point_Discount', $siteLangId) ?></td>                    
                                                                    <td style="padding:10px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1"><?php echo CommonHelper::displayMoneyFormat($rewardPointDiscount, true, false, true, false, true); ?></td>                                           
                                                                </tr>
                                                                <?php } */ ?>
                                                            <?php if (CommonHelper::orderProductAmount($childOrder, 'TAX') > 0) { ?>
                                                                <tr>
                                                                    <td style="padding:10px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2"><?php echo Labels::getLabel('LBL_Tax_Charges', $siteLangId) ?></td>
                                                                    <td style="padding:10px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1"><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder, 'TAX'), true, false, true, false, true); ?></td>
                                                                </tr>
                                                            <?php } ?>
                                                            <?php
                                                            if ($totalSavings != 0) { ?>
                                                                <tr>
                                                                    <td style="padding:10px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2"><?php echo Labels::getLabel('LBL_Total_Savings', $siteLangId) ?></td>
                                                                    <td style="padding:10px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1"><?php echo CommonHelper::displayMoneyFormat($totalSavings, true, false, true, false, true); ?></td>
                                                                </tr>
                                                            <?php } ?>
                                                            <?php
                                                            $rewardPointDiscount = CommonHelper::orderProductAmount($childOrder, 'REWARDPOINT');
                                                            if ($rewardPointDiscount != 0) { ?>
                                                                <tr>
                                                                    <td style="padding:10px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2"><?php echo Labels::getLabel('LBL_Reward_Points', $siteLangId) ?></td>
                                                                    <td style="padding:10px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1"><?php echo CommonHelper::displayMoneyFormat($rewardPointDiscount, true, false, true, false, true); ?></td>
                                                                </tr>
                                                            <?php } ?>
                                                            <?php if (array_key_exists('order_rounding_off', $orderDetail) && 0 != $orderDetail['order_rounding_off']) { ?>
                                                                <tr>
                                                                    <td style="padding:10px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2">
                                                                        <?php echo (0 < $orderDetail['order_rounding_off']) ? Labels::getLabel('LBL_Rounding_Up', $siteLangId) : Labels::getLabel('LBL_Rounding_Down', $siteLangId); ?>
                                                                    </td>
                                                                    <td style="padding:10px;text-align: center;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1">
                                                                        <?php echo CommonHelper::displayMoneyFormat($orderDetail['order_rounding_off'], true, false, true, false, true); ?>
                                                                    </td>
                                                                </tr>
                                                            <?php } ?>
                                                            <tr>
                                                                <td style="padding:10px;text-align: center;font-weight:700;font-size: 18px;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="2"><strong><?php echo Labels::getLabel('LBL_Grand_Total', $siteLangId) ?></strong> </td>
                                                                <td style="padding:10px;text-align: center;font-weight:700;font-size: 18px;border-top:1px solid #ddd;border-left:1px solid #ddd;" colspan="1"><strong><?php echo CommonHelper::displayMoneyFormat(CommonHelper::orderProductAmount($childOrder), true, false, true, false, true); ?></strong></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <td style="padding:15px;vertical-align: top; border:none;">
                                                    <h2 style="font-size: 20px;text-align: center;"><?php echo $childOrder['op_shop_name']; ?></h2>
                                                    <span style="padding-top: 150px;display: block;text-align: center;"><?php echo Labels::getLabel('LBL_Authorized_Signatory', $siteLangId); ?> </span>
                                                </td>
                                                <td style="text-align: center;  border:none;">
                                                    <table width="100%" border="0" cellpadding="10px" cellspacing="0" style="">
                                                        <tbody>
                                                            <tr>
                                                                <th style="padding:15px;background-color: #f0f0f0;" colspan="2"><?php echo Labels::getLabel('LBL_Tax_break-up', $siteLangId); ?></th>
                                                            </tr>
                                                           
                                                            <?php if (!empty($childOrder['taxOptions'])) {
                                                                    foreach ($childOrder['taxOptions'] as $key => $val) { ?>
                                                                     <tr> 
                                                                        <td style="padding:10px;border:1px solid #ddd;">
                                                                            <?php echo CommonHelper::displayTaxPercantage($val, true) ?>
                                                                        </td>
                                                                        <td style="padding:10px;border:1px solid #ddd;">
                                                                            <?php echo CommonHelper::displayMoneyFormat($val['value'], true, false, true, false, true) ?>
                                                                        </td>
                                                                    </tr> 
                                                                <?php
                                                                    }
                                                                } ?>                                                         
                                                         
                                                            <tr>
                                                                <td style="padding:10px;text-align: left;border:1px solid #ddd;border-bottom:none;border-right:none;font-size: 12px;background-color: #f0f0f0;" colspan="4">*<?php echo Labels::getLabel('LBL_Appropriated_product-wise_and_Rate_applicable_thereunder', $siteLangId); ?></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                <?php $count++;
                                } ?>
                                <table width="100%" border="0" cellpadding="10px" cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td style="padding:20px 15px;border-top:1px solid #ddd">
                                                <p><strong><?php echo Labels::getLabel('LBL_Regd._office', $siteLangId); ?>:</strong><?php echo nl2br(FatApp::getConfig('CONF_ADDRESS_' . $siteLangId, FatUtility::VAR_STRING, '')); ?></p>
                                                <?php $site_conatct = FatApp::getConfig('CONF_SITE_PHONE', FatUtility::VAR_INT, '');
                                                $email_id = FatApp::getConfig('CONF_CONTACT_EMAIL', FatUtility::VAR_STRING, '');
                                                if ($site_conatct || $email_id) { ?>
                                                    <p><strong><?php echo Labels::getLabel('LBL_Contact', $siteLangId) ?>:</strong>
                                                        <?php if ($site_conatct) {
                                                            echo $site_conatct;
                                                        } ?>
                                                        <?php if ($email_id) {
                                                            echo '|| ' . $email_id;
                                                        } ?>
                                                    </p>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <!--main End-->
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>